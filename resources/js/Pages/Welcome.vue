<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Star, Globe, Cpu } from 'lucide-vue-next';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    featuredProyeks: Array,
});
</script>

<template>
    <Head title="Showcase Proyek Mahasiswa" />

    <PublicLayout>
        <div class="relative bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto">
                <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                    <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                        <div class="sm:text-center lg:text-left">
                            <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                                <span class="block xl:inline">Pamerkan Karyamu,</span>
                                <span class="block text-indigo-600 xl:inline">Temukan Inspirasi.</span>
                            </h1>
                            <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                                IgnitePad adalah platform showcase proyek mahasiswa dan dosen. Dokumentasikan portofoliomu, ikuti kompetisi, dan bangun reputasi akademikmu sekarang.
                            </p>
                            <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                                <div class="rounded-md shadow">
                                    <Link :href="route('proyek.index')" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10">
                                        Jelajah Karya
                                    </Link>
                                </div>
                                <div class="mt-3 sm:mt-0 sm:ml-3">
                                    <Link :href="route('register')" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 md:py-4 md:text-lg md:px-10">
                                        Mulai Berkarya
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
            <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-gray-50 flex items-center justify-center">
                <div class="relative w-full h-full overflow-hidden">
                    <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
                    <div class="absolute top-1/3 right-1/4 w-64 h-64 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
                    <div class="absolute -bottom-8 left-1/3 w-64 h-64 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
                    <div class="relative m-8">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/web-development-2974925-2477511.png" alt="Hero Image" class="w-full object-contain drop-shadow-2xl transform hover:scale-105 transition-transform duration-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-base font-semibold text-indigo-600 tracking-wide uppercase">Showcase</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Proyek Unggulan Terbaru
                    </p>
                    <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                        Lihat apa yang sedang dibangun oleh mahasiswa berbakat lainnya.
                    </p>
                </div>

                <div class="mt-10">
                    <div v-if="featuredProyeks && featuredProyeks.length > 0" class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <Link v-for="proyek in featuredProyeks" :key="proyek.id" :href="route('proyek.show', proyek.slug)" class="flex flex-col rounded-lg shadow-lg overflow-hidden bg-white hover:shadow-xl transition-shadow duration-300 group">
                            <div class="flex-shrink-0 relative">
                                <img class="h-48 w-full object-cover group-hover:scale-105 transition-transform duration-500" :src="'/storage/' + proyek.thumbnail" :alt="proyek.judul">
                                <div class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded text-xs font-bold text-gray-800">
                                    {{ proyek.kategori.nama }}
                                </div>
                            </div>
                            <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                                <div class="flex-1">
                                    <h3 class="text-xl font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                        {{ proyek.judul }}
                                    </h3>
                                    <p class="mt-3 text-base text-gray-500 line-clamp-2">
                                        {{ proyek.deskripsi }}
                                    </p>
                                </div>
                                <div class="mt-6 flex items-center">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(proyek.user.nama)}&background=random`" alt="">
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ proyek.user.nama }}
                                        </p>
                                        <div class="flex space-x-1 text-sm text-gray-500">
                                            <time datetime="2020-03-16">
                                                {{ new Date(proyek.created_at).toLocaleDateString() }}
                                            </time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="text-center py-10 text-gray-500">
                        Belum ada proyek unggulan saat ini. Jadilah yang pertama!
                    </div>
                    
                    <div class="mt-10 text-center">
                        <Link :href="route('proyek.index')" class="text-base font-medium text-indigo-600 hover:text-indigo-500">
                            Lihat semua proyek <span aria-hidden="true"> &rarr;</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-16 bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-y-10 gap-x-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="text-center">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mx-auto mb-4">
                            <Globe :size="24" />
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Portofolio Digital</h3>
                        <p class="mt-2 text-base text-gray-500">
                            Simpan semua karyamu dalam satu tempat. Tunjukkan skillmu kepada dunia dan rekruter.
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mx-auto mb-4">
                            <Cpu :size="24" />
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Challenge & Kompetisi</h3>
                        <p class="mt-2 text-base text-gray-500">
                            Ikuti tantangan dari dosen, menangkan hadiah, dan dapatkan sertifikat prestasi.
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mx-auto mb-4">
                            <Star :size="24" />
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Inspirasi Tanpa Batas</h3>
                        <p class="mt-2 text-base text-gray-500">
                            Jelajahi ratusan proyek inovatif dari rekan mahasiswa lintas prodi.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </PublicLayout>
</template>

<style>
/* Animation for blob background */
@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
  100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
.animation-delay-4000 {
  animation-delay: 4s;
}
</style>