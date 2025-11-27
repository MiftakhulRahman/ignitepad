<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Layout, Image, Cpu, Settings, CheckCircle2,
    ChevronRight, ChevronLeft, Save, AlertCircle
} from 'lucide-vue-next';

// Custom Components
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import ImageUpload from '@/Components/ImageUpload.vue';

const props = defineProps({
    kategoris: Array,
    teknologis: Array,
});

// --- STEPS CONFIG ---
const steps = [
    { id: 1, title: 'Informasi Dasar', icon: Layout, desc: 'Judul & Deskripsi' },
    { id: 2, title: 'Konten Visual', icon: Image, desc: 'Gambar & Detail' },
    { id: 3, title: 'Teknologi & Link', icon: Cpu, desc: 'Stack & Repositori' },
    { id: 4, title: 'Pengaturan', icon: Settings, desc: 'Publikasi' },
];

const currentStep = ref(1);

// --- FORM ---
const form = useForm({
    judul: '',
    kategori_id: '',
    deskripsi: '', // Short description
    konten_html: '', // Rich text content
    thumbnail: null,
    galeri: [],
    teknologi_ids: [],
    url_demo: '',
    url_repository: '',
    status: 'draft', // draft | terbit
    visibilitas: 'publik',
    boleh_komentar: true,
});

