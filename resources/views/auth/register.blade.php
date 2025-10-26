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
            <div id="password-strength-container" class="hidden mt-4">
                <div id="password-strength-indicator" class="mt-2 text-sm"></div>
                <div class="flex mt-2 space-x-1">
                    <span id="strength-bar-1" class="h-2 w-1/3 rounded-full bg-gray-300"></span>
                    <span id="strength-bar-2" class="h-2 w-1/3 rounded-full bg-gray-300"></span>
                    <span id="strength-bar-3" class="h-2 w-1/3 rounded-full bg-gray-300"></span>
                </div>
                <span id="password-rules-summary" class="mt-2 text-sm text-gray-600 dark:text-gray-400"></span>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            <span id="password-mismatch-message" class="mt-2 text-sm text-red-500"></span>
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
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('password_confirmation');
        const passwordStrengthIndicator = document.getElementById('password-strength-indicator');
        const passwordRulesSummary = document.getElementById('password-rules-summary');
        const passwordMismatchMessage = document.getElementById('password-mismatch-message');
        const registerButton = document.getElementById('register-button');

        // Password Strength Bar elements
        const strengthBar1 = document.getElementById('strength-bar-1');
        const strengthBar2 = document.getElementById('strength-bar-2');
        const strengthBar3 = document.getElementById('strength-bar-3');

        let typingTimer;
        const doneTypingInterval = 500; // milliseconds
        let isUsernameAvailable = false;
        let isPasswordStrong = false;
        
        // --- Username Check Logic (existing) ---
        // ... (this part remains largely unchanged)
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

        // --- Password Strength Logic ---
        if (passwordInput && passwordConfirmationInput) {
            passwordInput.addEventListener('focus', () => {
                document.getElementById('password-strength-container').classList.remove('hidden');
            });
            passwordInput.addEventListener('keyup', checkPasswordStrength);
            passwordConfirmationInput.addEventListener('keyup', checkPasswordMatchAndStrength);
        }

        function checkPasswordMatchAndStrength() {
            checkPasswordMatch();
            checkPasswordStrength();
        }

        function checkPasswordStrength() {
            const password = passwordInput.value;
            let score = 0;
            let unmetRules = [];

            // Rule 1: Minimum 8 characters
            const hasLength = password.length >= 8;
            if (hasLength) score++; else unmetRules.push('8+ karakter');

            // Rule 2: Minimal 1 huruf besar (A-Z)
            const hasUppercase = /[A-Z]/.test(password);
            if (hasUppercase) score++; else unmetRules.push('1+ huruf besar');

            // Rule 3: Minimal 1 huruf kecil (a-z)
            const hasLowercase = /[a-z]/.test(password);
            if (hasLowercase) score++; else unmetRules.push('1+ huruf kecil');

            // Rule 4: Minimal 1 angka (0-9)
            const hasNumber = /[0-9]/.test(password);
            if (hasNumber) score++; else unmetRules.push('1+ angka');

            // Rule 5: Minimal 1 karakter khusus
            const hasSpecial = /[^A-Za-z0-9]/.test(password);
            if (hasSpecial) score++; else unmetRules.push('1+ karakter khusus');

            // Update concise rules summary
            if (unmetRules.length > 0 && password.length > 0) {
                passwordRulesSummary.textContent = 'Kata sandi minimal 8 karakter, mengandung huruf besar, angka, dan karakter khusus.';
                passwordRulesSummary.classList.remove('text-green-500');
                passwordRulesSummary.classList.add('text-red-500');
            } else if (password.length > 0) {
                passwordRulesSummary.textContent = 'Semua syarat terpenuhi.';
                passwordRulesSummary.classList.remove('text-red-500');
                passwordRulesSummary.classList.add('text-green-500');
            } else {
                passwordRulesSummary.textContent = '';
                passwordRulesSummary.classList.remove('text-red-500', 'text-green-500');
            }

            // Update strength bar
            strengthBar1.classList.remove('bg-red-500', 'bg-orange-500', 'bg-green-500');
            strengthBar2.classList.remove('bg-red-500', 'bg-orange-500', 'bg-green-500');
            strengthBar3.classList.remove('bg-red-500', 'bg-orange-500', 'bg-green-500');

            if (password.length === 0) {
                strengthBar1.classList.add('bg-gray-300');
                strengthBar2.classList.add('bg-gray-300');
                strengthBar3.classList.add('bg-gray-300');
                passwordStrengthIndicator.textContent = '';
            } else if (score < 3) {
                strengthBar1.classList.add('bg-red-500');
                strengthBar2.classList.add('bg-gray-300');
                strengthBar3.classList.add('bg-gray-300');
                passwordStrengthIndicator.textContent = 'Lemah';
                passwordStrengthIndicator.className = 'mt-2 text-sm text-red-500';
            } else if (score < 5) {
                strengthBar1.classList.add('bg-orange-500');
                strengthBar2.classList.add('bg-orange-500');
                strengthBar3.classList.add('bg-gray-300');
                passwordStrengthIndicator.textContent = 'Sedang';
                passwordStrengthIndicator.className = 'mt-2 text-sm text-orange-500';
            } else {
                strengthBar1.classList.add('bg-green-500');
                strengthBar2.classList.add('bg-green-500');
                strengthBar3.classList.add('bg-green-500');
                passwordStrengthIndicator.textContent = 'Kuat';
                passwordStrengthIndicator.className = 'mt-2 text-sm text-green-500';
            }

            // Only set isPasswordStrong to true if all rules are met AND passwords match
            // The match check is handled by checkPasswordMatch
            isPasswordStrong = (score === 5);
            toggleRegisterButton();
        }

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const passwordConfirmation = passwordConfirmationInput.value;

            if (password.length > 0 && passwordConfirmation.length > 0 && password !== passwordConfirmation) {
                passwordMismatchMessage.textContent = 'Kata sandi tidak cocok.';
            } else {
                passwordMismatchMessage.textContent = '';
            }
            toggleRegisterButton();
        }

        // --- Combined Button Toggle Logic ---
        function toggleRegisterButton() {
            const isUsernameFilled = usernameInput.value.length > 0;
            const isPasswordFilled = passwordInput.value.length > 0;
            const isPasswordConfirmed = (passwordInput.value === passwordConfirmationInput.value && passwordInput.value.length > 0);

            if (isUsernameAvailable && isUsernameFilled && isPasswordStrong && isPasswordConfirmed && isPasswordFilled) {
                registerButton.removeAttribute('disabled');
                registerButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                registerButton.setAttribute('disabled', 'true');
                registerButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // Initial checks on page load
        if (usernameInput.value) {
            checkUsernameAvailability();
        }
        // Initial password check only if password field has value
        if (passwordInput.value) {
            checkPasswordStrength();
        }
        toggleRegisterButton(); // Ensure button state is correct on load
    });
</script>
