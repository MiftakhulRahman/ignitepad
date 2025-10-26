@csrf
<div>
    <x-input-label for="name" :value="__('Nama Teknologi')" />
    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $technology->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="slug" :value="__('Slug (URL)')" />
    <x-text-input id="slug" class="block mt-1 w-full" type="text" name="slug" :value="old('slug', $technology->slug ?? '')" />
    <small class="text-gray-500 dark:text-gray-400">Biarkan kosong untuk generate otomatis.</small>
    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
</div>

<div class="flex items-center justify-end mt-4">
    <a href="{{ route('admin.technologies.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mr-4">
        Batal
    </a>
    <x-primary-button>
        {{ $buttonText ?? 'Simpan' }}
    </x-primary-button>
</div>