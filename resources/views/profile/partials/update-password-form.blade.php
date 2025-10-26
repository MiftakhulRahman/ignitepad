<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
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

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button id="save-button">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('update_password_password');
        const passwordConfirmationInput = document.getElementById('update_password_password_confirmation');
        const passwordStrengthIndicator = document.getElementById('password-strength-indicator');
        const passwordRulesSummary = document.getElementById('password-rules-summary');
        const strengthBar1 = document.getElementById('strength-bar-1');
        const strengthBar2 = document.getElementById('strength-bar-2');
        const strengthBar3 = document.getElementById('strength-bar-3');
        const saveButton = document.getElementById('save-button');

        let isPasswordStrong = false;

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

            const hasLength = password.length >= 8;
            if (hasLength) score++; else unmetRules.push('8+ karakter');

            const hasUppercase = /[A-Z]/.test(password);
            if (hasUppercase) score++; else unmetRules.push('1+ huruf besar');

            const hasLowercase = /[a-z]/.test(password);
            if (hasLowercase) score++; else unmetRules.push('1+ huruf kecil');

            const hasNumber = /[0-9]/.test(password);
            if (hasNumber) score++; else unmetRules.push('1+ angka');

            const hasSpecial = /[^A-Za-z0-9]/.test(password);
            if (hasSpecial) score++; else unmetRules.push('1+ karakter khusus');

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

            isPasswordStrong = (score === 5);
            toggleSaveButton();
        }

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const passwordConfirmation = passwordConfirmationInput.value;

            if (password.length > 0 && passwordConfirmation.length > 0 && password !== passwordConfirmation) {
                // passwordMismatchMessage.textContent = 'Kata sandi tidak cocok.';
            } else {
                // passwordMismatchMessage.textContent = '';
            }
            toggleSaveButton();
        }

        function toggleSaveButton() {
            const isPasswordFilled = passwordInput.value.length > 0;
            const isPasswordConfirmed = (passwordInput.value === passwordConfirmationInput.value && passwordInput.value.length > 0);

            if (isPasswordStrong && isPasswordConfirmed && isPasswordFilled) {
                saveButton.removeAttribute('disabled');
                saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                saveButton.setAttribute('disabled', 'true');
                saveButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        if (passwordInput.value) {
            checkPasswordStrength();
        }
        toggleSaveButton();
    });
</script>
