@csrf
<div>
    <x-input-label for="name" :value="__('Nama Kategori')" />
    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $category->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="slug" :value="__('Slug (URL)')" />
    <x-text-input id="slug" class="block mt-1 w-full" type="text" name="slug" :value="old('slug', $category->slug ?? '')" />
    <small class="text-gray-500 dark:text-gray-400">Biarkan kosong untuk generate otomatis.</small>
    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="description" :value="__('Deskripsi')" />
    <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $category->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="flex items-center justify-end mt-4">
    <a href="{{ route('admin.categories.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mr-4">
        Batal
    </a>
    <x-primary-button>
        {{ $buttonText ?? 'Simpan' }}
    </x-primary-button>
</div>