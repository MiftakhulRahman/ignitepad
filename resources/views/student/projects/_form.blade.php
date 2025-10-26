@csrf
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div>
            <x-input-label for="title" :value="__('Judul Proyek')" />
            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $project->title ?? '')" required autofocus />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="description" :value="__('Deskripsi Singkat')" />
            <textarea id="description" name="description" rows="5" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $project->description ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="thumbnail" :value="__('Thumbnail (Gambar Sampul)')" />
            <input id="thumbnail" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file" name="thumbnail" accept="image/png, image/jpeg, image/jpg, image/gif, image/webp">
            <small class="text-gray-500 dark:text-gray-400">Maks 2MB. Kosongkan jika tidak ingin mengubah thumbnail (saat edit).</small>
            <x-input-error :messages="$errors->get('thumbnail')" class="mt-2" />

            @if(isset($project) && $project->thumbnail)
                <div class="mt-2">
                    <img src="{{ Storage::url($project->thumbnail) }}" alt="Thumbnail" class="h-20 w-auto rounded border border-gray-300 dark:border-gray-700">
                </div>
            @endif
        </div>

        {{-- Blok Screenshot Tambah --}}
        <div class="mt-4">
            <x-input-label for="screenshots" :value="__('Screenshots / Galeri Proyek')" />
            <input id="screenshots" name="screenshots[]" type="file" multiple accept="image/png, image/jpeg, image/jpg, image/webp"
                    class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
            <small class="text-gray-500 dark:text-gray-400">Anda bisa memilih lebih dari satu gambar (Maks 2MB per file).</small>
            <x-input-error :messages="$errors->get('screenshots')" class="mt-2" />
            <x-input-error :messages="$errors->get('screenshots.*')" class="mt-2" />
        </div>

        {{-- Blok Screenshot Saat Ini dan Hapus --}}
        @if(isset($project) && $project->images->isNotEmpty())
        <div class="mt-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Screenshot Saat Ini:</h4>
            <div class="mt-2 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
                @foreach($project->images as $image)
                    <div class="relative group">
                        <img src="{{ Storage::url($image->image_path) }}" alt="Screenshot" class="h-24 w-full object-cover rounded-md border border-gray-300 dark:border-gray-700">
                        


                        <button type="submit" form="delete-image-form-{{ $image->id }}" 
                                class="absolute top-0 right-0 m-1 p-1 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-700"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus screenshot ini?')"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        {{-- Akhir Blok Screenshot --}}
        
        <hr class="my-6 border-gray-300 dark:border-gray-700">

        <div class="mt-4">
            <x-input-label for="demo_url" :value="__('Link Demo (Contoh: URL Vercel, YouTube, dll)')" />
            <x-text-input id="demo_url" class="block mt-1 w-full" type="url" name="demo_url" :value="old('demo_url', $project->demo_url ?? '')" placeholder="https://..." />
            <x-input-error :messages="$errors->get('demo_url')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="repository_url" :value="__('Link Repository (Contoh: GitHub, GitLab)')" />
            <x-text-input id="repository_url" class="block mt-1 w-full" type="url" name="repository_url" :value="old('repository_url', $project->repository_url ?? '')" placeholder="https://github.com/..." />
            <x-input-error :messages="$errors->get('repository_url')" class="mt-2" />
        </div>

    </div>

    <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg md:bg-transparent md:dark:bg-transparent md:p-0">
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select name="status" id="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                @php
                    // Default ke 'review' untuk proyek baru atau jika statusnya bukan 'draft'.
                    $defaultStatus = 'review'; 
                    if ($project->exists && $project->status === 'draft') {
                        $defaultStatus = 'draft';
                    }
                @endphp
                <option value="draft" @selected(old('status', $defaultStatus) == 'draft')>Draft (Simpan, jangan publikasi)</option>
                <option value="review" @selected(old('status', $defaultStatus) == 'review')>Review (Ajukan untuk direview Dosen)</option>
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="visibility" :value="__('Visibilitas')" />
            <select name="visibility" id="visibility" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="public" @selected(old('visibility', $project->visibility ?? '') == 'public')>Publik (Bisa dilihat siapa saja)</option>
                <option value="university_only" @selected(old('visibility', $project->visibility ?? '') == 'university_only')>Universitas (Hanya yang login)</option>
                <option value="private" @selected(old('visibility', $project->visibility ?? '') == 'private')>Private (Hanya Anda)</option>
            </select>
            <x-input-error :messages="$errors->get('visibility')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="supervisor_id" :value="__('Dosen Pembimbing')" />
            <select name="supervisor_id" id="supervisor_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="">-- Tidak Ada --</option>
                @foreach($lecturers as $lecturer)
                    <option value="{{ $lecturer->id }}" @selected(old('supervisor_id', $project->supervisor_id ?? '') == $lecturer->id)>
                        {{ $lecturer->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('supervisor_id')" class="mt-2" />
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <x-input-label for="semester" :value="__('Semester')" />
                <x-text-input id="semester" class="block mt-1 w-full" type="number" name="semester" :value="old('semester', $project->semester ?? '')" min="1" max="14" />
                <x-input-error :messages="$errors->get('semester')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="academic_year" :value="__('Tahun Ajaran')" />
                <x-text-input id="academic_year" class="block mt-1 w-full" type="text" name="academic_year" :value="old('academic_year', $project->academic_year ?? '')" placeholder="Contoh: 2024/2025" />
                <x-input-error :messages="$errors->get('academic_year')" class="mt-2" />
            </div>
        </div>
        
        <hr class="my-6 border-gray-300 dark:border-gray-700">

        <div class="mt-4">
            <x-input-label :value="__('Kategori Proyek')" />
            <div class="mt-2 space-y-2 max-h-40 overflow-y-auto p-2 border border-gray-300 dark:border-gray-700 rounded-md">
                @foreach($categories as $category)
                <label class="flex items-center">
                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                           @checked( in_array($category->id, old('categories', $project->categories->pluck('id')->toArray() ?? [])) )
                           class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $category->name }}</span>
                </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('categories')" class="mt-2" />
            <x-input-error :messages="$errors->get('categories.*')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label :value="__('Teknologi yang Digunakan')" />
            <div class="mt-2 space-y-2 max-h-40 overflow-y-auto p-2 border border-gray-300 dark:border-gray-700 rounded-md">
                @foreach($technologies as $technology)
                <label class="flex items-center">
                    <input type="checkbox" name="technologies[]" value="{{ $technology->id }}" 
                           @checked( in_array($technology->id, old('technologies', $project->technologies->pluck('id')->toArray() ?? [])) )
                           class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $technology->name }}</span>
                </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('technologies')" class="mt-2" />
            <x-input-error :messages="$errors->get('technologies.*')" class="mt-2" />
        </div>
    </div>
