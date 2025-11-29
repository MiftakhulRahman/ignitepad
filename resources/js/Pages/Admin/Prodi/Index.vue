<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import * as LucideIcons from 'lucide-vue-next';

// Components
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Toast from '@/Components/Toast.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import Dropdown from '@/Components/Dropdown.vue';

const {
    Search, Plus, Edit2, Trash2, Download, RefreshCw,
    ChevronDown, FileText, Sheet, GraduationCap, BookOpen, Library, X
} = LucideIcons;

const props = defineProps({
    prodis: Object,
    filters: Object,
    listFakultas: Array, // ['Fakultas Agama Islam', ...]
});

// --- STATES ---
const activeFakultas = ref(props.filters.fakultas || 'semua');
const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);

const selectedIds = ref([]);
const selectAll = ref(false);
const toastRef = ref(null);

const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const itemToDelete = ref(null);

const form = useForm({
    id: null,
    nama: '',
    kode: '',
    fakultas: '',
});

// --- TABS CONFIG (FAKULTAS) ---
const tabs = computed(() => [
    { id: 'semua', label: 'Semua', icon: Library },
    { id: 'Fakultas Ilmu Pendidikan', label: 'Ilmu Pendidikan', icon: GraduationCap },
    { id: 'Fakultas Sains dan Teknologi', label: 'Sains dan Teknologi', icon: LucideIcons.Cpu },
]);

// --- WATCHERS ---
watch([search, perPage], debounce(() => {
    fetchData();
}, 300));

watch(selectAll, (value) => {
    selectedIds.value = value ? props.prodis.data.map(item => item.id) : [];
});

// --- FUNCTIONS ---
const switchTab = (fakultasId) => {
    activeFakultas.value = fakultasId;
    search.value = '';
    selectedIds.value = [];
    selectAll.value = false;
    fetchData();
};

const fetchData = () => {
    router.get(route('prodi.index'), {
        fakultas: activeFakultas.value,
        search: search.value,
        per_page: perPage.value
    }, { preserveState: true, replace: true, preserveScroll: true });
};

const handleRefresh = () => {
    search.value = '';
    fetchData();
    if (toastRef.value) toastRef.value.fire('Data berhasil diperbarui', 'success');
};

const exportData = (type) => {
    const url = route('prodi.export') + `?type=${type}&fakultas=${activeFakultas.value}`;
    window.location.href = url;
};

// --- MODAL HANDLERS ---
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    // Auto-fill fakultas jika sedang di tab spesifik
    if (activeFakultas.value !== 'semua') {
        form.fakultas = activeFakultas.value;
    } else {
        form.fakultas = '';
    }
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    form.id = item.id;
    form.nama = item.nama;
    form.kode = item.kode;
    form.fakultas = item.fakultas;
    form.clearErrors();
    showModal.value = true;
};

const confirmDelete = (id) => {
    itemToDelete.value = id;
    showDeleteModal.value = true;
};

const confirmBulkDelete = () => {
    itemToDelete.value = null;
    showDeleteModal.value = true;
};

const submitForm = () => {
    const action = isEditing.value ? route('prodi.update', form.id) : route('prodi.store');
    const method = isEditing.value ? 'put' : 'post';
    form[method](action, { onSuccess: () => closeModal() });
};

const executeDelete = () => {
    if (itemToDelete.value) {
        router.delete(route('prodi.destroy', itemToDelete.value), {
            onSuccess: () => { showDeleteModal.value = false; itemToDelete.value = null; }
        });
    } else {
        router.post(route('prodi.bulk-delete'), { ids: selectedIds.value }, {
            onSuccess: () => { showDeleteModal.value = false; selectedIds.value = []; selectAll.value = false; }
        });
    }
};

const closeModal = () => { showModal.value = false; form.reset(); };
</script>

