# 📚 BookVerse

A full-stack online bookstore and e-reading platform — built from scratch in PHP and MySQL, with Stripe-powered checkout, a built-in PDF reader with per-user reading-progress tracking, and a full admin back office.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-database-4479A1?logo=mysql&logoColor=white)
![Stripe](https://img.shields.io/badge/Stripe-checkout-635BFF?logo=stripe&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-containerized-2496ED?logo=docker&logoColor=white)

**[Live demo →](#)** &nbsp;|&nbsp; See [Deployment](#deployment) for how it's hosted

---

## Why this project

Most bookstore clones stop at "add to cart, checkout." This one goes further — a purchased book unlocks an **in-browser reader that remembers where you left off**, and file access is gated server-side so a sample PDF and a paid PDF are never served the same way. It's also containerized and deployed end-to-end (Docker + Stripe + external MySQL), not just run locally.

## Features

**🛒 Storefront**
- Browse and search the catalog, with book detail pages and free sample previews
- Cart → Stripe Checkout → order history, fully wired to Stripe's hosted payment flow

**📖 Reading experience**
- Personal library of purchased books, plus a to-be-read list
- In-browser PDF reader that tracks reading progress per user, per book
- Access control on file delivery — sample PDFs are public, full books require a verified purchase

**🔐 Accounts & admin**
- Registration/login, contact form
- Admin panel for managing products, orders, users, and incoming messages

## Tech stack

| Layer | Choice |
|---|---|
| Backend | PHP 8.2, vanilla (no framework) |
| Database | MySQL, accessed via MySQLi with prepared statements |
| Payments | Stripe Checkout (`stripe/stripe-php`) |
| Infra | Docker (`php:8.2-apache`), deployed on Render with an external MySQL instance |

## Project structure

```
BookStore/
├── admin_*.php          # Admin panel pages
├── config.php             # DB connection + Stripe keys (reads from env vars)
├── create_checkout_session.php
├── success.php / cancel.php   # Stripe redirect handlers
├── reader.php              # In-browser PDF reader
├── pdf_access.php          # Access-controlled file serving
├── dbqueries                # Full MySQL schema (CREATE TABLE statements)
├── uploaded_img/            # Book cover images
├── uploaded_pdf/            # Full book PDFs
├── uploaded_samples/        # Sample/preview PDFs
├── Dockerfile
└── composer.json
```

## Database schema

Full schema in [`dbqueries`](./dbqueries): `products`, `register` (users), `cart`, `orders`, `purchased_books`, `reading_progress`, `tbr_list`, `message` (contact form).

## Running locally

**Requirements:** PHP 8.2+, MySQL, Composer, `mysqli` + `pdo_mysql` extensions.

```bash
git clone https://github.com/arya-lunawat/BookStore.git
cd BookStore
composer install
mysql -u <user> -p <database_name> < dbqueries
```

Set environment variables:
```
MYSQLHOST=localhost
MYSQLUSER=<your-mysql-user>
MYSQLPASSWORD=<your-mysql-password>
MYSQLDATABASE=<your-database-name>
MYSQLPORT=3306
STRIPE_SECRET_KEY=<your-stripe-test-secret-key>
STRIPE_PUBLISHABLE_KEY=<your-stripe-test-publishable-key>
```

```bash
php -S localhost:8000
```

## Running with Docker

```bash
docker build -t bookstore .
docker run -p 80:80 \
  -e MYSQLHOST=<host> -e MYSQLUSER=<user> -e MYSQLPASSWORD=<password> \
  -e MYSQLDATABASE=<database> -e MYSQLPORT=3306 \
  -e STRIPE_SECRET_KEY=<key> -e STRIPE_PUBLISHABLE_KEY=<key> \
  bookstore
```

## Deployment

Deployed as a Docker container (Render) against an external managed MySQL instance. The [`Dockerfile`](./Dockerfile):
- Installs `mysqli` and `pdo_mysql` PHP extensions and Composer dependencies (`stripe/stripe-php`)
- Explicitly forces Apache's `mpm_prefork` at container start — a fix for a known MPM conflict that shows up on some container platforms with the `php:apache` base image
- Reads all DB and Stripe credentials from environment variables; nothing sensitive is committed to the repo

## Security

- All database queries use prepared statements with parameter binding (MySQLi `prepare`/`bind_param`) — no string-concatenated SQL
- File access to purchased PDFs is verified server-side against the `purchased_books` table before serving
- Debug and seeding scripts used during development have been removed from the repository
- **Known issue, in progress:** passwords are currently hashed with MD5 in `login.php`/`register.php`; migrating this to PHP's `password_hash()`/`password_verify()` is the next planned fix

## License

Personal portfolio project.
