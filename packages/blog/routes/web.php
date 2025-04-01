<?php

use App\Blog\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/blog', [PostController::class, 'index'])->name('blog.posts.index');
    Route::get('/blog/{slug}', [PostController::class, 'show'])->name('blog.posts.show');
});