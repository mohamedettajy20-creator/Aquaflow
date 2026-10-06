# AquaFlow — Installation Guide

## Requirements
- PHP 8.0 or higher, with the `pdo_mysql` extension enabled
- MySQL 5.7+ or MariaDB 10.3+
- A web server (Apache/Nginx) OR just PHP's built-in server for local development/demo

No Composer, no Node build step — the project runs as-is.

## 1. Get the files onto your machine
Unzip the project. You should have an `aquaflow/` folder containing `app/`, `config/`,
`database/`, `public/`, etc.

## 2. Create the database
```bash
mysql -u root -p -e "CREATE DATABASE aquaflow CHARACTER SET utf8mb4;"
mysql -u root -p aquaflow < database/aquaflow.sql
```
This creates all 11 tables (with foreign keys and constraints) and loads realistic sample data:
4 customers, 2 agents, 4 meters, 6 readings, 5 invoices, 2 payments, and system settings
(including the tiered pricing table).

> **Tip:** in production, create a dedicated MySQL user instead of using `root`:
> ```sql
> CREATE USER 'aquaflow'@'localhost' IDENTIFIED BY 'choose_a_strong_password';
> GRANT ALL PRIVILEGES ON aquaflow.* TO 'aquaflow'@'localhost';
> FLUSH PRIVILEGES;
> ```

## 3. Configure the environment
```bash
cp .env.example .env
```
Edit `.env` and set your database credentials:
```
APP_URL=http://localhost/aquaflow/public
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=aquaflow
DB_USER=aquaflow
DB_PASS=choose_a_strong_password
```
`APP_URL` must match wherever `public/` is actually served from — the router strips this
prefix when matching routes, so getting it right matters if you're not serving from the
webserver root.

## 4. Point your web server at `public/`
The **document root must be the `public/` folder**, not the project root — this keeps
`app/`, `config/`, `database/`, and `storage/` outside the web-accessible path for security.

### Option A — quick local demo (PHP built-in server)
```bash
cd aquaflow
php -S localhost:8000 -t public
```
Then set `APP_URL=http://localhost:8000` in `.env` and visit http://localhost:8000.

### Option B — Apache (XAMPP / WAMP / MAMP / Laragon)
Point a VirtualHost's `DocumentRoot` at `aquaflow/public`, e.g.:
```apache
<VirtualHost *:80>
    ServerName aquaflow.local
    DocumentRoot "/path/to/aquaflow/public"
    <Directory "/path/to/aquaflow/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
If you're using XAMPP without a VirtualHost, you can instead place the whole project
under `htdocs/aquaflow` and set `APP_URL=http://localhost/aquaflow/public`.

### Option C — Nginx
```nginx
server {
    listen 80;
    server_name aquaflow.local;
    root /path/to/aquaflow/public;
    index index.php;

    location / {
        try_files $uri /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## 5. File permissions
Make sure the web server user can write to these folders (used for uploads and generated files):
```bash
chmod -R 775 storage public/uploads
```

## 6. Log in
Visit your configured `APP_URL` and sign in with any of the demo accounts (password
`Passw0rd!` for all of them):

| Role     | Email                          |
|----------|---------------------------------|
| Admin    | admin@aquaflow.local             |
| Agent    | sara.agent@aquaflow.local        |
| Customer | fatima@example.com               |

## Troubleshooting

**"Database connection error"** — double-check `.env` credentials and that MySQL is running
(`mysqladmin -u root -p ping`).

**Blank page / 500 error with no message** — set `APP_DEBUG=true` in `.env` temporarily to see
the full PHP error, then check `storage/logs` and your web server's PHP error log.

**Login always fails even with the right password** — this usually means the `users` table
wasn't seeded correctly. Re-import `database/aquaflow.sql` and confirm `SELECT COUNT(*) FROM
users;` returns 7.

**CSS/JS not loading (page looks unstyled)** — check that `APP_URL` in `.env` exactly matches
the URL you're browsing to (including the `/public` suffix if you didn't set a document root),
since all asset URLs are built from it.
