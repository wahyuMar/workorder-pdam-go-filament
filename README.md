# Workorder PDAM - Laravel 12 & Filament 4

A work order management system built with Laravel 12 and Filament 4.

## Tech Stack

- **Laravel 12.47.0** - The latest version of the Laravel PHP framework
- **Filament 4.5.2** - A modern admin panel and form builder for Laravel
- **PHP 8.3+** - Required PHP version
- **SQLite** - Default database (can be changed to MySQL/PostgreSQL)

## Features

- Modern admin panel powered by Filament 4
- User authentication and authorization
- SQLite database out of the box
- Responsive design with Tailwind CSS
- Built-in form components and tables

## Requirements

- PHP >= 8.3
- Composer
- Node.js & NPM (for frontend assets)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/wahyuMar/workorder-pdam-go-filament.git
cd workorder-pdam-go-filament
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install NPM dependencies:
```bash
npm install
```

4. Copy the environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Run database migrations:
```bash
php artisan migrate
```

7. Create an admin user:
```bash
php artisan make:filament-user
```
Follow the prompts to enter:
- Name
- Email address
- Password

8. Build frontend assets:
```bash
npm run build
```

## Running the Application

Start the Laravel development server:
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

Access the admin panel at: `http://localhost:8000/admin`

## Development

For development with auto-reloading assets:
```bash
npm run dev
```

## Testing

Run the test suite:
```bash
php artisan test
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
