<x-public-layout> 
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        
        {{-- Hero Section / Profile Header --}}
        <div class="relative bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-700 dark:to-purple-700">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="relative max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center md:flex-row md:items-end space-y-6 md:space-y-0 md:space-x-8">
                    {{-- Profile Picture --}}
                    <div class="relative">
                        <img class="h-32 w-32 md:h-40 md:w-40 rounded-2xl object-cover ring-4 ring-white dark:ring-gray-800 shadow-xl"
                             src="{{ $user->photo ? Storage::url($user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=160&color=7F9CF5&background=EBF4FF' }}"
                             alt="{{ $user->name }}">
                        <div class="absolute -bottom-2 -right-2 bg-green-500 h-8 w-8 rounded-full ring-4 ring-white dark:ring-gray-800"></div>
                    </div>
                    
                    {{-- Profile Info --}}
                    <div class="flex-1 text-center md:text-left">
                        <h1 class="text-4xl md:text-5xl font-bold text-white mb-2">{{ $user->name }}</h1>
                        <p class="text-xl text-blue-100 mb-3">@<span>{{ $user->username }}</span></p>
                        
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white/20 backdrop-blur-sm text-white text-sm font-medium">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                Mahasiswa Informatika
                            </span>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white/20 backdrop-blur-sm text-white text-sm font-medium">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Angkatan {{ $user->batch_year ?? 'N/A' }}
                            </span>
                        </div>

                        {{-- Social Links --}}
                        @if($user->profile && ($user->profile->github_url || $user->profile->linkedin_url || $user->profile->portfolio_url))
                        <div class="mt-4 flex justify-center md:justify-start space-x-3">
                            @if($user->profile->github_url)
                            <a href="{{ $user->profile->github_url }}" target="_blank" rel="noopener noreferrer" 
                               class="p-2.5 rounded-lg bg-white/10 backdrop-blur-sm text-white hover:bg-white/20 transition-all duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.009-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.026 2.747-1.026.546 1.379.202 2.398.1 2.65.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.001 10.001 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" /></svg>
                            </a>
                            @endif
                            @if($user->profile->linkedin_url)
                            <a href="{{ $user->profile->linkedin_url }}" target="_blank" rel="noopener noreferrer" 
                               class="p-2.5 rounded-lg bg-white/10 backdrop-blur-sm text-white hover:bg-white/20 transition-all duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" /></svg>
                            </a>
                            @endif
                            @if($user->profile->portfolio_url)
                            <a href="{{ $user->profile->portfolio_url }}" target="_blank" rel="noopener noreferrer" 
                               class="p-2.5 rounded-lg bg-white/10 backdrop-blur-sm text-white hover:bg-white/20 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                
                {{-- Sidebar --}}
                <div class="lg:col-span-1 space-y-6">
                    
                    {{-- Bio Card --}}
                    @if($user->profile && $user->profile->bio)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tentang</h3>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ $user->profile->bio }}
                        </p>
                    </div>
                    @endif

                    {{-- Skills Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keahlian</h3>
                        </div>
                        @if($user->profile && !empty($user->profile->skills))
                        <div class="flex flex-wrap gap-2">
                            @foreach($user->profile->skills as $skill)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada keahlian yang ditambahkan</p>
                        @endif
                    </div>
                    
                    {{-- Achievements Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pencapaian</h3>
                        </div>
                        @if($user->profile && !empty($user->profile->achievements))
                        <ul class="space-y-3">
                            @foreach($user->profile->achievements as $achievement)
                                <li class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center mt-0.5">
                                        <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $achievement }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada pencapaian yang ditambahkan</p>
                        @endif
                    </div>
                </div>

                {{-- Projects Section --}}
                <div class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Proyek
                            </h2>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ $user->projects->count() }}
                            </span>
                        </div>
                    </div>
                    
                    @if($user->projects->isEmpty())
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="mt-4 text-gray-500 dark:text-gray-400 text-lg">
                                {{ $user->name }} belum mempublikasikan proyek apapun
                            </p>
                        </div>
                    @else
                        <div class="grid gap-6 md:grid-cols-2">
                            @foreach($user->projects as $project)
                                <x-project-card :project="$project" />
                            @endforeach
                        </div>
                    @endif
                </div>
                
            </div>
        </div>
        
    </div>
</x-public-layout>