<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Eye, Edit, Trash2, Globe, ExternalLink, Github, Video,
    Heart, MessageSquare, Bookmark, Users, Code, Calendar,
    ArrowLeft, Share2
} from 'lucide-vue-next';
import ColaboratorSearch from '@/Components/Proyek/ColaboratorSearch.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
    proyek: Object,
    isOwner: Boolean,
    isAdmin: Boolean,
    kolaborators: Array
});

const activeTab = ref('detail');

const confirmDelete = () => {
    if (confirm('Apakah Anda yakin ingin menghapus proyek ini? Tindakan ini tidak dapat dibatalkan.')) {
        router.delete(route('proyek.destroy', props.proyek.id), {
            onSuccess: () => {
                router.visit(route('proyek.saya'));
            }
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>

    <Head :title="proyek.judul" />
    <Toast />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Breadcrumb & Actions -->
            <div class="flex items-center justify-between">
                <Link :href="route('proyek.saya')"
                    class="flex items-center gap-2 text-slate-600 hover:text-indigo-600 transition-colors">
                <ArrowLeft :size="18" />
                <span class="font-medium">Kembali ke Proyek Saya</span>
                </Link>

                <div class="flex items-center gap-3">
                    <Link :href="route('proyek.show', proyek.slug)"
                        class="flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors"
                        target="_blank">
                    <ExternalLink :size="16" />
                    Lihat Halaman Publik
                    </Link>

                    <Link v-if="isOwner || isAdmin" :href="route('proyek.edit', proyek.slug)"
                        class="flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-xl transition-colors">
                    <Edit :size="16" />
                    Edit
                    </Link>

                    <button v-if="isOwner || isAdmin" @click="confirmDelete"
                        class="flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-colors">
                        <Trash2 :size="16" />
                        Hapus
                    </button>
                </div>
            </div>

            <!-- Header Card -->
            <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-sm overflow-hidden">
                <!-- Thumbnail -->
                <div class="relative w-full aspect-video bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-100">
                    <img v-if="proyek.thumbnail" :src="'/storage/' + proyek.thumbnail" :alt="proyek.judul"
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                    <!-- Statistics Overlay -->
                    <div class="absolute bottom-6 left-6 right-6 flex items-center gap-4 text-white">
                        <div class="flex items-center gap-2 bg-black/40 backdrop-blur-sm px-4 py-2 rounded-full">
                            <Eye :size="18" />
                            <span class="font-semibold">{{ proyek.jumlah_lihat }}</span>
                            <span class="text-sm">Views</span>
                        </div>
                        <div class="flex items-center gap-2 bg-black/40 backdrop-blur-sm px-4 py-2 rounded-full">
                            <Heart :size="18" />
                            <span class="font-semibold">{{ proyek.jumlah_suka }}</span>
                            <span class="text-sm">Suka</span>
                        </div>
                        <div class="flex items-center gap-2 bg-black/40 backdrop-blur-sm px-4 py-2 rounded-full">
                            <MessageSquare :size="18" />
                            <span class="font-semibold">{{ proyek.jumlah_komentar }}</span>
                            <span class="text-sm">Komentar</span>
                        </div>
                        <div class="flex items-center gap-2 bg-black/40 backdrop-blur-sm px-4 py-2 rounded-full">
                            <Bookmark :size="18" />
                            <span class="font-semibold">{{ proyek.jumlah_simpan }}</span>
                            <span class="text-sm">Tersimpan</span>
                        </div>
                    </div>
                </div>

                <!-- Title & Meta -->
                <div class="p-8">
                    <div class="flex items-start justify-between gap-6 mb-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-sm font-semibold">
                                    {{ proyek.kategori?.nama }}
                                </span>
                                <span class="px-3 py-1 rounded-lg text-sm font-semibold border-2 capitalize"
                                    :class="proyek.status === 'terbit' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100  text-amber-700 border-amber-200'">
                                    {{ proyek.status }}
                                </span>
                            </div>
                            <h1 class="text-4xl font-bold text-slate-900 mb-3">{{ proyek.judul }}</h1>
                            <p class="text-lg text-slate-600">{{ proyek.deskripsi }}</p>
                        </div>
                    </div>

                    <!-- Meta Info -->
                    <div class="flex items-center gap-6 text-sm text-slate-600 pb-6 border-b border-slate-200">
                        <div class="flex items-center gap-2">
                            <Calendar :size="16" />
                            <span>Dibuat: {{ formatDate(proyek.created_at) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Calendar :size="16" />
                            <span>Diperbarui: {{ formatDate(proyek.updated_at) }}</span>
                        </div>
                        <div class="flex items-center gap-2 capitalize">
                            <Globe :size="16" />
                            <span>Visibilitas: {{ proyek.visibilitas }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-sm overflow-hidden">
                <!-- Tab Navigation -->
                <div class="flex border-b-2 border-slate-200">
                    <button @click="activeTab = 'detail'"
                        :class="activeTab === 'detail' ? 'border-b-4 border-indigo-600 text-indigo-600' : 'text-slate-600 hover:text-indigo-600'"
                        class="px-6 py-4 font-semibold transition-colors -mb-0.5">
                        Detail Proyek
                    </button>
                    <button v-if="isOwner || isAdmin" @click="activeTab = 'kolaborator'"
                        :class="activeTab === 'kolaborator' ? 'border-b-4 border-indigo-600 text-indigo-600' : 'text-slate-600 hover:text-indigo-600'"
                        class="px-6 py-4 font-semibold transition-colors -mb-0.5 flex items-center gap-2">
                        <Users :size="18" />
                        Kolaborator
                        <span v-if="kolaborators.length > 0"
                            class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">
                            {{ kolaborators.length }}
                        </span>
                    </button>
                    <button @click="activeTab = 'teknologi'"
                        :class="activeTab === 'teknologi' ? 'border-b-4 border-indigo-600 text-indigo-600' : 'text-slate-600 hover:text-indigo-600'"
                        class="px-6 py-4 font-semibold transition-colors -mb-0.5 flex items-center gap-2">
                        <Code :size="18" />
                        Teknologi
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="p-8">
                    <!-- Detail Tab -->
                    <div v-if="activeTab === 'detail'" class="space-y-8">
                        <!-- Konten HTML -->
                        <div v-if="proyek.konten_html" class="prose prose-lg max-w-none" v-html="proyek.konten_html">
                        </div>
                        <p v-else class="text-slate-500 text-center py-8">Tidak ada konten detail</p>

                        <!-- Links -->
                        <div v-if="proyek.url_demo || proyek.url_repository || proyek.url_video"
                            class="flex flex-wrap gap-4 pt-6 border-t border-slate-200">
                            <a v-if="proyek.url_demo" :href="proyek.url_demo" target="_blank"
                                class="flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors">
                                <Globe :size="18" />
                                Lihat Demo
                            </a>
                            <a v-if="proyek.url_repository" :href="proyek.url_repository" target="_blank"
                                class="flex items-center gap-2 px-6 py-3 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl transition-colors">
                                <Github :size="18" />
                                Repository
                            </a>
                            <a v-if="proyek.url_video" :href="proyek.url_video" target="_blank"
                                class="flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-colors">
                                <Video :size="18" />
                                Video Demo
                            </a>
                        </div>

                        <!-- Gallery -->
                        <div v-if="proyek.galeri_gambar && proyek.galeri_gambar.length > 0"
                            class="pt-6 border-t border-slate-200">
                            <h3 class="text-xl font-bold text-slate-900 mb-4">Galeri</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <img v-for="(img, index) in proyek.galeri_gambar" :key="index" :src="'/storage/' + img"
                                    :alt="`Gallery ${index + 1}`"
                                    class="w-full h-48 object-cover rounded-xl border-2 border-slate-200 hover:border-indigo-300 transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Kolaborator Tab -->
                    <div v-if="activeTab === 'kolaborator' && (isOwner || isAdmin)">
                        <ColaboratorSearch :proyek-id="proyek.id" :existing-kolaborators="kolaborators" />
                    </div>

                    <!-- Teknologi Tab -->
                    <div v-if="activeTab === 'teknologi'">
                        <div v-if="proyek.teknologi && proyek.teknologi.length > 0"
                            class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="tek in proyek.teknologi" :key="tek.id"
                                class="flex items-center gap-3 p-4 bg-slate-50 border-2 border-slate-200 rounded-xl hover:border-indigo-300 transition-colors">
                                <Code :size="24" class="text-indigo-600" />
                                <div>
                                    <div class="font-semibold text-slate-900">{{ tek.nama }}</div>
                                    <div v-if="tek.kategori_teknologi" class="text-xs text-slate-500">{{
                                        tek.kategori_teknologi }}</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-slate-500 text-center py-8">Tidak ada teknologi yang ditambahkan</p>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
