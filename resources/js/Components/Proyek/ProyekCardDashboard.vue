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

// Get category icon dynamically
const getCategoryIcon = (iconName) => {
    if (!iconName) return Code;
    const icon = LucideIcons[iconName];
    return icon || Code;
};

// Format numbers
const formatNumber = (num) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k';
    }
    return num;
};

// Get status badge color
const getStatusColor = (status) => {
    const colors = {
        'terbit': 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'draft': 'bg-amber-100 text-amber-700 border-amber-200',
        'arsip': 'bg-slate-100 text-slate-700 border-slate-200'
    };
    return colors[status] || colors.draft;
};

// Get visibility icon
const getVisibilityIcon = (visibilitas) => {
    return visibilitas === 'privat' ? Lock : Globe;
};

const CategoryIcon = computed(() => getCategoryIcon(props.proyek.kategori?.ikon));
const VisibilityIcon = computed(() => getVisibilityIcon(props.proyek.visibilitas));

// Check if has collaborators
const hasCollaborators = computed(() => {
    return props.proyek.kolaborators && props.proyek.kolaborators.length > 0;
});
</script>

<template>
    <div
        class="group bg-white rounded-2xl border-2 border-slate-200 shadow-sm hover:shadow-xl hover:border-indigo-300 transition-all duration-300 overflow-hidden flex flex-col">

        <!-- Thumbnail Section (16:9 aspect ratio) -->
        <div
            class="relative w-full aspect-video bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-100 overflow-hidden">
            <img v-if="proyek.thumbnail" :src="'/storage/' + proyek.thumbnail" :alt="proyek.judul"
                class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">

            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>

            <!-- Top Badges -->
            <div class="absolute top-3 left-3 right-3 flex items-start justify-between gap-2">
                <div
                    class="flex items-center gap-2 bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-full text-sm font-semibold shadow-lg">
                    <component :is="CategoryIcon" :size="16" class="text-indigo-600" />
                    <span class="text-slate-700">{{ proyek.kategori?.nama }}</span>
                </div>

                <span class="px-3 py-1.5 rounded-full text-xs font-bold border-2 capitalize shadow-lg"
                    :class="getStatusColor(proyek.status)">
                    {{ proyek.status }}
                </span>
            </div>

            <!-- Bottom Stats -->
            <div class="absolute bottom-3 left-3 right-3 flex items-center gap-4 text-white text-sm">
                <div class="flex items-center gap-1.5 bg-black/40 backdrop-blur-sm px-2.5 py-1 rounded-full">
                    <Eye :size="14" />
                    <span class="font-medium">{{ formatNumber(proyek.jumlah_lihat) }}</span>
                </div>
                <div class="flex items-center gap-1.5 bg-black/40 backdrop-blur-sm px-2.5 py-1 rounded-full">
                    <Heart :size="14" />
                    <span class="font-medium">{{ formatNumber(proyek.jumlah_suka) }}</span>
                </div>
                <div class="flex items-center gap-1.5 bg-black/40 backdrop-blur-sm px-2.5 py-1 rounded-full">
                    <MessageSquare :size="14" />
                    <span class="font-medium">{{ formatNumber(proyek.komentar_count || 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="p-5 flex-1 flex flex-col">
            <!-- Title -->
            <Link :href="route('proyek.dashboard.show', proyek.slug)" class="block">
            <h3
                class="text-xl font-bold text-slate-900 line-clamp-2 mb-3 group-hover:text-indigo-600 transition-colors leading-tight">
                {{ proyek.judul }}
            </h3>
            </Link>

            <!-- Description -->
            <p class="text-sm text-slate-600 line-clamp-2 mb-4 flex-1">
                {{ proyek.deskripsi || 'Tidak ada deskripsi' }}
            </p>

            <!-- Technology Stack -->
            <div v-if="proyek.teknologi && proyek.teknologi.length > 0" class="flex flex-wrap gap-2 mb-4">
                <span v-for="(tek, index) in proyek.teknologi.slice(0, 4)" :key="tek.id"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-medium">
                    <img v-if="tek.ikon_url" :src="tek.ikon_url" :alt="tek.nama" class="w-4 h-4 object-contain">
                    <Code v-else :size="12" />
                    {{ tek.nama }}
                </span>
                <span v-if="proyek.teknologi.length > 4"
                    class="inline-flex items-center px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold">
                    +{{ proyek.teknologi.length - 4 }}
                </span>
            </div>

            <!-- Owner & Collaborators Section -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 mb-4">
                <!-- Owner Profile -->
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(proyek.user?.nama || 'User')}&background=random`"
                        :alt="proyek.user?.nama"
                        class="w-8 h-8 rounded-full border-2 border-white shadow-sm flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-semibold text-slate-900 truncate">{{ proyek.user?.nama }}</div>
                        <div class="text-xs text-slate-500 truncate">
                            {{ proyek.user?.prodi?.nama || 'Universitas' }}
                            <span v-if="proyek.user?.nim"> · {{ proyek.user.nim }}</span>
                            <span v-else-if="proyek.user?.nidn"> · {{ proyek.user.nidn }}</span>
                        </div>
                    </div>
                </div>

                <!-- Collaborators (if any) -->
                <div v-if="hasCollaborators" class="flex items-center gap-1 ml-2">
                    <div class="flex -space-x-2">
                        <div v-for="(kolaborator, index) in proyek.kolaborators.slice(0, 2)" :key="kolaborator.id"
                            class="w-8 h-8 rounded-full border-2 border-white overflow-hidden shadow-sm"
                            :title="kolaborator.user?.nama">
                            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(kolaborator.user?.nama || 'User')}&background=random`"
                                :alt="kolaborator.user?.nama" class="w-full h-full object-cover">
                        </div>
                        <div v-if="proyek.kolaborators.length > 2"
                            class="w-8 h-8 rounded-full border-2 border-white bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-700 shadow-sm">
                            +{{ proyek.kolaborators.length - 2 }}
                        </div>
                    </div>
                </div>

                <!-- Visibility Icon -->
                <div class="flex items-center gap-1.5 text-xs text-slate-500 ml-2">
                    <component :is="VisibilityIcon" :size="14" />
                </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="showActions" class="flex items-center gap-2">
                <Link :href="route('proyek.dashboard.show', proyek.slug)"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors">
                <Eye :size="16" />
                Lihat
                </Link>

                <Link :href="route('proyek.edit', proyek.slug)"
                    class="flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-xl transition-colors"
                    title="Edit">
                <Edit :size="16" />
                </Link>

                <button @click="emit('delete', proyek)"
                    class="flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-colors"
                    title="Hapus">
                    <Trash2 :size="16" />
                </button>
            </div>
        </div>
    </div>
</template>
