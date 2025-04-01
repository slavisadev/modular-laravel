<?php

namespace App\Blog\Database\Seeders;

use App\Blog\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $samplePosts = [
            [
                'title' => 'Getting Started with Laravel',
                'content' => 'Laravel is a web application framework with expressive, elegant syntax. We have already laid the foundation — freeing you to create without sweating the small things.',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Modular Code Organization',
                'content' => 'Breaking your application into modules or packages helps maintain a clean codebase. This approach improves maintainability, testability, and reusability of components.',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Package Development in Laravel',
                'content' => 'Laravel provides excellent support for package development. You can create reusable components for your applications or share them with the community.',
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($samplePosts as $post) {
            Post::create([
                'title' => $post['title'],
                'slug' => Str::slug($post['title']),
                'content' => $post['content'],
                'published_at' => $post['published_at'],
            ]);
        }
    }
}