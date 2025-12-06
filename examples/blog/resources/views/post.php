<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($post->getAttribute('title')) ?></title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding: 24px; }
        a { color: #0066cc; text-decoration: none; }
        .meta { color: #666; font-size: 13px; }
    </style>
</head>
<body>
    <a href="/">← Back</a>
    <h1><?php echo htmlspecialchars($post->getAttribute('title')) ?></h1>
    <div class="meta">By <?php echo htmlspecialchars($post->getAttribute('author') ?? 'Unknown') ?> • <?php echo htmlspecialchars($post->getAttribute('created_at') ?? '') ?></div>

    <article>
        <p><?php echo nl2br(htmlspecialchars($post->getAttribute('body') ?? '')) ?></p>
    </article>
</body>
</html>
