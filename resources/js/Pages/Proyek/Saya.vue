<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Eye, Edit, Trash2, Clock, CheckCircle2, Lock } from 'lucide-vue-next';
import Pagination from '@/Components/Pagination.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
    proyeks: Object,
});

const getStatusColor = (status) => {
    return status === 'terbit' 
        ? 'bg-green-100 text-green-800 border-green-200' 
        : 'bg-yellow-100 text-yellow-800 border-yellow-200';
};
</script>

<template>
    <Head title="Proyek Saya" />
    <Toast />

    <AuthenticatedLayout>
        <div class="space-y-6">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Proyek Saya</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola portofolio proyek yang telah Anda buat.</p>
                </div>
                <Link :href="route('proyek.create')" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                    <Plus :size="18" /> Upload Proyek Baru
                </Link>
            </div>

            <div v-if="proyeks.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="proyek in proyeks.data" :key="proyek.id" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    
                    <div class="relative h-48 bg-gray-100">
                        <img :src="'/storage/' + proyek.thumbnail" class="w-full h-full object-cover" :alt="proyek.judul">
                        <div class="absolute top-3 right-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium border capitalize" :class="getStatusColor(proyek.status)">
                                {{ proyek.status }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ proyek.kategori.nama }}</span>
                            <div class="flex items-center text-gray-400 text-xs gap-1">
                                <Lock v-if="proyek.visibilitas === 'privat'" :size="12" />
                                <span class="capitalize">{{ proyek.visibilitas }}</span>
                            </div>
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-900 line-clamp-1 mb-2">{{ proyek.judul }}</h3>
                        <p class="text-sm text-gray-500 line-clamp-2 mb-4 flex-1">{{ proyek.deskripsi }}</p>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                            <span class="flex items-center gap-1"><Eye :size="14" /> {{ proyek.jumlah_lihat }} views</span>
                            <span class="text-xs">{{ new Date(proyek.updated_at).toLocaleDateString('id-ID') }}</span>
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
                        <Link :href="route('proyek.show', proyek.slug)" class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-white rounded-lg transition-colors" title="Lihat">
                            <Eye :size="18" />
                        </Link>
                        <button class="p-2 text-gray-600 hover:text-amber-600 hover:bg-white rounded-lg transition-colors" title="Edit">
                            <Edit :size="18" />
                        </button>
                        <button class="p-2 text-gray-600 hover:text-red-600 hover:bg-white rounded-lg transition-colors" title="Hapus">
                            <Trash2 :size="18" />
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-20 bg-white rounded-xl border border-gray-200 border-dashed">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada proyek</h3>
                <p class="mt-1 text-sm text-gray-500">Mulai bangun portofoliomu sekarang.</p>
                <div class="mt-6">
                    <Link :href="route('proyek.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                        <Plus :size="16" class="-ml-1 mr-2" /> Buat Proyek Pertama
                    </Link>
                </div>
            </div>

            <div v-if="proyeks.links.length > 3" class="mt-6">
                <Pagination :links="proyeks.links" />
            </div>

        </div>
    </AuthenticatedLayout>
</template>