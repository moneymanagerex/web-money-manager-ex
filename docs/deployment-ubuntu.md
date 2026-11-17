# Ubuntu deployment guide

This guide describes a single-host deployment on Ubuntu 24.04 LTS with Apache 2.4 and PHP 8.3. Package names and PHP module names may differ on other Ubuntu releases. Use a PHP version that still receives security updates from its upstream project and your Ubuntu release.

The WebApp stores its configuration and SQLite database in its web-root directory and writes uploaded files to `attachments/`. This means the PHP process needs write access to parts of the web root. The Apache rules below block direct HTTP access to the data files, but write access still makes a compromised application more consequential. Use a dedicated host or isolated VM/container, do not host unrelated applications under the same writable directory, and keep the OS and PHP patched.

## 1. Install Apache, PHP, and SQLite support

```sh
sudo apt update
sudo apt install -y apache2 libapache2-mod-php8.3 php8.3-sqlite3 git
sudo a2enmod php8.3
sudo systemctl enable --now apache2
sudo systemctl restart apache2
```

Confirm that the CLI PHP has PDO SQLite. The Apache PHP module is installed by `libapache2-mod-php8.3`:

```sh
php -v
php -m | grep -iE '^(PDO|pdo_sqlite|sqlite3)$'
sudo apachectl -M | grep -i php
```

The module list should include `PDO` and `pdo_sqlite`; the Apache module check should show PHP. If PHP or its SQLite driver is missing, do not continue to first-run setup.

## 2. Install the WebApp files

The following fetches the repository's current default branch. For repeatable production upgrades, select and review a specific release/tag instead of following the branch automatically.

```sh
git clone --depth 1 https://github.com/moneymanagerex/web-money-manager-ex.git /tmp/web-money-manager-ex
sudo install -d -o root -g www-data -m 2770 /var/www/mmex
sudo cp -a /tmp/web-money-manager-ex/WebApp/. /var/www/mmex/
sudo chown -R root:www-data /var/www/mmex
sudo find /var/www/mmex -type d -exec chmod 0750 {} +
sudo chmod 2770 /var/www/mmex /var/www/mmex/attachments
sudo find /var/www/mmex -type f -exec chmod 0640 {} +
```

The setgid, group-writable permissions on the application directory let Apache/PHP create the configuration and SQLite database. The `attachments/` directory must also be writable for uploads. Do not use `chmod -R 777`. On a dedicated server, consider setting a restrictive umask for the Apache service so newly created data files are not readable by other local users.

## 3. Configure an Apache virtual host

Use your real DNS name in place of `mmex.example.com`:

```sh
sudo tee /etc/apache2/sites-available/mmex.conf >/dev/null <<'APACHE'
<VirtualHost *:80>
    ServerName mmex.example.com
    DocumentRoot /var/www/mmex
    DirectoryIndex index.php

    <Directory /var/www/mmex>
        Options -Indexes
        AllowOverride None
        Require all granted

        <FilesMatch "^(configuration_(user|user_default|system)\.php|MMEX_New_Transaction\.db)$">
            Require all denied
        </FilesMatch>
        <FilesMatch "^\.">
            Require all denied
        </FilesMatch>
    </Directory>

    <Directory /var/www/mmex/attachments>
        Options -Indexes
        Require all denied
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/mmex-error.log
    CustomLog ${APACHE_LOG_DIR}/mmex-access.log combined
</VirtualHost>
APACHE
sudo a2ensite mmex.conf
sudo apachectl configtest
sudo systemctl reload apache2
```

The explicit Apache rules are used instead of relying on the bundled `.htaccess` files, because this virtual host sets `AllowOverride None`. The database, user/system configuration files, hidden files, and attachment directory should not be downloadable directly. The application reads and serves attachments through its PHP endpoints.

For an Internet-facing installation, set the DNS A/AAAA record to this server, allow inbound HTTP/HTTPS in your cloud firewall and host firewall, and obtain a TLS certificate before entering credentials. For a public DNS name, Certbot can configure Apache:

```sh
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d mmex.example.com
```

Follow Certbot's prompts, choose the HTTPS redirect, and verify renewal with `sudo certbot renew --dry-run`. If the service is private, use a VPN or another controlled network and still configure HTTPS with a certificate trusted by its clients.

