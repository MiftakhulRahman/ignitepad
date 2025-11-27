<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
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
import DropdownLink from '@/Components/DropdownLink.vue';

// Extract specific UI icons for internal usage to allow tree-shaking if needed, 
// though we imported all for dynamic rendering anyway.
const { 
    Search, Plus, Edit2, Trash2, Filter, Download, RefreshCw, 
    ChevronDown, Check, X, FileText, Sheet 
} = LucideIcons;

const props = defineProps({
    kategoris: Object,
    filters: Object,
});

// --- ICON DYNAMIC RESOLVER ---
// Mengambil komponen icon dari Lucide berdasarkan nama string (misal: "Globe" -> <Globe />)
const getIconComponent = (iconName) => {
    if (!iconName) return LucideIcons['HelpCircle']; // Default fallback
    return LucideIcons[iconName] || LucideIcons['HelpCircle']; // Fallback jika typo
};

// Daftar Icon Populer untuk Pilihan di Modal
const availableIcons = [
    { value: 'Globe', label: 'Web / Global' },
    { value: 'Smartphone', label: 'Mobile App' },
    { value: 'Cpu', label: 'AI / Robotik' },
    { value: 'HardDrive', label: 'IoT / Hardware' },
    { value: 'Gamepad2', label: 'Game Dev' },
    { value: 'PenTool', label: 'Desain / UIUX' },
    { value: 'Code', label: 'Programming' },
    { value: 'Terminal', label: 'DevOps / System' },
    { value: 'Activity', label: 'Kesehatan / Aktivitas' },
    { value: 'Briefcase', label: 'Bisnis' },
    { value: 'Zap', label: 'Elektronika / Power' },
    { value: 'Layers', label: 'Fullstack' },
];

// --- STATES ---
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const perPage = ref(props.filters.per_page || 10);
const selectedIds = ref([]);
const selectAll = ref(false);
const toastRef = ref(null);

const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const itemToDelete = ref(null);

// State untuk UI Filter Dropdown
const showFilterDropdown = ref(false);

const form = useForm({
    id: null,
    nama: '',
    warna: '#3B82F6',
    ikon: 'Layers', // Default Lucide Icon Name
    deskripsi: '',
    urutan: null,
    status_aktif: true,
});

// --- WATCHERS ---
watch([search, statusFilter, perPage], debounce(() => {
    refreshTable();
}, 300));

watch(selectAll, (value) => {
    selectedIds.value = value ? props.kategoris.data.map(item => item.id) : [];
});

// --- FUNCTIONS ---
const refreshTable = () => {
    router.get(route('kategori.index'), { 
        search: search.value, 
        status: statusFilter.value,
        per_page: perPage.value 
    }, { preserveState: true, replace: true, preserveScroll: true });
};

const clearSearch = () => {
    search.value = '';
};

const resetFilter = () => {
    statusFilter.value = '';
    search.value = '';
    showFilterDropdown.value = false; 
};

const handleRefresh = () => {
    resetFilter();
    selectedIds.value = [];
    selectAll.value = false;
    refreshTable();
    if(toastRef.value) toastRef.value.fire('Data berhasil diperbarui', 'success');
};

const toggleStatus = (kategori) => {
    router.patch(route('kategori.toggle', kategori.id), {}, {
        preserveScroll: true,
    });
};

const exportData = (type) => {
    const url = route('kategori.export') + `?type=${type}`;
    window.location.href = url;
};

// --- MODAL HANDLERS ---
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    form.ikon = 'Globe'; // Default
    form.urutan = null;
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    form.id = item.id;
    form.nama = item.nama;
    form.warna = item.warna;
    form.ikon = item.ikon || 'Globe';
    form.deskripsi = item.deskripsi;
    form.urutan = item.urutan;
    form.status_aktif = Boolean(item.status_aktif);
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
    const action = isEditing.value ? route('kategori.update', form.id) : route('kategori.store');
    const method = isEditing.value ? 'put' : 'post';
    
    form[method](action, {
        onSuccess: () => closeModal(),
    });
};

const executeDelete = () => {
    if (itemToDelete.value) {
        router.delete(route('kategori.destroy', itemToDelete.value), {
            onSuccess: () => { showDeleteModal.value = false; itemToDelete.value = null; }
        });
    } else {
        router.post(route('kategori.bulk-delete'), { ids: selectedIds.value }, {
            onSuccess: () => { showDeleteModal.value = false; selectedIds.value = []; selectAll.value = false; }
        });
    }
};

