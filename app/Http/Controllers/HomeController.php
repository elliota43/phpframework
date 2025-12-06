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
        // Find a user or create a dummy one for demo
        $user = User::find(1);
        
        if (!$user) {
            // Create a demo user if none exists
            $user = new User([
                'id' => 1,
                'name' => 'Elliot',
                'email' => 'elliot@example.com',
            ]);
        }

        return View::make('home', [
            'user' => $user,
        ]);
    }


    public function hello(Request $request, string $name): Response
    {
        return new Response("Hello, {$name}");
    }
}