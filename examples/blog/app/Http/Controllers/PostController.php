<?php

declare(strict_types=1);

namespace Examples\Blog\Http\Controllers;

use Examples\Blog\Models\Post;
use Framework\View\View;
use Framework\Http\Response;

class PostController
{
    public function show(int $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return new Response('Not Found', 404);
        }

        return View::make('post', ['post' => $post]);
    }
}
