<x-public-layout> 
    <div class="bg-gray-50 dark:bg-gray-800 min-h-screen">
        
        <div class="bg-white dark:bg-gray-900 shadow-md">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center md:flex-row md:items-start space-y-4 md:space-y-0 md:space-x-6">
                    <img class="h-32 w-32 rounded-full object-cover ring-4 ring-blue-500 dark:ring-blue-400"
                         src="{{ $user->photo ? Storage::url($user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=128&color=7F9CF5&background=EBF4FF' }}"
                         alt="{{ $user->name }}">
                    
                    <div class="flex-1 text-center md:text-left">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                        <p class="text-lg text-gray-500 dark:text-gray-400">@<span>{{ $user->nim }}</span></p>
                        
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            Mahasiswa Informatika Angkatan {{ $user->batch_year ?? 'N/A' }}
                        </p>

                        @if($user->profile && $user->profile->bio)
                        <p class="mt-3 max-w-2xl text-base text-gray-700 dark:text-gray-300">
                            {{ $user->profile->bio }}
                        </p>
                        @endif

                        <div class="mt-4 flex justify-center md:justify-start space-x-4">
                            @if($user->profile && $user->profile->github_url)
                            <a href="{{ $user->profile->github_url }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <span class="sr-only">GitHub</span>
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.009-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.026 2.747-1.026.546 1.379.202 2.398.1 2.65.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.001 10.001 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" /></svg>
                            </a>
                            @endif
                            @if($user->profile && $user->profile->linkedin_url)
                            <a href="{{ $user->profile->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <span class="sr-only">LinkedIn</span>
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" /></svg>
                            </a>
                            @endif
                            @if($user->profile && $user->profile->portfolio_url)
                            <a href="{{ $user->profile->portfolio_url }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <span class="sr-only">Portfolio</span>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Keahlian (Skills)</h3>
                        @if($user->profile && !empty($user->profile->skills))
                        <div class="flex flex-wrap gap-2">
                            @foreach($user->profile->skills as $skill)
                                <span class="inline-block bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full dark:bg-gray-700 dark:text-gray-300">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">User belum menambahkan keahlian.</p>
                        @endif
                    </div>
                    
                    <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Pencapaian</h3>
                        @if($user->profile && !empty($user->profile->achievements))
                        <ul class="space-y-3">
                            @foreach($user->profile->achievements as $achievement)
                                <li class="flex items-start space-x-2">
                                    <svg class="w-5 h-5 text-yellow-500 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $achievement }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">User belum menambahkan pencapaian.</p>
                        @endif
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        Proyek ({{ $user->projects->count() }})
                    </h2>
                    
                    @if($user->projects->isEmpty())
                        <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6 text-center">
                            <p class="text-gray-500 dark:text-gray-400">
                                {{ $user->name }} belum mempublikasikan proyek apapun.
                            </p>
                        </div>
                    @else
                        <div class="grid gap-6 md:grid-cols-2">
                            @foreach($user->projects as $project)
                                <x-project-card :project="$project" />
                            @endforeach
                        </div>

                        <div class="mt-8">
                            
                        </div>
                    @endif
                </div>
                
            </div>
        </div>
        
    </div>
</x-public-layout>