Do not expose the site over plain HTTP. The desktop synchronization GUID is accepted as an API token and is sent as a query parameter. HTTPS protects it in transit, but it can still appear in Apache access logs; restrict access to those logs as well as network access, and never publish the GUID.

## 4. Complete first-run setup

After HTTPS is working, open `https://mmex.example.com/`. The initial visit creates the SQLite database and directs you to Settings.

- Leave authentication enabled.
- Set a unique username and a strong password; do not rely on the template values in `configuration_user_default.php`.
- Keep the generated desktop GUID and store it as a secret.
- Choose the default account and other preferences, then save.

The WebApp creates `configuration_user.php` when you save the initial settings. The Guide page displays the WebApp URL and desktop GUID. In the Money Manager EX desktop app, enter those values under **Options → Network → WebApp Settings**, then choose **Tools → Refresh WebApp** to synchronize the initial account, payee, and category lists.

After saving the first-run settings, restrict the on-disk permissions of the newly created data files:

```sh
sudo chown www-data:www-data /var/www/mmex/configuration_user.php /var/www/mmex/MMEX_New_Transaction.db
sudo chmod 0640 /var/www/mmex/configuration_user.php /var/www/mmex/MMEX_New_Transaction.db
```

The database remains writable by PHP through its owner permissions. If you later save settings again, the app recreates `configuration_user.php`; reapply its ownership and mode.

## 5. Check the installation

- Open the WebApp over HTTPS and confirm that login works.
- Confirm that `configuration_user.php` and `MMEX_New_Transaction.db` cannot be fetched directly; Apache should return `403 Forbidden`.
- Confirm that a direct request to a file in `/attachments/` is denied.
- Create a test transaction and verify that it appears in the WebApp and synchronizes with the desktop app.
- Check `/var/log/apache2/mmex-error.log` if setup or database writes fail.

If SQLite reports a read-only database or cannot create a journal file, check that `/var/www/mmex` is group-writable by `www-data`. If attachment upload fails, check `/var/www/mmex/attachments` the same way. Do not make the entire filesystem or unrelated application directories writable.

## Backups

The minimum data to preserve is `configuration_user.php`, `MMEX_New_Transaction.db`, and the entire `attachments/` directory. These contain credentials/settings, pending transactions and reference data, and uploaded files, respectively. Protect backups as financial data and copy them off the server.

For a simple consistent archive, schedule a short maintenance window, stop Apache while creating the archive, and start it again afterward:

```sh
sudo install -d -m 0700 /var/backups/mmex
sudo systemctl stop apache2
sudo tar -czf "/var/backups/mmex/backup-$(date +%F-%H%M%S).tar.gz" \
  -C /var/www/mmex configuration_user.php MMEX_New_Transaction.db attachments
sudo systemctl start apache2
```

If the archive command fails, start Apache again before investigating. Restrict access to `/var/backups/mmex` and copy backups to separate storage. Periodically test restoring a backup to a separate instance.

## Upgrading

1. Put the WebApp in maintenance mode or stop Apache, then back up the three data items above.
2. Download and review the new release.
3. Replace the application code from the new `WebApp/` tree, but preserve `configuration_user.php`, `MMEX_New_Transaction.db`, and `attachments/`.
4. Restore the code and data permissions. Keep the database owned by `www-data`; do not run a recursive `chown` that leaves the SQLite database owned by root.

   ```sh
   sudo find /var/www/mmex -type d -exec chown root:www-data {} +
   sudo find /var/www/mmex -type f ! -name configuration_user.php ! -name MMEX_New_Transaction.db -exec chown root:www-data {} +
   sudo find /var/www/mmex -type d -exec chmod 0750 {} +
   sudo chmod 2770 /var/www/mmex /var/www/mmex/attachments
   sudo find /var/www/mmex -type f ! -name configuration_user.php ! -name MMEX_New_Transaction.db -exec chmod 0640 {} +
   sudo chown www-data:www-data /var/www/mmex/configuration_user.php /var/www/mmex/MMEX_New_Transaction.db
   sudo chmod 0640 /var/www/mmex/configuration_user.php /var/www/mmex/MMEX_New_Transaction.db
   ```

5. Start/reload Apache and open the WebApp. The application attempts to upgrade the database on first access after an update.
6. Confirm login, transactions, attachments, and desktop synchronization. Keep the backup until verification is complete.

Never delete the database or attachments during an application upgrade. Do not replace `configuration_user.php` with the template file.
