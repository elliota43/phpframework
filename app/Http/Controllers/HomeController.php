<?php

namespace App\Http\Controllers;

use Framework\Http\Request;
use Framework\Http\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return new Response('Hello from HomeController@index');
    }

    public function hello(Request $request, string $name): Response
    {
        return new Response("Hello, {$name}");
    }
}