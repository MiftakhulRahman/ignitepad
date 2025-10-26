@csrf
<div class="space-y-4">
    <div>
        <x-input-label for="name" :value="__('Nama Lengkap')" />
        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    
    <div>
        <x-input-label for="nim" :value="__('NIM (atau NIP/NIDN untuk Dosen)')" />
        <x-text-input id="nim" class="block mt-1 w-full" type="text" name="nim" :value="old('nim', $user->nim ?? '')" required />
        <x-input-error :messages="$errors->get('nim')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="role" :value="__('Role Akun')" />
        <select name="role" id="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            <option value="student" @selected(old('role', $user->role ?? '') == 'student')>Student (Mahasiswa)</option>
            <option value="lecturer" @selected(old('role', $user->role ?? '') == 'lecturer')>Lecturer (Dosen)</option>
            <option value="admin" @selected(old('role', $user->role ?? '') == 'admin')>Admin</option>
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>
    
    <div>
        <x-input-label for="status" :value="__('Status Akun')" />
        <select name="status" id="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            <option value="active" @selected(old('status', $user->status ?? '') == 'active')>Active</option>
            <option value="inactive" @selected(old('status', $user->status ?? '') == 'inactive')>Inactive (Nonaktif)</option>
            <option value="graduated" @selected(old('status', $user->status ?? '') == 'graduated')>Graduated (Lulus)</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="__('Password')" />
        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
        @if(isset($user->id))
            <small class="text-gray-500 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password.</small>
        @else
            <small class="text-gray-500 dark:text-gray-400">Wajib diisi untuk user baru.</small>
        @endif
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('admin.users.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mr-4">
        Batal
    </a>
    <x-primary-button>
        {{ $buttonText ?? 'Simpan' }}
    </x-primary-button>
</div>