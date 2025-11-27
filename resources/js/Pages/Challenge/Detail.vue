<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, usePage, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Clock, Users, Trophy, Calendar, CheckCircle2, AlertCircle, 
    ChevronLeft, FileText, ListChecks, UploadCloud
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    challenge: Object,
    kriterias: Array,
    pesertas: Array,
    isJoined: Boolean,
    isOwner: Boolean,
    mySubmission: Object,
    myProjects: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const activeTab = ref('overview'); // overview | rules | participants
const showSubmitModal = ref(false);
const submitForm = useForm({
    proyek_id: '',
    catatan: '',
});

// Formatting helpers
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

const daysLeft = computed(() => {
    const diff = new Date(props.challenge.batas_waktu) - new Date();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
});

// User Role Checker
const isStudent = computed(() => user.value && user.value.perans.some(r => r.slug === 'mahasiswa'));

const joinChallenge = () => {
    if (!user.value) {
        router.get(route('login'));
        return;
    }
    if(confirm('Apakah Anda yakin ingin mengikuti challenge ini?')) {
        router.post(route('challenge.join', props.challenge.id));
    }
};

const openSubmitModal = () => {
    submitForm.proyek_id = '';
    submitForm.catatan = '';
    showSubmitModal.value = true;
};

const submitProyek = () => {
    submitForm.post(route('challenge.submit', props.challenge.id), {
        onSuccess: () => showSubmitModal.value = false,
    });
};
</script>

