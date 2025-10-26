<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Detail Portofolio') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Lengkapi profil portofolio Anda. Informasi ini akan tampil di halaman publik.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.details.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="bio" :value="__('Bio Singkat')" />
            <textarea id="bio" name="bio" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('bio', $user->profile?->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <x-input-label for="skills" :value="__('Keahlian (Skills)')" />
            <x-text-input id="skills" name="skills" type="text" class="mt-1 block w-full" 
                          :value="old('skills', $user->profile?->skills ? implode(', ', $user->profile->skills) : '')" 
                          placeholder="Contoh: Laravel, React, Figma" />
            <small class="text-gray-500 dark:text-gray-400">Pisahkan dengan koma ( , ).</small>
            <x-input-error class="mt-2" :messages="$errors->get('skills')" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="github_url" :value="__('Link GitHub')" />
                <x-text-input id="github_url" name="github_url" type="url" class="mt-1 block w-full" :value="old('github_url', $user->profile?->github_url)" placeholder="https://github.com/username" />
                <x-input-error class="mt-2" :messages="$errors->get('github_url')" />
            </div>
            
            <div>
                <x-input-label for="linkedin_url" :value="__('Link LinkedIn')" />
                <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="mt-1 block w-full" :value="old('linkedin_url', $user->profile?->linkedin_url)" placeholder="https://linkedin.com/in/username" />
                <x-input-error class="mt-2" :messages="$errors->get('linkedin_url')" />
            </div>
        </div>

        <div>
            <x-input-label for="portfolio_url" :value="__('Link Portofolio (Website Pribadi)')" />
            <x-text-input id="portfolio_url" name="portfolio_url" type="url" class="mt-1 block w-full" :value="old('portfolio_url', $user->profile?->portfolio_url)" placeholder="https://website-anda.com" />
            <x-input-error class="mt-2" :messages="$errors->get('portfolio_url')" />
        </div>

        <div>
            <x-input-label for="achievements" :value="__('Pencapaian (Achievements)')" />
            <textarea id="achievements" name="achievements" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Contoh: Juara 1 Lomba Web Design">{{ old('achievements', $user->profile?->achievements ? implode("\n", $user->profile->achievements) : '') }}</textarea>
            <small class="text-gray-500 dark:text-gray-400">Pisahkan dengan baris baru (Enter).</small>
            <x-input-error class="mt-2" :messages="$errors->get('achievements')" />
        </div>
        



        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan Detail') }}</x-primary-button>

            @if (session('status') === 'profile-details-updated')
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