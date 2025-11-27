<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Layout, Trophy, ScrollText, CheckCircle2, Plus, Trash2, AlertCircle } from 'lucide-vue-next';

// Components
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    kategoris: Array,
});

const form = useForm({
    judul: '',
    kategori_ids: [],
    banner: null,
    deskripsi: '',
    aturan_html: '',
    hadiah: '',
    tanggal_mulai: '',
    batas_waktu: '',
    maks_peserta: 0,
    kriteria: [
        { nama: 'Kreativitas', bobot: 30, deskripsi: '' }, // Default item
        { nama: 'Fungsionalitas', bobot: 40, deskripsi: '' },
    ],
    status: 'buka',
});

// --- CRITERIA LOGIC ---
const addKriteria = () => {
    form.kriteria.push({ nama: '', bobot: 0, deskripsi: '' });
};

const removeKriteria = (index) => {
    form.kriteria.splice(index, 1);
};

const totalBobot = computed(() => {
    return form.kriteria.reduce((sum, item) => sum + Number(item.bobot), 0);
});

const isBobotValid = computed(() => totalBobot.value === 100);

// --- CATEGORY LOGIC ---
const toggleKategori = (id) => {
    if (form.kategori_ids.includes(id)) {
        form.kategori_ids = form.kategori_ids.filter(k => k !== id);
    } else {
        form.kategori_ids.push(id);
    }
};

const submit = () => {
    if (!isBobotValid.value) {
        alert('Total bobot penilaian harus pas 100%. Saat ini: ' + totalBobot.value + '%');
        return;
    }
    form.post(route('challenge.store'));
};
</script>

<template>
    <Head title="Buat Challenge Baru" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-6 pb-20">
            
            <div class="flex items-center gap-4">
                <div class="p-3 bg-indigo-100 text-indigo-600 rounded-xl">
                    <Trophy :size="32" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Buat Challenge Baru</h1>
                    <p class="text-gray-500">Tantang mahasiswa untuk berkarya dan berinovasi.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <Layout :size="20" class="text-gray-400" /> Informasi Dasar
                        </h3>
                        
                        <div>
                            <InputLabel value="Judul Challenge" />
                            <TextInput v-model="form.judul" type="text" class="mt-1 block w-full text-lg font-medium" placeholder="Contoh: Hackathon UI/UX 2025" />
                            <InputError :message="form.errors.judul" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="Banner Utama (16:9)" />
                            <ImageUpload v-model="form.banner" previewSize="h-48" class="mt-1" />
                            <InputError :message="form.errors.banner" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="Deskripsi Singkat" />
                            <textarea v-model="form.deskripsi" rows="3" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Jelaskan secara ringkas tentang challenge ini..."></textarea>
                            <InputError :message="form.errors.deskripsi" class="mt-2" />
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <ScrollText :size="20" class="text-gray-400" /> Detail & Aturan Main
                        </h3>

                        <div>
                            <InputLabel value="Aturan Lengkap & Persyaratan" class="mb-2" />
                            <RichTextEditor v-model="form.aturan_html" placeholder="Tuliskan aturan main, syarat teknis, dan detail lainnya di sini..." />
                            <InputError :message="form.errors.aturan_html" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="Hadiah Pemenang" />
                            <TextInput v-model="form.hadiah" type="text" class="mt-1 block w-full" placeholder="Contoh: Sertifikat + Saldo E-Wallet Total 1 Juta" />
                            <p class="text-xs text-gray-500 mt-1">Bisa berupa barang, uang, nilai tambahan, atau sertifikat.</p>
                            <InputError :message="form.hadiah" class="mt-2" />
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900">Kriteria Penilaian</h3>
                            <div class="text-sm font-medium" :class="isBobotValid ? 'text-green-600' : 'text-red-600'">
                                Total Bobot: {{ totalBobot }}% / 100%
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div v-for="(item, index) in form.kriteria" :key="index" class="flex gap-3 items-start p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex-1">
                                    <TextInput v-model="item.nama" type="text" class="block w-full text-sm" placeholder="Nama Kriteria (mis: Desain)" />
                                </div>
                                <div class="w-24 relative">
                                    <TextInput v-model="item.bobot" type="number" class="block w-full text-sm pr-6" placeholder="%" />
                                    <span class="absolute right-2 top-2 text-gray-400 text-xs">%</span>
                                </div>
                                <button type="button" @click="removeKriteria(index)" class="p-2 text-gray-400 hover:text-red-500 transition-colors mt-0.5">
                                    <Trash2 :size="18" />
                                </button>
                            </div>
                        </div>

                        <button type="button" @click="addKriteria" class="flex items-center gap-2 text-sm text-indigo-600 font-medium hover:text-indigo-800">
                            <Plus :size="16" /> Tambah Kriteria
                        </button>
                        <InputError :message="form.errors.kriteria" class="mt-2" />
                    </div>

                </div>

                <div class="space-y-6">
                    
                    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-4">
                        <h4 class="font-bold text-gray-900">Jadwal Pelaksanaan</h4>
                        <div>
                            <InputLabel value="Tanggal Mulai" />
                            <TextInput v-model="form.tanggal_mulai" type="date" class="mt-1 block w-full" />
                            <InputError :message="form.errors.tanggal_mulai" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Batas Waktu (Deadline)" />
                            <TextInput v-model="form.batas_waktu" type="date" class="mt-1 block w-full" />
                            <InputError :message="form.errors.batas_waktu" class="mt-2" />
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-4">
                        <h4 class="font-bold text-gray-900">Batasan Peserta</h4>
                        
                        <div>
                            <InputLabel value="Kategori Proyek Diizinkan" class="mb-2" />
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                <div 
                                    v-for="kat in kategoris" :key="kat.id"
                                    @click="toggleKategori(kat.id)"
                                    class="flex items-center p-2 rounded cursor-pointer border transition-colors"
                                    :class="form.kategori_ids.includes(kat.id) ? 'bg-indigo-50 border-indigo-200' : 'bg-white border-gray-200 hover:bg-gray-50'"
                                >
                                    <div class="w-4 h-4 rounded border flex items-center justify-center mr-3" :class="form.kategori_ids.includes(kat.id) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300'">
                                        <CheckCircle2 v-if="form.kategori_ids.includes(kat.id)" :size="12" class="text-white" />
                                    </div>
                                    <span class="text-sm text-gray-700">{{ kat.nama }}</span>
                                </div>
                            </div>
                            <InputError :message="form.errors.kategori_ids" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="Maksimal Peserta (0 = Unlimited)" />
                            <TextInput v-model="form.maks_peserta" type="number" class="mt-1 block w-full" />
                        </div>
                    </div>

                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 shadow-sm space-y-4">
                        <div class="flex gap-3 items-start">
                            <AlertCircle class="text-indigo-600 shrink-0 mt-0.5" :size="20" />
                            <p class="text-xs text-indigo-800">
                                Setelah diterbitkan, mahasiswa akan mendapatkan notifikasi. Pastikan data sudah benar.
                            </p>
                        </div>
                        
                        <div class="flex gap-2 pt-2">
                            <PrimaryButton class="w-full justify-center" :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                                Terbitkan Challenge
                            </PrimaryButton>
                        </div>
                    </div>

                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>