<?php

declare(strict_types=1);

namespace Examples\Blog\Http\Controllers;

use Examples\Blog\Models\Post;
use Framework\View\View;

class HomeController
{
    public function index()
    {
        $posts = Post::all();
        return View::make('home', ['posts' => $posts]);
    }
}
