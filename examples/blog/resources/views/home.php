<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Example Blog</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding: 24px; }
        a { color: #0066cc; text-decoration: none; }
        .post { padding: 8px 0; border-bottom: 1px solid #eee; }
        .post h2 { margin: 0 0 6px; font-size: 18px; }
        .meta { color: #666; font-size: 13px; }
    </style>
</head>
<body>
    <h1>Example Blog</h1>

    <?php if (empty($posts)): ?>
        <p>No posts yet.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <h2><a href="/posts/<?php echo $post->getAttribute('id') ?>"><?php echo htmlspecialchars($post->getAttribute('title')) ?></a></h2>
                <div class="meta">By <?php echo htmlspecialchars($post->getAttribute('author') ?? 'Unknown') ?> • <?php echo htmlspecialchars($post->getAttribute('created_at') ?? '') ?></div>
                <p><?php echo htmlspecialchars(substr($post->getAttribute('body') ?? '', 0, 180)) ?>…</p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
