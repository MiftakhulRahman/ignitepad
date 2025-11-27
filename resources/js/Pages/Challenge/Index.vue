<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import { Search, Trophy, Filter, X, Plus } from 'lucide-vue-next';
import ChallengeCard from '@/Components/ChallengeCard.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    challenges: Object,
    kategoris: Array,
    filters: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const userRole = computed(() => user.value ? user.value.perans[0]?.slug : null);
const isDosen = computed(() => userRole.value === 'dosen');

// State
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'buka');
const kategoriFilter = ref(props.filters.kategori || '');

// Watcher untuk Auto Filter
watch([search, statusFilter, kategoriFilter], debounce(() => {
    router.get(route('challenge.index'), {
        search: search.value,
        status: statusFilter.value,
        kategori: kategoriFilter.value
    }, { preserveState: true, replace: true, preserveScroll: true });
}, 300));

const clearFilters = () => {
    search.value = '';
    statusFilter.value = 'buka';
    kategoriFilter.value = '';
};
</script>

<template>
    <Head title="Challenge & Kompetisi" />

    <PublicLayout>
        <div class="relative">
            
            <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-indigo-50 to-white -z-10"></div>
            <div class="absolute top-10 right-0 w-64 h-64 bg-purple-200 rounded-full blur-3xl opacity-30 -z-10"></div>
            <div class="absolute top-20 left-10 w-40 h-40 bg-blue-200 rounded-full blur-2xl opacity-30 -z-10"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                
                <div class="text-center max-w-3xl mx-auto mb-12 animate-fade-in-up">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold uppercase tracking-wider mb-4 border border-indigo-200">
                        <Trophy :size="14" /> Arena Kompetisi
                    </div>
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight mb-4">
                        Tantang Dirimu, <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Raih Prestasi Gemilang</span>
                    </h1>
                    <p class="text-lg text-gray-600 mb-8">
                        Ikuti challenge dari dosen, asah skill codingmu, dan menangkan hadiah serta pengakuan akademik.
                    </p>

                    <div v-if="isDosen">
                        <Link :href="route('challenge.create')" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-lg text-white bg-gray-900 hover:bg-gray-800 transition-all hover:scale-105">
                            <Plus :size="20" class="mr-2" /> Buat Challenge Baru
                        </Link>
                    </div>
                </div>

                <div class="sticky top-20 z-30 mb-10">
                    <div class="bg-white/80 backdrop-blur-lg border border-gray-200/60 shadow-lg shadow-gray-200/20 rounded-2xl p-2 flex flex-col md:flex-row gap-3 items-center justify-between">
                        
                        <div class="flex bg-gray-100/80 p-1 rounded-xl w-full md:w-auto">
                            <button 
                                @click="statusFilter = 'buka'"
                                class="flex-1 md:flex-none px-6 py-2 rounded-lg text-sm font-bold transition-all"
                                :class="statusFilter === 'buka' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            >
                                Sedang Berlangsung
                            </button>
                            <button 
                                @click="statusFilter = 'selesai'"
                                class="flex-1 md:flex-none px-6 py-2 rounded-lg text-sm font-bold transition-all"
                                :class="statusFilter === 'selesai' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            >
                                Selesai
                            </button>
                        </div>

                        <div class="flex gap-2 w-full md:w-auto">
                            <div class="relative flex-1 md:w-64">
                                <Search :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                                <input 
                                    v-model="search" 
                                    type="text" 
                                    placeholder="Cari challenge..." 
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-transparent focus:bg-white focus:border-indigo-300 focus:ring-indigo-200 rounded-xl text-sm transition-all"
                                >
                            </div>
                            
                            <div class="relative md:w-48">
                                <select 
                                    v-model="kategoriFilter"
                                    class="w-full pl-4 pr-8 py-2.5 bg-gray-50 border-transparent focus:bg-white focus:border-indigo-300 focus:ring-indigo-200 rounded-xl text-sm transition-all cursor-pointer appearance-none"
                                >
                                    <option value="">Semua Kategori</option>
                                    <option v-for="kat in kategoris" :key="kat.id" :value="kat.id">{{ kat.nama }}</option>
                                </select>
                                <Filter :size="16" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            </div>

                            <button 
                                v-if="search || kategoriFilter"
                                @click="clearFilters"
                                class="p-2.5 text-gray-500 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors"
                                title="Reset Filter"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="challenges.data.length > 0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                        <ChallengeCard 
                            v-for="challenge in challenges.data" 
                            :key="challenge.id" 
                            :challenge="challenge" 
                        />
                    </div>
                    
                    <div class="mt-10 flex justify-center">
                        <Pagination :links="challenges.links" />
                    </div>
                </div>

                <div v-else class="text-center py-20">
                    <div class="bg-gray-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200">
                        <Trophy :size="40" class="text-gray-300" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Belum Ada Challenge</h3>
                    <p class="text-gray-500 max-w-sm mx-auto mt-2">
                        Saat ini belum ada challenge yang sesuai dengan filter Anda. Coba reset filter atau cek kembali nanti.
                    </p>
                    <button @click="clearFilters" class="mt-6 text-indigo-600 font-medium hover:underline">Lihat Semua Challenge</button>
                </div>

            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out forwards;
}
</style>
