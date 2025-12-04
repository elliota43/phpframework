<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Mini • Home</title>
    </head>
    <body>
        <h1>Welcome to mini</h1>

        <?php if (isset($name)): ?>
        <p>Hello, <?= $e($name) ?>!</p>
    <?php else: ?>
        <p>Hello, world.</p>
    <?php endif; ?>

    </body>
</html>