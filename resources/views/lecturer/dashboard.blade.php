<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lecturer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-yellow-100 dark:bg-yellow-900 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Menunggu Review</h3>
                    <p class="mt-1 text-3xl font-semibold text-yellow-900 dark:text-yellow-100">{{ $stats['projectsPendingReview'] }}</p>
                    <a href="{{ route('lecturer.reviews.index') }}" class="text-sm font-medium text-yellow-700 dark:text-yellow-200 hover:underline">
                        Buka Halaman Review &raquo;
                    </a>
                </div>
                
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Proyek Dibimbing</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ $stats['supervisedProjectsCount'] }}</p>
                </div>
                
                <div class="bg-green-100 dark:bg-green-900 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Total Proyek Terpublikasi</h3>
                    <p class="mt-1 text-3xl font-semibold text-green-900 dark:text-green-100">{{ $stats['totalPublished'] }}</p>
                </div>
            </div>

            <!-- Notification Section -->
            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Notifikasi</h3>
                </div>
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @forelse ($notifications as $notification)
                        <a href="{{ route('lecturer.notifications.read', $notification->id) }}" class="block p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <p>{!! $notification->data['message'] !!}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </a>
                    @empty
                        <p>Tidak ada notifikasi baru.</p>
                    @endforelse
                </div>
            </div>



            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Proyek Bimbingan Terbaru Anda</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Judul Proyek</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mahasiswa</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($recentSupervisedProjects as $project)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ Str::limit($project->title, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $project->user->name }}
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
                                        <a href="{{ route('projects.show', $project) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600">
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                        Anda belum menjadi pembimbing di proyek manapun.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>