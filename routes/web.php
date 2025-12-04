<?php

use App\Http\Controllers\HomeController;
use Framework\Routing\Router;
use Framework\Http\Request;
use Framework\Http\Response;

use App\Models\User;

return function (Router $router): void {
    
    $router->get('/test-rel', function () {
        $user = User::find(1);

        if (!$user) {
            return new Response('No user 1', 404);
        }

        $posts = $user->posts();

        $out = "User: " . $user->name . "\n";
        foreach ($posts as $post) {
            $out .= "- " . $post->title . "\n";
        }

        return new Response("<pre>{$out}</pre>");
    });

    $router->get('/attr-test', function() {
        $users = User::query()
        ->where('id', '>=', 1)
        ->orderBy('id', 'desc')
        ->limit(5)
        ->get();

        $out = "Users:\n\n";
        foreach ($users as $user) {
            $out .= $user->id . ' - ' . ($user->name ?? '[no name]') . "\n";
        }

        return new Response("<pre>" . $out . "</pre>");
    });


    $router->get('/debug-users', function() {
        $users = User::all();

        $body = "Users seen by the app:\n\n";

        foreach ($users as $user) {
            $arr = $user->toArray();
            $body .= json_encode($arr, JSON_PRETTY_PRINT) . "\n\n";
        }

        return new Response('<pre>' . $body .'</pre>');
    });
};