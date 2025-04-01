<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-3xl font-bold mb-2">{{ $post->title }}</h1>
                    <div class="mb-6 text-sm text-gray-500">
                        Published: {{ $post->published_at->format('M d, Y') }}
                    </div>
                    
                    <div class="prose max-w-none">
                        {{ $post->content }}
                    </div>
                    
                    <div class="mt-8">
                        <a href="{{ route('blog.posts.index') }}" class="text-blue-600 hover:text-blue-800">
                            &larr; Back to all posts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>