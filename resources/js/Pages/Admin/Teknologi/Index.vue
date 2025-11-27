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
    Search, Plus, Edit2, Trash2, Filter, Download, RefreshCw, 
    ChevronDown, Check, X, FileText, Sheet, Layers, ExternalLink 
} = LucideIcons;

const props = defineProps({
    teknologis: Object,
    jenisOptions: Array, // ['Framework', 'Language', 'Database', ...]
    filters: Object,
});

// --- STATES ---
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const jenisFilter = ref(props.filters.jenis || ''); // Filter Jenis
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
    ikon_url: '',
    kategori_teknologi: 'Framework', // Default
    warna: '#64748b',
    status_aktif: true,
});

// Pilihan statis untuk dropdown form
const kategoriOptions = ['Framework', 'Language', 'Database', 'Tool', 'Library', 'Platform'];

// --- WATCHERS ---
watch([search, statusFilter, jenisFilter, perPage], debounce(() => {
    refreshTable();
}, 300));

watch(selectAll, (value) => {
    selectedIds.value = value ? props.teknologis.data.map(item => item.id) : [];
});

// --- FUNCTIONS ---
const refreshTable = () => {
    router.get(route('teknologi.index'), { 
        search: search.value, 
        status: statusFilter.value,
        jenis: jenisFilter.value,
        per_page: perPage.value 
    }, { preserveState: true, replace: true, preserveScroll: true });
};

const handleRefresh = () => {
    resetFilter();
    selectedIds.value = [];
    selectAll.value = false;
    refreshTable();
    if(toastRef.value) toastRef.value.fire('Data berhasil diperbarui', 'success');
};

const resetFilter = () => {
    search.value = '';
    statusFilter.value = '';
    jenisFilter.value = '';
};

const toggleStatus = (item) => {
    router.patch(route('teknologi.toggle', item.id), {}, { preserveScroll: true });
};

const exportData = (type) => {
    const url = route('teknologi.export') + `?type=${type}`;
    window.location.href = url;
};

// --- MODAL HANDLERS ---
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    form.kategori_teknologi = 'Framework';
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    form.id = item.id;
    form.nama = item.nama;
    form.ikon_url = item.ikon_url;
    form.kategori_teknologi = item.kategori_teknologi;
    form.warna = item.warna;
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
    const action = isEditing.value ? route('teknologi.update', form.id) : route('teknologi.store');
    const method = isEditing.value ? 'put' : 'post';
    form[method](action, { onSuccess: () => closeModal() });
};

const executeDelete = () => {
    if (itemToDelete.value) {
        router.delete(route('teknologi.destroy', itemToDelete.value), {
            onSuccess: () => { showDeleteModal.value = false; itemToDelete.value = null; }
        });
    } else {
        router.post(route('teknologi.bulk-delete'), { ids: selectedIds.value }, {
            onSuccess: () => { showDeleteModal.value = false; selectedIds.value = []; selectAll.value = false; }
        });
    }
};

const closeModal = () => { showModal.value = false; form.reset(); };

// --- HELPERS ---
const isValidUrl = computed(() => {
    try { return Boolean(new URL(form.ikon_url)); } catch(e){ return false; }
});

const isFilterActive = computed(() => {
    return statusFilter.value !== '' || jenisFilter.value !== '';
});
</script>

