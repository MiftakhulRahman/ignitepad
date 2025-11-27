<script setup>
// ... Import sama seperti Buat.vue ...
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Layout, Image, Cpu, Settings, CheckCircle2, ChevronRight, ChevronLeft, Save, AlertCircle } from 'lucide-vue-next';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import ImageUpload from '@/Components/ImageUpload.vue';

const props = defineProps({
    proyek: Object, // Data proyek existing
    kategoris: Array,
    teknologis: Array,
    currentTeknologiIds: Array,
});

const steps = [
    { id: 1, title: 'Informasi Dasar', icon: Layout, desc: 'Judul & Deskripsi' },
    { id: 2, title: 'Konten Visual', icon: Image, desc: 'Gambar & Detail' },
    { id: 3, title: 'Teknologi & Link', icon: Cpu, desc: 'Stack & Repositori' },
    { id: 4, title: 'Pengaturan', icon: Settings, desc: 'Publikasi' },
];

const currentStep = ref(1);

// Init form dengan data yang ada
const form = useForm({
    _method: 'POST', // Trick agar Laravel terima file upload seolah PUT
    judul: props.proyek.judul,
    kategori_id: props.proyek.kategori_id,
    deskripsi: props.proyek.deskripsi,
    konten_html: props.proyek.konten_html,
    thumbnail: null, // Kosongkan, backend handle jika null = gak ganti
    galeri: [],
    teknologi_ids: props.currentTeknologiIds, // Pre-filled
    url_demo: props.proyek.url_demo,
    url_repository: props.proyek.url_repository,
    status: props.proyek.status,
    visibilitas: props.proyek.visibilitas,
    boleh_komentar: Boolean(props.proyek.boleh_komentar),
});

const nextStep = () => { currentStep.value++; }; // Simplifikasi validasi utk edit
const prevStep = () => { currentStep.value--; };

const submit = () => {
    // Kirim ke route Update
    form.post(route('proyek.update', props.proyek.id), {
        preserveScroll: true,
    });
};

const selectTeknologi = (id) => {
    if (form.teknologi_ids.includes(id)) {
        form.teknologi_ids = form.teknologi_ids.filter(t => t !== id);
    } else {
        form.teknologi_ids.push(id);
    }
};
</script>

<template>
    <Head title="Edit Proyek" />
    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-8 pb-20">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Proyek: {{ proyek.judul }}</h1>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                <form @submit.prevent="submit">
                    <div class="p-6 sm:p-8 min-h-[400px]">
                        
                        <div v-show="currentStep === 1" class="space-y-6">
                             <div>
                                <InputLabel for="judul" value="Judul Proyek" />
                                <TextInput id="judul" v-model="form.judul" type="text" class="mt-1 block w-full" />
                             </div>
                             </div>

                        <div v-show="currentStep === 2" class="space-y-8">
                            <div class="bg-blue-50 p-4 rounded-lg text-blue-700 text-sm">
                                Biarkan kosong jika tidak ingin mengubah gambar thumbnail.
                            </div>
                            </div>

                        </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                        <button type="submit" v-if="currentStep === 4" class="...button-style...">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>