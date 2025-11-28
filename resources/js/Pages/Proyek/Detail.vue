<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, usePage, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import {
    ThumbsUp,
    ThumbsDown,
    Bookmark,
    Share2,
    Eye,
    Github,
    ExternalLink,
    MessageSquare,
    MessageCircle,
    Calendar,
    ChevronLeft,
    ChevronDown,
    ChevronRight,
    ChevronUp,
    Edit,
    Trash2,
    ShieldAlert,
    Send,
    Heart,
    Settings,
    BarChart3,
    Clock,
    MoreHorizontal,
    CornerDownRight
} from 'lucide-vue-next';

const props = defineProps({
    proyek: Object,
    isOwner: Boolean,
    hasLiked: Boolean,
    hasSaved: Boolean,
    komentars: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Cek apakah admin
const isAdmin = computed(
    () => user.value && user.value.perans.some((r) => r.slug === 'superadmin'),
);

// Mode manajemen = pemilik atau admin
const isManagementMode = computed(() => props.isOwner || isAdmin.value);

// Hitung total komentar (Induk + Balasan)
const totalKomentarCount = computed(() => {
    if (!props.komentars) return 0;
    return props.komentars.reduce((total, k) => {
        return total + 1 + (k.balasan ? k.balasan.length : 0);
    }, 0);
});

// State like dan save
const isLiked = ref(props.hasLiked);
const likeCount = ref(props.proyek.jumlah_suka);
const isSaved = ref(props.hasSaved);
const isProcessing = ref(false);

// Toggle like proyek
const toggleLike = async () => {
    if (!user.value) {
        router.get(route('login'));
        return;
    }
    if (isProcessing.value) return;

    isProcessing.value = true;
    const previousState = isLiked.value;

    // Update optimistik
    isLiked.value = !isLiked.value;
    likeCount.value += isLiked.value ? 1 : -1;

    try {
        const response = await axios.post(route('proyek.like', props.proyek.id));
        isLiked.value = response.data.liked;
        likeCount.value = response.data.count;
    } catch (error) {
        // Kembalikan jika gagal
        isLiked.value = previousState;
        likeCount.value += isLiked.value ? 1 : -1;
    } finally {
        isProcessing.value = false;
    }
};

// Toggle save proyek
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

// Bagikan proyek
const shareProyek = () => {
    const url = window.location.href;

    if (navigator.share) {
        navigator
            .share({
                title: props.proyek.judul,
                text: 'Lihat proyek keren ini di IgnitePad!',
                url,
            })
            .catch((error) => console.log('Error sharing', error));
    } else {
        navigator.clipboard
            .writeText(url)
            .then(() => {
                alert('Link berhasil disalin ke clipboard!');
            })
            .catch(() => {
                prompt('Salin link ini:', url);
            });
    }
};

// Edit proyek
const editProyek = () => {
    router.get(route('proyek.edit', props.proyek.slug));
};

// Hapus proyek
const deleteProyek = () => {
    if (
        confirm(
            'Apakah Anda yakin ingin menghapus proyek ini? Tindakan ini tidak bisa dibatalkan.',
        )
    ) {
        router.delete(route('proyek.destroy', props.proyek.id));
    }
};

// ================ KOMENTAR UTAMA ================\
const commentForm = useForm({
    isi: '',
});

const submitComment = () => {
    if (!user.value) {
        router.get(route('login'));
        return;
    }

    commentForm.post(route('komentar.store', props.proyek.id), {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
};

const deleteComment = (id) => {
    if (confirm('Hapus komentar ini?')) {
        router.delete(route('komentar.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Edit komentar
const editingCommentId = ref(null);
const editCommentForm = useForm({
    isi: '',
});

const startEditComment = (komentar) => {
    editingCommentId.value = komentar.id;
    let content = komentar.isi || '';
    if (!content && komentar.isi_html) {
        const temp = document.createElement('div');
        temp.innerHTML = komentar.isi_html;
        content = temp.textContent || temp.innerText || '';
    }
    editCommentForm.isi = content;
};

const cancelEditComment = () => {
    editingCommentId.value = null;
    editCommentForm.reset();
};

const updateComment = () => {
    editCommentForm.put(route('komentar.update', editingCommentId.value), {
        preserveScroll: true,
        onSuccess: () => cancelEditComment(),
    });
};

// Like komentar (utama dan balasan)
const toggleLikeComment = async (komentar) => {
    if (!user.value) {
        router.get(route('login'));
        return;
    }

    const wasLiked = komentar.is_liked;
    const wasDisliked = komentar.is_disliked;

    // Update optimistik
    komentar.is_liked = !wasLiked;
    komentar.is_disliked = false;
    komentar.jumlah_suka += wasLiked ? -1 : 1;
    if (wasDisliked) komentar.jumlah_dislikes -= 1;

    try {
        const response = await axios.post(route('komentar.like', komentar.id));
        komentar.is_liked = response.data.liked;
        komentar.is_disliked = response.data.disliked;
        komentar.jumlah_suka = response.data.count;
        komentar.jumlah_dislikes = response.data.dislike_count;
    } catch (error) {
        komentar.is_liked = wasLiked;
        komentar.is_disliked = wasDisliked;
        komentar.jumlah_suka += wasLiked ? 1 : -1;
        if (wasDisliked) komentar.jumlah_dislikes += 1;
    }
};

// Dislike komentar (utama dan balasan)
const toggleDislikeComment = async (komentar) => {
    if (!user.value) {
        router.get(route('login'));
        return;
    }

    const wasLiked = komentar.is_liked;
    const wasDisliked = komentar.is_disliked;

    // Update optimistik
    komentar.is_disliked = !wasDisliked;
    komentar.is_liked = false;
    komentar.jumlah_dislikes += wasDisliked ? -1 : 1;
    if (wasLiked) komentar.jumlah_suka -= 1;

    try {
        const response = await axios.post(route('komentar.dislike', komentar.id));
        komentar.is_liked = response.data.liked;
        komentar.is_disliked = response.data.disliked;
        komentar.jumlah_suka = response.data.count;
        komentar.jumlah_dislikes = response.data.dislike_count;
    } catch (error) {
        komentar.is_liked = wasLiked;
        komentar.is_disliked = wasDisliked;
        komentar.jumlah_dislikes += wasDisliked ? 1 : -1;
        if (wasLiked) komentar.jumlah_suka += 1;
    }
};

// ================ BALASAN KOMENTAR ================\
const replyingToId = ref(null);
const replyForm = useForm({
    isi: '',
    induk_id: null,
});

const startReply = (komentarId) => {
    replyingToId.value = komentarId;
    replyForm.induk_id = komentarId;
    replyForm.isi = '';
};

const cancelReply = () => {
    replyingToId.value = null;
    replyForm.reset();
};

const submitReply = () => {
    replyForm.post(route('komentar.store', props.proyek.id), {
        preserveScroll: true,
        onSuccess: () => cancelReply(),
    });
};

// ================ EDIT BALASAN ================\
const editingReplyId = ref(null);
const editReplyForm = useForm({
    isi: '',
});

const startEditReply = (balasan) => {
    editingReplyId.value = balasan.id;
    let content = balasan.isi || '';
    if (!content && balasan.isi_html) {
        const temp = document.createElement('div');
        temp.innerHTML = balasan.isi_html;
        content = temp.textContent || temp.innerText || '';
    }
    editReplyForm.isi = content;
};

const cancelEditReply = () => {
    editingReplyId.value = null;
    editReplyForm.reset();
};

const updateReply = () => {
    editReplyForm.put(route('komentar.update', editingReplyId.value), {
        preserveScroll: true,
        onSuccess: () => cancelEditReply(),
    });
};

// ================ MODAL KONFIRMASI HAPUS ================\
const deleteModalOpen = ref(false);
const komentarToDelete = ref(null);

const showDeleteModal = (id) => {
    komentarToDelete.value = id;
    deleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (komentarToDelete.value) {
        router.delete(route('komentar.destroy', komentarToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                deleteModalOpen.value = false;
                komentarToDelete.value = null;
            },
        });
    }
};

const cancelDelete = () => {
    deleteModalOpen.value = false;
    komentarToDelete.value = null;
};

// Format tanggal
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Cek apakah komentar sudah diedit
const isEdited = (komentar) => {
    if (!komentar.created_at || !komentar.updated_at) return false;
    const created = new Date(komentar.created_at).getTime();
    const updated = new Date(komentar.updated_at).getTime();
    return (updated - created) > 60000;
};

// Format waktu relatif
const relativeTime = (date) => {
    const now = new Date();
    const past = new Date(date);
    const diffMs = now - past;
    const diffSeconds = Math.floor(diffMs / 1000);
    const diffMinutes = Math.floor(diffSeconds / 60);
    const diffHours = Math.floor(diffMinutes / 60);
    const diffDays = Math.floor(diffHours / 24);
    const diffWeeks = Math.floor(diffDays / 7);
    const diffMonths = Math.floor(diffDays / 30);
    const diffYears = Math.floor(diffDays / 365);

    if (diffSeconds < 30) return 'baru saja';
    if (diffSeconds < 60) return `${diffSeconds} detik lalu`;
    if (diffMinutes < 60) return `${diffMinutes} menit lalu`;
    if (diffHours < 24) return `${diffHours} jam lalu`;
    if (diffDays === 1) return 'kemarin';
    if (diffDays < 7) return `${diffDays} hari lalu`;
    if (diffWeeks === 1) return '1 minggu lalu';
    if (diffWeeks < 4) return `${diffWeeks} minggu lalu`;
    if (diffMonths === 1) return '1 bulan lalu';
    if (diffMonths < 12) return `${diffMonths} bulan lalu`;
    if (diffYears === 1) return '1 tahun lalu';
    return `${diffYears} tahun lalu`;
};

// State untuk show/hide balasan (Default: hidden untuk kerapian, atau visible sesuai preferensi)
// Kita buat default expanded untuk UX yang lebih baik, tapi user bisa toggle
const hiddenReplies = ref(new Set());

const toggleReplies = (komentarId) => {
    if (hiddenReplies.value.has(komentarId)) {
        hiddenReplies.value.delete(komentarId);
    } else {
        hiddenReplies.value.add(komentarId);
    }
    // Re-assign to trigger reactivity if needed (Vue 3 Set reactivity can be tricky with direct mutation depending on version)
    hiddenReplies.value = new Set(hiddenReplies.value);
};
</script>

<template>
    <Head :title="proyek.judul" />

    <PublicLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Navigasi Kembali -->
            <div class="mb-6">
                <Link :href="isOwner ? route('proyek.saya') : route('proyek.index')"
                    class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors group">
                <div class="bg-gray-100 group-hover:bg-indigo-50 p-1.5 rounded-full mr-2 transition-colors">
                    <ChevronLeft :size="16" />
                </div>
                {{ isOwner ? 'Kembali ke Dashboard Saya' : 'Jelajah Proyek Lain' }}
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- KONTEN KIRI (8 kolom) -->
                <div class="lg:col-span-8 space-y-8">
                    <!-- Gambar Hero -->
                    <div
                        class="rounded-3xl overflow-hidden shadow-xl shadow-indigo-100/50 border border-gray-100 aspect-video relative group">
                        <img :src="'/storage/' + proyek.thumbnail"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                            :alt="proyek.judul">

                        <!-- Badge Admin -->
                        <div v-if="isAdmin && !isOwner"
                            class="absolute top-4 right-4 bg-white/90 backdrop-blur text-red-600 px-3 py-1.5 rounded-full shadow-sm border border-red-100 flex items-center gap-1.5 text-xs font-bold">
                            <ShieldAlert :size="14" />
                            MODE ADMIN
                        </div>
                    </div>

                    <!-- Judul & Konten -->
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <!-- Baris kategori & tanggal -->
                        <div class="flex items-center gap-3 mb-4">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-wider">
                                {{ proyek.kategori.nama }}
                            </span>
                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                <Clock :size="12" />
                                {{ new Date(proyek.terbit_pada).toLocaleDateString('id-ID', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric'
                                }) }}
                            </span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-6 tracking-tight leading-tight">
                            {{ proyek.judul }}
                        </h1>

                        <div class="prose prose-lg prose-indigo max-w-none text-gray-600 leading-relaxed"
                            v-html="proyek.konten_html"></div>
                    </div>

                    <!-- Galeri Screenshot -->
                    <div v-if="proyek.galeri_gambar && proyek.galeri_gambar.length > 0"
                        class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span>
                            Galeri Proyek
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="(img, i) in proyek.galeri_gambar" :key="i"
                                class="rounded-2xl overflow-hidden border border-gray-100 hover:shadow-md transition-all cursor-pointer h-48">
                                <img :src="'/storage/' + img"
                                    class="w-full h-full object-cover hover:scale-110 transition-transform duration-500"
                                    alt="Screenshot proyek" />
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN KOMENTAR -->
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100" id="komentar">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <MessageSquare :size="24" class="text-indigo-600" />
                                Diskusi
                            </h3>
                            <span class="bg-gray-50 text-gray-600 px-4 py-1.5 rounded-full text-xs font-bold border border-gray-100">
                                {{ totalKomentarCount }} Komentar
                            </span>
                        </div>

                        <!-- Form Input Komentar -->
                        <div class="flex gap-4 mb-10">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0">
                                <img v-if="user"
                                    :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&background=random`"
                                    class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    <MessageCircle :size="20" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <div v-if="user">
                                    <div class="relative group">
                                        <textarea v-model="commentForm.isi" rows="3"
                                            class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-0 text-sm p-4 pr-4 transition-all resize-none placeholder:text-gray-400"
                                            placeholder="Tambahkan komentar..."></textarea>
                                        
                                        <!-- Tombol Kirim Professional -->
                                        <div class="flex justify-end mt-2">
                                             <button @click="submitComment" :disabled="!commentForm.isi"
                                                class="inline-flex items-center gap-2 px-6 py-2 bg-indigo-600 text-white rounded-full text-sm font-bold hover:bg-indigo-700 transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:shadow-none transform active:scale-95">
                                                Kirim <Send :size="14" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-else
                                    class="bg-gray-50 rounded-2xl p-6 text-center border border-gray-200 border-dashed">
                                    <p class="text-sm text-gray-500 mb-3">Login untuk bergabung dalam diskusi</p>
                                    <Link :href="route('login')"
                                        class="inline-block px-6 py-2 bg-white border border-gray-200 rounded-full text-sm font-bold text-gray-700 hover:border-indigo-300 hover:text-indigo-600 transition-all shadow-sm">
                                    Login / Daftar
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Komentar -->
                        <div class="space-y-8">
                            <!-- State Kosong -->
                            <div v-if="komentars.length === 0" class="text-center py-12 text-gray-400">
                                <MessageSquare :size="48" class="mx-auto mb-4 opacity-30" />
                                <p class="text-sm font-medium">Belum ada komentar. Jadilah yang pertama!</p>
                            </div>

                            <!-- Komentar Utama Loop -->
                            <div v-for="k in komentars" :key="k.id" class="relative group">
                                <div class="flex gap-4">
                                    <!-- Avatar Komentar Utama -->
                                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(k.user.nama)}&background=random`"
                                        class="w-10 h-10 rounded-full border border-gray-100 shadow-sm z-10 relative bg-white object-cover flex-shrink-0">

                                    <div class="flex-1 min-w-0">
                                        <!-- Header Komentar -->
                                        <div class="flex items-start justify-between gap-2 mb-1">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-sm font-bold text-gray-900">{{ k.user.nama }}</h4>
                                                    <span v-if="k.user_id === proyek.user_id"
                                                        class="bg-indigo-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold tracking-wide">PEMBUAT</span>
                                                    <span class="text-xs text-gray-400">{{ relativeTime(k.created_at) }}</span>
                                                    <span v-if="isEdited(k)" class="text-xs text-gray-400 italic">· (diedit)</span>
                                                </div>
                                            </div>

                                            <!-- Aksi Admin/Pemilik -->
                                            <div v-if="user && (user.id === k.user_id || isAdmin)"
                                                class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button v-if="user.id === k.user_id" @click="startEditComment(k)"
                                                    class="p-1 text-gray-400 hover:text-indigo-600 transition-colors rounded-md hover:bg-indigo-50"
                                                    title="Edit">
                                                    <Edit :size="14" />
                                                </button>
                                                <button @click="showDeleteModal(k.id)"
                                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors rounded-md hover:bg-red-50"
                                                    title="Hapus">
                                                    <Trash2 :size="14" />
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Isi Komentar -->
                                        <div v-if="editingCommentId !== k.id"
                                            class="text-gray-700 text-sm leading-relaxed mb-3 prose prose-sm max-w-none"
                                            v-html="k.isi_html || k.isi"></div>
                                        <div v-else class="mb-3">
                                            <textarea v-model="editCommentForm.isi" rows="3"
                                                class="w-full rounded-xl border-gray-300 text-sm p-3 focus:border-indigo-500 focus:ring-indigo-200 shadow-sm"></textarea>
                                            <div class="flex justify-end gap-2 mt-2">
                                                <button @click="cancelEditComment"
                                                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-100 transition-colors">Batal</button>
                                                <button @click="updateComment"
                                                    :disabled="editCommentForm.processing || !editCommentForm.isi"
                                                    class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition-colors disabled:opacity-50">Simpan</button>
                                            </div>
                                        </div>

                                        <!-- Action Bar (Like, Dislike, Reply, View Replies) -->
                                        <div class="flex items-center gap-5">
                                            <!-- Like Button (Tanpa Text "Suka") -->
                                            <button @click="toggleLikeComment(k)"
                                                class="flex items-center gap-1.5 text-xs font-bold transition-colors group/btn"
                                                :class="k.is_liked ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600'">
                                                <ThumbsUp :size="16" :class="{ 'fill-current': k.is_liked }" class="transition-transform group-active/btn:scale-90" />
                                                <span v-if="k.jumlah_suka > 0">{{ k.jumlah_suka }}</span>
                                            </button>

                                            <!-- Dislike -->
                                            <button @click="toggleDislikeComment(k)"
                                                class="flex items-center gap-1.5 text-xs font-bold transition-colors group/btn"
                                                :class="k.is_disliked ? 'text-red-500' : 'text-gray-400 hover:text-gray-600'">
                                                <ThumbsDown :size="16" :class="{ 'fill-current': k.is_disliked }" class="transition-transform group-active/btn:scale-90" />
                                                <span v-if="k.jumlah_dislikes > 0">{{ k.jumlah_dislikes }}</span>
                                            </button>

                                            <!-- Tombol Balas -->
                                            <button v-if="user" @click="startReply(k.id)"
                                                class="text-xs font-bold text-gray-500 hover:text-gray-900 flex items-center gap-1.5 transition-colors py-1 px-2 rounded-md hover:bg-gray-100">
                                                <MessageCircle :size="16" />
                                                Balas
                                            </button>

                                            <!-- Tombol Toggle Balasan (Style sama dengan Balas) -->
                                            <button v-if="k.balasan && k.balasan.length > 0" @click="toggleReplies(k.id)"
                                                 class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 transition-colors py-1 px-2 rounded-md hover:bg-indigo-50">
                                                <template v-if="hiddenReplies.has(k.id)">
                                                    <ChevronDown :size="16" />
                                                    Lihat {{ k.balasan.length }} balasan
                                                </template>
                                                <template v-else>
                                                    <ChevronUp :size="16" />
                                                    Sembunyikan
                                                </template>
                                            </button>
                                        </div>

                                        <!-- CONTAINER BALASAN (TREE VISUAL) -->
                                        <div v-if="k.balasan && k.balasan.length > 0 && !hiddenReplies.has(k.id)" class="mt-2 relative">
                                            <!-- Garis Vertikal Induk (Menghubungkan Avatar Utama ke Bawah) -->
                                            <!-- Offset left harus pas ditengah avatar utama (w-10 -> center 1.25rem / 20px) -->
                                            <!-- Kita geser container balasan ke kanan agar avatar balasan sejajar dengan garis -->
                                            
                                            <div class="pt-2 pb-2"> <!-- Spacer -->
                                                <div v-for="(b, index) in k.balasan" :key="b.id" class="relative pl-12 mb-4 last:mb-0">
                                                    <!-- VISUAL ROUTECAUSE / LINE THREAD (CURVED) -->
                                                    <!-- Garis Vertikal dari atas sampai belokan -->
                                                    <!-- Posisi absolute relative terhadap parent comment avatar center (-left 44px approx dari pl-12) -->
                                                    <!-- Kita gunakan pseudo-element atau div absolute di dalam loop -->
                                                    
                                                    <!-- Garis Melengkung (L Shape) -->
                                                    <div class="absolute left-[20px] top-[-10px] bottom-auto w-8 h-[40px] border-b-2 border-l-2 border-gray-200 rounded-bl-2xl pointer-events-none">
                                                        <!-- Top adjustment depends on spacing. height adjustment connects to avatar center -->
                                                    </div>
                                                    
                                                    <!-- Jika bukan item terakhir, lanjutkan garis vertikal ke bawah -->
                                                    <div v-if="index !== k.balasan.length - 1" 
                                                         class="absolute left-[20px] top-[30px] bottom-[-20px] w-0.5 bg-gray-200 pointer-events-none">
                                                    </div>

                                                    <!-- Item Balasan -->
                                                    <div class="flex gap-4 group/reply">
                                                        <!-- Avatar Balasan (Ukuran SAMA dengan utama) -->
                                                        <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(b.user.nama)}&background=random`"
                                                            class="w-10 h-10 rounded-full border border-white shadow-sm z-10 relative bg-white object-cover flex-shrink-0">

                                                        <div class="flex-1 min-w-0">
                                                            <!-- Header Balasan -->
                                                            <div class="flex items-start justify-between gap-2 mb-1">
                                                                <div class="flex-1">
                                                                    <div class="flex items-center gap-2 flex-wrap">
                                                                        <span class="font-bold text-sm text-gray-900">{{ b.user.nama }}</span>
                                                                        <span v-if="b.user_id === proyek.user_id"
                                                                            class="bg-indigo-100 text-indigo-700 text-[10px] px-2 py-0.5 rounded-full font-bold">PEMBUAT</span>
                                                                        <span class="text-xs text-gray-400">{{ relativeTime(b.created_at) }}</span>
                                                                    </div>
                                                                </div>
                                                                <!-- Aksi Edit/Hapus Balasan -->
                                                                <div v-if="user && (user.id === b.user_id || isAdmin)"
                                                                    class="flex gap-1 opacity-0 group-hover/reply:opacity-100 transition-opacity">
                                                                    <button v-if="user.id === b.user_id" @click="startEditReply(b)"
                                                                        class="p-1 text-gray-400 hover:text-indigo-600 rounded-md hover:bg-indigo-50">
                                                                        <Edit :size="14" />
                                                                    </button>
                                                                    <button @click="showDeleteModal(b.id)"
                                                                        class="p-1 text-gray-400 hover:text-red-600 rounded-md hover:bg-red-50">
                                                                        <Trash2 :size="14" />
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <!-- Isi Balasan -->
                                                            <div v-if="editingReplyId !== b.id">
                                                                <div class="text-gray-700 text-sm leading-relaxed mb-2 prose prose-sm max-w-none"
                                                                    v-html="b.isi_html || b.isi"></div>
                                                                
                                                                <!-- Aksi Balasan (Like/Dislike) -->
                                                                <div class="flex gap-4">
                                                                    <button @click="toggleLikeComment(b)"
                                                                        class="flex items-center gap-1 text-xs font-bold transition-colors"
                                                                        :class="b.is_liked ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600'">
                                                                        <ThumbsUp :size="14" :class="{ 'fill-current': b.is_liked }" />
                                                                        {{ b.jumlah_suka || '' }}
                                                                    </button>
                                                                    <button @click="toggleDislikeComment(b)"
                                                                        class="flex items-center gap-1 text-xs font-bold transition-colors"
                                                                        :class="b.is_disliked ? 'text-red-500' : 'text-gray-400 hover:text-gray-600'">
                                                                        <ThumbsDown :size="14" :class="{ 'fill-current': b.is_disliked }" />
                                                                        {{ b.jumlah_dislikes || '' }}
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <!-- Edit Form Balasan -->
                                                            <div v-else>
                                                                <textarea v-model="editReplyForm.isi" rows="2"
                                                                    class="w-full rounded-xl border-gray-300 text-sm p-3 focus:border-indigo-500 focus:ring-indigo-200 mb-2"></textarea>
                                                                <div class="flex justify-end gap-2">
                                                                    <button @click="cancelEditReply"
                                                                        class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-100">Batal</button>
                                                                    <button @click="updateReply"
                                                                        :disabled="editReplyForm.processing || !editReplyForm.isi"
                                                                        class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 disabled:opacity-50">Simpan</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- FORM BALASAN (DI BAWAH LIST BALASAN) -->
                                        <div v-if="replyingToId === k.id" class="mt-4 pl-12 relative animate-in fade-in slide-in-from-top-2 duration-200">
                                             <!-- Garis konektor untuk form -->
                                             <div class="absolute left-[20px] top-[-10px] w-8 h-[40px] border-b-2 border-l-2 border-gray-200 rounded-bl-2xl pointer-events-none"></div>
                                             
                                            <div class="flex gap-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 flex-shrink-0 overflow-hidden z-10">
                                                    <img v-if="user"
                                                        :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&background=random`"
                                                        class="w-full h-full object-cover">
                                                </div>
                                                <div class="flex-1">
                                                    <textarea v-model="replyForm.isi" rows="2"
                                                        class="w-full rounded-xl border-gray-200 text-sm bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-200 p-3"
                                                        placeholder="Tulis balasan Anda..."></textarea>
                                                    <div class="flex justify-end gap-2 mt-2">
                                                        <button @click="cancelReply"
                                                            class="px-4 py-2 rounded-full text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">Batal</button>
                                                        <button @click="submitReply"
                                                            :disabled="replyForm.processing || !replyForm.isi"
                                                            class="px-4 py-2 bg-indigo-600 text-white rounded-full text-xs font-bold hover:bg-indigo-700 transition-all shadow-sm hover:shadow disabled:opacity-50">
                                                            Kirim Balasan
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR KANAN (4 kolom) -->
                <div class="lg:col-span-4 space-y-6">

                    <!-- CARD DASHBOARD PEMBUAT / ADMIN (DESAIN BARU CLEAN PROFESSIONAL) -->
                    <div v-if="isManagementMode"
                        class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden relative ring-1 ring-gray-900/5">
                        
                        <!-- Header Clean White -->
                        <div class="p-6 border-b border-gray-100 bg-white">
                            <div class="flex justify-between items-center mb-1">
                                <h3 class="font-bold text-gray-900 text-base tracking-tight flex items-center gap-2">
                                    <Settings :size="18" class="text-indigo-600" /> 
                                    {{ isOwner ? 'Panel Creator' : 'Administrasi' }}
                                </h3>
                                <span
                                    class="px-2.5 py-1 rounded-md text-[10px] font-bold border tracking-wide"
                                    :class="proyek.status === 'published' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-gray-50 text-gray-600 border-gray-100'">
                                    {{ proyek.status.toUpperCase() }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">Kelola proyek dan pantau performa.</p>
                        </div>

                        <!-- Konten Card -->
                        <div class="p-6 bg-gray-50/30">
                            <div class="grid grid-cols-3 gap-3 mb-6">
                                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
                                    <div class="text-xs text-gray-400 font-medium mb-1">Views</div>
                                    <div class="text-lg font-extrabold text-gray-900">{{ proyek.jumlah_lihat }}</div>
                                </div>
                                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
                                    <div class="text-xs text-gray-400 font-medium mb-1">Likes</div>
                                    <div class="text-lg font-extrabold text-gray-900">{{ likeCount }}</div>
                                </div>
                                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
                                    <div class="text-xs text-gray-400 font-medium mb-1">Saves</div>
                                    <div class="text-lg font-extrabold text-gray-900">{{ proyek.jumlah_simpan }}</div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <button @click="editProyek"
                                    class="w-full py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold text-sm hover:border-indigo-500 hover:text-indigo-600 hover:shadow-md transition-all flex justify-center items-center gap-2">
                                    <Edit :size="16" /> Edit Proyek
                                </button>
                                <button @click="deleteProyek"
                                    class="w-full py-2.5 bg-white border border-red-100 text-red-500 rounded-xl font-bold text-sm hover:bg-red-50 transition-all flex justify-center items-center gap-2">
                                    <Trash2 :size="16" /> Hapus Proyek
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- CARD STATISTIK PUBLIK -->
                    <div v-else class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center gap-2">
                                <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(proyek.user.nama)}&background=random`"
                                    class="w-10 h-10 rounded-full border-2 border-white shadow-sm object-cover">
                                <div>
                                    <div class="text-xs text-gray-500 font-medium">Dibuat oleh</div>
                                    <div class="text-sm font-bold text-gray-900">{{ proyek.user.nama }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-around py-4 border-t border-b border-gray-50 mb-6">
                            <div class="text-center">
                                <div class="font-extrabold text-gray-900 text-lg">{{ proyek.jumlah_lihat }}</div>
                                <div class="text-[10px] uppercase font-bold text-gray-400 tracking-wide">Dilihat</div>
                            </div>
                            <div class="w-px h-8 bg-gray-100"></div>
                            <div class="text-center">
                                <div class="font-extrabold text-gray-900 text-lg">{{ likeCount }}</div>
                                <div class="text-[10px] uppercase font-bold text-gray-400 tracking-wide">Disukai</div>
                            </div>
                            <div class="w-px h-8 bg-gray-100"></div>
                            <div class="text-center">
                                <div class="font-extrabold text-gray-900 text-lg">{{
                                    new Date(proyek.terbit_pada).getFullYear() }}</div>
                                <div class="text-[10px] uppercase font-bold text-gray-400 tracking-wide">Tahun</div>
                            </div>
                        </div>

                        <!-- TOMBOL AKSI -->
                        <div class="flex items-center gap-3">
                            <button @click="toggleLike"
                                class="h-12 w-12 rounded-full flex items-center justify-center transition-all duration-300 border-2 flex-shrink-0"
                                :class="isLiked ? 'bg-red-50 border-red-100 text-red-500 scale-110 shadow-red-100 shadow-lg' : 'bg-gray-50 border-gray-50 text-gray-400 hover:bg-white hover:border-gray-200 hover:shadow-md'"
                                title="Sukai proyek ini">
                                <Heart :size="22" :class="{ 'fill-current': isLiked }" />
                            </button>

                            <button @click="toggleSave"
                                class="h-12 w-12 rounded-full flex items-center justify-center transition-all duration-300 border-2 flex-shrink-0"
                                :class="isSaved ? 'bg-indigo-50 border-indigo-100 text-indigo-600 scale-110 shadow-indigo-100 shadow-lg' : 'bg-gray-50 border-gray-50 text-gray-400 hover:bg-white hover:border-gray-200 hover:shadow-md'"
                                title="Simpan proyek">
                                <Bookmark :size="22" :class="{ 'fill-current': isSaved }" />
                            </button>

                            <button @click="shareProyek"
                                class="h-12 flex-1 rounded-full bg-gray-900 text-white font-bold text-sm flex items-center justify-center gap-2 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-200 transition-all">
                                <Share2 :size="18" />
                                <span>Bagikan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Pills Teknologi -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Teknologi</h4>
                        <div class="flex flex-wrap gap-2">
                            <div v-for="t in proyek.teknologi" :key="t.id"
                                class="px-3 py-2 bg-white border border-gray-100 rounded-xl text-xs font-bold text-gray-600 flex items-center gap-2 shadow-sm hover:border-indigo-200 hover:shadow-md transition-all">
                                <img :src="t.ikon_url" class="w-4 h-4 object-contain"> {{ t.nama }}
                            </div>
                        </div>
                    </div>

                    <!-- Tautan Demo & Repo -->
                    <div class="space-y-3">
                        <a v-if="proyek.url_demo" :href="proyek.url_demo" target="_blank"
                            class="block w-full py-4 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-2xl font-bold text-center shadow-lg shadow-indigo-200 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                            <div class="flex items-center justify-center gap-2">
                                <ExternalLink :size="20" /> Demo Langsung
                            </div>
                        </a>
                        <a v-if="proyek.url_repository" :href="proyek.url_repository" target="_blank"
                            class="block w-full py-4 bg-white border-2 border-gray-100 text-gray-800 rounded-2xl font-bold text-center hover:border-gray-900 hover:text-gray-900 hover:shadow-md transition-all">
                            <div class="flex items-center justify-center gap-2">
                                <Github :size="20" /> Repositori
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="deleteModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity backdrop-blur-sm"
                        @click="cancelDelete"></div>
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div
                            class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                            <div
                                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                <Trash2 class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="text-center">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    Hapus Komentar
                                </h3>
                                <p class="text-sm text-gray-500 mb-6">
                                    Apakah Anda yakin ingin menghapus komentar ini? Tindakan ini tidak dapat
                                    dibatalkan.
                                </p>
                            </div>
                            <div class="flex gap-3">
                                <button @click="cancelDelete" type="button"
                                    class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition-colors">
                                    Batal
                                </button>
                                <button @click="confirmDelete" type="button"
                                    class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium text-sm transition-colors">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </PublicLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Penyesuaian tipografi */
h1, h2, h3, h4, h5, h6 {
    letter-spacing: -0.025em;
}
</style>
