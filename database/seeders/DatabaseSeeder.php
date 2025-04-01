<?php

namespace Database\Seeders;

use App\Blog\Database\Seeders\PostSeeder;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clear existing data
        \Schema::disableForeignKeyConstraints();
        \App\Models\User::truncate();
        if (class_exists(\App\Blog\Models\Post::class)) {
            \App\Blog\Models\Post::truncate();
        }
        \Schema::enableForeignKeyConstraints();
        
        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        // Call seeders from packages
        $this->call([
            PostSeeder::class,
        ]);
    }
}
