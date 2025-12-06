<?php

declare(strict_types=1);

// creates database.sqlite and seeds sample data for the example blog

$dbFile = __DIR__ . '/../database.sqlite';
if (!file_exists($dbFile)) {
    touch($dbFile);
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    body TEXT NOT NULL,
    author TEXT,
    created_at TEXT,
    updated_at TEXT
);
SQL
);

// seed sample user & posts if empty
$count = $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
if ((int)$count === 0) {
    $now = date('Y-m-d H:i:s');
    $pdo->beginTransaction();
    $pdo->exec("INSERT INTO users (name, email) VALUES ('Elliot Anderson', 'elliot@example.com')");
    $userId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, body, author, created_at, updated_at) VALUES (:user_id, :title, :body, :author, :now, :now)");

    $stmt->execute([
        'user_id' => $userId,
        'title' => 'Welcome to the example blog',
        'body' => 'This is a demo post powered by the mini framework. Edit the example code to make it your own.',
        'author' => 'Elliot Anderson',
        'now' => $now,
    ]);

    $stmt->execute([
        'user_id' => $userId,
        'title' => 'Second post',
        'body' => "More content here. Try editing this post in examples/blog/bin/setup.php or via SQL.",
        'author' => 'Elliot Anderson',
        'now' => $now,
    ]);

    $pdo->commit();
    echo "Seeded example DB at {$dbFile}\n";
} else {
    echo "Example DB already has posts, skipping seed.\n";
}
