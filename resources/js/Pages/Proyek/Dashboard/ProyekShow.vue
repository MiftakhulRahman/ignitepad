<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Eye, Edit, Trash2, Globe, ExternalLink, Github, Video,
    Heart, MessageSquare, Bookmark, Code, Calendar,
    Share2, ArrowLeft
} from 'lucide-vue-next';
import Toast from '@/Components/Toast.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    proyek: Object,
    isOwner: Boolean,
    isAdmin: Boolean,
});

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
        day: 'numeric', month: 'short', year: 'numeric'
    });
};

const formatNumber = (num) => {
    if (!num) return 0; // Handle null/undefined
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num;
};
</script>

<template>

    <Head :title="proyek.judul" />
    <Toast />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto pb-10">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <Breadcrumb :items="[
                    { label: 'Proyek Saya', url: route('proyek.saya') },
                    { label: proyek.judul }
                ]" />

                <div class="flex items-center gap-2">
                    <Link :href="route('proyek.show', proyek.slug)" target="_blank"
                        class="h-10 px-5 flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-full transition-colors">
                    <ExternalLink :size="16" />
                    <span class="hidden sm:inline">Lihat Publik</span>
                    </Link>

                    <Link v-if="isOwner || isAdmin" :href="route('proyek.edit', proyek.slug)"
                        class="h-10 w-10 sm:w-auto sm:px-5 flex items-center justify-center gap-2 bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-200 text-sm font-medium rounded-full transition-colors">
                    <Edit :size="16" />
                    <span class="hidden sm:inline">Edit</span>
                    </Link>

                    <button v-if="isOwner || isAdmin" @click="confirmDelete"
                        class="h-10 w-10 sm:w-auto sm:px-5 flex items-center justify-center gap-2 bg-rose-100 hover:bg-rose-200 text-rose-800 border border-rose-200 text-sm font-medium rounded-full transition-colors">
                    <Trash2 :size="16" />
                    <span class="hidden sm:inline">Hapus</span>
                    </button>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-[28px] border border-slate-200 p-6 shadow-sm flex flex-col md:flex-row gap-6">
                    <div class="relative w-full md:w-48 aspect-video md:aspect-square flex-shrink-0 bg-slate-100 rounded-2xl overflow-hidden border border-slate-100">
                        <img v-if="proyek.thumbnail" :src="'/storage/' + proyek.thumbnail" :alt="proyek.judul"
                            class="w-full h-full object-cover">
                        <div v-else class="flex items-center justify-center h-full text-slate-300">
                            <Code :size="32" />
                        </div>
                    </div>

                    <div class="flex-1 min-w-0 flex flex-col">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold border border-indigo-100">
                                {{ proyek.kategori?.nama }}
                            </span>
                            <div class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium border capitalize"
                                :class="proyek.status === 'terbit' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                                {{ proyek.status }}
                            </div>
                             <div class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium border bg-slate-50 text-slate-600 border-slate-200 capitalize">
                                <Globe :size="12" />
                                {{ proyek.visibilitas }}
                            </div>
                        </div>

                        <h1 class="text-2xl font-bold text-slate-900 leading-tight mb-3">{{ proyek.judul }}</h1>
                        
                        <p class="text-slate-600 text-sm leading-relaxed mb-4">
                            {{ proyek.deskripsi || 'Tidak ada deskripsi singkat.' }}
                        </p>
                        
                        <div class="flex items-center gap-4 text-xs text-slate-400 font-medium mt-auto pt-2 border-t border-slate-50">
                             <div class="flex items-center gap-1.5">
                                <Calendar :size="14" />
                                Dibuat: {{ formatDate(proyek.created_at) }}
                            </div>
                            <span class="text-slate-300">|</span>
                             <div class="flex items-center gap-1.5">
                                <Calendar :size="14" />
                                Diupdate: {{ formatDate(proyek.updated_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white p-4 rounded-[24px] border border-slate-200 flex flex-col items-center justify-center text-center hover:border-indigo-200 transition-colors">
                        <Eye :size="20" class="text-slate-400 mb-1" />
                        <span class="text-lg font-bold text-slate-900">{{ formatNumber(proyek.jumlah_lihat) }}</span>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Views</span>
                    </div>
                    <div class="bg-white p-4 rounded-[24px] border border-slate-200 flex flex-col items-center justify-center text-center hover:border-rose-200 transition-colors">
                        <Heart :size="20" class="text-rose-400 mb-1" />
                        <span class="text-lg font-bold text-slate-900">{{ formatNumber(proyek.jumlah_suka) }}</span>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Likes</span>
                    </div>
                    <div class="bg-white p-4 rounded-[24px] border border-slate-200 flex flex-col items-center justify-center text-center hover:border-sky-200 transition-colors">
                        <MessageSquare :size="20" class="text-sky-400 mb-1" />
                        <span class="text-lg font-bold text-slate-900">
                            {{ formatNumber(proyek.komentar_count !== undefined ? proyek.komentar_count : proyek.jumlah_komentar) }}
                        </span>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Comments</span>
                    </div>
                    <div class="bg-white p-4 rounded-[24px] border border-slate-200 flex flex-col items-center justify-center text-center hover:border-emerald-200 transition-colors">
                        <Bookmark :size="20" class="text-emerald-400 mb-1" />
                        <span class="text-lg font-bold text-slate-900">{{ formatNumber(proyek.jumlah_simpan) }}</span>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Saves</span>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 bg-white rounded-[28px] border border-slate-200 p-6 md:p-8 shadow-sm h-fit">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        Tentang Proyek
                    </h3>
                    <div v-if="proyek.konten_html" class="prose prose-slate prose-sm md:prose-base max-w-none prose-headings:font-bold prose-a:text-indigo-600 prose-img:rounded-xl" v-html="proyek.konten_html">
                    </div>
                    <p v-else class="text-slate-600 leading-relaxed">
                        {{ proyek.deskripsi || 'Tidak ada deskripsi detail.' }}
                    </p>
                </div>

                <div class="space-y-6">
                    
                    <div class="bg-white rounded-[28px] border border-slate-200 p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <Code :size="16" class="text-indigo-600" />
                            Teknologi
                        </h3>
                        
                        <div v-if="proyek.teknologi && proyek.teknologi.length > 0" class="flex flex-wrap gap-2">
                            <div v-for="tek in proyek.teknologi" :key="tek.id"
                                class="flex items-center gap-2 px-3 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition-colors select-none cursor-default">
                                <img v-if="tek.ikon_url" :src="tek.ikon_url" :alt="tek.nama" class="w-5 h-5 object-contain opacity-90">
                                <Code v-else :size="16" class="text-slate-400" />
                                <span class="text-sm font-medium text-slate-700">{{ tek.nama }}</span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-slate-400 italic">Belum ada teknologi.</p>
                    </div>

                    <div v-if="proyek.url_demo || proyek.url_repository || proyek.url_video" 
                         class="bg-white rounded-[28px] border border-slate-200 p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <Share2 :size="16" class="text-indigo-600" />
                            Tautan
                        </h3>
                        <div class="flex flex-col gap-2">
                            <a v-if="proyek.url_demo" :href="proyek.url_demo" target="_blank"
                                class="flex items-center justify-between px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl transition-colors group">
                                <span class="text-sm font-semibold flex items-center gap-2">
                                    <Globe :size="16" /> Live Demo
                                </span>
                                <ExternalLink :size="14" class="opacity-50 group-hover:translate-x-1 transition-transform" />
                            </a>
                            
                            <a v-if="proyek.url_repository" :href="proyek.url_repository" target="_blank"
                                class="flex items-center justify-between px-4 py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200 transition-colors group">
                                <span class="text-sm font-semibold flex items-center gap-2">
                                    <Github :size="16" /> Repository
                                </span>
                                <ExternalLink :size="14" class="opacity-50 group-hover:translate-x-1 transition-transform" />
                            </a>

                             <a v-if="proyek.url_video" :href="proyek.url_video" target="_blank"
                                class="flex items-center justify-between px-4 py-3 bg-red-50 hover:bg-red-100 text-red-700 rounded-xl transition-colors group">
                                <span class="text-sm font-semibold flex items-center gap-2">
                                    <Video :size="16" /> Video Demo
                                </span>
                                <ExternalLink :size="14" class="opacity-50 group-hover:translate-x-1 transition-transform" />
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>