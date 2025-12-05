# mini — PHP Micro Framework

A tiny, educational PHP framework used for learning how common framework pieces fit together: a service container, HTTP kernel, routing, a small ActiveRecord-style ORM, migrations, and a minimal view engine.

## Requirements

- **PHP 8.0+** with PDO and SQLite support
- Composer (optional for autoloading and installing dependencies)

## Quick Install

1. Clone the repo:

```bash
git clone <repo-url> mini
cd mini
```

2. Install dependencies (if you add any via Composer):

```bash
composer install
```

3. Ensure the database file exists (project root):

```bash
touch database.sqlite
```

4. Run migrations:

```bash
php mini migrate
```

This will create the `users`, `posts`, and `migrations` tables.

## Run the dev server

Start the built-in PHP server (recommended for local development):

```bash
php mini serve
# or use php directly:
php -S 127.0.0.1:9003 -t public public/index.php
```

Open http://127.0.0.1:9003 in your browser.

## Seed a sample user

If you'd like a quick user to test `User::find(1)`, insert one with sqlite3:

```bash
sqlite3 database.sqlite "INSERT INTO users (name, email) VALUES ('Elliot Anderson', 'elliot@example.com');"
sqlite3 database.sqlite "SELECT id, name, email FROM users;"
```

If you see `database is locked` errors, close any DB browsers (e.g. DB Browser for SQLite) that may be holding the file open.

## Basic Tutorial

1. Add a route in `routes/web.php`:

```php
use Framework\Routing\Router;
use Framework\Http\Response;
use App\Models\User;

return function (Router $router): void {
    $router->get('/hello', function () {
        return new Response('Hello from mini');
    });

    $router->get('/user-test', function () {
        $user = User::find(1);
        return new Response($user ? "User: {$user->getAttribute('name')}" : 'No user');
    });
};
```

2. Create a controller with the CLI (convenience):

```bash
php mini make:controller UserController
```

Then edit `app/Http/Controllers/UserController.php` and register routes pointing to `UserController@method`.

3. Create a migration:

```bash
php mini make:migration add_profile_to_users
```

Edit the generated file in `database/migrations/` and then run `php mini migrate` again.

## Views & Templates

- Views live in `resources/views` and are compiled to `storage/views`.
- Use `View::make('view.name', ['var' => 'value'])` to render templates.

## Documentation Site

- The small docs site is at `docs/index.html`. You can preview it locally with a static server:

```bash
# from project root
python3 -m http.server 8000
# then open http://localhost:8000/docs/index.html
```

I added a small scrollspy and syntax highlighting (Highlight.js) to make the docs easier to navigate.

## Troubleshooting

- "database is locked": close other applications holding the SQLite file, or kill the process.
- If migrations fail, check the SQL in `database/migrations/*.php` and run them manually via `sqlite3 database.sqlite` for debugging.

## Contributing

This project is intentionally tiny and educational. Feel free to open issues or PRs with improvements or features you want to try.

---
If you'd like, I can also add a `README` badge, or generate a CONTRIBUTING.md with development guidelines.