// --- NAVIGATION LOGIC ---
const nextStep = () => {
    if (validateStep(currentStep.value)) {
        currentStep.value++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const prevStep = () => {
    currentStep.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Simple Frontend Validation per Step
const validateStep = (step) => {
    form.clearErrors();
    let valid = true;

    if (step === 1) {
        if (!form.judul) { form.setError('judul', 'Judul wajib diisi'); valid = false; }
        if (!form.kategori_id) { form.setError('kategori_id', 'Pilih kategori proyek'); valid = false; }
        if (!form.deskripsi) { form.setError('deskripsi', 'Deskripsi singkat wajib diisi'); valid = false; }
    }
    if (step === 2) {
        if (!form.thumbnail) { form.setError('thumbnail', 'Thumbnail wajib diupload'); valid = false; }
        if (!form.konten_html || form.konten_html === '<p></p>') {
            form.setError('konten_html', 'Konten detail proyek wajib diisi'); valid = false;
        }
    }
    if (step === 3) {
        if (form.teknologi_ids.length === 0) {
            form.setError('teknologi_ids', 'Pilih minimal satu teknologi'); valid = false;
        }
    }

    return valid;
};

const submit = () => {
    form.post(route('proyek.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        }
    });
};

// --- HELPERS ---
const selectTeknologi = (id) => {
    if (form.teknologi_ids.includes(id)) {
        form.teknologi_ids = form.teknologi_ids.filter(t => t !== id);
    } else {
        form.teknologi_ids.push(id);
    }
};
</script>

<template>

    <Head title="Upload Proyek Baru" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-8 pb-20">

            <div class="space-y-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Upload Proyek Baru</h1>
                    <p class="text-gray-500">Bagikan karyamu kepada dunia.</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <div class="flex items-center justify-between relative">
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-100 -z-0 rounded-full">
                        </div>
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-indigo-500 -z-0 rounded-full transition-all duration-500"
                            :style="{ width: `${((currentStep - 1) / (steps.length - 1)) * 100}%` }"></div>

                        <div v-for="step in steps" :key="step.id"
                            class="relative z-10 flex flex-col items-center group cursor-pointer"
                            @click="step.id < currentStep ? currentStep = step.id : null">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                                :class="currentStep >= step.id
                                    ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-200'
                                    : 'bg-white border-gray-300 text-gray-400'">
                                <component :is="step.icon" :size="20" />
                            </div>
                            <div class="mt-2 text-center hidden sm:block">
                                <p class="text-xs font-bold transition-colors"
                                    :class="currentStep >= step.id ? 'text-indigo-600' : 'text-gray-500'">{{ step.title
                                    }}</p>
                                <p class="text-[10px] text-gray-400">{{ step.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                <form @submit.prevent="submit">

                    <div class="p-6 sm:p-8 min-h-[400px]">

                        <div v-show="currentStep === 1" class="space-y-6 animate-fade-in">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-2 space-y-4">
                                    <div>
                                        <InputLabel for="judul" value="Judul Proyek" />
                                        <TextInput id="judul" v-model="form.judul" type="text"
                                            class="mt-1 block w-full text-lg"
                                            placeholder="Contoh: Sistem Presensi Wajah AI" autofocus />
                                        <InputError :message="form.errors.judul" class="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel for="deskripsi" value="Deskripsi Singkat (Teaser)" />
                                        <textarea id="deskripsi" v-model="form.deskripsi" rows="3"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            placeholder="Jelaskan inti proyekmu dalam 1-2 kalimat untuk menarik perhatian..."></textarea>
                                        <p class="text-xs text-gray-500 mt-1 text-right">{{ form.deskripsi.length }}/255
                                            karakter</p>
                                        <InputError :message="form.errors.deskripsi" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <InputLabel value="Kategori Proyek" class="mb-2" />
                                    <div class="space-y-2 max-h-[300px] overflow-y-auto pr-1 custom-scrollbar">
                                        <div v-for="kat in kategoris" :key="kat.id" @click="form.kategori_id = kat.id"
                                            class="flex items-center p-3 rounded-lg bordercursor-pointer transition-all border hover:shadow-sm cursor-pointer"
                                            :class="form.kategori_id === kat.id ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-gray-200 hover:border-indigo-300'">
                                            <div class="w-3 h-3 rounded-full mr-3 flex-shrink-0"
                                                :style="{ backgroundColor: kat.warna }"></div>
                                            <span class="text-sm font-medium text-gray-700">{{ kat.nama }}</span>
                                            <CheckCircle2 v-if="form.kategori_id === kat.id" :size="16"
                                                class="ml-auto text-indigo-600" />
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.kategori_id" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div v-show="currentStep === 2" class="space-y-8 animate-fade-in">
                            <div>
                                <InputLabel value="Thumbnail Utama" class="mb-2" />
                                <ImageUpload v-model="form.thumbnail" previewSize="h-64" />
                                <InputError :message="form.errors.thumbnail" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel value="Cerita Proyek (Detail Lengkap)" class="mb-2" />
                                <RichTextEditor v-model="form.konten_html"
                                    placeholder="Ceritakan latar belakang, fitur utama, dan tantangan yang kamu hadapi..." />
                                <InputError :message="form.errors.konten_html" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel value="Galeri Tambahan (Opsional)" class="mb-2" />
                                <ImageUpload v-model="form.galeri" multiple />
                                <p class="text-xs text-gray-500 mt-1">Upload screenshot aplikasi atau diagram sistem
                                    (Max 5 gambar).</p>
                                <InputError :message="form.errors.galeri" class="mt-2" />
                            </div>
                        </div>

                        <div v-show="currentStep === 3" class="space-y-8 animate-fade-in">
                            <div>
                                <InputLabel value="Teknologi & Tools yang Digunakan" class="mb-3" />
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                    <div v-for="tech in teknologis" :key="tech.id" @click="selectTeknologi(tech.id)"
                                        class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition-all duration-200 text-center h-24"
                                        :class="form.teknologi_ids.includes(tech.id)
                                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm ring-1 ring-indigo-500'
                                            : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm text-gray-600'">
                                        <img :src="tech.ikon_url" class="w-8 h-8 mb-2 object-contain" :alt="tech.nama">
                                        <span class="text-xs font-medium truncate w-full px-1">{{ tech.nama }}</span>
                                    </div>
                                </div>
                                <InputError :message="form.errors.teknologi_ids" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="repo" value="URL Repository (GitHub/GitLab)" />
                                    <TextInput id="repo" v-model="form.url_repository" type="url"
                                        class="mt-1 block w-full" placeholder="https://github.com/username/repo" />
                                </div>
                                <div>
                                    <InputLabel for="demo" value="URL Demo / Live Preview" />
                                    <TextInput id="demo" v-model="form.url_demo" type="url" class="mt-1 block w-full"
                                        placeholder="https://myproject.com" />
                                </div>
                            </div>
                        </div>

                        <div v-show="currentStep === 4" class="space-y-8 animate-fade-in">
                            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 flex gap-3 items-start">
                                <AlertCircle class="text-indigo-600 shrink-0 mt-0.5" :size="20" />
                                <div>
                                    <h4 class="font-bold text-indigo-900 text-sm">Hampir Selesai!</h4>
                                    <p class="text-indigo-700 text-sm mt-1">Pastikan semua data sudah benar sebelum
                                        mempublikasikan proyek ini.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel value="Status Publikasi" class="mb-3" />
                                    <div class="space-y-3">
                                        <label
                                            class="flex items-center p-4 border rounded-lg cursor-pointer transition-all"
                                            :class="form.status === 'terbit' ? 'border-green-500 bg-green-50 ring-1 ring-green-500' : 'border-gray-200 hover:bg-gray-50'">
                                            <input type="radio" v-model="form.status" value="terbit"
                                                class="text-green-600 focus:ring-green-500">
                                            <div class="ml-3">
                                                <span class="block text-sm font-bold text-gray-900">Terbitkan
                                                    Sekarang</span>
                                                <span class="block text-xs text-gray-500">Proyek akan langsung bisa
                                                    dilihat oleh publik.</span>
                                            </div>
                                        </label>
                                        <label
                                            class="flex items-center p-4 border rounded-lg cursor-pointer transition-all"
                                            :class="form.status === 'draft' ? 'border-amber-500 bg-amber-50 ring-1 ring-amber-500' : 'border-gray-200 hover:bg-gray-50'">
                                            <input type="radio" v-model="form.status" value="draft"
                                                class="text-amber-600 focus:ring-amber-500">
                                            <div class="ml-3">
                                                <span class="block text-sm font-bold text-gray-900">Simpan sebagai
                                                    Draft</span>
                                                <span class="block text-xs text-gray-500">Simpan dulu, edit dan
                                                    terbitkan nanti.</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <InputLabel value="Pengaturan Lainnya" class="mb-3" />
                                    <div class="space-y-4">
                                        <label class="flex items-center justify-between p-3 border rounded-lg bg-white">
                                            <span class="text-sm font-medium text-gray-700">Izinkan Komentar</span>
                                            <input type="checkbox" v-model="form.boleh_komentar"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        </label>

                                        <div>
                                            <InputLabel value="Visibilitas" class="text-xs text-gray-500 mb-1" />
                                            <select v-model="form.visibilitas"
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="publik">Publik (Semua Orang)</option>
                                                <option value="terbatas">Terbatas (Hanya User Login)</option>
                                                <option value="privat">Privat (Hanya Saya)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                        <button type="button" v-if="currentStep > 1" @click="prevStep"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            <ChevronLeft :size="16" class="mr-2" /> Kembali
                        </button>
                        <div v-else></div> <button type="button" v-if="currentStep < 4" @click="nextStep"
                            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                            Lanjut
                            <ChevronRight :size="16" class="ml-2" />
                        </button>

                        <button type="submit" v-if="currentStep === 4" :disabled="form.processing"
                            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none disabled:opacity-50">
                            <Save :size="16" class="mr-2" />
                            {{ form.status === 'terbit' ? 'Terbitkan Proyek' : 'Simpan Draft' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 4px;
}

/* Animasi sederhana untuk transisi step */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>