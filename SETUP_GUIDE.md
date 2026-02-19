# Project Setup Guide

This guide describes how to set up the Domus Naturae project locally after cloning the repository. The project consists of a **Laravel 10 Backend** and an **Angular Frontend**.

## Prerequisites

Ensure you have the following installed on your machine:

- **PHP**: 8.1 or higher
- **Composer**: Dependency manager for PHP
- **Node.js**: LTS version recommended (compatible with Angular 11)
- **NPM** or **Yarn**: Package manager
- **MySQL**: Database server

---

## 1. Backend Setup (Laravel 10)

Navigate to the backend directory:

```bash
cd domus-naturae-backend-laravel10
```

### Install Dependencies

Install PHP dependencies using Composer:

```bash
composer install
```

### Environment Configuration

1. Copy the example environment file:

    ```bash
    cp .env.example .env
    ```

    _(On Windows PowerShell: `copy .env.example .env`)_

2. Open various `.env` file and configure your database settings:

    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name  # Ensure this database exists in MySQL
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

3. Generate the application key:
    ```bash
    php artisan key:generate
    ```

### Database Migration & Seeding

Run migrations to create database tables and seed initial data:

```bash
php artisan migrate
php artisan db:seed
```

### Run the Server

Start the Laravel development server:

```bash
php artisan serve
```

The backend will be available at `http://127.0.0.1:8000`.

---

## 2. Frontend Setup (Angular)

Open a new terminal and navigate to the frontend directory:

```bash
cd domus-naturae-frontend
```

### Install Dependencies

Install Node modules:

```bash
npm install
# OR
yarn install
```

### Run the Server

Start the Angular development server:

```bash
ng serve
```

The frontend will be available at `http://localhost:4200`.

---

## 3. Connecting Frontend to Backend

Ensure the frontend is pointing to the correct backend URL. Check `src/environments/environment.ts` (or simply `environment.ts` depending on structure) to verify the API URL matches your Laravel server (usually `http://127.0.0.1:8000/api`).

## Troubleshooting

### Composer Issues

**Problem: "Install or enable PHP's gd extension"**
If you see an error about `ext-gd` missing:

1. Locate your `php.ini` file. (Run `php --ini` in your terminal to find the path).
2. Open it and find the line `;extension=gd`.
3. Remove the semicolon `;` at the beginning to uncomment it: `extension=gd`.
4. Save the file and retry.

**Problem: Missing Extensions (gd, zip)**
If you see an error about `ext-gd` or `ext-zip` missing:

1. Locate your `php.ini` file (run `php --ini` in your terminal).
2. Open it and uncomment the relevant lines by removing the semicolon `;` at the start:
    - `extension=gd`
    - `extension=zip`
3. Save the file and retry `composer install` (or `update`).

**Problem: Version Mismatch / Lock File Error**
If `composer install` fails because your PHP version doesn't match the lock file (e.g. Lock file requires PHP 8.3/8.4 but you have 8.2):

1. **Do not** use `composer install`.
2. Instead, run the following command to regenerate the lock file for your version and resolve dependencies:
    ```bash
    composer update -W
    ```
    _(The `-W` flag allows upgrades/downgrades of dependencies to find a compatible set)._

### Other Common Issues

- **500 Internal Server Error**: Check `storage/logs/laravel.log` for details. Ensure `.env` is configured correctly.
- **Database Connection Refused**: Verify MySQL is running and credentials in `.env` are correct.
- **Node/Angular Version Mismatch**: Since this is an Angular 11 project, ensure your Node version is compatible (Node 14 or 12 is often recommended for older Angular versions, though newer ones might work with legacy flags).

---

**You are now ready to develop!**
