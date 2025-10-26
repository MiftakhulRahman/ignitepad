<x-public-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">

            {{-- x-data Utama untuk Like/Bookmark & Tab --}}
            <div x-data="{
                isLoggedIn: {{ auth()->check() ? 'true' : 'false' }},
                isLiked: {{ $isLiked ? 'true' : 'false' }},
                isBookmarked: {{ $isBookmarked ? 'true' : 'false' }},
                likeCount: {{ $project->like_count }},
                tab: 'deskripsi',
            
                requireLogin() {
                    if (!this.isLoggedIn) {
                        window.location.href = '{{ route('login') }}';
                        return false;
                    }
                    return true;
                },
            
                async toggleLike() {
                    if (!this.requireLogin()) return;
                    this.isLiked = !this.isLiked;
                    this.likeCount += this.isLiked ? 1 : -1;
            
                    try {
                        let response = await fetch('{{ route('api.projects.like', $project) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            }
                        });
                        let data = await response.json();
                        this.isLiked = data.liked;
                        this.likeCount = data.count;
                    } catch (error) {
                        console.error('Error toggling like:', error);
                    }
                },
            
                async toggleBookmark() {
                    if (!this.requireLogin()) return;
                    this.isBookmarked = !this.isBookmarked;
            
                    try {
                        let response = await fetch('{{ route('api.projects.bookmark', $project) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            }
                        });
                        let data = await response.json();
                        this.isBookmarked = data.bookmarked;
                    } catch (error) {
                        console.error('Error toggling bookmark:', error);
                    }
                }
            }">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

                    {{-- Kolom Konten Utama --}}
                    <div class="lg:col-span-2 space-y-6">
                        
                        {{-- Header Card --}}
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 lg:p-8">
                            <div class="flex items-start justify-between gap-4 mb-6">
                                <div class="flex-1">
                                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                                        {{ $project->title }}
                                    </h1>
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-medium">
                                            Semester {{ $project->semester ?? 'N/A' }}
                                        </span>
                                        <span>•</span>
                                        <span>T.A {{ $project->academic_year ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex items-center gap-2">
                                    <button @click="toggleLike"
                                        :class="{
                                            'bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 ring-2 ring-red-200 dark:ring-red-800': isLiked,
                                            'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600': !isLiked
                                        }"
                                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl transition-all duration-200 font-medium text-sm">
                                        <svg class="w-5 h-5" :class="{ 'fill-current': isLiked }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <span x-text="likeCount"></span>
                                    </button>

                                    <button @click="toggleBookmark"
                                        :class="{
                                            'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 ring-2 ring-blue-200 dark:ring-blue-800': isBookmarked,
                                            'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600': !isBookmarked
                                        }"
                                        class="p-2 rounded-xl transition-all duration-200">
                                        <svg class="w-5 h-5" :class="{ 'fill-current': isBookmarked }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Thumbnail --}}
                            <div class="rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 aspect-video shadow-sm">
                                @if ($project->thumbnail)
                                    <img src="{{ Storage::url($project->thumbnail) }}" alt="{{ $project->title }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-1.586-1.586a2 2 0 00-2.828 0L6 14m6-6l.01.01"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Tab Navigation --}}
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                            <div class="border-b border-gray-200 dark:border-gray-700">
                                <nav class="flex px-6" aria-label="Tabs">
                                    <button @click="tab = 'deskripsi'"
                                        :class="{
                                            'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'deskripsi',
                                            'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': tab !== 'deskripsi'
                                        }"
                                        class="py-4 px-1 border-b-2 font-semibold text-sm mr-8 transition-colors">
                                        Deskripsi
                                    </button>
                                    <button @click="tab = 'screenshots'"
                                        :class="{
                                            'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'screenshots',
                                            'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': tab !== 'screenshots'
                                        }"
                                        class="py-4 px-1 border-b-2 font-semibold text-sm mr-8 transition-colors">
                                        Screenshots <span class="ml-1 text-xs">({{ $project->images->count() }})</span>
                                    </button>
                                    <button @click="tab = 'review'"
                                        :class="{
                                            'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'review',
                                            'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': tab !== 'review'
                                        }"
                                        class="py-4 px-1 border-b-2 font-semibold text-sm transition-colors">
                                        Review <span class="ml-1 text-xs">({{ $project->reviews->where('status', 'approved')->count() }})</span>
                                    </button>
                                </nav>
                            </div>

                            {{-- Tab Content --}}
                            <div x-data="{
                                lightboxOpen: false,
                                lightboxImage: '',
                                lightboxIndex: 0,
                                images: {{ json_encode($project->images->pluck('image_path')->map(fn($path) => Storage::url($path))) }},

                                openLightbox(index) {
                                    if (this.images.length === 0) return;
                                    this.lightboxIndex = index;
                                    this.lightboxImage = this.images[index];
                                    this.lightboxOpen = true;
                                    document.body.style.overflow = 'hidden';
                                },
                                closeLightbox() {
                                    this.lightboxOpen = false;
                                    document.body.style.overflow = '';
                                },
                                nextImage() {
                                    this.lightboxIndex = (this.lightboxIndex + 1) % this.images.length;
                                    this.lightboxImage = this.images[this.lightboxIndex];
                                },
                                prevImage() {
                                    this.lightboxIndex = (this.lightboxIndex - 1 + this.images.length) % this.images.length;
                                    this.lightboxImage = this.images[this.lightboxIndex];
                                }
                            }" @keydown.arrow-right.window="lightboxOpen && nextImage()" @keydown.arrow-left.window="lightboxOpen && prevImage()">

                                <div class="p-6">
                                    {{-- Tab Deskripsi --}}
                                    <div x-show="tab === 'deskripsi'" class="prose prose-gray dark:prose-invert max-w-none">
                                        <div class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                            {!! nl2br(e($project->description)) !!}
                                        </div>
                                    </div>

                                    {{-- Tab Screenshots --}}
                                    <div x-show="tab === 'screenshots'" style="display: none;">
                                        @if ($project->images->isEmpty())
                                            <div class="text-center py-12">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-1.586-1.586a2 2 0 00-2.828 0L6 14m6-6l.01.01"></path>
                                                </svg>
                                                <p class="mt-2 text-gray-500 dark:text-gray-400">Tidak ada screenshot untuk proyek ini</p>
                                            </div>
                                        @else
                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                                @foreach ($project->images as $index => $image)
                                                    <div @click="openLightbox({{ $index }})" 
                                                        class="relative group cursor-pointer rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 aspect-video">
                                                        <img src="{{ Storage::url($image->image_path) }}" alt="Screenshot {{ $index + 1 }}"
                                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                                                            <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Tab Review --}}
                                    <div x-show="tab === 'review'" style="display: none;">
                                        @auth
                                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-5 mb-6">
                                                <form action="{{ route('projects.reviews.store', $project) }}" method="POST" class="space-y-4">
                                                    @csrf
                                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Tulis Review</h3>

                                                    <div>
                                                        <label for="rating" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rating</label>
                                                        <select name="rating" id="rating"
                                                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                            <option value="">-- Pilih Rating --</option>
                                                            <option value="5">⭐️⭐️⭐️⭐️⭐️ Sangat Bagus</option>
                                                            <option value="4">⭐️⭐️⭐️⭐️ Bagus</option>
                                                            <option value="3">⭐️⭐️⭐️ Cukup</option>
                                                            <option value="2">⭐️⭐️ Kurang</option>
                                                            <option value="1">⭐️ Buruk</option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <textarea name="comment" rows="4"
                                                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                                            placeholder="Bagikan pendapat Anda tentang proyek ini...">{{ old('comment') }}</textarea>
                                                        <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                                                        <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                                                    </div>
                                                    
                                                    <x-primary-button>Kirim Review</x-primary-button>
                                                </form>
                                            </div>
                                        @else
                                            <div class="bg-blue-50 dark:bg-blue-900/30 rounded-xl p-6 mb-6 text-center">
                                                <p class="text-gray-700 dark:text-gray-300">
                                                    <a href="{{ route('login') }}" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">Login</a> untuk memberikan review
                                                </p>
                                            </div>
                                        @endauth

                                        @if (session('success'))
                                            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-xl">
                                                {{ session('success') }}
                                            </div>
                                        @endif

                                        <div class="space-y-4">
                                            @forelse ($project->reviews->where('status', 'approved') as $review)
                                                <div class="flex gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50">
                                                    <img class="h-10 w-10 rounded-full object-cover flex-shrink-0"
                                                        src="{{ $review->user->photo ? Storage::url($review->user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($review->user->name) . '&color=7F9CF5&background=EBF4FF' }}"
                                                        alt="{{ $review->user->name }}">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center justify-between gap-2 mb-1">
                                                            <div class="flex items-center gap-2">
                                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $review->user->name }}</span>
                                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                                                            </div>
                                                            @if ($review->rating)
                                                                <span class="text-yellow-500 text-sm font-medium">{{ $review->rating }} ⭐️</span>
                                                            @endif
                                                        </div>
                                                        <p class="text-gray-700 dark:text-gray-300 text-sm">{{ $review->comment }}</p>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-12">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                    <p class="mt-2 text-gray-500 dark:text-gray-400">Belum ada review</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                {{-- Lightbox --}}
                                <div x-show="lightboxOpen" style="display: none;"
                                    x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 backdrop-blur-sm"
                                    @keydown.escape.window="closeLightbox()"
                                    @click.self="closeLightbox()">
                                    
                                    <button @click="closeLightbox()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-50 p-2">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>

                                    <button @click.stop="prevImage()" x-show="images.length > 1" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:scale-110 transition-transform bg-black bg-opacity-50 rounded-full p-3 z-50">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                    </button>

                                    <div class="relative max-w-6xl max-h-[90vh] mx-auto p-4" @click.stop>
                                        <img :src="lightboxImage" alt="Lightbox Image" class="max-w-full max-h-full object-contain rounded-lg">
                                    </div>

                                    <button @click.stop="nextImage()" x-show="images.length > 1" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:scale-110 transition-transform bg-black bg-opacity-50 rounded-full p-3 z-50">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Sidebar --}}
                    <div class="lg:col-span-1 space-y-6">

                        {{-- Action Buttons --}}
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-3">
                            @if ($project->demo_url)
                                <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                                    class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl transition-all duration-200 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Lihat Demo
                                </a>
                            @endif
                            @if ($project->repository_url)
                                <a href="{{ $project->repository_url }}" target="_blank" rel="noopener noreferrer"
                                    class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-semibold rounded-xl transition-all duration-200">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.009-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.026 2.747-1.026.546 1.379.202 2.398.1 2.65.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.001 10.001 0 0022 12.017C22 6.484 17.522 2 12 2z"></path>
                                    </svg>
                                    Repository
                                </a>
                            @endif
                        </div>

                        {{-- Pembuat Proyek --}}
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Pembuat Proyek</h3>
                            <a href="{{ route('profile.public.show', $project->user) }}" class="flex items-center gap-3 group">
                                <img class="h-12 w-12 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700 group-hover:ring-blue-500 transition"
                                    src="{{ $project->user->photo ? Storage::url($project->user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($project->user->name).'&color=7F9CF5&background=EBF4FF' }}"
                                    alt="{{ $project->user->name }}">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition truncate">{{ $project->user->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->user->nim ?? 'NIM tidak tersedia' }}</p>
                                </div>
                            </a>
                        </div>

                        {{-- Dosen Pembimbing --}}
                        @if ($project->supervisor)
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Dosen Pembimbing</h3>
                                <a href="{{ route('profile.public.show', $project->supervisor) }}" class="flex items-center gap-3 group">
                                    <img class="h-12 w-12 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700 group-hover:ring-blue-500 transition"
                                        src="{{ $project->supervisor->photo ? Storage::url($project->supervisor->photo) : 'https://ui-avatars.com/api/?name='.urlencode($project->supervisor->name).'&color=7F9CF5&background=EBF4FF' }}"
                                        alt="{{ $project->supervisor->name }}">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition truncate">{{ $project->supervisor->name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Dosen Pembimbing</p>
                                    </div>
                                </a>
                            </div>
                        @endif

                        {{-- Tim Kolaborator --}}
                        @if ($project->collaborators->count() > 1)
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Tim Kolaborator</h3>
                                <div class="space-y-3">
                                    @foreach ($project->collaborators as $collaborator)
                                        <a href="{{ route('profile.public.show', $collaborator) }}" class="flex items-center gap-3 group">
                                            <img class="h-10 w-10 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700 group-hover:ring-blue-500 transition"
                                                src="{{ $collaborator->photo ? Storage::url($collaborator->photo) : 'https://ui-avatars.com/api/?name='.urlencode($collaborator->name).'&color=7F9CF5&background=EBF4FF' }}"
                                                alt="{{ $collaborator->name }}">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition truncate">
                                                    {{ $collaborator->name }}
                                                    @if ($collaborator->id === $project->user_id)
                                                        <span class="text-xs text-blue-600 dark:text-blue-400">(Pemilik)</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Kategori & Teknologi --}}
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Kategori</h3>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($project->categories as $category)
                                        <a href="{{ route('categories.show', $category) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                            {{ $category->name }}
                                        </a>
                                    @empty
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Tidak ada kategori</span>
                                    @endforelse
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Teknologi</h3>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($project->technologies as $technology)
                                        <a href="{{ route('technologies.show', $technology) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                            {{ $technology->name }}
                                        </a>
                                    @empty
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Tidak ada teknologi</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-public-layout>