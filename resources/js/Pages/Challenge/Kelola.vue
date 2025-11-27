<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ChevronLeft, User, ExternalLink, Github, Save, CheckCircle2, Trophy } from 'lucide-vue-next';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
    challenge: Object,
    submisis: Array,
});

const selectedSubmisi = ref(null);
const toastRef = ref(null);

const form = useForm({
    nilai: {}, // { kriteria_id: score }
    umpan_balik: '',
});

// Pilih Peserta untuk Dinilai
const selectPeserta = (submisi) => {
    selectedSubmisi.value = submisi;
    
    // Reset Form
    form.nilai = {};
    form.umpan_balik = submisi.umpan_balik_html || '';

    // Isi nilai jika sudah pernah dinilai (Logic backend transform tadi berguna disini)
    if (submisi.nilai_map) {
        form.nilai = { ...submisi.nilai_map };
    }
};

// Hitung Live Score
const currentTotalScore = computed(() => {
    let total = 0;
    props.challenge.kriteria_penilaian.forEach(k => {
        const score = form.nilai[k.id] || 0;
        total += (score * k.bobot_persen / 100);
    });
    return total.toFixed(2);
});

const currentGrade = computed(() => {
    const s = currentTotalScore.value;
    if (s >= 85) return 'A';
    if (s >= 75) return 'B';
    if (s >= 60) return 'C';
    if (s >= 50) return 'D';
    return 'E';
});

const submitNilai = () => {
    if (!selectedSubmisi.value) return;
    
    form.post(route('submisi.nilai', selectedSubmisi.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            if(toastRef.value) toastRef.value.fire('Nilai berhasil disimpan', 'success');
            // Update local state (optional, inertia reload handles it)
        }
    });
};
</script>

<template>
    <Head title="Kelola Penilaian" />
    <Toast ref="toastRef" />

    <AuthenticatedLayout>
        <div class="h-[calc(100vh-80px)] flex flex-col">
            
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-4">
                    <Link :href="route('challenge.show', challenge.slug)" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <ChevronLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Penilaian: {{ challenge.judul }}</h1>
                        <p class="text-sm text-gray-500">{{ submisis.length }} Submisi Masuk</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-1 overflow-hidden">
                
                <div class="w-80 bg-white border-r border-gray-200 overflow-y-auto">
                    <div v-if="submisis.length === 0" class="p-6 text-center text-gray-500">
                        Belum ada submisi.
                    </div>
                    <div v-else>
                        <div 
                            v-for="sub in submisis" :key="sub.id"
                            @click="selectPeserta(sub)"
                            class="p-4 border-b border-gray-100 cursor-pointer hover:bg-indigo-50 transition-colors"
                            :class="selectedSubmisi?.id === sub.id ? 'bg-indigo-50 border-l-4 border-l-indigo-600' : 'border-l-4 border-l-transparent'"
                        >
                            <div class="flex items-center gap-3">
                                <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(sub.user.nama)}&background=random`" class="w-10 h-10 rounded-full">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ sub.user.nama }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ sub.proyek?.judul || 'Belum submit' }}</p>
                                </div>
                                <div v-if="sub.status === 'dinilai'" class="flex flex-col items-end">
                                    <span class="text-lg font-bold text-green-600">{{ sub.nilai_total }}</span>
                                    <span class="text-xs font-bold bg-green-100 text-green-700 px-1.5 rounded">{{ sub.grade }}</span>
                                </div>
                                <div v-else-if="sub.status === 'terkirim'">
                                    <span class="w-3 h-3 bg-amber-500 rounded-full block" title="Perlu Dinilai"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 bg-gray-50 overflow-y-auto p-6">
                    
                    <div v-if="selectedSubmisi && selectedSubmisi.proyek" class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <div class="space-y-6">
                            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                                <img :src="'/storage/' + selectedSubmisi.proyek.thumbnail" class="w-full h-48 object-cover">
                                <div class="p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <h2 class="text-xl font-bold text-gray-900">{{ selectedSubmisi.proyek.judul }}</h2>
                                        <div class="flex gap-2">
                                            <a v-if="selectedSubmisi.proyek.url_demo" :href="selectedSubmisi.proyek.url_demo" target="_blank" class="p-2 bg-gray-100 rounded hover:bg-gray-200"><ExternalLink :size="18"/></a>
                                            <a v-if="selectedSubmisi.proyek.url_repository" :href="selectedSubmisi.proyek.url_repository" target="_blank" class="p-2 bg-gray-100 rounded hover:bg-gray-200"><Github :size="18"/></a>
                                        </div>
                                    </div>
                                    <div class="prose prose-sm text-gray-600 max-h-60 overflow-y-auto custom-scrollbar" v-html="selectedSubmisi.proyek.konten_html"></div>
                                    
                                    <div v-if="selectedSubmisi.catatan_peserta" class="mt-4 p-3 bg-yellow-50 border border-yellow-100 rounded-lg text-sm text-yellow-800">
                                        <strong>Catatan Mahasiswa:</strong> {{ selectedSubmisi.catatan_peserta }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sticky top-0">
                                <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
                                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                        <Trophy :size="20" class="text-indigo-600" /> Form Penilaian
                                    </h3>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500">Total Skor</p>
                                        <div class="text-2xl font-black text-indigo-600">{{ currentTotalScore }} <span class="text-sm font-normal text-gray-400">/ 100</span></div>
                                    </div>
                                </div>

                                <div class="space-y-5">
                                    <div v-for="kriteria in challenge.kriteria_penilaian" :key="kriteria.id">
                                        <div class="flex justify-between mb-1">
                                            <label class="text-sm font-medium text-gray-700">{{ kriteria.nama_kriteria }} ({{ kriteria.bobot_persen }}%)</label>
                                            <span class="text-sm font-bold text-indigo-600">{{ form.nilai[kriteria.id] || 0 }}</span>
                                        </div>
                                        <input 
                                            type="range" min="0" max="100" step="1" 
                                            v-model="form.nilai[kriteria.id]"
                                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Umpan Balik / Feedback</label>
                                        <textarea v-model="form.umpan_balik" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Berikan saran atau apresiasi..."></textarea>
                                    </div>

                                    <button 
                                        @click="submitNilai" 
                                        :disabled="form.processing"
                                        class="w-full flex justify-center items-center gap-2 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 disabled:opacity-50"
                                    >
                                        <Save :size="18" /> Simpan Penilaian
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div v-else class="h-full flex flex-col items-center justify-center text-gray-400">
                        <User :size="64" class="mb-4 text-gray-200" />
                        <p class="text-lg font-medium">Pilih peserta dari sidebar untuk mulai menilai.</p>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
