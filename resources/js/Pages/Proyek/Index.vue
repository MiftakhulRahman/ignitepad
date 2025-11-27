<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import { Search } from 'lucide-vue-next';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    proyeks: Object,
    filters: Object,
    isAdmin: Boolean,
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

watch([search, statusFilter], debounce(() => {
    router.get(route('proyek.index'), { 
        search: search.value,
        status: statusFilter.value 
    }, { preserveState: true, replace: true });
}, 500));
</script>

<template>
    <Head title="Jelajah Proyek" />
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Jelajah Proyek</h1>
                    <p class="text-gray-500 mt-1">Temukan inovasi terbaru dari mahasiswa dan dosen.</p>
                </div>

                <div class="flex gap-3 w-full md:w-auto">
                    <select v-if="isAdmin" v-model="statusFilter" class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="terbit">Terbit</option>
                        <option value="draft">Draft</option>
                    </select>

                    <div class="relative w-full md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search :size="18" class="text-gray-400"/>
                        </div>
                        <input v-model="search" type="text" class="pl-10 block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500" placeholder="Cari judul proyek...">
                    </div>
                </div>
            </div>

            <div v-if="proyeks.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <Link v-for="proyek in proyeks.data" :key="proyek.id" :href="route('proyek.show', proyek.slug)" class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col h-full">
                    <div class="aspect-video bg-gray-100 overflow-hidden relative">
                        <img :src="'/storage/' + proyek.thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div v-if="isAdmin && proyek.status === 'draft'" class="absolute top-2 right-2 bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">
                            Draft
                        </div>
                    </div>
                    <div class="p-4 flex-1 flex flex-col">
                        <div class="text-xs text-indigo-600 font-medium mb-1">{{ proyek.kategori.nama }}</div>
                        <h3 class="text-lg font-bold text-gray-900 leading-tight mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">{{ proyek.judul }}</h3>
                        <div class="mt-auto pt-4 flex items-center gap-2">
                            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(proyek.user.nama)}&background=random`" class="w-6 h-6 rounded-full">
                            <span class="text-xs text-gray-500 truncate">{{ proyek.user.nama }}</span>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-20">
                <p class="text-gray-500">Belum ada proyek yang ditemukan.</p>
            </div>

            <div class="mt-8">
                <Pagination :links="proyeks.links" />
            </div>

        </div>
    </PublicLayout>
</template>