<?php

namespace App\Blog\Http\Controllers;

use App\Blog\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest('published_at')->paginate(config('blog.posts_per_page'));
        
        return view('blog::posts.index', compact('posts'));
    }
    
    public function show($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        
        return view('blog::posts.show', compact('post'));
    }
}