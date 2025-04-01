<?php

namespace App\Http\Controllers;

use App\Blog\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Using the blog package model
        $latestPosts = Post::latest('published_at')->take(3)->get();
        
        return view('home', compact('latestPosts'));
    }
}