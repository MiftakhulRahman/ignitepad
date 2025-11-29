<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Layout, Image as ImageIcon, Cpu, Settings,
    ChevronRight, ChevronLeft, Save, Globe, Smartphone, HardDrive, Gamepad2, PenTool,
    CheckCircle2
} from 'lucide-vue-next';

// Components
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import CollaboratorInput from '@/Components/Proyek/CollaboratorInput.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
import Toast from '@/Components/Toast.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
// IMPORT COMPONENT STEPPER MANUAL
import VerticalStepper from '@/Components/VerticalStepper.vue';

const props = defineProps({
    kategoris: Array,
    teknologis: Array,
});

const iconMap = {
    'Globe': Globe, 'Smartphone': Smartphone, 'Cpu': Cpu,
    'HardDrive': HardDrive, 'Gamepad2': Gamepad2, 'PenTool': PenTool,
};
const getIcon = (name) => iconMap[name] || Layout;

// Data Steps untuk Component Stepper
const steps = [
    { step: 1, title: 'Informasi Dasar', description: 'Judul & Kategori' },
    { step: 2, title: 'Visual & Konten', description: 'Media & Cerita' },
    { step: 3, title: 'Teknologi', description: 'Stack & Link' },
    { step: 4, title: 'Finalisasi', description: 'Review & Publish' },
];

const currentStep = ref(1);
const maxStepReached = ref(1);

const form = useForm({
    judul: '',
    kategori_id: '',
    deskripsi: '',
    konten_html: '',
    thumbnail: null,
    teknologi_ids: [],
    kolaborators: [],
    url_demo: '',
    url_repository: '',
    status: 'draft',
    visibilitas: 'publik',
    boleh_komentar: true,
});

// Logic Validasi (Sama seperti sebelumnya)
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
        if (!form.konten_html || form.konten_html === '<p></p>') { form.setError('konten_html', 'Konten detail wajib diisi'); valid = false; }
    }
    if (step === 3) {
        if (form.teknologi_ids.length === 0) {
            form.setError('teknologi_ids', 'Pilih minimal satu teknologi');
            valid = false;
        }

        // Validasi URL Repository (jika diisi)
        if (form.url_repository && !isValidUrl(form.url_repository)) {
            form.setError('url_repository', 'Link repository harus berupa URL yang valid (contoh: https://github.com/username/repo)');
            valid = false;
        }

        // Validasi URL Demo (jika diisi)
        if (form.url_demo && !isValidUrl(form.url_demo)) {
            form.setError('url_demo', 'Link demo harus berupa URL yang valid (contoh: https://example.com)');
            valid = false;
        }
    }
    return valid;
};

// Helper function untuk validasi URL
const isValidUrl = (string) => {
    try {
        const url = new URL(string);
        return url.protocol === 'http:' || url.protocol === 'https:';
    } catch (_) {
        return false;
    }
};

