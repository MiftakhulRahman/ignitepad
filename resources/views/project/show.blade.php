<x-public-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="bg-white dark:bg-gray-900 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- x-data Utama untuk Like/Bookmark & Tab --}}
            <div x-data="{
                isLoggedIn: {{ auth()->check() ? 'true' : 'false' }},
                isLiked: {{ $isLiked ? 'true' : 'false' }},
                isBookmarked: {{ $isBookmarked ? 'true' : 'false' }},
                likeCount: {{ $project->like_count }},
                tab: 'deskripsi', // <-- State untuk tab
            
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

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    {{-- Kolom Konten Utama (Kiri) --}}
                    <div class="lg:col-span-2">
                        <div class="mb-4">
                            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                                {{ $project->title }}
                            </h1>
                            <p class="mt-2 text-lg text-gray-500 dark:text-gray-400">
                                Semester {{ $project->semester ?? 'N/A' }} - T.A {{ $project->academic_year ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="mb-6 rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-700 aspect-video">
                            @if ($project->thumbnail)
                                <img src="{{ Storage::url($project->thumbnail) }}" alt="{{ $project->title }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div
                                    class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-1.586-1.586a2 2 0 00-2.828 0L6 14m6-6l.01.01">
                                        </path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Tombol Like & Bookmark (Tidak Berubah) --}}
                        <div class="flex items-center space-x-4 mb-6">
                            <button @click="toggleLike"
                                :class="{
                                            'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300': isLiked,
                                            'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700':
                                                !isLiked
                                        }"
                                class="flex items-center space-x-2 px-4 py-2 rounded-md transition-colors">
                                <svg class="w-5 h-5" :class="{ 'fill-current': isLiked }" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                    </path>
                                </svg>
                                <span x-text="isLiked ? 'Liked' : 'Like'"></span>
                                <span x-text="`(${likeCount})`"></span>
                            </button>

                            <button @click="toggleBookmark"
                                :class="{
                                            'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300': isBookmarked,
                                            'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700':
                                                !isBookmarked
                                        }"
                                class="flex items-center space-x-2 px-4 py-2 rounded-md transition-colors">
                                <svg class="w-5 h-5" :class="{ 'fill-current': isBookmarked }" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z">
                                    </path>
                                </svg>
                                <span x-text="isBookmarked ? 'Disimpan' : 'Simpan'"></span>
                            </button>
                        </div>

                        {{-- Navigasi Tab (Tidak Berubah) --}}
                        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <a href="#" @click.prevent="tab = 'deskripsi'"
                                    :class="{
                                            'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400': tab === 'deskripsi',
                                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': tab !== 'deskripsi'
                                        }"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Deskripsi
                                </a>
                                <a href="#" @click.prevent="tab = 'screenshots'"
                                    :class="{
                                            'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400': tab === 'screenshots',
                                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': tab !== 'screenshots'
                                        }"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Screenshots ({{ $project->images->count() }})
                                </a>
                                <a href="#" @click.prevent="tab = 'review'"
                                    :class="{
                                            'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400': tab === 'review',
                                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': tab !== 'review'
                                        }"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Review & Komentar ({{ $project->reviews->where('status', 'approved')->count() }})
                                </a>
                            </nav>
                        </div>

                        {{-- ================================== --}}
                        {{-- KONTEN TAB dengan LIGHTBOX (Tidak Berubah) --}}
                        {{-- ================================== --}}
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
                                document.body.style.overflow = 'hidden'; // Stop background scroll
                            },
                            closeLightbox() {
                                this.lightboxOpen = false;
                                document.body.style.overflow = ''; // Allow scroll again
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
                        {{-- Akhir x-data lightbox --}}

                            {{-- Konten Tab Deskripsi (Tidak Berubah) --}}
                            <div x-show="tab === 'deskripsi'" class="prose prose-lg dark:prose-invert max-w-none">
                                {!! nl2br(e($project->description)) !!}
                            </div>

                            {{-- Konten Tab Screenshots (Tidak Berubah) --}}
                            <div x-show="tab === 'screenshots'" style="display: none;">
                                @if ($project->images->isEmpty())
                                    <p class="text-gray-500 dark:text-gray-400">Tidak ada screenshot untuk proyek ini.</p>
                                @else
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                        @foreach ($project->images as $index => $image)
                                            <div @click="openLightbox({{ $index }})" class="cursor-pointer">
                                                <img src="{{ Storage::url($image->image_path) }}" alt="Screenshot {{ $index + 1 }}"
                                                    class="w-full h-auto object-cover rounded-lg shadow-md border border-gray-200 dark:border-gray-700 hover:opacity-80 transition-opacity">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Konten Tab Review (Tidak Berubah) --}}
                            <div x-show="tab === 'review'" style="display: none;">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                                    Review & Komentar
                                </h2>

                                @auth
                                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg mb-6">
                                        <form action="{{ route('projects.reviews.store', $project) }}" method="POST">
                                            @csrf
                                            <h3 class="font-medium text-gray-900 dark:text-gray-100">Beri komentar:</h3>

                                            <div class="my-2">
                                                <label for="rating" class="text-sm text-gray-600 dark:text-gray-400">Rating
                                                    (Opsional):</label>
                                                <select name="rating" id="rating"
                                                    class="mt-1 block w-full max-w-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                                    <option value="">-- Beri Rating --</option>
                                                    <option value="5">⭐️⭐️⭐️⭐️⭐️ (Sangat Bagus)</option>
                                                    <option value="4">⭐️⭐️⭐️⭐️ (Bagus)</option>
                                                    <option value="3">⭐️⭐️⭐️ (Cukup)</option>
                                                    <option value="2">⭐️⭐️ (Kurang)</option>
                                                    <option value="1">⭐️ (Buruk)</option>
                                                </select>
                                            </div>

                                            <div>
                                                <textarea name="comment" rows="4"
                                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                    placeholder="Tulis komentar Anda di sini...">{{ old('comment') }}</textarea>
                                                <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                                                <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                                            </div>
                                            <div class="mt-3">
                                                <x-primary-button>Kirim Komentar</x-primary-button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg mb-6 text-center">
                                        <p class="text-gray-700 dark:text-gray-300">
                                            <a href="{{ route('login') }}"
                                                class="font-medium text-blue-600 dark:text-blue-400 hover:underline">Login</a>
                                            untuk memberi komentar.
                                        </p>
                                    </div>
                                @endauth

                                @if (session('success'))
                                    <div
                                        class="mb-4 p-4 bg-green-100 text-green-700 rounded-md dark:bg-green-900 dark:text-green-300">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                <div class="space-y-6">
                                    @forelse ($project->reviews as $review)
                                        @if ($review->status == 'approved')
                                            <div class="flex space-x-4">
                                                <img class="h-10 w-10 rounded-full object-cover"
                                                    src="{{ $review->user->photo ? Storage::url($review->user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($review->user->name) . '&color=7F9CF5&background=EBF4FF' }}"
                                                    alt="{{ $review->user->name }}">
                                                <div class="flex-1">
                                                    <div class="flex justify-between items-center">
                                                        <div>
                                                            <span
                                                                class="font-bold text-gray-900 dark:text-white">{{ $review->user->name }}</span>
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">·
                                                                {{ $review->created_at->diffForHumans() }}</span>
                                                        </div>
                                                        @if ($review->rating)
                                                            <span class="text-yellow-500 flex items-center">
                                                                {{ $review->rating }} ⭐️
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="mt-2 text-gray-700 dark:text-gray-300">
                                                        {{ $review->comment }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="text-gray-500 dark:text-gray-400 text-center py-4">
                                            Jadilah yang pertama memberi komentar.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- ================================== --}}
                            {{-- LIGHTBOX OVERLAY (Tidak Berubah) --}}
                            {{-- ================================== --}}
                            <div x-show="lightboxOpen" style="display: none;"
                                x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
                                @keydown.escape.window="closeLightbox()"
                                @click.self="closeLightbox()"> {{-- Close saat klik background --}}
                                {{-- Tombol Close (X) --}}
                                <button @click="closeLightbox()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-50">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>

                                {{-- Tombol Navigasi Kiri --}}
                                <button @click.stop="prevImage()" x-show="images.length > 1" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2 z-50">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                </button>

                                {{-- Kontainer Gambar --}}
                                <div class="relative max-w-4xl max-h-[80vh] mx-auto p-4" @click.stop> {{-- stop agar klik gambar tidak close --}}
                                    <img :src="lightboxImage" alt="Lightbox Image" class="max-w-full max-h-full object-contain">
                                </div>

                                {{-- Tombol Navigasi Kanan --}}
                                <button @click.stop="nextImage()" x-show="images.length > 1" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2 z-50">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                            </div>
                            {{-- ================================== --}}

                        </div> {{-- Akhir x-data lightbox --}}

                    </div> {{-- Akhir Kolom Konten Utama (Kiri) --}}

                    {{-- Kolom Samping (Sidebar) --}}
                    <div class="lg:col-span-1 space-y-6">

                        {{-- Tombol Demo & Repo (Tidak Berubah) --}}
                        <div class="space-y-3">
                            @if ($project->demo_url)
                                <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                                    class="w-full flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                        </path>
                                    </svg>
                                    Lihat Demo
                                </a>
                            @endif
                            @if ($project->repository_url)
                                <a href="{{ $project->repository_url }}" target="_blank" rel="noopener noreferrer"
                                    class="w-full flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483
                                                0-.237-.009-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343
                                                -.454-1.158-1.11-1.466-1.11-1.466
                                                -.908-.62.069-.608.069-.608
                                                1.003.07 1.531 1.032 1.531 1.032
                                                .892 1.53 2.341 1.088 2.91.832
                                                .092-.647.35-1.088.636-1.338
                                                -2.22-.253-4.555-1.113-4.555-4.951
                                                0-1.093.39-1.988 1.029-2.688
                                                -.103-.253-.446-1.272.098-2.65
                                                0 0 .84-.27 2.75 1.026
                                                A9.564 9.564 0 0112 6.844
                                                c.85.004 1.705.115 2.504.337
                                                1.909-1.296 2.747-1.026 2.747-1.026
                                                .546 1.379.202 2.398.1 2.65
                                                .64.7 1.028 1.595 1.028 2.688
                                                0 3.848-2.339 4.695-4.566 4.943
                                                .359.309.678.92.678 1.855
                                                0 1.338-.012 2.419-.012 2.747
                                                0 .268.18.58.688.482A10.001 10.001 0 0022 12.017C22 6.484 17.522 2 12 2z">
                                        </path>
                                    </svg>
                                    Lihat Repository
                                </a>
                            @endif
                        </div>

                        {{-- Pembuat Proyek (Diperbarui) --}}
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Pembuat Proyek</h3>
                            <a href="{{ route('profile.public.show', $project->user) }}" class="flex items-center group">
                                <img class="h-12 w-12 rounded-full object-cover mr-3 group-hover:ring-2 group-hover:ring-blue-500 transition"
                                    src="{{ $project->user->photo ? Storage::url($project->user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($project->user->name).'&color=7F9CF5&background=EBF4FF' }}"
                                    alt="{{ $project->user->name }}">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ $project->user->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->user->nim ?? 'NIM tidak tersedia' }}</p>
                                </div>
                            </a>
                        </div>

                        {{-- Dosen Pembimbing (Diperbarui) --}}
                        @if ($project->supervisor)
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Dosen Pembimbing</h3>
                                <a href="{{ route('profile.public.show', $project->supervisor) }}" class="flex items-center group">
                                    <img class="h-12 w-12 rounded-full object-cover mr-3 group-hover:ring-2 group-hover:ring-blue-500 transition"
                                        src="{{ $project->supervisor->photo ? Storage::url($project->supervisor->photo) : 'https://ui-avatars.com/api/?name='.urlencode($project->supervisor->name).'&color=7F9CF5&background=EBF4FF' }}"
                                        alt="{{ $project->supervisor->name }}">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ $project->supervisor->name }}</p>
                                        {{-- Dosen mungkin tidak punya NIM --}}
                                    </div>
                                </a>
                            </div>
                        @endif

                        {{-- Kolaborator (Diperbarui) --}}
                        <div class="lg:col-span-1 space-y-6">
                            @if ($project->collaborators->count() > 1) {{-- Tampilkan jika ada > 1 (pemilik + min 1 kolaborator) --}}
                                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Tim Kolaborator</h3>
                                    <ul class="space-y-3">
                                        @foreach ($project->collaborators as $collaborator)
                                            {{-- Tampilkan semua, termasuk pemilik --}}
                                            <li>
                                                <a href="{{ route('profile.public.show', $collaborator) }}" class="flex items-center group">
                                                    <img class="h-10 w-10 rounded-full object-cover mr-3 group-hover:ring-2 group-hover:ring-blue-500 transition"
                                                        src="{{ $collaborator->photo ? Storage::url($collaborator->photo) : 'https://ui-avatars.com/api/?name='.urlencode($collaborator->name).'&color=7F9CF5&background=EBF4FF' }}"
                                                        alt="{{ $collaborator->name }}">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ $collaborator->name }}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $collaborator->id === $project->user_id ? '(Pemilik)' : '' }}
                                                        </p>
                                                    </div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        {{-- Kategori & Teknologi (Tidak Berubah) --}}
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <div class="mb-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Kategori</h3>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($project->categories as $category)
                                        <a href="{{ route('categories.show', $category) }}"
                                            class="inline-block bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded 
                                                dark:bg-blue-900 dark:text-blue-300 
                                                hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                                            {{ $category->name }}
                                        </a>
                                    @empty
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Tidak ada
                                            kategori</span>
                                    @endforelse
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Teknologi</h3>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($project->technologies as $technology)
                                        <a href="{{ route('technologies.show', $technology) }}"
                                            class="inline-block bg-gray-100 text-gray-800 text-sm font-medium px-2.5 py-0.5 rounded 
                                                dark:bg-gray-700 dark:text-gray-300
                                                hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                            {{ $technology->name }}
                                        </a>
                                    @empty
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Tidak ada
                                            teknologi</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div> {{-- AKHIR DARI x-data UTAMA --}}
        </div>
    </div>
</x-public-layout>