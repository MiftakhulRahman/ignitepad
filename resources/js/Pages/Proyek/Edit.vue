<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Layout, Image as ImageIcon, Cpu, Settings,
    ChevronRight, ChevronLeft, Save, Globe, Smartphone, HardDrive, Gamepad2, PenTool, ArrowLeft,
    CheckCircle2
} from 'lucide-vue-next';

// Components
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import CollaboratorInput from '@/Components/Proyek/CollaboratorInput.vue';
import Toast from '@/Components/Toast.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
// IMPORT COMPONENT STEPPER MANUAL
import VerticalStepper from '@/Components/VerticalStepper.vue';

const props = defineProps({
    proyek: Object,
    kategoris: Array,
    teknologis: Array,
    currentTeknologiIds: Array,
    kolaborators: Array,
});

const iconMap = {
    'Globe': Globe, 'Smartphone': Smartphone, 'Cpu': Cpu,
    'HardDrive': HardDrive, 'Gamepad2': Gamepad2, 'PenTool': PenTool,
};
const getIcon = (name) => iconMap[name] || Layout;

// Data Steps (Sama seperti Buat.vue)
const steps = [
    { step: 1, title: 'Informasi', description: 'Dasar' },
    { step: 2, title: 'Visual', description: 'Konten' },
    { step: 3, title: 'Teknologi', description: 'Stack' },
    { step: 4, title: 'Finalisasi', description: 'Akses' },
];

const currentStep = ref(1);

const form = useForm({
    _method: 'POST',
    judul: props.proyek.judul,
    kategori_id: props.proyek.kategori_id,
    deskripsi: props.proyek.deskripsi,
    konten_html: props.proyek.konten_html,
    thumbnail: null,
    teknologi_ids: props.currentTeknologiIds,
    url_demo: props.proyek.url_demo,
    url_repository: props.proyek.url_repository,
    status: props.proyek.status,
    visibilitas: props.proyek.visibilitas,
    boleh_komentar: Boolean(props.proyek.boleh_komentar),
    kolaborators: props.kolaborators ? props.kolaborators.map(k => ({
        user_id: k.user.id,
        nama: k.user.nama,
        email: k.user.email,
        username: k.user.username,
        avatar: k.user.avatar,
        peran_kolaborator: k.peran_kolaborator,
        access_level: (k.bisa_edit && k.bisa_hapus) ? 'full_access' : 'view_only',
        bisa_edit: Boolean(k.bisa_edit),
        bisa_hapus: Boolean(k.bisa_hapus)
    })) : [],
});

const existingThumbnailUrl = ref(props.proyek.thumbnail ? `/storage/${props.proyek.thumbnail}` : null);

const selectTeknologi = (id) => {
    if (form.teknologi_ids.includes(id)) form.teknologi_ids = form.teknologi_ids.filter(t => t !== id);
    else form.teknologi_ids.push(id);
};