<template>
    <Head title="Kelola Teknologi" />
    <Toast ref="toastRef" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <Breadcrumb :items="[{ label: 'Teknologi / Stack' }]" />

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Teknologi & Tools</h2>
                    <p class="text-sm text-gray-500 mt-1">Daftar bahasa pemrograman, framework, dan tools.</p>
                </div>
                <div class="flex gap-2">
                    <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
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
                        
                        <div class="relative w-full sm:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><Search :size="16" class="text-gray-400" /></div>
                            <TextInput v-model="search" type="text" placeholder="Cari teknologi..." class="pl-9 pr-8 block w-full text-sm rounded-lg" />
                            <button v-if="search" @click="search=''" class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"><X :size="14" class="bg-gray-200 rounded-full p-0.5 w-5 h-5" /></button>
                        </div>

                        <div class="relative">
                            <Dropdown align="left" width="64">
                                <template #trigger>
                                    <button class="flex items-center gap-2 px-3 py-2.5 text-sm font-medium border rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                        :class="isFilterActive ? 'text-indigo-600 bg-indigo-50 border-indigo-200' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50'"
                                    >
                                        <Filter :size="16" :class="isFilterActive ? 'text-indigo-600' : 'text-gray-500'" />
                                        <span>{{ isFilterActive ? 'Terfilter' : 'Filter' }}</span>
                                        <ChevronDown :size="14" class="text-gray-400" />
                                    </button>
                                </template>
                                <template #content>
                                    <div class="p-3 w-64 max-h-[400px] overflow-y-auto">
                                        <div class="flex items-center justify-between mb-3 px-1">
                                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Filter Data</span>
                                            <button v-if="isFilterActive" @click="resetFilter" class="text-xs text-red-600 hover:underline">Reset</button>
                                        </div>

                                        <div class="mb-4">
                                            <p class="text-xs text-gray-400 mb-1.5 px-1">Status</p>
                                            <div class="space-y-1">
                                                <button @click="statusFilter = statusFilter === '1' ? '' : '1'" 
                                                    class="w-full flex items-center justify-between px-2 py-1.5 text-sm rounded-md hover:bg-gray-100"
                                                    :class="statusFilter === '1' ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700'"
                                                >
                                                    <span>Aktif</span>
                                                    <Check v-if="statusFilter === '1'" :size="14" />
                                                </button>
                                                <button @click="statusFilter = statusFilter === '0' ? '' : '0'" 
                                                    class="w-full flex items-center justify-between px-2 py-1.5 text-sm rounded-md hover:bg-gray-100"
                                                    :class="statusFilter === '0' ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700'"
                                                >
                                                    <span>Nonaktif</span>
                                                    <Check v-if="statusFilter === '0'" :size="14" />
                                                </button>
                                            </div>
                                        </div>

                                        <div>
                                            <p class="text-xs text-gray-400 mb-1.5 px-1">Jenis Teknologi</p>
                                            <div class="space-y-1">
                                                <button v-for="jenis in jenisOptions" :key="jenis"
                                                    @click="jenisFilter = jenisFilter === jenis ? '' : jenis" 
                                                    class="w-full flex items-center justify-between px-2 py-1.5 text-sm rounded-md hover:bg-gray-100"
                                                    :class="jenisFilter === jenis ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700'"
                                                >
                                                    <span>{{ jenis }}</span>
                                                    <Check v-if="jenisFilter === jenis" :size="14" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="border-t border-gray-100 mt-3 pt-2" v-if="isFilterActive">
                                            <button @click="resetFilter" class="w-full flex items-center justify-center gap-2 px-2 py-1.5 text-sm text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
                                                <X :size="14" /> Hapus Semua Filter
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <button @click="handleRefresh" class="p-2.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100" title="Refresh Data"><RefreshCw :size="18" /></button>
                        <div class="h-6 w-px bg-gray-300 mx-1"></div>
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                                    <Download :size="16" /> Export <ChevronDown :size="14" class="text-gray-400" />
                                </button>
                            </template>
                            <template #content>
                                <div class="py-1">
                                    <button @click="exportData('csv')" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><FileText :size="16" class="text-green-600" /> CSV</button>
                                    <button @click="exportData('excel')" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><Sheet :size="16" class="text-green-600" /> Excel</button>
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
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Teknologi</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in teknologis.data" :key="item.id" class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap align-middle"><input type="checkbox" :value="item.id" v-model="selectedIds" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer"></td>
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0 h-10 w-10 bg-white border border-gray-100 rounded-lg p-1 shadow-sm flex items-center justify-center">
                                            <img :src="item.ikon_url" :alt="item.nama" class="h-full w-full object-contain" @error="$event.target.src = 'https://via.placeholder.com/40?text=Error'" />
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900">{{ item.nama }}</div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10px] text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100 font-mono">{{ item.slug }}</span>
                                                <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: item.warna }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-4 py-1.5 text-xs font-medium rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ item.kategori_teknologi }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button @click="toggleStatus(item)" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600" :class="item.status_aktif ? 'bg-green-500' : 'bg-gray-200'">
                                        <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out flex items-center justify-center" :class="item.status_aktif ? 'translate-x-5' : 'translate-x-0'">
                                            <Check v-if="item.status_aktif" :size="10" class="text-green-600 stroke-[3]" />
                                            <X v-else :size="10" class="text-gray-400 stroke-[3]" />
                                        </span>
                                    </button>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal(item)" class="p-2 text-indigo-600 bg-white hover:bg-indigo-50 border border-gray-200 rounded-lg transition-all shadow-sm"><Edit2 :size="16" /></button>
                                        <button @click="confirmDelete(item.id)" class="p-2 text-red-600 bg-white hover:bg-red-50 border border-gray-200 rounded-lg transition-all shadow-sm"><Trash2 :size="16" /></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="teknologis.data.length === 0">
                                <td colspan="5" class="px-6 py-24 text-center text-gray-500 bg-gray-50/30">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-gray-100 p-4 rounded-full mb-3"><Layers :size="32" class="text-gray-300" /></div>
                                        <p class="text-base font-medium text-gray-900">Tidak ada data ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
                    <div class="text-sm text-gray-500">Menampilkan {{ teknologis.from || 0 }} - {{ teknologis.to || 0 }} dari {{ teknologis.total }} data</div>
                    <div class="flex items-center gap-4">
                        <select v-model="perPage" class="text-xs border-gray-300 rounded-lg focus:ring-indigo-500 py-1.5 pl-2 pr-7 cursor-pointer">
                            <option :value="10">10 / hal</option>
                            <option :value="25">25 / hal</option>
                            <option :value="50">50 / hal</option>
                        </select>
                        <Pagination :links="teknologis.links" />
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">{{ isEditing ? 'Edit Teknologi' : 'Tambah Teknologi' }}</h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600"><X :size="24"/></button>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="nama" value="Nama Teknologi" />
                            <TextInput id="nama" v-model="form.nama" type="text" class="mt-1 block w-full" placeholder="Laravel" />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="kategori" value="Jenis / Kategori" />
                            <select id="kategori" v-model="form.kategori_teknologi" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                <option v-for="opt in kategoriOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                            <InputError :message="form.errors.kategori_teknologi" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="ikon_url" value="URL Icon (SVG/PNG)" />
                        <div class="flex gap-3 mt-1">
                            <TextInput id="ikon_url" v-model="form.ikon_url" type="url" class="block w-full font-mono text-xs" placeholder="https://..." />
                            <div class="flex-shrink-0 w-10 h-10 border border-gray-300 rounded-lg bg-gray-50 flex items-center justify-center p-1 overflow-hidden">
                                <img v-if="isValidUrl" :src="form.ikon_url" class="h-full w-full object-contain" />
                                <Layers v-else :size="20" class="text-gray-300" />
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">Gunakan URL dari devicon.dev atau hosting gambar lainnya.</p>
                        <InputError :message="form.errors.ikon_url" class="mt-2" />
                    </div>

                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         <div>
                            <InputLabel for="warna" value="Warna Identitas" />
                            <div class="flex gap-2 mt-1">
                                <input type="color" v-model="form.warna" class="h-10 w-12 rounded border border-gray-300 cursor-pointer p-0.5">
                                <TextInput id="warna" v-model="form.warna" type="text" class="block w-full uppercase" />
                            </div>
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" v-model="form.status_aktif" class="sr-only">
                                    <div class="block bg-gray-200 w-12 h-7 rounded-full transition-colors group-hover:bg-gray-300" :class="{ '!bg-green-200': form.status_aktif }"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition-transform duration-200 shadow-sm" :class="form.status_aktif ? 'translate-x-5' : ''"></div>
                                </div>
                                <div class="ml-3 text-sm font-medium transition-colors" :class="form.status_aktif ? 'text-green-600' : 'text-gray-500'">
                                    {{ form.status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </label>
                        </div>
                     </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <SecondaryButton @click="closeModal"> Batal </SecondaryButton>
                    <PrimaryButton @click="submitForm" :disabled="form.processing" class="shadow-md px-6">{{ isEditing ? 'Simpan' : 'Tambah' }}</PrimaryButton>
                </div>
            </div>
        </Modal>

        <ConfirmationModal :show="showDeleteModal" :title="itemToDelete ? 'Hapus Teknologi?' : 'Hapus Data Terpilih?'" :content="itemToDelete ? 'Yakin hapus teknologi ini?' : 'Yakin hapus semua yang dipilih?'" @close="showDeleteModal = false" @confirm="executeDelete" />
    </AuthenticatedLayout>
</template>