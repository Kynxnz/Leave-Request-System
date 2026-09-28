# Leave Request System

This archive intentionally does NOT include the Composer `vendor/` directory or Node `node_modules/` directory.
Those are generated dependencies and can contain executable helper files that antivirus software may flag.

## Setup

1. Install PHP and Composer.
2. Open a terminal in this project folder.
3. Run:

   composer install

4. Copy `.env.example` to `.env` if `.env` does not exist.
5. Configure your database in `.env`.
6. Run:

   php artisan key:generate
   php artisan migrate --seed
   php artisan serve

Then open the address shown by Laravel.