const validateStep = (step) => {
    form.clearErrors();
    let valid = true;
    if (step === 1) {
        if (!form.judul) { form.setError('judul', 'Judul wajib diisi'); valid = false; }
        if (!form.deskripsi) { form.setError('deskripsi', 'Deskripsi wajib diisi'); valid = false; }
    }
    if (step === 2) {
        if (!form.konten_html || form.konten_html === '<p></p>') { form.setError('konten_html', 'Konten wajib diisi'); valid = false; }
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
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const prevStep = () => {
    currentStep.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Logic Navigasi via Stepper
const goToStep = (step) => {
    // Logic: Boleh mundur kapan saja. Boleh maju hanya jika step saat ini valid.
    if (step < currentStep.value) {
        currentStep.value = step;
    }
    else if (step > currentStep.value) {
        // Cek validasi step saat ini sebelum loncat
        if (validateStep(currentStep.value)) {
            // Opsional: Batasi agar tidak bisa loncat lebih dari 1 step jika belum pernah diisi
            // Tapi untuk Edit biasanya user bebas loncat-loncat asalkan data valid
            currentStep.value = step;
        }
    }
};

const submit = () => {
    // Validate all steps before submit
    const step1Valid = validateStep(1);
    const step2Valid = validateStep(2);
    const step3Valid = validateStep(3);

    if (!step1Valid || !step2Valid || !step3Valid) {
        // Navigate to first invalid step
        if (!step1Valid) {
            currentStep.value = 1;
        } else if (!step2Valid) {
            currentStep.value = 2;
        } else if (!step3Valid) {
            currentStep.value = 3;
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    form.post(route('proyek.update', props.proyek.id), {
        preserveScroll: true,
        onError: (errors) => {
            // Navigate to step with error
            if (errors.judul || errors.kategori_id || errors.deskripsi) {
                currentStep.value = 1;
            } else if (errors.thumbnail || errors.konten_html) {
                currentStep.value = 2;
            } else if (errors.teknologi_ids || errors.url_demo || errors.url_repository) {
                currentStep.value = 3;
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
};
</script>

<template>

    <Head title="Edit Proyek" />
    <Toast />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#FDFDFD] pb-24 pt-8 font-sans">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">

                <Breadcrumb :items="[
                    { label: 'Proyek Saya', url: route('proyek.saya') },
                    { label: proyek.judul, url: route('proyek.dashboard.show', proyek.slug) },
                    { label: 'Edit' }
                ]" />

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <Link :href="route('proyek.dashboard.show', proyek.slug)"
                        class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-900 bg-white px-4 py-2 rounded-full border border-slate-200 shadow-sm transition-all hover:shadow-md">
                    <ArrowLeft :size="16" class="mr-2" />
                    Kembali ke Preview
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <div class="lg:col-span-3 sticky top-8">
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 overflow-hidden">
                            <VerticalStepper :steps="steps" :current-step="currentStep" @go-to-step="goToStep" />
                        </div>
                    </div>

                    <div class="lg:col-span-9">
                        <div
                            class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative flex flex-col min-h-[600px]">
                            <form @submit.prevent="submit" class="flex-1 flex flex-col">
                                <div class="p-8 md:p-12 flex-1">

                                    <div class="mb-8 border-b border-slate-50 pb-4">
                                        <h2 class="text-2xl font-semibold text-slate-800">{{ steps[currentStep -
                                            1].title
                                        }}</h2>
                                        <p class="text-slate-500">Edit informasi proyek.</p>
                                    </div>

                                    <div v-show="currentStep === 1" class="animate-enter space-y-6">
                                        <div class="space-y-4">
                                            <div>
                                                <InputLabel value="Judul Proyek" />
                                                <TextInput v-model="form.judul"
                                                    class="mt-2 block w-full text-lg rounded-2xl" />
                                                <InputError :message="form.errors.judul" />
                                            </div>
                                            <div>
                                                <InputLabel value="Deskripsi Singkat" />
                                                <textarea v-model="form.deskripsi" rows="3"
                                                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 focus:bg-white resize-none"></textarea>
                                                <InputError :message="form.errors.deskripsi" />
                                            </div>
                                            <div>
                                                <InputLabel value="Kategori" class="mb-2" />
                                                <div class="flex flex-wrap gap-3">
                                                    <div v-for="kat in kategoris" :key="kat.id"
                                                        @click="form.kategori_id = kat.id"
                                                        class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition-all"
                                                        :class="form.kategori_id === kat.id ? 'border-indigo-600 bg-indigo-50 ring-1 ring-indigo-600' : 'border-slate-200 bg-white hover:bg-slate-50'">
                                                        <component :is="getIcon(kat.ikon)" class="w-4 h-4"
                                                            :class="form.kategori_id === kat.id ? 'text-indigo-600' : 'text-slate-500'" />
                                                        <span class="text-sm font-medium"
                                                            :class="form.kategori_id === kat.id ? 'text-indigo-900' : 'text-slate-700'">{{
                                                                kat.nama }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-show="currentStep === 2" class="animate-enter space-y-8">
                                        <div>
                                            <InputLabel value="Thumbnail" class="mb-2" />
                                            <ImageUpload v-model="form.thumbnail" :preview="existingThumbnailUrl"
                                                :aspect-ratio="16 / 9" class="w-full rounded-2xl" />
                                        </div>
                                        <div>
                                            <InputLabel value="Konten" class="mb-2" />
                                            <RichTextEditor v-model="form.konten_html" />
                                        </div>
                                    </div>

                                    <div v-show="currentStep === 3" class="animate-enter space-y-8">
                                        <div>
                                            <InputLabel value="Tech Stack" class="mb-4" />
                                            <div class="flex flex-wrap gap-4">
                                                <div v-for="tech in teknologis" :key="tech.id"
                                                    @click="selectTeknologi(tech.id)"
                                                    class="flex items-center gap-3 px-4 py-2.5 rounded-full border cursor-pointer transition-all duration-300 select-none group"
                                                    :class="form.teknologi_ids.includes(tech.id)
                                                        ? 'bg-indigo-50 border-indigo-600 ring-1 ring-indigo-600 scale-105'
                                                        : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50'">

                                                    <img :src="tech.ikon_url"
                                                        class="w-5 h-5 object-contain transition-all duration-300"
                                                        :class="form.teknologi_ids.includes(tech.id) ? 'grayscale-0' : 'grayscale group-hover:grayscale-0 opacity-70 group-hover:opacity-100'">

                                                    <span class="text-xs font-semibold"
                                                        :class="form.teknologi_ids.includes(tech.id) ? 'text-indigo-900' : 'text-slate-600'">
                                                        {{ tech.nama }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="w-full">
                                                <InputLabel value="Link Repository" />
                                                <TextInput v-model="form.url_repository" type="url"
                                                    class="rounded-xl w-full" placeholder="https://github.com/..." />
                                                <InputError :message="form.errors.url_repository" class="mt-2" />
                                            </div>
                                            <div class="w-full">
                                                <InputLabel value="Link Demo" />
                                                <TextInput v-model="form.url_demo" type="url" class="rounded-xl w-full"
                                                    placeholder="https://example.com" />
                                                <InputError :message="form.errors.url_demo" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>

                                    <div v-show="currentStep === 4" class="animate-enter space-y-8">
                                        <CollaboratorInput v-model="form.kolaborators" />
                                        <div class="bg-slate-50 rounded-2xl p-6 flex flex-col sm:flex-row gap-6">
                                            <div class="flex-1">
                                                <InputLabel value="Status" class="mb-2" />
                                                <select v-model="form.status"
                                                    class="w-full rounded-xl border-slate-200">
                                                    <option value="draft">Draft</option>
                                                    <option value="terbit">Terbit</option>
                                                </select>
                                            </div>
                                            <div class="flex-1">
                                                <InputLabel value="Visibilitas" class="mb-2" />
                                                <select v-model="form.visibilitas"
                                                    class="w-full rounded-xl border-slate-200">
                                                    <option value="publik">Publik</option>
                                                    <option value="terbatas">Terbatas</option>
                                                    <option value="privat">Privat</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-between items-center rounded-b-[2rem]">
                                    <button type="button" v-if="currentStep > 1" @click="prevStep"
                                        class="inline-flex items-center justify-center h-12 px-8 min-w-[140px] border border-slate-200 rounded-full bg-white text-slate-600 hover:bg-slate-100 transition-colors">
                                        <ChevronLeft :size="20" class="mr-1" /> Kembali
                                    </button>
                                    <div v-else></div>

                                    <button type="button" v-if="currentStep < 4" @click="nextStep"
                                        class="inline-flex items-center justify-center h-12 px-8 min-w-[140px] bg-slate-900 text-white rounded-full font-medium text-sm hover:bg-slate-800 transition-all">
                                        Lanjut
                                        <ChevronRight :size="18" class="ml-1" />
                                    </button>

                                    <button type="submit" v-if="currentStep === 4" :disabled="form.processing"
                                        class="inline-flex items-center justify-center h-12 px-8 min-w-[140px] bg-indigo-600 text-white rounded-full font-medium text-sm hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
                                        <Save :size="16" /> Simpan
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
    animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>