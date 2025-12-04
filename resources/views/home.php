<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mini • Home</title>
</head>
<body>
    <h1>Welcome, {{ $user->name }}</h1>

    @if ($user->posts->isEmpty())
        <p>No posts yet.</p>
    @else
        <ul>
            @foreach ($user->posts as $post)
                <li>{{ $post->title }}</li>
            @endforeach
        </ul>
    @endif
</body>
</html>