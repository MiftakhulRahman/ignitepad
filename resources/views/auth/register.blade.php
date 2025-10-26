<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="mt-4">
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
            <span id="username-availability-message" class="text-sm mt-2"></span>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4" id="register-button">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const usernameInput = document.getElementById('username');
        const usernameAvailabilityMessage = document.getElementById('username-availability-message');
        const registerButton = document.getElementById('register-button');
        let typingTimer;
        const doneTypingInterval = 500; // milliseconds
        let isUsernameAvailable = false;

        if (usernameInput) {
            usernameInput.addEventListener('keyup', function () {
                clearTimeout(typingTimer);
                if (usernameInput.value) {
                    typingTimer = setTimeout(checkUsernameAvailability, doneTypingInterval);
                } else {
                    usernameAvailabilityMessage.textContent = '';
                    usernameAvailabilityMessage.classList.remove('text-green-500', 'text-red-500');
                    isUsernameAvailable = false;
                    toggleRegisterButton();
                }
            });
        }

        function checkUsernameAvailability() {
            const username = usernameInput.value;
            if (!username) return;

            fetch(`/check-username?username=${username}`)
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
                    toggleRegisterButton();
                })
                .catch(error => {
                    console.error('Error checking username availability:', error);
                    usernameAvailabilityMessage.textContent = 'Terjadi kesalahan saat memeriksa username.';
                    usernameAvailabilityMessage.classList.remove('text-green-500');
                    usernameAvailabilityMessage.classList.add('text-red-500');
                    isUsernameAvailable = false;
                    toggleRegisterButton();
                });
        }

        function toggleRegisterButton() {
            if (isUsernameAvailable && usernameInput.value.length > 0) {
                registerButton.removeAttribute('disabled');
                registerButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                registerButton.setAttribute('disabled', 'true');
                registerButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // Initial check on page load if username field has a value (e.g., old input)
        if (usernameInput.value) {
            checkUsernameAvailability();
        } else {
            toggleRegisterButton(); // Disable button initially if username is empty
        }
    });
</script>
