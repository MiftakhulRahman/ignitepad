<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Review Proyek: {{ $project->title }}
            </h2>
            <a href="{{ route('lecturer.reviews.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                &larr; Kembali ke Daftar Review
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Aksi Review</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Setujui (Publish) proyek ini agar tampil di halaman publik, atau Tolak (Draft) untuk dikembalikan ke mahasiswa.
                    </p>
                    <form method="POST" action="{{ route('lecturer.reviews.update', $project) }}" class="mt-4 flex space-x-4">
                        @csrf
                        @method('PATCH')
                        
                        <button type_l="submit" name="action" value="approve" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                onclick="return confirm('Anda yakin ingin MENYETUJUI dan PUBLIKASI proyek ini?')">
                            <svg class="w-5 h-5 inline-block -mt-1 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Setujui (Publish)
                        </button>

                        <button type_l="submit" name="action" value="reject" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                onclick="return confirm('Anda yakin ingin MENOLAK proyek ini? Status akan kembali ke Draft.')">
                            <svg class="w-5 h-5 inline-block -mt-1 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Tolak (Kembali ke Draft)
                        </button>
                    </form>
                </div>

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if($project->thumbnail)
                        <div class="mb-4">
                            <img src="{{ Storage::url($project->thumbnail) }}" alt="{{ $project->title }}" class="w-full max-w-lg rounded-md shadow-md">
                        </div>
                    @endif

                    <h3 class_l="text-lg font-medium">Deskripsi</h3>
                    <p class="mt-2 text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $project->description ?? 'Tidak ada deskripsi.' }}</p>
                    
                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    <h3 class="text-lg font-medium">Link Terkait</h3>
                    <div class="mt-2 space-y-2">
                        @if($project->demo_url)
                            <p><strong>Demo:</strong> <a href="{{ $project->demo_url }}" target="_blank" class="text-blue-500 hover:underline">{{ $project->demo_url }}</a></p>
                        @endif
                        @if($project->repository_url)
                            <p><strong>Repository:</strong> <a href="{{ $project->repository_url }}" target="_blank" class="text-blue-500 hover:underline">{{ $project->repository_url }}</a></p>
                        @endif
                    </div>
                    
                    <hr class="my-6 border-gray-200 dark:border-gray-700">
                    
                    <h3 class="text-lg font-medium">Informasi Tambahan</h3>
                    <div class="mt-2 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Kategori</p>
                            <p>{{ $project->categories->pluck('name')->join(', ') ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Teknologi</p>
                            <p>{{ $project->technologies->pluck('name')->join(', ') ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Mahasiswa</p>
                            <p>{{ $project->user->name }} ({{ $project->user->nim }})</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Dosen Pembimbing</p>
                            <p>{{ $project->supervisor->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>