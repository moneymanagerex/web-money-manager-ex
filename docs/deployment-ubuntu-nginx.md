# Ubuntu 24.04 deployment with Nginx and PHP-FPM 8.3

This guide deploys the WebApp on Ubuntu 24.04 LTS using Nginx and PHP 8.3-FPM from Ubuntu packages.

The WebApp keeps its configuration and SQLite database in the web-root directory and writes uploaded files to `attachments/`. PHP-FPM therefore needs write access to parts of the web root. This is a single shared instance, not a multi-user or read-only demo: visitors using the same login share and can change the same data. Use synthetic data on an isolated, disposable host, and arrange a reset from a known-clean backup.

## 1. Install Nginx and PHP-FPM

```sh
sudo apt update
sudo apt install -y nginx php8.3-cli php8.3-fpm php8.3-sqlite3 git
sudo systemctl enable --now nginx php8.3-fpm
```

Check PHP and its SQLite driver, and confirm the FPM socket is present:

```sh
php8.3 -v
php8.3 -m | grep -iE '^(PDO|pdo_sqlite|sqlite3)$'
sudo systemctl status php8.3-fpm --no-pager
test -S /run/php/php8.3-fpm.sock
```

The module list should include `PDO` and `pdo_sqlite`. Ubuntu's default PHP-FPM pool runs as `www-data`; verify `user` and `group` in `/etc/php/8.3/fpm/pool.d/www.conf` if the pool has been customized.

## 2. Install the WebApp files

This fetches the repository's current default branch. For repeatable deployments, select and review a specific release/tag.

```sh
git clone --depth 1 https://github.com/moneymanagerex/web-money-manager-ex.git /tmp/web-money-manager-ex
sudo install -d -o root -g www-data -m 2770 /var/www/mmex
sudo cp -a /tmp/web-money-manager-ex/WebApp/. /var/www/mmex/
sudo chown -R root:www-data /var/www/mmex
sudo find /var/www/mmex -type d -exec chmod 0750 {} +
sudo chmod 2770 /var/www/mmex /var/www/mmex/attachments
sudo find /var/www/mmex -type f -exec chmod 0640 {} +
```

The application directory must be group-writable so PHP can create the configuration and SQLite database (including SQLite journal files); `attachments/` must be writable for uploads. Do not use `chmod -R 777`. On a dedicated server, consider setting a restrictive umask for PHP-FPM so newly created data files are not readable by other local users.

## 3. Configure Nginx

Use your real DNS name in place of `mmex.example.com`. First define an access-log format that omits query strings: the desktop sync GUID is sent in a query parameter and the default Nginx log format includes it.

```sh
sudo tee /etc/nginx/conf.d/mmex-log-format.conf >/dev/null <<'NGINX'
log_format mmex '$remote_addr [$time_local] "$request_method $uri $server_protocol" $status $body_bytes_sent';
NGINX
```

Create the site configuration:

```sh
sudo tee /etc/nginx/sites-available/mmex >/dev/null <<'NGINX'
server {
    listen 80;
    listen [::]:80;
    server_name mmex.example.com;

    root /var/www/mmex;
    index index.php;
    server_tokens off;
    client_max_body_size 10m;

    access_log /var/log/nginx/mmex-access.log mmex;
    error_log /var/log/nginx/mmex-error.log;

    location = /attachments {
        return 404;
    }

    location ^~ /attachments/ {
        return 404;
    }

    location ^~ /.well-known/acme-challenge/ {
        root /var/www/mmex;
        default_type text/plain;
        try_files $uri =404;
    }

    location ~* ^/(configuration_(user|user_default|system)\.php|MMEX_New_Transaction\.db)$ {
        return 404;
    }

    location ~ /\. {
        return 404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location / {
        try_files $uri $uri/ =404;
    }
}
NGINX

sudo ln -s /etc/nginx/sites-available/mmex /etc/nginx/sites-enabled/mmex
sudo nginx -t
sudo systemctl reload nginx
```

Nginx does not read `.htaccess`; the rules above explicitly deny web access to the settings files, database, hidden files, and attachment directory. The application can still read attachments from disk and serve them through its PHP endpoint. Do not remove these deny rules.

If the symlink already exists, do not create it again. If Nginx reports a duplicate server name or selects the default site, inspect `/etc/nginx/sites-enabled/` and disable only the conflicting site after confirming it is not serving other content.

### Limit attachment upload size

