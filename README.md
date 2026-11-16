# Money Manager EX WebApp

The WebApp lets you record transaction reminders from a browser on any device and synchronize them with the Money Manager EX desktop application. The desktop app can download transactions and keep the WebApp's account, payee, and category lists in sync.

The application stores its data in a local SQLite database. It does not require a separate database server.

## Requirements

- A web server with PHP and the `PDO_SQLite` extension enabled.
- The PHP/web-server process must be able to create and update `configuration_user.php` and `MMEX_New_Transaction.db` in the application directory, and write files in `attachments/`.
- A modern HTML5-compatible browser. The interface is optimized for portrait mobile screens.

Use a PHP release that is still supported by your operating system. The repository's Dockerfile uses PHP 8.3; keep the image updated with current PHP 8.3 security patches.

## Installation

For a detailed, security-conscious installation on Ubuntu, see [Ubuntu deployment](docs/deployment-ubuntu.md). It covers Apache, PHP/SQLite, file permissions, HTTPS, first-run setup, backups, and upgrades.

For another hosting environment, deploy the contents of `WebApp/` as the web root, ensure the requirements above are met, and protect the configuration file, SQLite database, and attachments from direct web access. Configure HTTPS before entering credentials or synchronizing financial data.

## Docker

The included Dockerfile can be used for a local preview:

```sh
docker build -t webmmx:local .
docker run --rm -p 8080:80 webmmx:local
```

Open `http://localhost:8080`. This is a disposable preview only: the current image has no persistent data-volume setup, so its configuration, database, and attachments are stored in the container's writable layer and are lost when the container is removed. Do not use this Dockerfile as-is for a public or persistent installation.

## First-run and desktop setup

1. Open the WebApp URL in a browser. On a new installation, the app sends you to Settings.
2. Keep authentication enabled and set a unique username and strong password. Keep the generated desktop GUID; it is the token used by desktop synchronization.
3. Save the settings and use the URL and GUID shown by the WebApp's Guide page in the desktop app under **Options → Network → WebApp Settings**.
4. In the desktop app, choose **Tools → Refresh WebApp** to populate the WebApp with the desktop account, payee, and category data.

Treat the GUID as a secret. Use HTTPS for the WebApp URL and do not publish the GUID.

## Upgrading and backups

Back up the installation before upgrading. Preserve `configuration_user.php`, `MMEX_New_Transaction.db`, and the contents of `attachments/`; replace the application files with the new release without deleting those data files. The app attempts to upgrade the database when it is opened after an update.

Back up all three data items regularly and keep the backup somewhere separate from the web server. The Ubuntu guide includes an example backup procedure.

## Project version

The application version is defined in `WebApp/configuration_system.php`. The repository's current version is 1.2.3.
