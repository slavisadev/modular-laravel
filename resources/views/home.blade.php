<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-3xl font-bold mb-6">Welcome to Modular Laravel</h1>
                    
                    <p class="mb-4">This application demonstrates the use of internal packages for modular code organization.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                        <div class="border p-4 rounded-lg">
                            <h2 class="text-xl font-semibold mb-2">Blog Package</h2>
                            <p>This package provides blog functionality with posts and comments.</p>
                            <a href="{{ route('blog.posts.index') }}" class="text-blue-600 hover:text-blue-800 mt-2 block">
                                View Blog &rarr;
                            </a>
                        </div>
                        
                        <div class="border p-4 rounded-lg">
                            <h2 class="text-xl font-semibold mb-2">Admin Package</h2>
                            <p>This package provides administrative functionality.</p>
                            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 mt-2 block">
                                View Admin Dashboard &rarr;
                            </a>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-semibold mb-4">Latest Blog Posts</h2>
                    
                    @forelse ($latestPosts as $post)
                        <div class="mb-4 pb-4 border-b">
                            <h3 class="text-lg font-semibold">
                                <a href="{{ route('blog.posts.show', $post->slug) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            <p class="text-gray-600">{{ \Illuminate\Support\Str::limit($post->content, 100) }}</p>
                        </div>
                    @empty
                        <p>No posts found. Run database seeder to create sample content.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>