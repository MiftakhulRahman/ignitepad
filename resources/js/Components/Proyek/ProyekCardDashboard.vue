<script setup>
import { Link } from '@inertiajs/vue3';
import { Eye, Heart, MessageSquare, Edit, Trash2, Globe, Lock, Code } from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    proyek: Object,
    showActions: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['delete']);

// Helper: Dynamic Category Icon
const getCategoryIcon = (iconName) => {
    if (!iconName) return Code;
    const icon = LucideIcons[iconName];
    return icon || Code;
};

// Helper: Format Number (1.2k)
const formatNumber = (num) => {
    if (num >= 1000) return (num / 1000).toFixed(1) + 'k';
    return num;
};

// Visibility Icon
const getVisibilityIcon = (visibilitas) => {
    return visibilitas === 'privat' ? Lock : Globe;
};

const CategoryIcon = computed(() => getCategoryIcon(props.proyek.kategori?.ikon));
const VisibilityIcon = computed(() => getVisibilityIcon(props.proyek.visibilitas));

const hasCollaborators = computed(() => {
    return props.proyek.kolaborators && props.proyek.kolaborators.length > 0;
});
</script>

<template>
    <div
        class="group relative bg-white rounded-[28px] border border-slate-200 shadow-sm hover:shadow-xl hover:border-indigo-100 transition-all duration-300 flex flex-col h-full overflow-hidden">

        <div class="relative w-full aspect-[16/10] bg-slate-100 overflow-hidden">
            <img v-if="proyek.thumbnail" :src="'/storage/' + proyek.thumbnail" :alt="proyek.judul"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
            
            <div v-else class="absolute inset-0 flex items-center justify-center bg-indigo-50 text-indigo-200">
                <Code :size="56" />
            </div>

            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent pointer-events-none"></div>

            <div class="absolute top-4 right-4 z-10">
                <div class="flex items-center gap-2 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-xl shadow-sm border border-white/20">
                    <component :is="CategoryIcon" :size="16" class="text-indigo-600" />
                    <span class="text-sm font-semibold text-slate-800 capitalize">
                        {{ proyek.kategori?.nama?.toLowerCase() }}
                    </span>
                </div>
            </div>

            <div class="absolute bottom-4 left-4 right-4 flex items-center gap-4 text-white">
                <div class="flex items-center gap-1.5" title="Dilihat">
                    <Eye :size="18" class="text-white/90" />
                    <span class="text-sm font-semibold drop-shadow-md">{{ formatNumber(proyek.jumlah_lihat) }}</span>
                </div>
                <div class="flex items-center gap-1.5" title="Disukai">
                    <Heart :size="18" class="text-rose-400 drop-shadow-md" />
                    <span class="text-sm font-semibold drop-shadow-md">{{ formatNumber(proyek.jumlah_suka) }}</span>
                </div>
                <div class="flex items-center gap-1.5" title="Komentar">
                    <MessageSquare :size="18" class="text-sky-300 drop-shadow-md" />
                    <span class="text-sm font-semibold drop-shadow-md">{{ formatNumber(proyek.komentar_count || 0) }}</span>
                </div>
            </div>
        </div>

        <div class="p-6 flex-1 flex flex-col">
            <Link :href="route('proyek.dashboard.show', proyek.slug)" class="block group/title mb-2">
                <h3 class="text-[20px] leading-snug font-medium text-slate-900 group-hover/title:text-indigo-600 transition-colors line-clamp-2">
                    {{ proyek.judul }}
                </h3>
            </Link>

            <p class="text-[15px] leading-relaxed text-slate-600 line-clamp-2 mb-5">
                {{ proyek.deskripsi || 'Tidak ada deskripsi singkat.' }}
            </p>

            <div v-if="proyek.teknologi && proyek.teknologi.length > 0" class="flex flex-wrap gap-2 mb-6">
                <span v-for="(tek, index) in proyek.teknologi.slice(0, 3)" :key="tek.id"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium border border-slate-200">
                    
                    <img v-if="tek.ikon_url" :src="tek.ikon_url" :alt="tek.nama" class="w-4 h-4 object-contain opacity-80">
                    <Code v-else :size="14" class="text-slate-500" />
                    
                    {{ tek.nama }}
                </span>
                
                <span v-if="proyek.teknologi.length > 3" class="px-2 py-1.5 text-slate-500 text-sm font-medium">
                    +{{ proyek.teknologi.length - 3 }}
                </span>
            </div>

            <div class="mt-auto pt-5 border-t border-slate-100 flex items-center justify-between">
                
                <div class="flex items-center gap-3 overflow-hidden">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(proyek.user?.nama || 'User')}&background=random`"
                        class="w-10 h-10 rounded-full border border-slate-200 shadow-sm flex-shrink-0" alt="Avatar">
                    
                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-bold text-slate-900 leading-tight truncate">
                            {{ proyek.user?.nama }}
                        </span>
                        <div class="flex items-center gap-1 text-xs text-slate-500 mt-0.5 truncate">
                            <span class="truncate">{{ proyek.user?.prodi?.nama || 'Informatika' }}</span>
                            <span v-if="proyek.user?.nim" class="flex-shrink-0">
                                • {{ proyek.user.nim }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="hasCollaborators" class="flex -space-x-3 ml-2 flex-shrink-0">
                    <div v-for="kol in proyek.kolaborators.slice(0, 3)" :key="kol.id" 
                        class="w-8 h-8 rounded-full border-2 border-white bg-slate-200 overflow-hidden ring-1 ring-slate-100"
                        :title="kol.user?.nama">
                        <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(kol.user?.nama || 'U')}&background=random`" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>

            <div v-if="showActions" class="flex items-center gap-2 mt-5">
                <Link :href="route('proyek.dashboard.show', proyek.slug)"
                    class="flex-1 h-11 flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-[15px] font-medium rounded-full transition-all shadow-sm hover:shadow-md">
                    <Eye :size="18" />
                    <span>Detail</span>
                </Link>

                <Link :href="route('proyek.edit', proyek.slug)"
                    class="w-11 h-11 flex items-center justify-center bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 rounded-full transition-colors"
                    title="Edit">
                    <Edit :size="20" />
                </Link>

                <button @click="emit('delete', proyek)"
                    class="w-11 h-11 flex items-center justify-center bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-full transition-colors"
                    title="Hapus">
                    <Trash2 :size="20" />
                </button>
            </div>
        </div>
    </div>
</template>