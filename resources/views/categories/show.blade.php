<x-public-layout>
    <div class="bg-gray-50 dark:bg-gray-800 min-h-screen">
        <div class="py-12 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    Proyek Kategori: {{ $category->name }}
                </h1>
                @if($category->description)
                <p class="mt-3 max-w-2xl mx-auto text-lg text-gray-500 dark:text-gray-400">
                    {{ $category->description }}
                </p>
                @endif
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if($projects->isEmpty())
                    <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6 text-center">
                        <p class="text-gray-500 dark:text-gray-400 text-lg">
                            Belum ada proyek yang dipublikasi dalam kategori ini.
                        </p>
                    </div>
                @else
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($projects as $project)
                            <x-project-card :project="$project" />
                        @endforeach
                    </div>

                    <div class="mt-12">
                        {{ $projects->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-public-layout>