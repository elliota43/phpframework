<?php

declare(strict_types=1);

// Creates database.sqlite and seeds sample data for the API example

$dbFile = __DIR__ . '/../database.sqlite';
if (!file_exists($dbFile)) {
    touch($dbFile);
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create categories table
$pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    created_at TEXT,
    updated_at TEXT
);
SQL
);

// Create tasks table
$pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    status TEXT NOT NULL DEFAULT 'pending',
    priority TEXT NOT NULL DEFAULT 'medium',
    category_id INTEGER,
    due_date TEXT,
    created_at TEXT,
    updated_at TEXT,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
SQL
);

// Seed sample data if empty
$taskCount = $pdo->query('SELECT COUNT(*) FROM tasks')->fetchColumn();
if ((int)$taskCount === 0) {
    $now = date('Y-m-d H:i:s');
    $pdo->beginTransaction();
    
    // Insert categories
    $pdo->exec("INSERT INTO categories (name, description, created_at, updated_at) VALUES 
        ('Work', 'Work-related tasks', '{$now}', '{$now}'),
        ('Personal', 'Personal tasks and errands', '{$now}', '{$now}'),
        ('Shopping', 'Shopping lists and items', '{$now}', '{$now}'),
        ('Health', 'Health and fitness tasks', '{$now}', '{$now}')
    ");
    
    // Get category IDs
    $workCat = $pdo->query("SELECT id FROM categories WHERE name = 'Work'")->fetchColumn();
    $personalCat = $pdo->query("SELECT id FROM categories WHERE name = 'Personal'")->fetchColumn();
    $shoppingCat = $pdo->query("SELECT id FROM categories WHERE name = 'Shopping'")->fetchColumn();
    
    // Insert tasks
    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, status, priority, category_id, due_date, created_at, updated_at) VALUES 
        (:title, :description, :status, :priority, :category_id, :due_date, :now, :now)");
    
    $tasks = [
        [
            'title' => 'Complete API documentation',
            'description' => 'Write comprehensive documentation for the new REST API endpoints',
            'status' => 'in_progress',
            'priority' => 'high',
            'category_id' => $workCat,
            'due_date' => date('Y-m-d', strtotime('+3 days')),
        ],
        [
            'title' => 'Review pull requests',
            'description' => 'Review and provide feedback on pending pull requests',
            'status' => 'pending',
            'priority' => 'medium',
            'category_id' => $workCat,
            'due_date' => date('Y-m-d', strtotime('+1 day')),
        ],
        [
            'title' => 'Grocery shopping',
            'description' => 'Buy vegetables, fruits, and dairy products',
            'status' => 'pending',
            'priority' => 'medium',
            'category_id' => $shoppingCat,
            'due_date' => date('Y-m-d'),
        ],
        [
            'title' => 'Morning workout',
            'description' => '30 minutes cardio and strength training',
            'status' => 'completed',
            'priority' => 'high',
            'category_id' => null,
            'due_date' => date('Y-m-d', strtotime('-1 day')),
        ],
        [
            'title' => 'Plan weekend trip',
            'description' => 'Research and book accommodations for the weekend getaway',
            'status' => 'pending',
            'priority' => 'low',
            'category_id' => $personalCat,
            'due_date' => date('Y-m-d', strtotime('+5 days')),
        ],
        [
            'title' => 'Fix critical bug in production',
            'description' => 'Investigate and fix the authentication issue reported by users',
            'status' => 'in_progress',
            'priority' => 'urgent',
            'category_id' => $workCat,
            'due_date' => date('Y-m-d'),
        ],
    ];
    
    foreach ($tasks as $task) {
        $stmt->execute([
            'title' => $task['title'],
            'description' => $task['description'],
            'status' => $task['status'],
            'priority' => $task['priority'],
            'category_id' => $task['category_id'],
            'due_date' => $task['due_date'],
            'now' => $now,
        ]);
    }
    
    $pdo->commit();
    echo "✓ Seeded example database at {$dbFile}\n";
    echo "  - Created 4 categories\n";
    echo "  - Created 6 tasks\n\n";
} else {
    echo "Example database already has data, skipping seed.\n";
}

echo "Database ready at: {$dbFile}\n";

