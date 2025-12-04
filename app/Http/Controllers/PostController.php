<?php

namespace App\Http\Controllers;

use Framework\Http\Request;
use Framework\Http\Response;

class PostController extends Controller
{
    public function show($id): Response
    {
        return new Response("Post ID: {$id}");
    }
}