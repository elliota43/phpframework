<?php

namespace App\Http\Controllers;

use App\Models\User;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\View\View;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $html = View::make('home', [
            'name' => 'Elliot',
        ]);

        return new Response($html);
    }


    public function hello(Request $request, string $name): Response
    {
        return new Response("Hello, {$name}");
    }
}