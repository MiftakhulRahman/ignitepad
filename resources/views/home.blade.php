<x-public-layout>
    <div class="bg-white dark:bg-gray-900">

        {{-- Hero Section (Versi Baru dengan Search Bar) --}}
        <div class="relative bg-gray-50 dark:bg-gray-800 overflow-hidden">
            <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1
                        class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                        <span class="block">IgnitePad</span>
                        <span class="block text-blue-600 dark:text-blue-400">Ignite Your Digital Journey</span>
                    </h1>
                    <p
                        class="mt-3 max-w-md mx-auto text-base text-gray-500 dark:text-gray-400 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                        Platform Repositori Proyek Mahasiswa Informatika Universitas Nurul Huda.
                        Tampilkan karyamu, temukan inspirasi, dan jalin kolaborasi.
                    </p>

                    <div class="mt-8 max-w-lg mx-auto">
                        <form action="{{ route('projects.index') }}" method="GET"
                            class="flex flex-col sm:flex-row gap-3">
                            <label for="search_home" class="sr-only">Cari Proyek</label>
                            <input type="search" name="search" id="search_home" class="block w-full ..."
                                placeholder="Cari proyek (judul, user, tech...)" {{-- <== UBAH INI --}}>

                            <button type="submit"
                                class="flex-shrink-0 px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                Cari
                            </button>
                        </form>
                    </div>
                    <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-6">
                        <div class="rounded-md shadow">
                            <a href="{{ route('projects.index') }}"
                                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800 md:py-2 md:text-lg md:px-10">
                                Lihat Semua
                            </a>
                        </div>
                        @guest
                            <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
                                <a href="{{ route('register') }}"
                                    class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 md:py-2 md:text-lg md:px-10">
                                    Daftar
                                </a>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>
        </div>

        {{-- Section Proyek Unggulan (Tetap Sama) --}}
        <div class="py-12 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white text-center mb-8">
                    Proyek Unggulan Terbaru
                </h2>

                @if ($latestProjects->isEmpty())
                    <p class="text-center text-gray-500 dark:text-gray-400">
                        Belum ada proyek yang dipublikasi.
                    </p>
                @else
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($latestProjects as $project)
                            <x-project-card :project="$project" />
                        @endforeach
                    </div>
                @endif

                <div class="mt-10 text-center">
                    <a href="{{ route('projects.index') }}"
                        class="text-base font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                        Lihat semua proyek &rarr;
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-public-layout>