const nextStep = () => {
    if (validateStep(currentStep.value)) {
        currentStep.value++;
        if (currentStep.value > maxStepReached.value) maxStepReached.value = currentStep.value;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const prevStep = () => {
    currentStep.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Logic Pindah Step (Dipanggil oleh VerticalStepper component)
const goToStep = (step) => {
    // Hanya boleh mundur, atau maju 1 langkah jika valid, atau maju ke step yang sudah pernah dicapai
    if (step < currentStep.value) {
        currentStep.value = step;
    } else if (step === currentStep.value + 1) {
        if (validateStep(currentStep.value)) {
            currentStep.value = step;
            if (currentStep.value > maxStepReached.value) maxStepReached.value = currentStep.value;
        }
    } else if (step > currentStep.value && step <= maxStepReached.value) {
        currentStep.value = step;
    }
};

const submit = () => {
    // Validate all steps sebelum submit
    const step1Valid = validateStep(1);
    const step2Valid = validateStep(2);
    const step3Valid = validateStep(3);

    if (!step1Valid || !step2Valid || !step3Valid) {
        // Jika ada step yang tidak valid, pindah ke step pertama yang error
        if (!step1Valid) {
            currentStep.value = 1;
        } else if (!step2Valid) {
            currentStep.value = 2;
        } else if (!step3Valid) {
            currentStep.value = 3;
        }
        return;
    }

    console.log('Submitting form with data:', form.data());

    form.post(route('proyek.store'), {
        onSuccess: (response) => {
            console.log('Success response:', response);
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);

            // Deteksi step mana yang punya error dan pindah ke sana
            if (errors.judul || errors.kategori_id || errors.deskripsi) {
                currentStep.value = 1;
            } else if (errors.thumbnail || errors.konten_html) {
                currentStep.value = 2;
            } else if (errors.teknologi_ids || errors.url_demo || errors.url_repository) {
                currentStep.value = 3;
            }

            // Scroll ke atas untuk melihat error
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        onFinish: () => {
            console.log('Request finished');
        }
    });
};

const selectTeknologi = (id) => {
    if (form.teknologi_ids.includes(id)) form.teknologi_ids = form.teknologi_ids.filter(t => t !== id);
    else form.teknologi_ids.push(id);
};
</script>

<template>

    <Head title="Buat Proyek Baru" />
    <Toast />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#FDFDFD] pb-24 pt-10 font-sans">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">

                <Breadcrumb :items="[
                    { label: 'Proyek Saya', url: route('proyek.saya') },
                    { label: 'Buat Proyek Baru' }
                ]" />

                <div class="mb-10 text-center sm:text-left">
                    <h1 class="text-4xl font-medium text-slate-800 tracking-tight mb-2">Buat Proyek Baru</h1>
                    <p class="text-slate-500 text-lg">Mulai bagikan inovasi dan karyamu.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <div class="lg:col-span-3 sticky top-8">
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                            <VerticalStepper :steps="steps" :current-step="currentStep" @go-to-step="goToStep" />
                        </div>
                    </div>

                    <div class="lg:col-span-9">
                        <div
                            class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden relative flex flex-col min-h-[600px]">
                            <form @submit.prevent="submit" class="flex-1 flex flex-col" novalidate>
                                <div class="p-8 md:p-12 flex-1">

                                    <div v-show="currentStep === 1" class="animate-enter space-y-8">
                                        <div class="space-y-6">
                                            <div>
                                                <InputLabel for="judul" value="Judul Proyek" />
                                                <TextInput id="judul" v-model="form.judul" type="text"
                                                    class="mt-2 block w-full text-lg px-5 py-4 rounded-2xl bg-slate-50 border-slate-200 focus:bg-white transition-all"
                                                    placeholder="Contoh: Smart Attendance System" autofocus />
                                                <InputError :message="form.errors.judul" class="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel for="deskripsi" value="Deskripsi Singkat" />
                                                <textarea id="deskripsi" v-model="form.deskripsi" rows="3"
                                                    class="mt-2 block w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-all resize-none"
                                                    placeholder="Jelaskan inti proyekmu dalam 2-3 kalimat..."></textarea>
                                                <div class="flex justify-between mt-2 px-1">
                                                    <InputError :message="form.errors.deskripsi" />
                                                    <span class="text-xs font-medium text-slate-400">{{
                                                        form.deskripsi.length }}/255</span>
                                                </div>
                                            </div>

                                            <div>
                                                <InputLabel value="Kategori Proyek" class="mb-4 block" />
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <div v-for="kat in kategoris" :key="kat.id"
                                                        @click="form.kategori_id = kat.id"
                                                        class="relative flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300 group hover:shadow-md"
                                                        :class="form.kategori_id === kat.id ? 'border-indigo-600 bg-indigo-50/50' : 'border-slate-100 hover:border-indigo-100 bg-white'">

                                                        <div class="flex items-center gap-4">
                                                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                                                                :style="{ backgroundColor: kat.warna + '20', color: kat.warna }">
                                                                <component :is="getIcon(kat.ikon)" class="w-5 h-5" />
                                                            </div>
                                                            <span class="font-semibold text-slate-700">{{ kat.nama
                                                            }}</span>
                                                        </div>
                                                        <div v-if="form.kategori_id === kat.id" class="text-indigo-600">
                                                            <CheckCircle2 :size="22" class="fill-indigo-100" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <InputError :message="form.errors.kategori_id" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>

                                    <div v-show="currentStep === 2" class="animate-enter space-y-8">
                                        <div class="bg-slate-50 p-6 rounded-[24px] border border-slate-100">
                                            <InputLabel value="Thumbnail Utama" class="mb-3" />
                                            <ImageUpload v-model="form.thumbnail" :preview="null" :aspect-ratio="16 / 9"
                                                class="w-full rounded-xl overflow-hidden shadow-sm" />
                                            <InputError :message="form.errors.thumbnail" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel value="Cerita & Detail" class="mb-3" />
                                            <div class="prose-editor-wrapper">
                                                <RichTextEditor v-model="form.konten_html"
                                                    placeholder="Ceritakan detail fitur, tantangan, dan solusi..." />
                                            </div>
                                            <InputError :message="form.errors.konten_html" class="mt-2" />
                                        </div>
                                    </div>

                                    <div v-show="currentStep === 3" class="animate-enter space-y-8">
                                        <div>
                                            <InputLabel value="Tech Stack (Pilih yang digunakan)" class="mb-5" />
                                            <div class="flex flex-wrap gap-4">
                                                <div v-for="tech in teknologis" :key="tech.id"
                                                    @click="selectTeknologi(tech.id)"
                                                    class="flex items-center gap-3 px-5 py-3 rounded-full border cursor-pointer transition-all duration-300 select-none group"
                                                    :class="form.teknologi_ids.includes(tech.id)
                                                        ? 'bg-indigo-50 border-indigo-600 ring-1 ring-indigo-600 shadow-sm scale-105'
                                                        : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50'">

                                                    <img :src="tech.ikon_url"
                                                        class="w-6 h-6 object-contain transition-all duration-300"
                                                        :class="form.teknologi_ids.includes(tech.id) ? 'grayscale-0' : 'grayscale group-hover:grayscale-0 opacity-70 group-hover:opacity-100'">
                                                    <span class="text-sm font-medium transition-colors"
                                                        :class="form.teknologi_ids.includes(tech.id) ? 'text-indigo-900' : 'text-slate-600'">
                                                        {{ tech.nama }}
                                                    </span>
                                                </div>
                                            </div>
                                            <InputError :message="form.errors.teknologi_ids" class="mt-2" />
                                        </div>

                                        <div class="grid grid-cols-1 gap-6 pt-6 border-t border-slate-100">
                                            <div class="space-y-2 w-full">
                                                <InputLabel value="Link Repository" />
                                                <TextInput v-model="form.url_repository" type="url"
                                                    placeholder="https://github.com/..." class="rounded-2xl w-full" />
                                                <InputError :message="form.errors.url_repository" class="mt-2" />
                                            </div>
                                            <div class="space-y-2 w-full">
                                                <InputLabel value="Link Demo / Live Preview" />
                                                <TextInput v-model="form.url_demo" type="url"
                                                    placeholder="https://my-app.com" class="rounded-2xl w-full" />
                                                <InputError :message="form.errors.url_demo" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>

                                    <div v-show="currentStep === 4" class="animate-enter space-y-8">
                                        <div class="space-y-6">
                                            <div>
                                                <h3 class="text-lg font-semibold text-slate-800 mb-4">Tim & Kolaborator
                                                </h3>
                                                <CollaboratorInput v-model="form.kolaborators" />
                                            </div>

                                            <div
                                                class="bg-indigo-50/50 p-6 rounded-[24px] border border-indigo-100 space-y-6">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <span
                                                            class="text-base font-semibold text-slate-800 block">Izinkan
                                                            Komentar</span>
                                                        <span class="text-sm text-slate-500">Pengguna lain dapat memberi
                                                            masukan</span>
                                                    </div>
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" v-model="form.boleh_komentar"
                                                            class="sr-only peer">
                                                        <div
                                                            class="w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600">
                                                        </div>
                                                    </label>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <div class="space-y-2">
                                                        <InputLabel value="Status Publikasi" />
                                                        <div
                                                            class="flex bg-white p-1 rounded-full border border-slate-200 relative">
                                                            <div class="absolute top-1 bottom-1 w-[48%] bg-slate-800 rounded-full transition-all duration-300 shadow-sm"
                                                                :class="form.status === 'terbit' ? 'left-1' : 'left-[51%]'">
                                                            </div>
                                                            <button type="button" @click="form.status = 'terbit'"
                                                                class="flex-1 py-2 text-sm font-medium rounded-full z-10 transition-colors relative"
                                                                :class="form.status === 'terbit' ? 'text-white' : 'text-slate-500 hover:text-slate-700'">
                                                                Publikasikan
                                                            </button>
                                                            <button type="button" @click="form.status = 'draft'"
                                                                class="flex-1 py-2 text-sm font-medium rounded-full z-10 transition-colors relative"
                                                                :class="form.status === 'draft' ? 'text-white' : 'text-slate-500 hover:text-slate-700'">
                                                                Draft
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-2">
                                                        <InputLabel value="Visibilitas" />
                                                        <div class="relative">
                                                            <select v-model="form.visibilitas"
                                                                class="block w-full pl-4 pr-10 py-3 text-base border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-2xl bg-white appearance-none cursor-pointer">
                                                                <option value="publik">Publik (Semua Orang)</option>
                                                                <option value="terbatas">Terbatas (User Login)</option>
                                                                <option value="privat">Privat (Hanya Saya)</option>
                                                            </select>
                                                            <ChevronRight
                                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 rotate-90"
                                                                :size="18" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="px-6 md:px-12 py-6 border-t border-slate-100 bg-white flex justify-between items-center rounded-b-[2.5rem]">
                                    <button type="button" v-if="currentStep > 1" @click="prevStep"
                                        class="inline-flex items-center justify-center h-12 px-8 min-w-[140px] border border-slate-200 text-sm font-semibold rounded-full text-slate-600 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all">
                                        <ChevronLeft :size="18" class="mr-2" /> Kembali
                                    </button>
                                    <div v-else></div>

                                    <button type="button" v-if="currentStep < 4" @click="nextStep"
                                        class="inline-flex items-center justify-center h-12 px-8 min-w-[140px] border border-transparent text-sm font-semibold rounded-full text-white bg-slate-900 hover:bg-slate-800 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                                        Lanjut
                                        <ChevronRight :size="18" class="ml-2" />
                                    </button>

                                    <button type="submit" v-if="currentStep === 4" :disabled="form.processing"
                                        class="inline-flex items-center justify-center h-12 px-8 min-w-[140px] border border-transparent text-sm font-semibold rounded-full text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg hover:shadow-indigo-200 hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed">
                                        <Save :size="18" class="mr-2" />
                                        {{ form.status === 'terbit' ? 'Terbitkan' : 'Simpan Draft' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-enter {
    animation: slideUpFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes slideUpFade {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>