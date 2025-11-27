<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Clock, Users, Trophy, ChevronRight, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    challenge: Object,
});

// Helper untuk format tanggal sisa
const timeLeft = computed(() => {
    const now = new Date();
    const deadline = new Date(props.challenge.batas_waktu);
    const diffTime = deadline - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 

    if (diffDays < 0) return 'Berakhir';
    if (diffDays === 0) return 'Hari Terakhir!';
    return `${diffDays} Hari Lagi`;
});

const isUrgent = computed(() => {
    const text = timeLeft.value;
    return text === 'Hari Terakhir!' || (typeof text === 'string' && parseInt(text) <= 3);
});
</script>

<template>
    <Link 
        :href="route('challenge.index')" 
        class="group relative flex flex-col bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300 hover:-translate-y-1"
    >
        <div class="h-40 w-full bg-gray-100 relative overflow-hidden">
            <img 
                v-if="challenge.banner" 
                :src="'/storage/' + challenge.banner" 
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
            >
            <div v-else class="w-full h-full bg-gradient-to-br from-indigo-600 to-purple-700 flex items-center justify-center relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-black/10 rounded-full blur-xl -ml-5 -mb-5"></div>
                <Trophy :size="48" class="text-white/20 rotate-12" />
            </div>

            <div class="absolute top-3 right-3">
                <span v-if="challenge.status === 'buka'" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-white/90 backdrop-blur-md text-green-600 shadow-sm border border-white/50">
                    <span class="w-2 h-2 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                    Terbuka
                </span>
                <span v-else class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-900/80 backdrop-blur-md text-white border border-white/10">
                    Selesai
                </span>
            </div>
        </div>

        <div class="p-5 flex-1 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(challenge.pembuat.nama)}&background=random`" class="w-6 h-6 rounded-full ring-2 ring-white">
                    <span class="text-xs text-gray-500 font-medium truncate max-w-[120px]">{{ challenge.pembuat.nama }}</span>
                </div>
                <div v-if="challenge.is_joined" class="flex items-center gap-1 text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
                    <CheckCircle2 :size="12" /> Mengikuti
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 leading-snug mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                {{ challenge.judul }}
            </h3>

            <div class="mt-auto pt-4">
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 p-2 rounded-lg border border-gray-100">
                    <Trophy :size="16" class="text-amber-500" />
                    <span class="truncate font-medium">{{ challenge.hadiah || 'Sertifikat & Poin' }}</span>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="flex items-center gap-1.5" :class="isUrgent ? 'text-red-600' : 'text-gray-500'">
                    <Clock :size="16" />
                    <span class="text-xs font-bold">{{ timeLeft }}</span>
                </div>

                <div class="flex items-center gap-1.5 text-gray-500">
                    <Users :size="16" />
                    <span class="text-xs font-medium">{{ challenge.jumlah_peserta }} Peserta</span>
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 w-full h-1 bg-indigo-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
    </Link>
</template>