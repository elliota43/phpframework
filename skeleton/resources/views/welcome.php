<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        h1 { color: #333; }
        .box {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Welcome to PHP Framework</h1>
    <p>Your application is ready to go!</p>
    
    <div class="box">
        <h2>Next Steps</h2>
        <ul>
            <li>Create routes in <code>routes/web.php</code></li>
            <li>Create controllers in <code>app/Http/Controllers/</code></li>
            <li>Create models in <code>app/Models/</code></li>
            <li>Create views in <code>resources/views/</code></li>
        </ul>
    </div>
    
    <div class="box">
        <h2>Commands</h2>
        <ul>
            <li><code>php mini serve</code> - Start development server</li>
            <li><code>php mini migrate</code> - Run database migrations</li>
            <li><code>php mini make:controller Name</code> - Create controller</li>
        </ul>
    </div>
</body>
</html>