<template>
    <Head :title="challenge.judul" />

    <PublicLayout>
        <div class="pb-20">
            
            <div class="relative bg-gray-900 text-white overflow-hidden">
                <div class="absolute inset-0">
                    <img :src="'/storage/' + challenge.banner" class="w-full h-full object-cover opacity-40">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
                </div>

                <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-12">
                    <Link :href="route('challenge.index')" class="inline-flex items-center text-gray-300 hover:text-white mb-6 transition-colors">
                        <ChevronLeft :size="16" class="mr-1" /> Kembali ke Daftar Challenge
                    </Link>
                    
                    <div class="flex flex-col md:flex-row gap-6 md:items-end justify-between">
                        <div class="max-w-3xl">
                            <div v-if="challenge.status === 'buka'" class="inline-flex items-center px-3 py-1 rounded-full bg-green-500/20 text-green-300 text-xs font-bold border border-green-500/30 mb-4">
                                <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span> Terbuka untuk Submisi
                            </div>
                            <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-4">{{ challenge.judul }}</h1>
                            <p class="text-lg text-gray-300 line-clamp-2">{{ challenge.deskripsi }}</p>
                        </div>
                        
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-xl min-w-[200px]">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Hadiah Utama</p>
                            <div class="flex items-center gap-2 text-amber-400 font-bold text-lg">
                                <Trophy :size="24" /> {{ challenge.hadiah }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden min-h-[500px]">
                        <div class="flex border-b border-gray-200">
                            <button @click="activeTab = 'overview'" class="flex-1 py-4 text-sm font-medium text-center border-b-2 transition-colors" :class="activeTab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                                Deskripsi
                            </button>
                            <button @click="activeTab = 'rules'" class="flex-1 py-4 text-sm font-medium text-center border-b-2 transition-colors" :class="activeTab === 'rules' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                                Aturan & Syarat
                            </button>
                            <button @click="activeTab = 'participants'" class="flex-1 py-4 text-sm font-medium text-center border-b-2 transition-colors" :class="activeTab === 'participants' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                                Peserta ({{ challenge.jumlah_peserta }})
                            </button>
                        </div>

                        <div class="p-6 sm:p-8">
                            
                            <div v-if="activeTab === 'overview'" class="space-y-8 animate-fade-in">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">Tentang Challenge</h3>
                                    <p class="text-gray-600 leading-relaxed">{{ challenge.deskripsi }}</p>
                                </div>

                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">Kriteria Penilaian</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div v-for="k in kriterias" :key="k.id" class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="font-bold text-gray-800">{{ k.nama_kriteria }}</span>
                                                <span class="text-sm font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ k.bobot_persen }}%</span>
                                            </div>
                                            <p class="text-xs text-gray-500">{{ k.deskripsi || 'Tidak ada detail tambahan.' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="activeTab === 'rules'" class="animate-fade-in">
                                <div class="prose prose-indigo max-w-none text-gray-600" v-html="challenge.aturan_html"></div>
                            </div>

                            <div v-if="activeTab === 'participants'" class="animate-fade-in">
                                <div v-if="pesertas.length > 0" class="space-y-4">
                                    <div v-for="p in pesertas" :key="p.id" class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(p.user.nama)}&background=random`" class="w-10 h-10 rounded-full">
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">{{ p.user.nama }}</p>
                                                <p class="text-xs text-gray-500">Bergabung: {{ formatDate(p.created_at) }}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-1 rounded capitalize">{{ p.status }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-center py-12 text-gray-500">
                                    <Users :size="48" class="mx-auto mb-3 text-gray-300" />
                                    <p>Belum ada peserta yang bergabung.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="lg:col-span-1 space-y-6">
                        
                        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-sm text-gray-500">Deadline</span>
                                <span class="font-bold text-gray-900 flex items-center gap-1">
                                    <Calendar :size="16" /> {{ formatDate(challenge.batas_waktu) }}
                                </span>
                            </div>

                            <div class="mb-6">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium" :class="daysLeft <= 3 ? 'text-red-600' : 'text-gray-600'">{{ daysLeft }} Hari Lagi</span>
                                    <span class="text-gray-400">Target</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: 70%"></div> </div>
                            </div>

                            <div v-if="isOwner">
                                <Link :href="route('challenge.kelola', challenge.slug)" class="flex items-center justify-center w-full py-3 rounded-xl bg-gray-900 text-white font-bold hover:bg-gray-800 transition-colors shadow-lg">
                                    <Trophy :size="18" class="mr-2" /> Kelola & Nilai
                                </Link>
                            </div>

                            <div v-else-if="isJoined">
                                <div v-if="mySubmission && mySubmission.status === 'bergabung'">
                                    <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-xl text-center mb-3">
                                        <CheckCircle2 :size="32" class="text-indigo-600 mx-auto mb-2" />
                                        <p class="font-bold text-indigo-900">Anda Terdaftar!</p>
                                        <p class="text-xs text-indigo-700 mt-1">Segera kirim proyekmu.</p>
                                    </div>
                                    <button @click="openSubmitModal" class="w-full py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                                        <UploadCloud :size="20" /> Submit Proyek
                                    </button>
                                </div>
                                
                                <div v-else-if="mySubmission && (mySubmission.status === 'terkirim' || mySubmission.status === 'dinilai')">
                                    <div class="p-4 bg-green-50 border border-green-100 rounded-xl text-center mb-3">
                                        <CheckCircle2 :size="32" class="text-green-600 mx-auto mb-2" />
                                        <p class="font-bold text-green-900">Proyek Terkirim</p>
                                        <p class="text-xs text-green-700 mt-1">Menunggu penilaian dosen.</p>
                                    </div>
                                    <div v-if="mySubmission.status === 'dinilai'" class="mt-4 text-center">
                                        <p class="text-sm font-medium text-gray-500">Nilai Kamu:</p>
                                        <p class="text-4xl font-extrabold text-indigo-600">{{ mySubmission.nilai_total }}</p>
                                        <p class="text-lg font-bold text-gray-400">Grade: {{ mySubmission.grade }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="isStudent">
                                <button @click="joinChallenge" class="w-full py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 animate-pulse">
                                    Ikuti Challenge Ini
                                </button>
                                <p class="text-xs text-center text-gray-500 mt-3">Klik untuk mendaftar sebagai peserta.</p>
                            </div>
                            <div v-else-if="user">
                                <div class="text-center p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <p class="text-sm text-gray-600">Hanya Mahasiswa yang dapat mengikuti challenge ini.</p>
                                </div>
                            </div>
                            <div v-else class="text-center p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <Link :href="route('login')" class="text-indigo-600 font-bold hover:underline">Login</Link>
                                <span class="text-sm text-gray-600"> untuk mengikuti challenge ini.</span>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center gap-4">
                            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(challenge.pembuat.nama)}&background=random`" class="w-12 h-12 rounded-full border border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide font-bold">Diselenggarakan Oleh</p>
                                <h4 class="font-bold text-gray-900">{{ challenge.pembuat.nama }}</h4>
                                <p class="text-xs text-gray-500">{{ challenge.pembuat.prodi?.nama || 'Dosen IgnitePad' }}</p>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </PublicLayout>

    <Modal :show="showSubmitModal" @close="showSubmitModal = false">
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Pilih Proyek untuk Disubmit</h2>
            
            <div v-if="myProjects && myProjects.length > 0">
                <div class="space-y-3 mb-4 max-h-60 overflow-y-auto pr-2">
                    <div 
                        v-for="proj in myProjects" :key="proj.id"
                        @click="submitForm.proyek_id = proj.id"
                        class="flex items-center p-3 rounded-lg border cursor-pointer transition-all"
                        :class="submitForm.proyek_id === proj.id ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <img :src="'/storage/' + proj.thumbnail" class="w-12 h-12 rounded object-cover mr-3 bg-gray-100">
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-gray-900 line-clamp-1">{{ proj.judul }}</h4>
                            <p class="text-xs text-gray-500">{{ proj.kategori.nama }}</p>
                        </div>
                        <div v-if="submitForm.proyek_id === proj.id" class="text-indigo-600">
                            <CheckCircle2 :size="20" />
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <InputLabel value="Catatan untuk Juri (Opsional)" />
                    <textarea v-model="submitForm.catatan" rows="2" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Pesan tambahan..."></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <SecondaryButton @click="showSubmitModal = false">Batal</SecondaryButton>
                    <PrimaryButton @click="submitProyek" :disabled="submitForm.processing || !submitForm.proyek_id">
                        Kirim Proyek
                    </PrimaryButton>
                </div>
            </div>

            <div v-else class="text-center py-8">
                <AlertCircle :size="32" class="mx-auto text-amber-500 mb-2" />
                <p class="text-gray-900 font-medium">Anda belum memiliki proyek.</p>
                <p class="text-sm text-gray-500 mb-4">Buat proyek terlebih dahulu di menu Proyek Saya.</p>
                <Link :href="route('proyek.create')" class="text-indigo-600 font-bold hover:underline">Buat Proyek Sekarang</Link>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