<template>

    <Head title="Kelola Program Studi" />
    <Toast ref="toastRef" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <Breadcrumb :items="[{ label: 'Program Studi' }]" />
                    <h2 class="text-[28px] leading-9 font-normal text-slate-900">Program Studi</h2>
                </div>
                
                <div class="flex items-center gap-3">
                     <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                        <button v-if="selectedIds.length > 0" @click="confirmBulkDelete" class="flex items-center gap-2 px-4 py-2.5 bg-rose-100 hover:bg-rose-200 text-rose-800 rounded-full text-sm font-medium transition-colors">
                            <Trash2 :size="18" />
                            <span>Hapus ({{ selectedIds.length }})</span>
                        </button>
                    </Transition>
                    <button @click="openCreateModal" class="flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full font-medium transition-all shadow-sm hover:shadow-md">
                        <Plus :size="20" />
                        <span>Tambah Prodi</span>
                    </button>
                </div>
            </div>

            <div class="border-b border-slate-200">
                <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                    <button v-for="tab in tabs" :key="tab.id" @click="switchTab(tab.id)"
                        class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 whitespace-nowrap"
                        :class="activeFakultas === tab.id
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                        <component :is="tab.icon" :size="18" />
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <div class="bg-white rounded-[28px] border border-slate-200 shadow-sm overflow-hidden flex flex-col">

                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row gap-4 justify-between items-center bg-white">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative w-full sm:w-80 group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Search :size="18" class="text-slate-400 group-focus-within:text-indigo-600 transition-colors" />
                            </div>
                            <input v-model="search" type="text" placeholder="Cari nama prodi atau kode..."
                                class="pl-11 pr-10 block w-full py-2.5 bg-slate-100 border-none rounded-full text-sm focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-slate-500" />
                            <button v-if="search" @click="search = ''"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                <X :size="16" class="bg-slate-200 rounded-full p-0.5" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="handleRefresh" class="p-2.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-colors">
                            <RefreshCw :size="20" />
                        </button>
                        <div class="h-8 w-px bg-slate-200 mx-1"></div>
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-full hover:bg-slate-50 transition-colors">
                                    <Download :size="16" /> Export
                                </button>
                            </template>
                            <template #content>
                                <div class="p-1">
                                    <button @click="exportData('csv')" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-md">
                                        <FileText :size="16" class="text-emerald-600" /> CSV
                                    </button>
                                    <button @click="exportData('excel')" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-md">
                                        <Sheet :size="16" class="text-emerald-600" /> Excel
                                    </button>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                         <thead class="bg-slate-50/50">
                            <tr>
                                <th scope="col" class="pl-6 pr-3 py-4 text-left w-10">
                                    <input type="checkbox" v-model="selectAll" class="rounded-[4px] border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500/50 cursor-pointer w-4 h-4">
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Program Studi</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Fakultas</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="prodi in prodis.data" :key="prodi.id"
                                class="hover:bg-slate-50 transition-colors group">
                                <td class="pl-6 pr-3 py-4 align-middle">
                                    <input type="checkbox" :value="prodi.id" v-model="selectedIds" class="rounded-[4px] border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500/50 cursor-pointer w-4 h-4">
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-mono font-bold bg-slate-100 border border-slate-200 rounded-md text-slate-700">
                                        {{ prodi.kode }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-base font-semibold text-slate-900">{{ prodi.nama }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-50 text-slate-600 border border-slate-100 text-xs font-medium">
                                        <Library :size="12" />
                                        {{ prodi.fakultas }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal(prodi)"
                                            class="w-9 h-9 flex items-center justify-center text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-full transition-colors">
                                            <Edit2 :size="18" />
                                        </button>
                                        <button @click="confirmDelete(prodi.id)"
                                            class="w-9 h-9 flex items-center justify-center text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-full transition-colors">
                                            <Trash2 :size="18" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="prodis.data.length === 0">
                                <td colspan="5" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-slate-50 p-6 rounded-full mb-4">
                                            <GraduationCap :size="48" class="text-slate-300" />
                                        </div>
                                        <h3 class="text-lg font-medium text-slate-900">Belum ada data Program Studi</h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
                    <div class="text-sm text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">{{ prodis.from || 0 }}</span> - <span class="font-bold text-slate-700">{{ prodis.to || 0 }}</span> dari <span class="font-bold text-slate-700">{{ prodis.total }}</span> data
                    </div>
                    <div class="flex items-center gap-4">
                         <div class="relative">
                            <select v-model="perPage" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-8 cursor-pointer">
                                <option :value="10">10 / hal</option>
                                <option :value="25">25 / hal</option>
                                <option :value="50">50 / hal</option>
                            </select>
                            <ChevronDown :size="14" class="absolute right-3 top-3.5 text-slate-500 pointer-events-none" />
                        </div>
                        <Pagination :links="prodis.links" />
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-normal text-slate-900">{{ isEditing ? 'Edit Program Studi' : 'Tambah Program Studi' }}</h2>
                    <button @click="closeModal" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-600"><X :size="24"/></button>
                </div>

                <div class="space-y-6">
                    <div>
                        <InputLabel for="fakultas" value="Fakultas" class="mb-2" />
                        <select id="fakultas" v-model="form.fakultas"
                            class="block w-full rounded-xl bg-slate-50 border-slate-200 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 text-sm">
                            <option value="" disabled>Pilih Fakultas...</option>
                            <option v-for="fak in listFakultas.filter(f => f !== 'Fakultas Agama Islam')" :key="fak" :value="fak">
                                {{ fak }}
                            </option>
                        </select>
                        <InputError :message="form.errors.fakultas" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-2">
                            <InputLabel for="nama" value="Nama Program Studi" class="mb-2" />
                            <TextInput id="nama" v-model="form.nama" type="text" class="block w-full rounded-xl bg-slate-50 border-slate-200 focus:bg-white" placeholder="Contoh: Informatika" />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="kode" value="Kode (Singkatan)" class="mb-2" />
                            <TextInput id="kode" v-model="form.kode" type="text"
                                class="block w-full rounded-xl bg-slate-50 border-slate-200 focus:bg-white uppercase font-mono" placeholder="INF" maxlength="10" />
                            <InputError :message="form.errors.kode" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex justify-end gap-3">
                    <button @click="closeModal" class="px-6 py-2.5 rounded-full text-slate-700 font-medium hover:bg-slate-100 transition-colors">Batal</button>
                    <button @click="submitForm" :disabled="form.processing" class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full font-medium shadow-md hover:shadow-lg transition-all transform active:scale-95">
                        {{ isEditing ? 'Simpan' : 'Tambah' }}
                    </button>
                </div>
            </div>
        </Modal>

        <ConfirmationModal :show="showDeleteModal" :title="'Hapus Prodi?'"
            :content="'Data yang dihapus tidak bisa dikembalikan. Pastikan tidak ada mahasiswa di prodi ini.'"
            @close="showDeleteModal = false" @confirm="executeDelete" />
    </AuthenticatedLayout>
</template>