<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold mb-4">Blog Posts</h1>
                    
                    @forelse ($posts as $post)
                        <div class="mb-4 pb-4 border-b">
                            <h2 class="text-xl font-semibold">
                                <a href="{{ route('blog.posts.show', $post->slug) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $post->title }}
                                </a>
                            </h2>
                            <p class="text-gray-600">{{ \Illuminate\Support\Str::limit($post->content, 200) }}</p>
                            <div class="mt-2 text-sm text-gray-500">
                                Published: {{ $post->published_at->format('M d, Y') }}
                            </div>
                        </div>
                    @empty
                        <p>No posts found.</p>
                    @endforelse
                    
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>