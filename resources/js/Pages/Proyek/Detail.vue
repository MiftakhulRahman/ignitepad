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
    MoreHorizontal,
    CornerDownRight,
    Code2,
    Users
} from 'lucide-vue-next';

const props = defineProps({
    proyek: Object,
    isOwner: Boolean,
    isAdmin: Boolean,
    hasLiked: Boolean,
    hasSaved: Boolean,
    komentars: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

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

// Removed editProyek and deleteProyek - use Dashboard for management

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
    if (diffSeconds < 60) return `${diffSeconds}d lalu`;
    if (diffMinutes < 60) return `${diffMinutes}m lalu`;
    if (diffHours < 24) return `${diffHours}j lalu`;
    if (diffDays === 1) return 'kemarin';
    if (diffDays < 7) return `${diffDays}h lalu`;
    if (diffWeeks === 1) return '1mg lalu';
    if (diffMonths < 12) return `${diffMonths}bln lalu`;
    return `${diffYears}th lalu`;
};

const hiddenReplies = ref(new Set());
const toggleReplies = (komentarId) => {
    if (hiddenReplies.value.has(komentarId)) {
        hiddenReplies.value.delete(komentarId);
    } else {
        hiddenReplies.value.add(komentarId);
    }
    hiddenReplies.value = new Set(hiddenReplies.value);
};
</script>

<template>

    <Head :title="proyek.judul" />

    <PublicLayout>
        <div class="min-h-screen bg-[#FDF8FD]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

                <div class="mb-8">
                    <Link :href="route('proyek.index')"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-full text-sm font-medium text-gray-600 hover:bg-gray-200/50 hover:text-gray-900 transition-all">
                    <ChevronLeft :size="20" />
                    <span>Kembali ke Jelajah</span>
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

                    <div class="lg:col-span-8 space-y-8">

                        <div class="bg-white rounded-[28px] p-2 shadow-sm border border-gray-100/50">
                            <div class="rounded-[24px] overflow-hidden aspect-video relative group bg-gray-100">
                                <img :src="'/storage/' + proyek.thumbnail"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                    :alt="proyek.judul">
                            </div>

                            <div class="px-6 py-6 sm:px-8 sm:py-8">
                                <div class="flex flex-wrap items-center gap-3 mb-6">
                                    <span
                                        class="px-4 py-1.5 rounded-lg text-sm font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ proyek.kategori.nama }}
                                    </span>

                                    <span class="text-gray-300 mx-1">•</span>

                                    <div class="flex items-center gap-1.5 text-gray-500 text-sm">
                                        <Calendar :size="16" class="text-gray-400" />
                                        <span>{{ formatDate(proyek.terbit_pada) }}</span>
                                    </div>
                                </div>

                                <h1
                                    class="text-4xl sm:text-5xl font-normal text-[#1d1b20] mb-8 leading-tight tracking-tight">
                                    {{ proyek.judul }}
                                </h1>

                                <div class="prose prose-lg prose-slate max-w-none text-gray-600 leading-relaxed 
                                    prose-headings:font-normal prose-headings:text-[#1d1b20] prose-a:text-indigo-600 prose-img:rounded-[20px]"
                                    v-html="proyek.konten_html">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-gray-100/50"
                            id="komentar">
                            <div class="flex items-center justify-between mb-8">
                                <h3 class="text-2xl font-normal text-[#1d1b20]">Diskusi</h3>
                                <div class="bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-full text-sm font-bold">
                                    {{ totalKomentarCount }}
                                </div>
                            </div>

                            <div class="flex gap-4 mb-10">
                                <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                                    <img v-if="user"
                                        :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&background=random`"
                                        class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                        <Users :size="20" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div v-if="user">
                                        <div
                                            class="relative group bg-gray-50 rounded-[24px] border border-transparent focus-within:border-indigo-300 focus-within:bg-white focus-within:shadow-md transition-all duration-200">
                                            <textarea v-model="commentForm.isi" rows="3"
                                                class="w-full bg-transparent border-none rounded-[24px] focus:ring-0 text-gray-700 p-4 resize-none placeholder:text-gray-400"
                                                placeholder="Tulis pendapat Anda..."></textarea>

                                            <div class="flex justify-end p-2 pr-3">
                                                <button @click="submitComment" :disabled="!commentForm.isi"
                                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-full text-sm font-medium hover:bg-indigo-700 hover:shadow-lg disabled:opacity-50 disabled:shadow-none transition-all active:scale-95">
                                                    Kirim
                                                    <Send :size="16" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else
                                        class="bg-gray-50 rounded-[20px] p-6 text-center border border-dashed border-gray-300">
                                        <p class="text-gray-500 mb-3">Ingin bergabung dalam diskusi?</p>
                                        <Link :href="route('login')"
                                            class="inline-block px-6 py-2 bg-white border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                        Masuk ke Akun
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-8">
                                <div v-if="komentars.length === 0" class="text-center py-10">
                                    <div
                                        class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-300">
                                        <MessageSquare :size="24" />
                                    </div>
                                    <p class="text-gray-400">Belum ada komentar.</p>
                                </div>

                                <div v-for="k in komentars" :key="k.id" class="group/comment">
                                    <div class="flex gap-4">
                                        <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(k.user.nama)}&background=random`"
                                            class="w-10 h-10 rounded-full shadow-sm object-cover flex-shrink-0">

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-semibold text-gray-900 text-sm">{{ k.user.nama
                                                    }}</span>
                                                <span v-if="k.user_id === proyek.user_id"
                                                    class="bg-indigo-100 text-indigo-700 text-[10px] px-2 py-0.5 rounded-full font-bold">AUTHOR</span>
                                                <span class="text-xs text-gray-400">• {{ relativeTime(k.created_at)
                                                    }}</span>
                                                <span v-if="isEdited(k)" class="text-xs text-gray-300">(diedit)</span>
                                            </div>

                                            <div v-if="editingCommentId !== k.id"
                                                class="text-gray-700 text-[15px] leading-relaxed mb-3 prose prose-sm max-w-none"
                                                v-html="k.isi_html || k.isi">
                                            </div>

                                            <div v-else class="mb-3">
                                                <textarea v-model="editCommentForm.isi" rows="3"
                                                    class="w-full rounded-[18px] border-gray-300 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-200 text-sm p-3"></textarea>
                                                <div class="flex justify-end gap-2 mt-2">
                                                    <button @click="cancelEditComment"
                                                        class="px-4 py-2 rounded-full text-xs font-bold text-gray-500 hover:bg-gray-100">Batal</button>
                                                    <button @click="updateComment"
                                                        class="px-4 py-2 bg-indigo-600 text-white rounded-full text-xs font-bold hover:shadow-md">Simpan</button>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-6">
                                                <button @click="toggleLikeComment(k)"
                                                    class="flex items-center gap-1.5 group/btn transition-colors"
                                                    :class="k.is_liked ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600'">
                                                    <div
                                                        class="p-1.5 rounded-full group-hover/btn:bg-indigo-50 transition-colors">
                                                        <ThumbsUp :size="18" :class="{ 'fill-current': k.is_liked }" />
                                                    </div>
                                                    <span class="text-xs font-bold" v-if="k.jumlah_suka > 0">{{
                                                        k.jumlah_suka }}</span>
                                                </button>

                                                <button @click="toggleDislikeComment(k)"
                                                    class="flex items-center gap-1.5 group/btn transition-colors"
                                                    :class="k.is_disliked ? 'text-red-500' : 'text-gray-400 hover:text-gray-600'">
                                                    <div
                                                        class="p-1.5 rounded-full group-hover/btn:bg-red-50 transition-colors">
                                                        <ThumbsDown :size="18"
                                                            :class="{ 'fill-current': k.is_disliked }" />
                                                    </div>
                                                </button>

                                                <button v-if="user" @click="startReply(k.id)"
                                                    class="text-xs font-bold text-gray-400 hover:text-gray-900 flex items-center gap-1 transition-colors">
                                                    Balas
                                                </button>

                                                <div v-if="user && (user.id === k.user_id || isAdmin)"
                                                    class="flex gap-2 ml-auto">
                                                    <button v-if="user.id === k.user_id" @click="startEditComment(k)"
                                                        class="text-gray-400 hover:text-indigo-600 p-1">
                                                        <Edit :size="14" />
                                                    </button>
                                                    <button @click="showDeleteModal(k.id)"
                                                        class="text-gray-400 hover:text-red-600 p-1">
                                                        <Trash2 :size="14" />
                                                    </button>
                                                </div>
                                            </div>

                                            <div v-if="k.balasan && k.balasan.length > 0" class="mt-3">
                                                <button @click="toggleReplies(k.id)"
                                                    class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 text-xs font-bold px-3 py-1.5 rounded-full hover:bg-indigo-50 transition-colors">
                                                    <div class="w-5 h-px bg-indigo-200"></div>
                                                    {{ hiddenReplies.has(k.id) ? `Lihat ${k.balasan.length} balasan` :
                                                    'Sembunyikan balasan' }}
                                                </button>
                                            </div>

                                            <div v-if="k.balasan && k.balasan.length > 0 && !hiddenReplies.has(k.id)"
                                                class="mt-4 pl-4 border-l-2 border-gray-100">
                                                <div v-for="b in k.balasan" :key="b.id"
                                                    class="flex gap-4 mb-6 last:mb-0 relative group/reply">
                                                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(b.user.nama)}&background=random`"
                                                        class="w-8 h-8 rounded-full shadow-sm object-cover flex-shrink-0">

                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <span class="font-semibold text-gray-900 text-sm">{{
                                                                b.user.nama }}</span>
                                                            <span v-if="b.user_id === proyek.user_id"
                                                                class="bg-indigo-100 text-indigo-700 text-[9px] px-1.5 py-0.5 rounded-full font-bold">AUTHOR</span>
                                                            <span class="text-xs text-gray-400">{{
                                                                relativeTime(b.created_at) }}</span>
                                                        </div>

                                                        <div v-if="editingReplyId !== b.id"
                                                            class="text-gray-700 text-sm mb-2"
                                                            v-html="b.isi_html || b.isi"></div>
                                                        <div v-else class="mb-2">
                                                            <textarea v-model="editReplyForm.isi" rows="2"
                                                                class="w-full rounded-[16px] border-gray-300 text-sm p-2"></textarea>
                                                            <div class="flex justify-end gap-2 mt-1">
                                                                <button @click="cancelEditReply"
                                                                    class="text-xs font-bold text-gray-500">Batal</button>
                                                                <button @click="updateReply"
                                                                    class="text-xs font-bold text-indigo-600">Simpan</button>
                                                            </div>
                                                        </div>

                                                        <div class="flex gap-4">
                                                            <button @click="toggleLikeComment(b)"
                                                                class="text-xs text-gray-400 hover:text-indigo-600 flex items-center gap-1">
                                                                <ThumbsUp :size="14"
                                                                    :class="{ 'fill-current text-indigo-600': b.is_liked }" />
                                                                {{ b.jumlah_suka || '' }}
                                                            </button>
                                                            <button @click="toggleDislikeComment(b)"
                                                                class="text-xs text-gray-400 hover:text-red-500">
                                                                <ThumbsDown :size="14"
                                                                    :class="{ 'fill-current text-red-500': b.is_disliked }" />
                                                            </button>
                                                            <div v-if="user && (user.id === b.user_id || isAdmin)"
                                                                class="flex gap-2 ml-auto opacity-0 group-hover/reply:opacity-100 transition-opacity">
                                                                <button v-if="user.id === b.user_id"
                                                                    @click="startEditReply(b)"
                                                                    class="text-gray-400 hover:text-indigo-600">
                                                                    <Edit :size="12" />
                                                                </button>
                                                                <button @click="showDeleteModal(b.id)"
                                                                    class="text-gray-400 hover:text-red-600">
                                                                    <Trash2 :size="12" />
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="replyingToId === k.id"
                                                class="mt-4 pl-4 border-l-2 border-gray-100">
                                                <div class="bg-gray-50 rounded-[20px] p-3 border border-indigo-100">
                                                    <textarea v-model="replyForm.isi" rows="2"
                                                        class="w-full bg-transparent border-none text-sm focus:ring-0 resize-none"
                                                        placeholder="Balas komentar..."></textarea>
                                                    <div class="flex justify-end gap-2 mt-2">
                                                        <button @click="cancelReply"
                                                            class="px-3 py-1.5 rounded-full text-xs font-bold text-gray-500 hover:bg-gray-200">Batal</button>
                                                        <button @click="submitReply"
                                                            class="px-3 py-1.5 bg-indigo-600 text-white rounded-full text-xs font-bold hover:shadow-md">Balas</button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 space-y-6">

                        <!-- Removed management mode panel - use Dashboard for project management -->

                        <!-- Creator & Engagement Section -->
                        <div class="bg-white rounded-[28px] p-6 shadow-sm border border-gray-100/50">
                            <div class="flex items-center gap-4 mb-6">
                                <img :src="proyek.user.avatar ? '/storage/' + proyek.user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(proyek.user.nama)}&background=random`"
                                    class="w-14 h-14 rounded-full border-4 border-gray-50 shadow-sm object-cover">
                                <div>
                                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Creator</div>
                                    <div class="text-lg font-normal text-[#1d1b20]">{{ proyek.user.nama }}</div>
                                    <div class="text-xs text-gray-500">{{ proyek.user.prodi?.nama_prodi || 'Mahasiswa'
                                        }}</div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-6">
                                <div class="flex items-center gap-2 bg-indigo-50 text-indigo-900 px-4 py-2 rounded-2xl flex-1 justify-center"
                                    title="Dilihat">
                                    <Eye :size="20" class="text-indigo-700" />
                                    <span class="font-bold text-sm">{{ proyek.jumlah_lihat }}</span>
                                </div>
                                <div class="flex items-center gap-2 bg-pink-50 text-pink-900 px-4 py-2 rounded-2xl flex-1 justify-center"
                                    title="Disukai">
                                    <Heart :size="20" class="text-pink-600 fill-current" />
                                    <span class="font-bold text-sm">{{ likeCount }}</span>
                                </div>
                                <div class="flex items-center gap-2 bg-emerald-50 text-emerald-900 px-4 py-2 rounded-2xl flex-1 justify-center"
                                    title="Disimpan">
                                    <Bookmark :size="20" class="text-emerald-600 fill-current" />
                                    <span class="font-bold text-sm">{{ proyek.jumlah_simpan }}</span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button @click="toggleLike"
                                    class="h-14 w-14 rounded-[16px] flex items-center justify-center transition-all duration-300"
                                    :class="isLiked ? 'bg-pink-100 text-pink-600' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                                    <Heart :size="24" :class="{ 'fill-current': isLiked }" />
                                </button>

                                <button @click="toggleSave"
                                    class="h-14 w-14 rounded-[16px] flex items-center justify-center transition-all duration-300"
                                    :class="isSaved ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                                    <Bookmark :size="24" :class="{ 'fill-current': isSaved }" />
                                </button>

                                <button @click="shareProyek"
                                    class="flex-1 h-14 bg-[#1d1b20] text-white rounded-[16px] font-medium flex items-center justify-center gap-2 hover:shadow-lg transition-all">
                                    <Share2 :size="20" />
                                    <span>Bagikan</span>
                                </button>
                            </div>
                        </div>

                        <div class="bg-white rounded-[28px] p-6 shadow-sm border border-gray-100/50">
                            <h4 class="text-sm font-medium text-gray-500 mb-4 flex items-center gap-2">
                                <Code2 :size="18" /> Stack Teknologi
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="t in proyek.teknologi" :key="t.id"
                                    class="pl-2 pr-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 flex items-center gap-2 hover:bg-white hover:shadow-sm transition-all">
                                    <img :src="t.ikon_url" class="w-5 h-5 object-contain">
                                    {{ t.nama }}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <a v-if="proyek.url_demo" :href="proyek.url_demo" target="_blank"
                                class="flex w-full py-4 px-6 bg-[#E8DEF8] text-[#1D192B] rounded-[20px] font-semibold items-center justify-between hover:shadow-md transition-all group">
                                <div class="flex items-center gap-3">
                                    <ExternalLink :size="20" /> Demo Live
                                </div>
                                <ChevronRight :size="20"
                                    class="opacity-50 group-hover:translate-x-1 transition-transform" />
                            </a>
                            <a v-if="proyek.url_repository" :href="proyek.url_repository" target="_blank"
                                class="flex w-full py-4 px-6 bg-white border border-gray-300 text-gray-800 rounded-[20px] font-semibold items-center justify-between hover:bg-gray-50 transition-all group">
                                <div class="flex items-center gap-3">
                                    <Github :size="20" /> Repository
                                </div>
                                <ChevronRight :size="20"
                                    class="opacity-50 group-hover:translate-x-1 transition-transform" />
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="fade">
                <div v-if="deleteModalOpen"
                    class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="cancelDelete">
                    </div>
                    <div
                        class="relative bg-white rounded-[28px] shadow-xl max-w-sm w-full p-6 transform transition-all">
                        <div class="text-center">
                            <div
                                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4 text-red-600">
                                <Trash2 :size="24" />
                            </div>
                            <h3 class="text-lg font-normal text-[#1d1b20] mb-2">Hapus Komentar?</h3>
                            <p class="text-sm text-gray-500 mb-6">Tindakan ini permanen dan tidak bisa dibatalkan.</p>
                        </div>
                        <div class="flex gap-3">
                            <button @click="cancelDelete"
                                class="flex-1 py-2.5 rounded-full text-indigo-600 font-medium hover:bg-indigo-50 transition-colors">Batal</button>
                            <button @click="confirmDelete"
                                class="flex-1 py-2.5 bg-red-600 text-white rounded-full font-medium hover:shadow-lg transition-all">Hapus</button>
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