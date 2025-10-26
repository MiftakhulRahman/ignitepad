<x-public-layout>
    <div class="bg-gray-50 dark:bg-gray-800 min-h-screen">
        <div class="py-12 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    Galeri Proyek
                </h1>
                <p class="mt-3 max-w-2xl mx-auto text-lg text-gray-500 dark:text-gray-400">
                    Jelajahi semua karya inovatif dari mahasiswa Informatika UNUHA.
                </p>
                <div class="mt-6 max-w-lg mx-auto">
                    <form action="{{ route('projects.index') }}" method="GET" class="flex items-center">
                        <label for="search" class="sr-only">Cari Proyek</label>
                        <input type="search" name="search" id="search" class="block w-full ..."
                            placeholder="Cari judul, deskripsi, user, kategori, teknologi..." {{-- <== UBAH INI --}}
                            value="{{ $search ?? '' }}">

                        <button type="submit"
                            class="ml-3 flex-shrink-0 px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            Cari
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

                    <aside class="lg:col-span-1 space-y-6">
                        <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Filter Kategori</h3>
                            <ul class="space-y-2">
                                <li>
                                    <a href="{{ route('projects.index', ['search' => $search]) }}"
                                        class="block text-sm hover:text-blue-600 dark:hover:text-blue-400 {{ !$filterCategory ? 'font-bold text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300' }}">
                                        Semua Kategori
                                    </a>
                                </li>
                                @foreach ($categories as $category)
                                    <li>
                                        <a href="{{ route('projects.index', ['category' => $category->slug, 'search' => $search]) }}"
                                            class="block text-sm hover:text-blue-600 dark:hover:text-blue-400 {{ $filterCategory == $category->slug ? 'font-bold text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300' }}">
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Filter Teknologi</h3>
                            <ul class="space-y-2">
                                <li>
                                    <a href="{{ route('projects.index', ['search' => $search]) }}"
                                        class="block text-sm hover:text-blue-600 dark:hover:text-blue-400 {{ !$filterTechnology ? 'font-bold text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300' }}">
                                        Semua Teknologi
                                    </a>
                                </li>
                                @foreach ($technologies as $technology)
                                    <li>
                                        <a href="{{ route('projects.index', ['technology' => $technology->slug, 'search' => $search]) }}"
                                            class="block text-sm hover:text-blue-600 dark:hover:text-blue-400 {{ $filterTechnology == $technology->slug ? 'font-bold text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300' }}">
                                            {{ $technology->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </aside>
                    <main class="lg:col-span-3">
                        @if ($search || $filterCategory || $filterTechnology)
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                                Menampilkan proyek
                                @if ($filterCategory)
                                    dalam kategori "<span
                                        class="text-blue-600 dark:text-blue-400">{{ $categories->firstWhere('slug', $filterCategory)->name ?? $filterCategory }}</span>"
                                @endif
                                @if ($filterTechnology)
                                    menggunakan "<span
                                        class="text-blue-600 dark:text-blue-400">{{ $technologies->firstWhere('slug', $filterTechnology)->name ?? $filterTechnology }}</span>"
                                @endif
                                @if ($search)
                                    dengan kata kunci "<span
                                        class="text-blue-600 dark:text-blue-400">{{ $search }}</span>"
                                @endif
                            </h2>
                        @endif

                        @if ($projects->isEmpty())
                            <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6 text-center">
                                <p class="text-gray-500 dark:text-gray-400 text-lg">
                                    Proyek tidak ditemukan dengan filter atau pencarian ini.
                                </p>
                                <a href="{{ route('projects.index') }}"
                                    class="mt-2 text-sm text-blue-500 hover:underline">Reset Filter & Pencarian</a>
                            </div>
                        @else
                            <div class="grid gap-6 md:grid-cols-2"> {{-- Grid di sini jadi 2 kolom agar pas dengan sidebar --}}
                                @foreach ($projects as $project)
                                    <x-project-card :project="$project" />
                                @endforeach
                            </div>

                            <div class="mt-12">
                                {{ $projects->links() }}
                            </div>
                        @endif
                    </main>

                </div>
            </div>
        </div>
    </div>
</x-public-layout>
