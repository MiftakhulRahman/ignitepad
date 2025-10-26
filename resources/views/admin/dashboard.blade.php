<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total User</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ $stats['totalUsers'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Proyek</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ $stats['totalProjects'] }}</p>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Menunggu Review</h3>
                    <p class="mt-1 text-3xl font-semibold text-yellow-900 dark:text-yellow-100">{{ $stats['projectsPendingReview'] }}</p>
                    <a href="{{ route('lecturer.reviews.index') }}" class="text-sm font-medium text-yellow-700 dark:text-yellow-200 hover:underline">
                        Lihat Daftar &raquo;
                    </a>
                </div>
                <div class="bg-green-100 dark:bg-green-900 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Proyek Terpublikasi</h3>
                    <p class="mt-1 text-3xl font-semibold text-green-900 dark:text-green-100">{{ $stats['projectsPublished'] }}</p>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">User Baru Terdaftar</h3>
                    </div>
                    <div>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($recentUsers as $user)
                                <li class="p-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <div>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ $user->name }}</a>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }} ({{ $user->role }})</p>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</span>
                                </li>
                            @empty
                                <li class="p-4 text-center text-gray-500 dark:text-gray-400">Tidak ada user baru.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Proyek Terbaru Menunggu Review</h3>
                    </div>
                    <div>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($recentPendingProjects as $project)
                                <li class="p-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <div>
                                        <a href="{{ route('lecturer.reviews.show', $project) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ Str::limit($project->title, 30) }}</a>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->user->name }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $project->updated_at->diffForHumans() }}</span>
                                </li>
                            @empty
                                <li class="p-4 text-center text-gray-500 dark:text-gray-400">Tidak ada proyek untuk direview.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>