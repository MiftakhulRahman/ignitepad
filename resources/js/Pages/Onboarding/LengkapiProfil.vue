<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { CheckCircle, User, BookOpen } from 'lucide-vue-next';

const props = defineProps({
    prodis: Array,
    auth: Object
});

// Form state
const form = useForm({
    peran: 'mahasiswa', // default
    nomor_induk: '',
    prodi_id: '',
    bio: '',
    linkedin_url: '',
    github_url: '',
    website_url: ''
});

// Pilih peran (UI Card Selection)
const selectPeran = (peran) => {
    form.peran = peran;
};
</script>

<template>
    <Head title="Lengkapi Profil" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div class="mx-auto h-12 w-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white">
                <CheckCircle :size="28" />
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Halo, {{ auth.user.nama }}!
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Mari lengkapi profil Anda sebelum mulai berkarya.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-100">
                
                <form @submit.prevent="form.post(route('onboarding.simpan'))">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Saya adalah seorang...</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div 
                                @click="selectPeran('mahasiswa')"
                                class="cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center transition-all"
                                :class="form.peran === 'mahasiswa' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <User :size="32" :class="form.peran === 'mahasiswa' ? 'text-indigo-600' : 'text-gray-400'" />
                                <span class="mt-2 font-semibold text-sm" :class="form.peran === 'mahasiswa' ? 'text-indigo-700' : 'text-gray-600'">Mahasiswa</span>
                            </div>

                            <div 
                                @click="selectPeran('dosen')"
                                class="cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center transition-all"
                                :class="form.peran === 'dosen' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <BookOpen :size="32" :class="form.peran === 'dosen' ? 'text-indigo-600' : 'text-gray-400'" />
                                <span class="mt-2 font-semibold text-sm" :class="form.peran === 'dosen' ? 'text-indigo-700' : 'text-gray-600'">Dosen</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div>
                            <InputLabel for="nomor_induk" :value="form.peran === 'mahasiswa' ? 'NIM (Nomor Induk Mahasiswa)' : 'NIDN (Nomor Induk Dosen)'" />
                            <TextInput id="nomor_induk" type="number" class="mt-1 block w-full" v-model="form.nomor_induk" required placeholder="Contoh: 2025001" />
                        </div>

                        <div>
                            <InputLabel for="prodi" value="Program Studi" />
                            <select 
                                id="prodi" 
                                v-model="form.prodi_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="" disabled>Pilih Program Studi</option>
                                <option v-for="prodi in prodis" :key="prodi.id" :value="prodi.id">
                                    {{ prodi.nama }} ({{ prodi.kode }})
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div>
                            <InputLabel for="bio" value="Bio Singkat (Opsional)" />
                            <textarea 
                                id="bio" 
                                v-model="form.bio"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3"
                                placeholder="Ceritakan sedikit tentang keahlian atau minat Anda..."
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="linkedin" value="LinkedIn URL (Opsional)" />
                                <TextInput id="linkedin" type="url" class="mt-1 block w-full" v-model="form.linkedin_url" placeholder="https://linkedin.com/in/..." />
                            </div>
                            <div>
                                <InputLabel for="github" value="GitHub URL (Opsional)" />
                                <TextInput id="github" type="url" class="mt-1 block w-full" v-model="form.github_url" placeholder="https://github.com/..." />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <PrimaryButton class="w-full justify-center py-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Simpan & Masuk Dashboard
                        </PrimaryButton>
                    </div>

                </form>
            </div>
        </div>
    </div>
</template>