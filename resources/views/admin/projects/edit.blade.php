<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Moderasi Proyek: {{ $project->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="mb-4">
                        <p><strong>Pembuat:</strong> {{ $project->user->name }} ({{ $project->user->nim }})</p>
                        <p><strong>Judul:</strong> {{ $project->title }}</p>
                        <p><strong>Link Publik:</strong> 
                            <a href="{{ route('projects.show', $project) }}" target="_blank" class="text-blue-500 hover:underline">
                                Lihat Proyek
                            </a>
                        </p>
                    </div>
                    <hr class_l="dark:border-gray-700">

                    <form method="POST" action="{{ route('admin.projects.update', $project) }}" class="mt-6 space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="status" :value="__('Status Proyek')" />
                            <select name="status" id="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="draft" @selected(old('status', $project->status) == 'draft')>Draft (Ditolak/Dikembalikan)</option>
                                <option value="review" @selected(old('status', $project->status) == 'review')>Review (Menunggu Review)</option>
                                <option value="published" @selected(old('status', $project->status) == 'published')>Published (Disetujui/Tampil)</option>
                                <option value="archived" @selected(old('status', $project->status) == 'archived')>Archived (Diarsipkan)</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                        
                        <div>
                            <x-input-label for="visibility" :value="__('Visibilitas')" />
                            <select name="visibility" id="visibility" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="public" @selected(old('visibility', $project->visibility) == 'public')>Publik (Bisa dilihat siapa saja)</option>
                                <option value="university_only" @selected(old('visibility', $project->visibility) == 'university_only')>Universitas (Hanya yang login)</option>
                                <option value="private" @selected(old('visibility', $project->visibility) == 'private')>Private (Hanya Pembuat)</option>
                            </select>
                            <x-input-error :messages="$errors->get('visibility')" class="mt-2" />
                        </div>

                        <div class="block mt-4">
                            <label for="featured" class="inline-flex items-center">
                                <input id="featured" type="checkbox" name="featured" value="1" 
                                       @checked(old('featured', $project->featured))
                                       class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Jadikan Proyek Unggulan (Featured)') }}</span>
                            </label>
                            <x-input-error :messages="$errors->get('featured')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.projects.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mr-4">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Update Moderasi') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>