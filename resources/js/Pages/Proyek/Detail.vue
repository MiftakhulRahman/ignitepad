<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, usePage, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import {
    Heart,
    Bookmark,
    Share2,
    Eye,
    Github,
    ExternalLink,
    MessageSquare,
    Calendar,
    ChevronLeft,
    Edit,
    Trash2,
    ShieldAlert,
    Send,
    Reply,
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

// Admin check
const isAdmin = computed(
    () => user.value && user.value.perans.some((r) => r.slug === 'superadmin'),
);

// Management mode = owner atau admin
const isManagementMode = computed(() => props.isOwner || isAdmin.value);

// State like/save
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

    // Optimistic update
    isLiked.value = !isLiked.value;
    likeCount.value += isLiked.value ? 1 : -1;

    try {
        const response = await axios.post(route('proyek.like', props.proyek.id));
        isLiked.value = response.data.liked;
        likeCount.value = response.data.count;
    } catch (error) {
        // Revert jika gagal
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

// Share link proyek
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

// ---------------- KOMENTAR UTAMA ----------------
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
    // Use isi if available, otherwise extract text from isi_html
    let content = komentar.isi || '';
    if (!content && komentar.isi_html) {
        // Strip HTML tags from isi_html
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
    komentar.is_liked = !wasLiked;
    komentar.jumlah_suka += wasLiked ? -1 : 1;

    try {
        await axios.post(route('komentar.like', komentar.id));
    } catch (error) {
        komentar.is_liked = wasLiked;
        komentar.jumlah_suka += wasLiked ? 1 : -1;
    }
};

// ---------------- BALASAN KOMENTAR ----------------
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

// ---------------- EDIT BALASAN ----------------
const editingReplyId = ref(null);
const editReplyForm = useForm({
    isi: '',
});

const startEditReply = (balasan) => {
    editingReplyId.value = balasan.id;
    // Use isi if available, otherwise extract text from isi_html
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

// ---------------- KONFIRMASI HAPUS ----------------
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

// Check if comment has been edited
const isEdited = (komentar) => {
    if (!komentar.created_at || !komentar.updated_at) return false;
    const created = new Date(komentar.created_at).getTime();
    const updated = new Date(komentar.updated_at).getTime();
    // Consider edited if updated is more than 1 minute after created
    return (updated - created) > 60000;
};
</script>

<template>

    <Head :title="proyek.judul" />

    <PublicLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back link -->
            <div class="mb-6">
                <Link :href="isOwner ? route('proyek.saya') : route('proyek.index')"
                    class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                <ChevronLeft :size="16" class="mr-1" />
                {{ isOwner ? 'Kembali ke Proyek Saya' : 'Kembali ke Jelajah' }}
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Kolom konten utama -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Thumbnail / Hero -->
                    <div
                        class="rounded-2xl overflow-hidden shadow-lg border border-gray-100 bg-gray-100 aspect-video relative">
                        <img :src="'/storage/' + proyek.thumbnail" class="w-full h-full object-cover"
                            :alt="proyek.judul">

                        <!-- Badge Admin Mode -->
                        <div v-if="isAdmin && !isOwner"
                            class="absolute top-4 left-4 bg-red-600 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1">
                            <ShieldAlert :size="14" />
                            Mode Admin
                        </div>
                    </div>

                    <!-- Konten Proyek -->
                    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100">
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">
                            {{ proyek.judul }}
                        </h1>
                        <div class="prose prose-indigo max-w-none text-gray-700 leading-relaxed"
                            v-html="proyek.konten_html"></div>
                    </div>

                    <!-- Galeri Screenshot -->
                    <div v-if="proyek.galeri_gambar && proyek.galeri_gambar.length > 0"
                        class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Galeri Screenshot
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="(img, index) in proyek.galeri_gambar" :key="index"
                                class="rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
                                <img :src="'/storage/' + img" class="w-full h-48 object-cover"
                                    :alt="'Screenshot ' + (index + 1)">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar kanan -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">
                        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                            <!-- Kategori -->
                            <div class="mb-5">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ proyek.kategori.nama }}
                                </span>
                            </div>

                            <!-- Management Mode (Owner / Admin) -->
                            <div v-if="isManagementMode"
                                class="-mx-6 -mt-6 mb-6 p-6 bg-gray-50 border-b border-gray-200 rounded-t-2xl space-y-4">
                                <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                    {{ isOwner ? 'Kontrol Pemilik' : 'Kontrol Admin' }}
                                </h3>

                                <!-- Status & statistik -->
                                <div
                                    class="flex items-center justify-between bg-white px-3 py-2 rounded-lg border border-gray-200">
                                    <span class="text-xs text-gray-500">Status</span>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold capitalize" :class="proyek.status === 'terbit'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-yellow-100 text-yellow-700'
                                        ">
                                        {{ proyek.status }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div class="bg-white px-2 py-2 rounded border border-gray-200">
                                        <div class="text-[11px] text-gray-400">Dilihat</div>
                                        <div class="font-semibold text-gray-800 text-sm">
                                            {{ proyek.jumlah_lihat }}
                                        </div>
                                    </div>
                                    <div class="bg-white px-2 py-2 rounded border border-gray-200">
                                        <div class="text-[11px] text-gray-400">Disukai</div>
                                        <div class="font-semibold text-gray-800 text-sm">
                                            {{ proyek.jumlah_suka }}
                                        </div>
                                    </div>
                                    <div class="bg-white px-2 py-2 rounded border border-gray-200">
                                        <div class="text-[11px] text-gray-400">Disimpan</div>
                                        <div class="font-semibold text-gray-800 text-sm">
                                            {{ proyek.jumlah_simpan }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Tombol edit/hapus -->
                                <div class="grid grid-cols-2 gap-3 pt-1">
                                    <button @click="editProyek"
                                        class="flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-xs font-medium shadow-sm">
                                        <Edit :size="16" />
                                        Edit
                                    </button>
                                    <button @click="deleteProyek"
                                        class="flex items-center justify-center gap-2 px-4 py-2 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 text-xs font-medium shadow-sm">
                                        <Trash2 :size="16" />
                                        Hapus
                                    </button>
                                </div>
                            </div>

                            <!-- Mode visitor -->
                            <div v-else>
                                <div
                                    class="flex items-center justify-between py-3 border-y border-gray-100 mb-5 text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <Eye :size="18" />
                                        <span class="text-xs">{{ proyek.jumlah_lihat }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <Heart :size="18" />
                                        <span class="text-xs">{{ likeCount }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <Calendar :size="18" />
                                        <span class="text-[11px]">
                                            {{
                                                new Date(proyek.terbit_pada).toLocaleDateString('id-ID', {
                                                    day: 'numeric',
                                                    month: 'short',
                                                    year: 'numeric',
                                                })
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <button @click="toggleLike"
                                        class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-medium transition-all active:scale-95"
                                        :class="isLiked
                                            ? 'bg-red-50 text-red-600 border border-red-200'
                                            : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'
                                            ">
                                        <Heart :size="18" :class="{ 'fill-current': isLiked }" />
                                        {{ isLiked ? 'Disukai' : 'Suka' }}
                                    </button>
                                    <button @click="toggleSave"
                                        class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-medium transition-all active:scale-95"
                                        :class="isSaved
                                            ? 'bg-indigo-50 text-indigo-600 border border-indigo-200'
                                            : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'
                                            ">
                                        <Bookmark :size="18" :class="{ 'fill-current': isSaved }" />
                                        {{ isSaved ? 'Disimpan' : 'Simpan' }}
                                    </button>
                                </div>

                                <button @click="shareProyek"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors text-xs font-medium">
                                    <Share2 :size="16" />
                                    Bagikan Proyek
                                </button>
                            </div>

                            <!-- Info Creator -->
                            <div class="mt-6 pt-5 border-t border-gray-100">
                                <div class="flex items-center gap-3">
                                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(
                                        proyek.user.nama,
                                    )}&background=random`" class="w-10 h-10 rounded-full border border-gray-200"
                                        alt="Avatar creator">
                                    <div class="flex-1 overflow-hidden">
                                        <p class="text-[11px] text-gray-500 uppercase tracking-wide font-semibold">
                                            Pembuat Proyek
                                        </p>
                                        <h4 class="text-sm font-semibold text-gray-900 truncate">
                                            {{ proyek.user.nama }}
                                        </h4>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ proyek.user.prodi?.nama || 'IgnitePad User' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Teknologi -->
                            <div class="mt-6 pt-5 border-t border-gray-100">
                                <h4 class="text-[11px] font-semibold text-gray-500 uppercase mb-3 tracking-wide">
                                    Teknologi
                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <div v-for="tech in proyek.teknologi" :key="tech.id"
                                        class="flex items-center gap-2 px-2.5 py-1 bg-gray-50 border border-gray-200 rounded-full text-[11px] font-medium text-gray-700">
                                        <img :src="tech.ikon_url" class="w-3.5 h-3.5 object-contain" :alt="tech.nama">
                                        <span>{{ tech.nama }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Link Demo & Repo -->
                            <div class="mt-6 space-y-2">
                                <a v-if="proyek.url_demo" :href="proyek.url_demo" target="_blank"
                                    class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium shadow-sm">
                                    <ExternalLink :size="16" />
                                    Live Demo
                                </a>
                                <a v-if="proyek.url_repository" :href="proyek.url_repository" target="_blank"
                                    class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-black transition-colors text-sm font-medium shadow-sm">
                                    <Github :size="16" />
                                    Repository
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION KOMENTAR -->
                <div class="lg:col-span-2 mt-8">
                    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2">
                            <MessageSquare :size="22" class="text-indigo-600" />
                            Komentar
                            <span class="text-sm font-normal text-gray-500">
                                ({{ komentars.length }})
                            </span>
                        </h3>

                        <!-- Form komentar -->
                        <div class="mb-8 flex gap-4">
                            <img :src="user
                                ? `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                    user.nama,
                                )}&background=random`
                                : 'https://ui-avatars.com/api/?name=Guest&background=cccccc'
                                " class="w-10 h-10 rounded-full border border-gray-200 flex-shrink-0" alt="Avatar">
                            <div class="flex-1">
                                <div v-if="user">
                                    <textarea v-model="commentForm.isi" rows="3"
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 resize-none"
                                        placeholder="Tulis komentar yang sopan dan membangun..."></textarea>
                                    <div class="mt-2 flex justify-end">
                                        <button @click="submitComment"
                                            :disabled="commentForm.processing || !commentForm.isi"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                            <Send :size="16" />
                                            Kirim
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 text-center">
                                    <p class="text-gray-600 text-sm mb-2">
                                        Silakan login untuk memberikan komentar.
                                    </p>
                                    <Link :href="route('login')"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition-colors">
                                    Login sekarang
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- List komentar -->
                        <div class="space-y-6">
                            <!-- Kosong -->
                            <div v-if="komentars.length === 0" class="text-center py-10 text-gray-500">
                                <MessageSquare :size="40" class="mx-auto mb-3 text-gray-300" />
                                <p class="text-sm">
                                    Belum ada komentar. Jadilah yang pertama berdiskusi.
                                </p>
                            </div>

                            <!-- Komentar utama -->
                            <div v-for="k in komentars" :key="k.id" class="flex gap-3 items-start">
                                <!-- Avatar -->
                                <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(
                                    k.user.nama,
                                )}&background=random`"
                                    class="w-9 h-9 rounded-full border border-gray-200 flex-shrink-0"
                                    alt="Avatar komentar">

                                <div class="flex-1 min-w-0">
                                    <!-- Kartu komentar -->
                                    <div class="group bg-white border border-gray-200 rounded-2xl px-4 py-3 shadow-sm">
                                        <!-- Header -->
                                        <div class="flex items-start justify-between gap-3 mb-1.5">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-semibold text-gray-900 text-sm truncate">
                                                        {{ k.user.nama }}
                                                    </span>
                                                    <!-- Satu badge Creator di sini -->
                                                    <span v-if="k.user_id === proyek.user_id"
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full border border-indigo-200 bg-indigo-50 text-[10px] font-medium text-indigo-700">
                                                        Creator
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                                    <span>{{ formatDate(k.created_at) }}</span>
                                                    <span v-if="isEdited(k)" class="text-[10px] text-gray-400 italic">
                                                        · Diedit
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Aksi edit/hapus -->
                                            <div v-if="user && (user.id === k.user_id || isAdmin)"
                                                class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button v-if="user.id === k.user_id" @click="startEditComment(k)"
                                                    class="text-gray-400 hover:text-indigo-600" title="Edit komentar">
                                                    <Edit :size="14" />
                                                </button>
                                                <button @click="showDeleteModal(k.id)"
                                                    class="text-gray-400 hover:text-red-600" title="Hapus komentar">
                                                    <Trash2 :size="14" />
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Mode lihat -->
                                        <div v-if="editingCommentId !== k.id">
                                            <div class="text-gray-800 text-sm leading-relaxed break-words mb-2.5 prose prose-sm max-w-none"
                                                style="color: #1f2937;" v-html="k.isi_html || k.isi">
                                            </div>

                                            <div class="flex items-center gap-4 text-xs">
                                                <button @click="toggleLikeComment(k)"
                                                    class="inline-flex items-center gap-1.5 font-medium transition-colors"
                                                    :class="k.is_liked
                                                        ? 'text-red-600'
                                                        : 'text-gray-500 hover:text-red-600'
                                                        ">
                                                    <Heart :size="14" :class="{ 'fill-current': k.is_liked }"
                                                        class="transition-transform" />
                                                    <span>
                                                        {{ k.jumlah_suka > 0 ? k.jumlah_suka : 'Suka' }}
                                                    </span>
                                                </button>

                                                <button v-if="user" @click="startReply(k.id)"
                                                    class="inline-flex items-center gap-1.5 font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                                                    <Reply :size="14" />
                                                    <span>Balas</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Mode edit -->
                                        <div v-else>
                                            <textarea v-model="editCommentForm.isi" rows="3"
                                                class="w-full rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm mb-2 px-3 py-2 resize-none"></textarea>
                                            <div class="flex justify-end gap-2 text-xs">
                                                <button @click="cancelEditComment"
                                                    class="px-3 py-1 font-medium text-gray-600 hover:text-gray-800">
                                                    Batal
                                                </button>
                                                <button @click="updateComment" :disabled="editCommentForm.processing || !editCommentForm.isi
                                                    "
                                                    class="px-3 py-1 rounded bg-indigo-600 text-white font-semibold hover:bg-indigo-700 disabled:opacity-50">
                                                    Simpan
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Form balasan -->
                                        <div v-if="replyingToId === k.id" class="mt-4 pt-3 border-t border-gray-100">
                                            <div class="flex gap-3">
                                                <img v-if="user" :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                    user.nama,
                                                )}&background=random`"
                                                    class="w-8 h-8 rounded-full border border-gray-200 flex-shrink-0"
                                                    alt="Avatar balasan">
                                                <div class="flex-1">
                                                    <textarea v-model="replyForm.isi" rows="2"
                                                        class="w-full rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm mb-2 px-3 py-2 resize-none"
                                                        placeholder="Tulis balasan..."></textarea>
                                                    <div class="flex justify-end gap-2 text-xs">
                                                        <button @click="cancelReply"
                                                            class="px-3 py-1 font-medium text-gray-600 hover:text-gray-800">
                                                            Batal
                                                        </button>
                                                        <button @click="submitReply"
                                                            :disabled="replyForm.processing || !replyForm.isi"
                                                            class="px-3 py-1 rounded bg-indigo-600 text-white font-semibold hover:bg-indigo-700 disabled:opacity-50">
                                                            Kirim balasan
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Balasan level 1 -->
                                    <div v-if="k.balasan && k.balasan.length > 0"
                                        class="mt-3 space-y-3 pl-6 border-l border-gray-200">
                                        <div v-for="b in k.balasan" :key="b.id" class="flex gap-3 items-start group">
                                            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                b.user.nama,
                                            )}&background=random`"
                                                class="w-8 h-8 rounded-full border border-gray-200 flex-shrink-0"
                                                alt="Avatar balasan">

                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="bg-white border border-gray-200 rounded-2xl px-3.5 py-2.5 shadow-sm">
                                                    <div class="flex items-start justify-between gap-3 mb-1">
                                                        <div class="min-w-0">
                                                            <div class="flex items-center gap-2">
                                                                <span
                                                                    class="font-semibold text-gray-900 text-xs truncate">
                                                                    {{ b.user.nama }}
                                                                </span>
                                                                <span v-if="b.user_id === proyek.user_id"
                                                                    class="inline-flex items-center px-1.5 py-0.5 rounded-full border border-indigo-200 bg-indigo-50 text-[9px] font-medium text-indigo-700">
                                                                    Creator
                                                                </span>
                                                            </div>
                                                            <div
                                                                class="flex items-center gap-1.5 text-[11px] text-gray-500">
                                                                <span>{{ formatDate(b.created_at) }}</span>
                                                                <span v-if="isEdited(b)"
                                                                    class="text-[9px] text-gray-400 italic">
                                                                    · Diedit
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div v-if="user && (user.id === b.user_id || isAdmin)"
                                                            class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button v-if="user.id === b.user_id"
                                                                @click="startEditReply(b)"
                                                                class="text-gray-400 hover:text-indigo-600"
                                                                title="Edit balasan">
                                                                <Edit :size="12" />
                                                            </button>
                                                            <button @click="showDeleteModal(b.id)"
                                                                class="text-gray-400 hover:text-red-600"
                                                                title="Hapus balasan">
                                                                <Trash2 :size="12" />
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Mode lihat balasan -->
                                                    <div v-if="editingReplyId !== b.id">
                                                        <div class="text-gray-800 text-xs leading-relaxed break-words mb-1.5 prose prose-sm max-w-none"
                                                            style="color: #1f2937;" v-html="b.isi_html || b.isi">
                                                        </div>

                                                        <button @click="toggleLikeComment(b)"
                                                            class="inline-flex items-center gap-1 text-[11px] font-medium transition-colors"
                                                            :class="b.is_liked
                                                                ? 'text-red-600'
                                                                : 'text-gray-500 hover:text-red-600'
                                                                ">
                                                            <Heart :size="12" :class="{ 'fill-current': b.is_liked }"
                                                                class="transition-transform" />
                                                            <span>
                                                                {{ b.jumlah_suka > 0 ? b.jumlah_suka : 'Suka' }}
                                                            </span>
                                                        </button>
                                                    </div>

                                                    <!-- Mode edit balasan -->
                                                    <div v-else>
                                                        <textarea v-model="editReplyForm.isi" rows="2"
                                                            class="w-full rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-xs mb-2 px-2.5 py-2 resize-none"></textarea>
                                                        <div class="flex justify-end gap-2 text-xs">
                                                            <button @click="cancelEditReply"
                                                                class="px-2.5 py-1 font-medium text-gray-600 hover:text-gray-800">
                                                                Batal
                                                            </button>
                                                            <button @click="updateReply"
                                                                :disabled="editReplyForm.processing || !editReplyForm.isi"
                                                                class="px-2.5 py-1 rounded bg-indigo-600 text-white font-semibold hover:bg-indigo-700 disabled:opacity-50">
                                                                Simpan
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End balasan -->
                                </div>
                            </div>
                            <!-- End v-for komentars -->
                        </div>
                    </div>
                </div>
                <!-- END SECTION KOMENTAR -->
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="deleteModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="cancelDelete"></div>

                    <!-- Modal Content -->
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div
                            class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                            <!-- Icon -->
                            <div
                                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                <Trash2 class="h-6 w-6 text-red-600" />
                            </div>

                            <!-- Content -->
                            <div class="text-center">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    Hapus Komentar
                                </h3>
                                <p class="text-sm text-gray-500 mb-6">
                                    Apakah Anda yakin ingin menghapus komentar ini? Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>

                            <!-- Actions -->
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
</style>
