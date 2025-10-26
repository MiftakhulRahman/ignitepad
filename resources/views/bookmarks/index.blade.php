<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Proyek yang Saya Simpan (Bookmark)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if($bookmarkedProjects->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                        Anda belum menyimpan (bookmark) proyek apapun. Jelajahi <a href="{{ route('projects.index') }}" class="text-blue-500 hover:underline">galeri proyek</a> dan klik tombol simpan!
                    </p>
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($bookmarkedProjects as $project)
                        <x-project-card :project="$project" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $bookmarkedProjects->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>