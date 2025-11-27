<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import { 
    Heart, Bookmark, Share2, Eye, Github, ExternalLink, 
    MessageSquare, Calendar, ChevronLeft, Edit, Trash2, ShieldAlert
} from 'lucide-vue-next';

const props = defineProps({
    proyek: Object,
    isOwner: Boolean, // Dikirim dari controller: true jika user login = pembuat
    hasLiked: Boolean,
    hasSaved: Boolean,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Cek apakah user adalah Admin
const isAdmin = computed(() => user.value && user.value.perans.some(r => r.slug === 'superadmin'));

// Mode Preview/Management aktif jika: Pemilik ATAU Admin
const isManagementMode = computed(() => props.isOwner || isAdmin.value);

// Reactive State
const isLiked = ref(props.hasLiked);
const likeCount = ref(props.proyek.jumlah_suka);
const isSaved = ref(props.hasSaved);
const isProcessing = ref(false);

// --- ACTIONS ---
const toggleLike = async () => {
    if (!user.value) {
        router.get(route('login'));
        return;
    }
    if (isProcessing.value) return;
    isProcessing.value = true;
    const previousState = isLiked.value;
    isLiked.value = !isLiked.value;
    likeCount.value += isLiked.value ? 1 : -1;

    try {
        const response = await axios.post(route('proyek.like', props.proyek.id));
        isLiked.value = response.data.liked;
        likeCount.value = response.data.count;
    } catch (error) {
        isLiked.value = previousState;
        likeCount.value += isLiked.value ? 1 : -1;
    } finally {
        isProcessing.value = false;
    }
};

const toggleSave = async () => {
    if (!user.value) {
        router.get(route('login'));
        return;
    }
    const previousState = isSaved.value;
    isSaved.value = !isSaved.value;
    try {
        await axios.post(route('proyek.save', props.proyek.id));
    } catch (error) {
        isSaved.value = previousState;
    }
};

const shareProyek = () => {
    navigator.clipboard.writeText(window.location.href);
    alert('Link berhasil disalin ke clipboard!');
};

// Fungsi Edit
const editProyek = () => {
    router.get(route('proyek.edit', props.proyek.slug));
};

// Fungsi Hapus
const deleteProyek = () => {
    if(confirm('Apakah Anda yakin ingin menghapus proyek ini? Tindakan ini tidak bisa dibatalkan.')) {
        router.delete(route('proyek.destroy', props.proyek.id));
    }
};
</script>

<template>
    <Head :title="proyek.judul" />

    <PublicLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="mb-6">
                <Link 
                    :href="isOwner ? route('proyek.saya') : route('proyek.index')" 
                    class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 transition-colors"
                >
                    <ChevronLeft :size="16" class="mr-1" /> 
                    {{ isOwner ? 'Kembali ke Proyek Saya' : 'Kembali ke Jelajah' }}
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-8">
                    <div class="rounded-2xl overflow-hidden shadow-lg border border-gray-100 bg-gray-100 aspect-video relative group">
                        <img :src="'/storage/' + proyek.thumbnail" class="w-full h-full object-cover" :alt="proyek.judul">
                        
                        <div v-if="isAdmin && !isOwner" class="absolute top-4 left-4 bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1">
                            <ShieldAlert :size="14" /> Mode Admin
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100">
                        <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ proyek.judul }}</h1>
                        <div class="prose prose-indigo max-w-none text-gray-600" v-html="proyek.konten_html"></div>
                    </div>

                    <div v-if="proyek.galeri_gambar && proyek.galeri_gambar.length > 0">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Galeri Screenshot</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="(img, index) in proyek.galeri_gambar" :key="index" class="rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                                <img :src="'/storage/' + img" class="w-full h-48 object-cover">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">
                        
                        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                            <div class="mb-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ proyek.kategori.nama }}
                                </span>
                            </div>

                            <div v-if="isManagementMode" class="space-y-4 bg-gray-50 -mx-6 -mt-6 p-6 mb-6 border-b border-gray-200 rounded-t-2xl">
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">
                                    {{ isOwner ? 'Kontrol Pemilik' : 'Kontrol Admin' }}
                                </h3>
                                
                                <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200">
                                    <span class="text-sm text-gray-500">Status</span>
                                    <span class="px-2 py-0.5 rounded text-xs font-bold capitalize" 
                                        :class="proyek.status === 'terbit' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                                        {{ proyek.status }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div class="bg-white p-2 rounded border border-gray-200">
                                        <div class="text-xs text-gray-400">Dilihat</div>
                                        <div class="font-bold text-gray-800">{{ proyek.jumlah_lihat }}</div>
                                    </div>
                                    <div class="bg-white p-2 rounded border border-gray-200">
                                        <div class="text-xs text-gray-400">Disukai</div>
                                        <div class="font-bold text-gray-800">{{ proyek.jumlah_suka }}</div>
                                    </div>
                                    <div class="bg-white p-2 rounded border border-gray-200">
                                        <div class="text-xs text-gray-400">Disimpan</div>
                                        <div class="font-bold text-gray-800">{{ proyek.jumlah_simpan }}</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <button @click="editProyek" class="flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm">
                                        <Edit :size="16" /> Edit
                                    </button>
                                    <button @click="deleteProyek" class="flex items-center justify-center gap-2 px-4 py-2 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 text-sm font-medium shadow-sm">
                                        <Trash2 :size="16" /> Hapus
                                    </button>
                                </div>
                            </div>

                            <div v-else>
                                <div class="flex items-center justify-between py-4 border-b border-gray-100 mb-6">
                                    <div class="flex items-center gap-1 text-gray-500"><Eye :size="18" /> <span class="text-sm">{{ proyek.jumlah_lihat }}</span></div>
                                    <div class="flex items-center gap-1 text-gray-500"><Heart :size="18" /> <span class="text-sm">{{ likeCount }}</span></div>
                                    <div class="flex items-center gap-1 text-gray-500"><Calendar :size="18" /> <span class="text-xs">{{ new Date(proyek.terbit_pada).toLocaleDateString() }}</span></div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <button @click="toggleLike" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-all active:scale-95" :class="isLiked ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'">
                                        <Heart :size="20" :class="{ 'fill-current': isLiked }" /> {{ isLiked ? 'Disukai' : 'Suka' }}
                                    </button>
                                    <button @click="toggleSave" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-all active:scale-95" :class="isSaved ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'">
                                        <Bookmark :size="20" :class="{ 'fill-current': isSaved }" /> {{ isSaved ? 'Disimpan' : 'Simpan' }}
                                    </button>
                                </div>
                                
                                <button @click="shareProyek" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors text-sm font-medium">
                                    <Share2 :size="18" /> Bagikan Proyek
                                </button>
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <div class="flex items-center gap-3">
                                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(proyek.user.nama)}&background=random`" class="w-10 h-10 rounded-full border border-gray-200">
                                    <div class="flex-1 overflow-hidden">
                                        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Creator</p>
                                        <h4 class="text-sm font-bold text-gray-900 truncate">{{ proyek.user.nama }}</h4>
                                        <p class="text-xs text-gray-500 truncate">{{ proyek.user.prodi?.nama || 'IgnitePad User' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Teknologi</h4>
                                <div class="flex flex-wrap gap-2">
                                    <div v-for="tech in proyek.teknologi" :key="tech.id" class="flex items-center gap-2 px-2 py-1 bg-gray-50 border border-gray-200 rounded text-xs font-medium text-gray-700">
                                        <img :src="tech.ikon_url" class="w-3 h-3 object-contain"> {{ tech.nama }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 space-y-2">
                                <a v-if="proyek.url_demo" :href="proyek.url_demo" target="_blank" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium shadow-sm">
                                    <ExternalLink :size="16" /> Live Demo
                                </a>
                                <a v-if="proyek.url_repository" :href="proyek.url_repository" target="_blank" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors text-sm font-medium shadow-sm">
                                    <Github :size="16" /> Repository
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </PublicLayout>
</template>
