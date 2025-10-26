<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Student Dashboard') }}
            </h2>
            <a href="{{ route('student.projects.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 w-full md:w-auto text-center">
                + Tambah Proyek Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Proyek</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ $stats['totalProjects'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Like Didapat</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ $stats['totalLikes'] }}</p>
                </div>
                <div class="bg-green-100 dark:bg-green-900 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Proyek Terpublikasi</h3>
                    <p class="mt-1 text-3xl font-semibold text-green-900 dark:text-green-100">{{ $stats['published'] }}</p>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Menunggu Review</h3>
                    <p class="mt-1 text-3xl font-semibold text-yellow-900 dark:text-yellow-100">{{ $stats['pendingReview'] }}</p>
                </div>
            </div>

            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Proyek Terbaru Anda</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Judul Proyek</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($recentProjects as $project)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ Str::limit($project->title, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($project->status == 'published') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                            @elseif($project->status == 'review') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('student.projects.edit', $project) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                        Anda belum memiliki proyek. <a href="{{ route('student.projects.create') }}" class="text-blue-500 hover:underline">Buat sekarang!</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('student.projects.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                        Lihat semua proyek Anda &rarr;
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>