The Nginx limit is 10 MB per request. Set PHP-FPM's upload limit lower than that so the uploaded file and multipart form data fit within the request limit:

```sh
sudo tee /etc/php/8.3/fpm/conf.d/99-mmex.ini >/dev/null <<'INI'
upload_max_filesize = 8M
post_max_size = 10M
INI
sudo systemctl restart php8.3-fpm
```

If the demo does not need attachments, disable or block the upload feature instead. These limits constrain request size; they do not add application-level validation of file type or content.

## 4. Enable HTTPS

Point the domain's DNS A/AAAA record at the server first. Allow inbound HTTP/HTTPS in your provider firewall and, if enabled, UFW. For UFW, ensure SSH remains allowed before changing firewall rules, then allow Nginx:

```sh
sudo ufw allow 'Nginx Full'
```

Install Certbot and let it configure TLS for Nginx:

```sh
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d mmex.example.com
sudo certbot renew --dry-run
```

Follow the prompts and enable the HTTP-to-HTTPS redirect. If this demo is restricted to a private network, still use HTTPS with a certificate trusted by its clients.

The custom access-log format above omits query strings, but keep access and error logs restricted to administrators. Never publish the desktop sync GUID or reuse real credentials.

## 5. Complete first-run setup

Open `https://mmex.example.com/`. The first visit creates the SQLite database and directs you to Settings.

- Keep authentication enabled.
- Set a unique username and strong password; do not use the `admin`/`admin` values shown in the template configuration.
- Keep the generated desktop GUID secret. Do not use the fixed sample GUID from the template.
- Choose the default account and other preferences, then save.

The Guide page shows the URL and GUID for desktop synchronization. If desktop sync is not part of the demo, block `/services.php` at Nginx after setup; otherwise keep the GUID private and remember that API access is separate from the browser login.

After saving settings, restrict the on-disk permissions of the newly created data files:

```sh
sudo chown www-data:www-data /var/www/mmex/configuration_user.php /var/www/mmex/MMEX_New_Transaction.db
sudo chmod 0640 /var/www/mmex/configuration_user.php /var/www/mmex/MMEX_New_Transaction.db
```

The database remains writable by PHP through its owner permissions. If you save settings again, the app recreates `configuration_user.php`; reapply its ownership and mode.

## 6. Verify the deployment

```sh
sudo nginx -t
sudo systemctl status nginx php8.3-fpm --no-pager
curl -I https://mmex.example.com/
curl -I https://mmex.example.com/configuration_user.php
curl -I https://mmex.example.com/MMEX_New_Transaction.db
curl -I https://mmex.example.com/attachments/
```

The home page should respond over HTTPS. Direct requests for the configuration, database, and attachments should return `404`. Check `/var/log/nginx/mmex-error.log` and the PHP-FPM journal if the app cannot write its database or upload directory:

```sh
sudo journalctl -u php8.3-fpm --since "15 minutes ago"
```

Test a transaction and, if enabled, an attachment using synthetic data. Keep the public demo isolated from any real Money Manager EX database.

## Backups and reset

Back up `configuration_user.php`, `MMEX_New_Transaction.db`, and all of `attachments/`. These contain application credentials/settings, transactions/reference data, and uploaded files. Treat the archive as sensitive and store it off-host.

For a consistent archive, stop Nginx and PHP-FPM during a short maintenance window:

```sh
sudo install -d -m 0700 /var/backups/mmex
sudo systemctl stop nginx php8.3-fpm
sudo tar -czf "/var/backups/mmex/backup-$(date +%F-%H%M%S).tar.gz" \
  -C /var/www/mmex configuration_user.php MMEX_New_Transaction.db attachments
sudo systemctl start php8.3-fpm nginx
```

If the archive command fails, start both services before investigating. Test restoring a known-clean snapshot before using it as an automated demo reset. Do not copy a live SQLite database while it is being written.

## Upgrading

1. Stop Nginx and PHP-FPM, then back up the three data items above.
2. Replace the application code from a reviewed release, preserving `configuration_user.php`, `MMEX_New_Transaction.db`, and `attachments/`.
3. Keep the SQLite database owned by `www-data`; do not leave it root-owned or read-only to PHP.
4. Start PHP-FPM and Nginx, then check logs and verify login, transactions, uploads, and (if enabled) desktop synchronization.
5. Keep the backup until the updated demo is verified.

The first request after an update attempts to upgrade the database. Never delete the database or attachments during an application upgrade.