const closeModal = () => { showModal.value = false; form.reset(); };
</script>

<template>
    <Head title="Kelola Kategori" />
    <Toast ref="toastRef" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            
            <Breadcrumb :items="[{ label: 'Kategori' }]" />

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Kategori Proyek</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola kategori untuk mengelompokkan proyek mahasiswa.</p>
                </div>
                <div class="flex gap-2">
                     <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <DangerButton v-if="selectedIds.length > 0" @click="confirmBulkDelete" class="flex items-center gap-2">
                            <Trash2 :size="16" /> Hapus ({{ selectedIds.length }})
                        </DangerButton>
                    </Transition>

                    <PrimaryButton @click="openCreateModal" class="flex items-center gap-2 shadow-sm">
                        <Plus :size="16" /> Tambah Baru
                    </PrimaryButton>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col min-h-[500px]">
                
                <div class="p-4 border-b border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row gap-4 justify-between items-center">
                    
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <!-- SEARCH -->
                        <div class="relative w-full sm:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Search :size="16" class="text-gray-400" />
                            </div>
                            <TextInput 
                                v-model="search" 
                                type="text" 
                                placeholder="Cari kategori..." 
                                class="pl-9 pr-8 block w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                            />
                            <!-- Clear Search Button -->
                            <button 
                                v-if="search" 
                                @click="clearSearch"
                                class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"
                            >
                                <X :size="14" class="bg-gray-200 rounded-full p-0.5 w-5 h-5" />
                            </button>
                        </div>
                        
                        <!-- FILTER CUSTOM -->
                        <div class="relative">
                            <Dropdown align="left" width="48">
                                <template #trigger>
                                    <button class="flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                        <Filter :size="16" :class="statusFilter !== '' ? 'text-indigo-600' : 'text-gray-500'" />
                                        <span v-if="statusFilter === ''">Filter</span>
                                        <span v-else class="text-indigo-600">Terfilter</span>
                                        <ChevronDown :size="14" class="text-gray-400" />
                                    </button>
                                </template>
                                <template #content>
                                    <div class="p-2">
                                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-2">Status</div>
                                        <button 
                                            @click="statusFilter = '1'"
                                            class="w-full text-left px-2 py-1.5 text-sm rounded-md flex items-center justify-between hover:bg-gray-100"
                                            :class="statusFilter === '1' ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-700'"
                                        >
                                            <span>Aktif</span>
                                            <Check v-if="statusFilter === '1'" :size="14" />
                                        </button>
                                        <button 
                                            @click="statusFilter = '0'"
                                            class="w-full text-left px-2 py-1.5 text-sm rounded-md flex items-center justify-between hover:bg-gray-100"
                                            :class="statusFilter === '0' ? 'text-indigo-600 bg-indigo-50 font-medium' : 'text-gray-700'"
                                        >
                                            <span>Nonaktif</span>
                                            <Check v-if="statusFilter === '0'" :size="14" />
                                        </button>
                                        
                                        <div class="border-t border-gray-100 my-2"></div>
                                        <button 
                                            @click="resetFilter"
                                            class="w-full text-left px-2 py-1.5 text-sm text-red-600 rounded-md hover:bg-red-50 flex items-center gap-2"
                                        >
                                            <X :size="14" /> Reset Filter
                                        </button>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <button @click="handleRefresh" class="p-2.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100" title="Refresh Data">
                            <RefreshCw :size="18" />
                        </button>
                        
                        <div class="h-6 w-px bg-gray-300 mx-1"></div>

                        <!-- EXPORT DROPDOWN -->
                         <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                                    <Download :size="16" /> Export <ChevronDown :size="14" class="text-gray-400" />
                                </button>
                            </template>
                            <template #content>
                                <div class="py-1">
                                    <button @click="exportData('csv')" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <FileText :size="16" class="text-green-600" /> CSV (.csv)
                                    </button>
                                    <button @click="exportData('excel')" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <Sheet :size="16" class="text-green-600" /> Excel (.xlsx)
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
                                <th scope="col" class="px-6 py-3 text-left w-10"><input type="checkbox" v-model="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer"></th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori & Deskripsi</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="kategori in kategoris.data" :key="kategori.id" class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap align-top pt-5">
                                    <input type="checkbox" :value="kategori.id" v-model="selectedIds" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-4">
                                        <!-- Icon Box -->
                                        <div 
                                            class="flex-shrink-0 h-12 w-12 rounded-xl flex items-center justify-center text-white shadow-md transition-transform group-hover:scale-105" 
                                            :style="{ backgroundColor: kategori.warna }"
                                        >
                                            <component :is="getIconComponent(kategori.ikon)" :size="24" />
                                        </div>
                                        
                                        <!-- Content -->
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-sm font-bold text-gray-900">{{ kategori.nama }}</span>
                                                <span v-if="kategori.urutan > 0" class="text-[10px] px-1.5 py-0.5 rounded-md bg-gray-100 text-gray-500 border border-gray-200 font-mono">#{{ kategori.urutan }}</span>
                                            </div>
                                            <p class="text-xs text-gray-500 leading-relaxed line-clamp-2 max-w-md">
                                                {{ kategori.deskripsi || 'Belum ada deskripsi untuk kategori ini.' }}
                                            </p>
                                            <div class="mt-1.5">
                                                <span class="text-[10px] text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100">
                                                    slug: {{ kategori.slug }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center align-middle">
                                    <button 
                                        @click="toggleStatus(kategori)"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                                        :class="kategori.status_aktif ? 'bg-green-500' : 'bg-gray-200'"
                                        title="Klik untuk mengubah status"
                                    >
                                        <span class="sr-only">Use setting</span>
                                        <span 
                                            class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out flex items-center justify-center"
                                            :class="kategori.status_aktif ? 'translate-x-5' : 'translate-x-0'"
                                        >
                                            <Check v-if="kategori.status_aktif" :size="10" class="text-green-600 stroke-[3]" />
                                            <X v-else :size="10" class="text-gray-400 stroke-[3]" />
                                        </span>
                                    </button>
                                    <div class="text-[10px] font-medium mt-1" :class="kategori.status_aktif ? 'text-green-600' : 'text-gray-400'">
                                        {{ kategori.status_aktif ? 'Aktif' : 'Nonaktif' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right align-middle">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal(kategori)" class="p-2 text-indigo-600 bg-white hover:bg-indigo-50 hover:text-indigo-700 rounded-lg transition-all border border-gray-200 hover:border-indigo-200 shadow-sm" title="Edit Detail">
                                            <Edit2 :size="16" />
                                        </button>
                                        <button @click="confirmDelete(kategori.id)" class="p-2 text-red-600 bg-white hover:bg-red-50 hover:text-red-700 rounded-lg transition-all border border-gray-200 hover:border-red-200 shadow-sm" title="Hapus Permanen">
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="kategoris.data.length === 0">
                                <td colspan="4" class="px-6 py-24 text-center text-gray-500 bg-gray-50/30">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-gray-100 p-4 rounded-full mb-3 animate-pulse">
                                            <Filter :size="40" class="text-gray-300" />
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">Tidak ada data ditemukan</h3>
                                        <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                                            Coba ubah kata kunci pencarian atau atur ulang filter status Anda.
                                        </p>
                                        <button @click="handleRefresh" class="mt-4 px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                                            Reset Semua Filter
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
                    <div class="text-sm text-gray-500">
                        Menampilkan <span class="font-medium text-gray-900">{{ kategoris.from || 0 }}</span> - <span class="font-medium text-gray-900">{{ kategoris.to || 0 }}</span> dari <span class="font-medium text-gray-900">{{ kategoris.total }}</span> data
                    </div>
                    <div class="flex items-center gap-4">
                        <select v-model="perPage" class="text-xs border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pl-2 pr-7 cursor-pointer">
                            <option :value="10">10 / hal</option>
                            <option :value="25">25 / hal</option>
                            <option :value="50">50 / hal</option>
                        </select>
                        <Pagination :links="kategoris.links" />
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">
                        {{ isEditing ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><X :size="24"/></button>
                </div>

                <div class="space-y-5">
                    <!-- Nama & Warna -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-8">
                            <InputLabel for="nama" value="Nama Kategori" />
                            <TextInput id="nama" v-model="form.nama" type="text" class="mt-1 block w-full" placeholder="Contoh: Web Development" />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div class="md:col-span-4">
                            <InputLabel for="warna" value="Warna Label" />
                            <div class="flex gap-2 mt-1">
                                <div class="relative overflow-hidden w-12 h-10 rounded-lg border border-gray-300 shadow-sm">
                                    <input type="color" v-model="form.warna" class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer p-0 border-0">
                                </div>
                                <TextInput id="warna" v-model="form.warna" type="text" class="block w-full uppercase font-mono text-sm" />
                            </div>
                            <InputError :message="form.errors.warna" class="mt-2" />
                        </div>
                    </div>

                    <!-- Icon & Urutan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Ikon Kategori (Lucide)" />
                            <div class="grid grid-cols-4 gap-2 mt-1 p-3 border border-gray-200 rounded-lg bg-gray-50 max-h-40 overflow-y-auto mb-3">
                                <button 
                                    v-for="icon in availableIcons" 
                                    :key="icon.value"
                                    type="button"
                                    @click="form.ikon = icon.value"
                                    class="flex flex-col items-center justify-center p-2 rounded-lg transition-all duration-200 gap-1"
                                    :class="form.ikon === icon.value ? 'bg-indigo-100 text-indigo-700 ring-2 ring-indigo-500 ring-offset-1' : 'bg-white text-gray-500 hover:bg-gray-100 border border-gray-200'"
                                    :title="icon.label"
                                >
                                    <component :is="getIconComponent(icon.value)" :size="20" />
                                </button>
                            </div>
                            
                            <!-- Custom Icon Input -->
                            <div class="relative">
                                <TextInput 
                                    v-model="form.ikon" 
                                    type="text" 
                                    class="block w-full text-xs font-mono" 
                                    placeholder="Nama Icon Lucide (cth: Activity, Zap, Box)..." 
                                />
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <component :is="getIconComponent(form.ikon)" :size="16" class="text-gray-400" />
                                </div>
                            </div>
                             <p class="text-[10px] text-gray-500 mt-1.5">Gunakan nama komponen Lucide (PascalCase).</p>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="urutan" value="Urutan Prioritas" />
                                <TextInput id="urutan" v-model="form.urutan" type="number" class="mt-1 block w-full" placeholder="Otomatis (Urutan Terakhir)" />
                                <p class="text-[11px] text-gray-500 mt-1">Kosongkan untuk otomatis menaruh di urutan paling akhir.</p>
                            </div>

                            <div class="pt-1">
                                <InputLabel value="Status Kategori" class="mb-1.5" />
                                <label class="flex items-center cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" v-model="form.status_aktif" class="sr-only">
                                        <div class="block bg-gray-200 w-12 h-7 rounded-full transition-colors group-hover:bg-gray-300" :class="{ '!bg-green-200': form.status_aktif }"></div>
                                        <div class="dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition-transform duration-200 shadow-sm" :class="form.status_aktif ? 'translate-x-5' : ''"></div>
                                    </div>
                                    <div class="ml-3 text-sm font-medium transition-colors" :class="form.status_aktif ? 'text-green-600' : 'text-gray-500'">
                                        {{ form.status_aktif ? 'Aktif (Dapat Digunakan)' : 'Nonaktif (Disembunyikan)' }}
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <InputLabel for="deskripsi" value="Deskripsi Singkat" />
                        <textarea 
                            id="deskripsi"
                            v-model="form.deskripsi" 
                            rows="3" 
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm" 
                            placeholder="Jelaskan secara singkat tentang kategori proyek ini..."
                        ></textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <SecondaryButton @click="closeModal"> Batal </SecondaryButton>
                    <PrimaryButton @click="submitForm" :disabled="form.processing" class="shadow-md px-6">
                        {{ isEditing ? 'Simpan Perubahan' : 'Simpan Kategori' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <ConfirmationModal 
            :show="showDeleteModal" 
            :title="itemToDelete ? 'Hapus Kategori?' : 'Hapus ' + selectedIds.length + ' Data Terpilih?'"
            :content="itemToDelete ? 'Apakah Anda yakin? Data yang dihapus tidak dapat dikembalikan.' : 'Tindakan ini akan menghapus semua data yang dipilih.'"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />

    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom scrollbar untuk pemilihan icon */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: #f1f1f1; 
}
::-webkit-scrollbar-thumb {
  background: #cbd5e1; 
  border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; 
}
</style>