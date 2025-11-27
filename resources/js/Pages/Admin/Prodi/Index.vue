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
    ChevronDown, FileText, Sheet, GraduationCap, BookOpen, Library
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
// Kita mapping nama fakultas panjang ke label pendek biar muat di tab
const tabs = computed(() => [
    { id: 'semua', label: 'Semua', icon: Library },
    // { id: 'Fakultas Agama Islam', label: 'Agama Islam', icon: BookOpen },
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
            <Breadcrumb :items="[{ label: 'Program Studi' }]" />

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Program Studi</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola data Program Studi dan Fakultas.</p>
                </div>
                <div class="flex gap-2">
                    <Transition enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95">
                        <DangerButton v-if="selectedIds.length > 0" @click="confirmBulkDelete"
                            class="flex items-center gap-2">
                            <Trash2 :size="16" /> Hapus ({{ selectedIds.length }})
                        </DangerButton>
                    </Transition>
                    <PrimaryButton @click="openCreateModal" class="flex items-center gap-2 shadow-sm">
                        <Plus :size="16" /> Tambah Prodi
                    </PrimaryButton>
                </div>
            </div>

            <div>
                <div class="bg-gray-100 rounded-lg p-1.5 inline-flex gap-1 overflow-x-auto max-w-full">
                    <button v-for="tab in tabs" :key="tab.id" @click="switchTab(tab.id)"
                        class="group inline-flex items-center justify-center px-4 py-2.5 rounded-md font-medium text-sm transition-all duration-200 whitespace-nowrap"
                        :class="activeFakultas === tab.id
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200/50'">
                        <component :is="tab.icon" class="mr-2 h-4 w-4"
                            :class="activeFakultas === tab.id ? 'text-indigo-600' : 'text-gray-500 group-hover:text-gray-700'" />
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col min-h-[500px]">

                <div
                    class="p-4 border-b border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row gap-4 justify-between items-center">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative w-full sm:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Search :size="16" class="text-gray-400" />
                            </div>
                            <TextInput v-model="search" type="text" placeholder="Cari nama prodi atau kode..."
                                class="pl-9 pr-8 block w-full text-sm rounded-lg" />
                            <button v-if="search" @click="search = ''"
                                class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer">
                                <X :size="14" class="bg-gray-200 rounded-full p-0.5 w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <button @click="handleRefresh"
                            class="p-2.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100">
                            <RefreshCw :size="18" />
                        </button>
                        <div class="h-6 w-px bg-gray-300 mx-1"></div>
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    class="flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                                    <Download :size="16" /> Export
                                    <ChevronDown :size="14" class="text-gray-400" />
                                </button>
                            </template>
                            <template #content>
                                <div class="py-1">
                                    <button @click="exportData('csv')"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <FileText :size="16" class="text-green-600" /> CSV
                                    </button>
                                    <button @click="exportData('excel')"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <Sheet :size="16" class="text-green-600" /> Excel
                                    </button>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left w-10"><input type="checkbox"
                                        v-model="selectAll"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Kode</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Nama
                                    Program Studi</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Fakultas
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="prodi in prodis.data" :key="prodi.id"
                                class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap align-middle"><input type="checkbox"
                                        :value="prodi.id" v-model="selectedIds"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2.5 py-1 text-xs font-mono font-bold bg-gray-100 border border-gray-300 rounded text-gray-700">{{
                                        prodi.kode }}</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ prodi.nama }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600">{{ prodi.fakultas }}</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal(prodi)"
                                            class="p-2 text-indigo-600 bg-white hover:bg-indigo-50 border border-gray-200 rounded-lg transition-all shadow-sm">
                                            <Edit2 :size="16" />
                                        </button>
                                        <button @click="confirmDelete(prodi.id)"
                                            class="p-2 text-red-600 bg-white hover:bg-red-50 border border-gray-200 rounded-lg transition-all shadow-sm">
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="prodis.data.length === 0">
                                <td colspan="5" class="px-6 py-24 text-center text-gray-500 bg-gray-50/30">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-gray-100 p-4 rounded-full mb-3">
                                            <GraduationCap :size="32" class="text-gray-300" />
                                        </div>
                                        <p class="text-base font-medium text-gray-900">Belum ada data Program Studi</p>
                                        <p v-if="activeFakultas !== 'semua'" class="text-sm text-gray-500 mt-1">di {{
                                            activeFakultas }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
                    <div class="text-sm text-gray-500">Menampilkan {{ prodis.from || 0 }} - {{ prodis.to || 0 }} dari {{
                        prodis.total }}
                        data</div>
                    <div class="flex items-center gap-4">
                        <select v-model="perPage"
                            class="text-xs border-gray-300 rounded-lg focus:ring-indigo-500 py-1.5 pl-2 pr-7 cursor-pointer">
                            <option :value="10">10 / hal</option>
                            <option :value="25">25 / hal</option>
                            <option :value="50">50 / hal</option>
                        </select>
                        <Pagination :links="prodis.links" />
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">{{ isEditing ? 'Edit Program Studi' : 'Tambah Program Studi' }}</h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <X :size="24" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="fakultas" value="Fakultas" />
                        <select id="fakultas" v-model="form.fakultas"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                            <option value="" disabled>Pilih Fakultas...</option>
                            <option v-for="fak in listFakultas.filter(f => f !== 'Fakultas Agama Islam')" :key="fak"
                                :value="fak">
                                {{ fak }}
                            </option>

                        </select>
                        <InputError :message="form.errors.fakultas" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <InputLabel for="nama" value="Nama Program Studi" />
                            <TextInput id="nama" v-model="form.nama" type="text" class="mt-1 block w-full"
                                placeholder="Contoh: Informatika" />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="kode" value="Kode (Singkatan)" />
                            <TextInput id="kode" v-model="form.kode" type="text"
                                class="mt-1 block w-full uppercase font-mono" placeholder="INF" maxlength="10" />
                            <InputError :message="form.errors.kode" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <SecondaryButton @click="closeModal"> Batal </SecondaryButton>
                    <PrimaryButton @click="submitForm" :disabled="form.processing" class="shadow-md px-6">{{ isEditing ?
                        'Simpan' : 'Tambah' }}</PrimaryButton>
                </div>
            </div>
        </Modal>

        <ConfirmationModal :show="showDeleteModal" :title="'Hapus Prodi?'"
            :content="'Data yang dihapus tidak bisa dikembalikan. Pastikan tidak ada mahasiswa di prodi ini.'"
            @close="showDeleteModal = false" @confirm="executeDelete" />
    </AuthenticatedLayout>
</template>