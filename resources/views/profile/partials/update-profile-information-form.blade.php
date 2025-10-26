<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="photo" :value="__('Foto Profil')" />
            
            <div class="mt-2 flex items-center space-x-4">
                <img id="avatar-preview" 
                     class="h-20 w-20 rounded-full object-cover" 
                     src="{{ $user->photo ? Storage::url($user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=80&color=7F9CF5&background=EBF4FF' }}" 
                     alt="{{ $user->name }}">
                
                <input id="photo" name="photo" type="file" 
                       accept="image/png, image/jpeg, image/jpg, image/webp"
                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                       onchange="document.getElementById('avatar-preview').src = window.URL.createObjectURL(this.files[0])">
            </div>
            <small class="text-gray-500 dark:text-gray-400">Maks 2MB. Kosongkan jika tidak ingin mengubah.</small>
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="mt-4">
            <x-input-label for="nim" :value="__('NIM (Nomor Induk Mahasiswa)')" />
            <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full" :value="old('nim', $user->nim)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('nim')" />
            <small class="text-gray-500 dark:text-gray-400">NIM ini akan digunakan untuk URL profil publik Anda (contoh: /@<span>{{ $user->nim ?? '123456' }}</span>).</small>
        </div>
        
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Nomor Telepon')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="mt-4">
            <x-input-label for="batch_year" :value="__('Tahun Angkatan')" />
            <x-text-input id="batch_year" name="batch_year" type="number" class="mt-1 block w-full" :value="old('batch_year', $user->batch_year)" placeholder="Contoh: 2021" />
            <x-input-error class="mt-2" :messages="$errors->get('batch_year')" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>