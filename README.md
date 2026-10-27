# Invoice & Budget Management System

A Laravel 13 application for managing sales and purchase invoices, payments, budgets, expenses, contacts, products/services, reports, and currency exchange rates.

## Requirements

- PHP 8.3 or later with the extensions required by Laravel
- Composer
- Node.js and npm
- MySQL 8.0+ (or a compatible MariaDB server) with the `pdo_mysql` PHP extension

## Local setup

1. Install PHP and JavaScript dependencies:

   ```sh
   composer install
   npm install
   ```

2. Create the local environment file and application key:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

   On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp`.

3. Create the MySQL database (for example, `invoice_budget_management_system`), then configure the connection values in `.env` (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`). The example assumes a local MySQL server.

4. Run migrations. To load the sample data as well, run the seeder:

   ```sh
   php artisan migrate --seed
   ```

5. Build the frontend assets and start the application:

   ```sh
   npm run build
   php artisan serve
   ```

   Open the URL printed by Artisan (typically `http://127.0.0.1:8000`). For frontend development with Vite hot reload, run `npm run dev` in a separate terminal.

## Demo data and sign-in

The database seeder creates a sample administrator with `admin@ledger.test` / `password`, plus sample customers, suppliers, products, invoices, budgets, and expenses. These credentials are for local evaluation only. Change or remove the seeded administrator before deploying the application; do not use the sample password in production.

## Included features

- Authenticated dashboard with sales, purchase, expense, outstanding-balance, overdue-invoice, and budget summaries
- Purchase and sales invoice CRUD with line-item tax, discounts, totals, payment recording, and payment status
- Customer, supplier, and product/service management
- Monthly and yearly budgets with expenses and budget utilization
- Year-to-date reports and outstanding invoice details
- Currency conversion rates from the public Frankfurter API, with cached and last-known values when the service is unavailable

## Tests

Tests use MySQL as well. Create a separate `invoice_budget_management_system_test` database before running them; PHPUnit is configured for `127.0.0.1:3306` with the `root` user and no password by default. Update the database connection values in `phpunit.xml` if your local MySQL credentials differ. Do not point the test suite at your development or production database: tests reset database state.

Run the automated suite with:

```sh
php artisan test
```

## Notes

- The application uses Blade templates and Vite-built CSS/JavaScript.
- The exchange-rate request requires outbound network access. The rest of the application remains usable if the public API is unavailable.
- For a production deployment, set `APP_ENV=production`, disable debug output, configure secure credentials and a production database, and replace the sample login setup with deployment-appropriate account provisioning.
