# Example: Blog (built with mini)

This is a small example application built entirely on the `mini` framework in this repository. It demonstrates routing, controllers, models, views, and a tiny SQLite-backed blog with users and posts.

Quick start

```bash
# create example DB and seed sample data
php examples/blog/bin/setup.php

# run the example server
php -S 127.0.0.1:9080 -t examples/blog/public examples/blog/public/index.php

# open http://127.0.0.1:9080
```

What this example includes

- Lightweight bootstrap that wires the `Framework` components to an example-local SQLite DB.
- Simple controllers: `HomeController` (list posts) and `PostController` (show post).
- Models: `Examples\\Blog\\Models\\User` and `Examples\\Blog\\Models\\Post` extending `Framework\\Database\\Model`.
- PHP-driven views under `examples/blog/resources/views`.

Notes

- The example uses the framework classes via the repository `vendor/autoload.php`. Be sure to run this project from the repo root so Composer's autoloader can find `Framework\\` classes.
- If you change the framework internals, the example will reflect those changes immediately.