</div>

{{-- BLOK KOLABORATOR --}}
<div class="mt-6 border-t dark:border-gray-700 pt-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
        Kolaborator Proyek
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <x-input-label for="collaborators" :value="__('Tambah Kolaborator (Cari berdasarkan Nama atau Email)')" />
            <select id="collaborators" name="collaborators[]" multiple class="mt-1"></select>
            <x-input-error :messages="$errors->get('collaborators')" class="mt-2" />
        </div>
    </div>
</div>
{{-- AKHIR BLOK KOLABORATOR --}}

<div class="flex items-center justify-end mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
    <a href="{{ route('student.projects.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mr-4">
        Batal
    </a>
    <x-primary-button>
        {{ $buttonText ?? 'Simpan Proyek' }}
    </x-primary-button>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const collaboratorSelect = new TomSelect('#collaborators',{
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            create: false,
            plugins: ['remove_button'],
            load: function(query, callback) {
                if (!query.length) return callback();
                fetch('{{ route("api.users.search") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => {
                        callback(json);
                    }).catch(()=>{
                        callback();
                    });
            },
            render: {
                option: function(item, escape) {
                    return `<div>${escape(item.name)} (${escape(item.email)})</div>`;
                },
                item: function(item, escape) {
                    return `<div>${escape(item.name)}</div>`;
                }
            },
        });

        // Pre-load existing collaborators for editing
        @php
            $existingCollaborators = [];
            // Dapatkan ID kolaborator, utamakan dari input lama (setelah validasi gagal), 
            // jika tidak ada, ambil dari relasi proyek yang sudah ada.
            $collaboratorIds = old('collaborators', isset($project) ? $project->collaborators->pluck('id')->toArray() : []);

            if (!empty($collaboratorIds)) {
                // Ambil data lengkap (id, nama, email) dari user berdasarkan ID yang didapat.
                $collaborators = \App\Models\User::find($collaboratorIds);
                if ($collaborators) {
                    $existingCollaborators = $collaborators->map(function($user) {
                        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email];
                    });
                }
            }
        @endphp

        const existingCollaborators = @json($existingCollaborators);

        if (existingCollaborators.length > 0) {
            collaboratorSelect.addOptions(existingCollaborators);
            collaboratorSelect.setValue(existingCollaborators.map(c => c.id));
        }

        new TomSelect('#supervisor_id', {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    });
</script>
@endpush