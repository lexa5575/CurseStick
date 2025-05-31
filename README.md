# CruseStick - Laravel + Filament Admin + Livewire + Blade + Vue.js

This is a web application built with Laravel, Filament Admin, Livewire, Blade, Vue.js, and Inertia.js. It includes a simple product management system with a user-friendly admin panel and interactive frontend components.

## Technologies Used

- PHP 8.4.2
- Laravel 11
- Filament Admin 3
- Livewire 3
- Blade Templating Engine
- Vue.js 3
- Inertia.js
- Alpine.js
- Tailwind CSS
- Bootstrap
- Vite

## Features

- Modern and responsive user interface
- Powerful admin panel with Filament
- Product management system
- User authentication and authorization
- Interactive frontend with Vue.js and Inertia.js
- Responsive design with Tailwind CSS and Bootstrap
- SPA-like experience with Inertia.js
- Hybrid approach with both Blade templates and Vue components

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd CruseStick
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install NPM dependencies:
```bash
npm install
```

4. Create a copy of the .env file:
```bash
cp .env.example .env
```

5. Generate an application key:
```bash
php artisan key:generate
```

6. Configure your database in the .env file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crusestick
DB_USERNAME=root
DB_PASSWORD=
```

7. Run the migrations:
```bash
php artisan migrate
```

8. Build the frontend assets:
```bash
npm run build
```

9. Create an admin user:
```bash
php artisan make:filament-user
```

10. Start the development server:
```bash
php artisan serve
```

## Usage

- Access the main website at: `http://localhost:8000`
- Access the admin panel at: `http://localhost:8000/admin`
- Login with the admin credentials you created

## Vue.js Components

The project uses Vue.js 3 with Inertia.js for interactive frontend components. The main components are:

### Pages Components (Inertia.js)

Located in `resources/js/Pages`:

- **Favorites/Index.vue**: Displays the user's favorite products
- **Orders/Index.vue**: Displays the user's order history
- **Orders/Show.vue**: Displays detailed information about a specific order
- **Faq/Index.vue**: Displays frequently asked questions in an accordion format

### Other Vue Components

Located in `resources/js/vue`:

- **BannerSlider.vue**: A carousel component for displaying banners on the home page
- **ProductCard.vue**: A reusable component for displaying product information

### Using Inertia.js with Laravel

Controllers use Inertia to render Vue components:

```php
return Inertia::render('ComponentName', [
    'data' => $data,
]);
```

### Hybrid Approach

The application uses a hybrid approach:
- Some pages are rendered using traditional Blade templates
- Interactive features use Vue.js components
- Alpine.js is used for simple interactions directly in Blade templates

## Admin Credentials

- Email: lexa5575@gmail.com
- Password: (the password you set during user creation)

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
