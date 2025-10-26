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

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <div class="flex items-center space-x-2">
                <x-text-input id="username" name="username" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700" :value="old('username', $user->username)" required autocomplete="username" readonly />
                <x-secondary-button type="button" id="toggle-username-edit">{{ __('Ubah') }}</x-secondary-button>
                <x-primary-button type="button" id="save-username-button" class="hidden">{{ __('Simpan') }}</x-primary-button>
                <x-secondary-button type="button" id="cancel-username-edit-button" class="hidden">{{ __('Batal') }}</x-secondary-button>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
            <span id="username-availability-message" class="text-sm mt-2"></span>
            <small class="text-gray-500 dark:text-gray-400">Username ini akan digunakan untuk URL profil publik Anda (contoh: /@<span>{{ $user->username ?? 'john.doe' }}</span>).</small>
        </div>

        @if (Auth::user()->isStudent())
        <div class="mt-4">
            <x-input-label for="nim" :value="__('NIM (Nomor Induk Mahasiswa)')" />
            <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full" :value="old('nim', $user->nim)" autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('nim')" />
            <small class="text-gray-500 dark:text-gray-400">NIM ini akan digunakan untuk URL profil publik Anda (contoh: /@<span>{{ $user->nim ?? '123456' }}</span>).</small>
        </div>
        @endif
        
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
            <x-primary-button id="save-profile-button">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Berhasil disimpan.') }}</p>
            @endif
        </div>
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const usernameInput = document.getElementById('username');
        const usernameAvailabilityMessage = document.getElementById('username-availability-message');
        const saveProfileButton = document.getElementById('save-profile-button');
        const toggleUsernameEditButton = document.getElementById('toggle-username-edit');
        const saveUsernameButton = document.getElementById('save-username-button');
        const cancelUsernameEditButton = document.getElementById('cancel-username-edit-button');
        let initialUsername = usernameInput.value; // Store initial username

        let typingTimer;
        const doneTypingInterval = 500; // milliseconds
        let isUsernameAvailable = true; // Assume available initially for profile update
        let isUsernameEditing = false; // Track if username is currently being edited
        const currentUserId = {{ Auth::user()->id }};

        // Function to toggle username edit mode
        function toggleUsernameEditMode(enable) {
            isUsernameEditing = enable;
            if (enable) {
                usernameInput.removeAttribute('readonly');
                usernameInput.classList.remove('bg-gray-100', 'dark:bg-gray-700');
                usernameInput.focus();
                toggleUsernameEditButton.classList.add('hidden');
                saveUsernameButton.classList.remove('hidden');
                cancelUsernameEditButton.classList.remove('hidden');
                // Trigger initial check if username is not empty
                if (usernameInput.value) {
                    checkUsernameAvailability();
                }
            } else {
                usernameInput.setAttribute('readonly', 'true');
                usernameInput.classList.add('bg-gray-100', 'dark:bg-gray-700');
                usernameInput.value = initialUsername; // Revert to initial username
                toggleUsernameEditButton.classList.remove('hidden');
                saveUsernameButton.classList.add('hidden');
                cancelUsernameEditButton.classList.add('hidden');
                usernameAvailabilityMessage.textContent = '';
                usernameAvailabilityMessage.classList.remove('text-green-500', 'text-red-500');
                isUsernameAvailable = true; // Reset availability state
            }
            toggleSaveUsernameButtonState(); // Update state of save username button
        }

        // Event listener for 'Ubah' button
        toggleUsernameEditButton.addEventListener('click', function() {
            toggleUsernameEditMode(true);
        });

        // Event listener for 'Batal' button
        cancelUsernameEditButton.addEventListener('click', function() {
            toggleUsernameEditMode(false);
        });

        // Event listener for 'Simpan' username button
        saveUsernameButton.addEventListener('click', function() {
            if (saveUsernameButton.hasAttribute('disabled')) return;

            const newUsername = usernameInput.value;
            fetch('{{ route('profile.username.update') }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ username: newUsername })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message); // Or display a more subtle success message
                    initialUsername = newUsername; // Update initial username
                    toggleUsernameEditMode(false); // Exit edit mode
                } else if (data.errors) {
                    // Handle validation errors from the server
                    usernameAvailabilityMessage.textContent = data.errors.username[0];
                    usernameAvailabilityMessage.classList.remove('text-green-500');
                    usernameAvailabilityMessage.classList.add('text-red-500');
                    isUsernameAvailable = false;
                    toggleSaveUsernameButtonState();
                }
            })
            .catch(error => {
                console.error('Error updating username:', error);
                alert('Terjadi kesalahan saat memperbarui username.');
            });
        });

        if (usernameInput) {
            usernameInput.addEventListener('keyup', function () {
                if (!isUsernameEditing) return; // Only check if editing is active

                clearTimeout(typingTimer);
                if (usernameInput.value) {
                    typingTimer = setTimeout(checkUsernameAvailability, doneTypingInterval);
                } else {
                  if (isUsernameEditing) { // Added this check
                    usernameAvailabilityMessage.textContent = '';
                    usernameAvailabilityMessage.classList.remove('text-green-500', 'text-red-500');
                    isUsernameAvailable = false;
                    toggleSaveUsernameButtonState();
                  }
                }
            });
        }

        function checkUsernameAvailability() {
            const username = usernameInput.value;
            if (!username) return;

            fetch(`/check-username?username=${username}&ignore_id=${currentUserId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        usernameAvailabilityMessage.textContent = 'Username tersedia.';
                        usernameAvailabilityMessage.classList.remove('text-red-500');
                        usernameAvailabilityMessage.classList.add('text-green-500');
                        isUsernameAvailable = true;
                    } else {
                        usernameAvailabilityMessage.textContent = 'Username tidak tersedia.';
                        usernameAvailabilityMessage.classList.remove('text-green-500');
                        usernameAvailabilityMessage.classList.add('text-red-500');
                        isUsernameAvailable = false;
                    }
                    toggleSaveUsernameButtonState();
                })
                .catch(error => {
                    console.error('Error checking username availability:', error);
                    usernameAvailabilityMessage.textContent = 'Terjadi kesalahan saat memeriksa username.';
                    usernameAvailabilityMessage.classList.remove('text-green-500');
                    usernameAvailabilityMessage.classList.add('text-red-500');
                    isUsernameAvailable = false;
                    toggleSaveUsernameButtonState();
                });
        }

        function toggleSaveUsernameButtonState() {
            const isUsernameChanged = usernameInput.value !== initialUsername;
            if (isUsernameEditing && isUsernameAvailable && isUsernameChanged && usernameInput.value.length > 0) {
                saveUsernameButton.removeAttribute('disabled');
                saveUsernameButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                saveUsernameButton.setAttribute('disabled', 'true');
                saveUsernameButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // Initial state: username is readonly, main save button is enabled
        usernameInput.setAttribute('readonly', 'true');
        usernameInput.classList.add('bg-gray-100', 'dark:bg-gray-700');
        saveProfileButton.removeAttribute('disabled');
        saveProfileButton.classList.remove('opacity-50', 'cursor-not-allowed');
    });
</script>