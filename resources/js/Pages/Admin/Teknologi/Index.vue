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
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <Breadcrumb :items="[{ label: 'Teknologi / Stack' }]" />
                    <h2 class="text-[28px] leading-9 font-normal text-slate-900">Teknologi & Tools</h2>
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
                        <span>Tambah Baru</span>
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-[28px] border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row gap-4 justify-between items-center bg-white">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative w-full sm:w-72 group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Search :size="18" class="text-slate-400 group-focus-within:text-indigo-600 transition-colors" />
                            </div>
                            <input 
                                v-model="search" 
                                type="text" 
                                placeholder="Cari teknologi..." 
                                class="pl-11 pr-10 block w-full py-2.5 bg-slate-100 border-none rounded-full text-sm focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-slate-500" 
                            />
                            <button v-if="search" @click="search=''" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                                <X :size="16" class="bg-slate-200 rounded-full p-0.5" />
                            </button>
                        </div>

                        <div class="relative">
                            <Dropdown align="left" width="64">
                                <template #trigger>
                                    <button class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-full transition-colors border border-slate-200 hover:bg-slate-50"
                                        :class="isFilterActive ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'text-slate-700'">
                                        <Filter :size="16" />
                                        <span>Filter</span>
                                        <div v-if="isFilterActive" class="w-2 h-2 rounded-full bg-indigo-600"></div>
                                    </button>
                                </template>
                                <template #content>
                                    <div class="p-4 w-72">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Filter Data</span>
                                            <button v-if="isFilterActive" @click="resetFilter" class="text-xs text-rose-600 hover:underline">Reset</button>
                                        </div>

                                        <div class="mb-4">
                                            <p class="text-xs text-slate-400 mb-2">Status</p>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button @click="statusFilter = statusFilter === '1' ? '' : '1'" 
                                                    class="px-3 py-2 text-xs border rounded-lg text-center flex items-center justify-center gap-1 transition-colors"
                                                    :class="statusFilter === '1' ? 'border-indigo-200 bg-indigo-50 text-indigo-700 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                                    <Check v-if="statusFilter === '1'" :size="14"/> Aktif
                                                </button>
                                                <button @click="statusFilter = statusFilter === '0' ? '' : '0'" 
                                                    class="px-3 py-2 text-xs border rounded-lg text-center flex items-center justify-center gap-1 transition-colors"
                                                    :class="statusFilter === '0' ? 'border-indigo-200 bg-indigo-50 text-indigo-700 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                                    <X v-if="statusFilter === '0'" :size="14"/> Nonaktif
                                                </button>
                                            </div>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-400 mb-2">Jenis</p>
                                            <div class="max-h-40 overflow-y-auto space-y-1 custom-scrollbar pr-1">
                                                <button v-for="jenis in jenisOptions" :key="jenis" 
                                                    @click="jenisFilter = jenisFilter === jenis ? '' : jenis" 
                                                    class="w-full text-left px-3 py-2 rounded-lg text-sm flex items-center justify-between transition-colors" 
                                                    :class="jenisFilter === jenis ? 'bg-indigo-50 text-indigo-700 font-medium' : 'hover:bg-slate-50 text-slate-700'">
                                                    <span>{{ jenis }}</span> <Check v-if="jenisFilter === jenis" :size="14"/>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="handleRefresh" class="p-2.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-colors" title="Refresh">
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
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Teknologi</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in teknologis.data" :key="item.id" class="hover:bg-slate-50 transition-colors group">
                                <td class="pl-6 pr-3 py-4 align-middle">
                                    <input type="checkbox" :value="item.id" v-model="selectedIds" class="rounded-[4px] border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500/50 cursor-pointer w-4 h-4">
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0 h-12 w-12 bg-white border border-slate-200 rounded-2xl p-2 shadow-sm flex items-center justify-center">
                                            <img :src="item.ikon_url" :alt="item.nama" class="h-full w-full object-contain" @error="$event.target.src = 'https://via.placeholder.com/40?text=Error'" />
                                        </div>
                                        <div>
                                            <div class="text-base font-semibold text-slate-900">{{ item.nama }}</div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200">
                                                    <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: item.warna }"></div>
                                                    <span class="text-[11px] font-mono text-slate-600">{{ item.slug }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ item.kategori_teknologi }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button @click="toggleStatus(item)" 
                                        class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2" 
                                        :class="item.status_aktif ? 'bg-emerald-500' : 'bg-slate-200'"
                                    >
                                        <span class="pointer-events-none relative inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out flex items-center justify-center" :class="item.status_aktif ? 'translate-x-5' : 'translate-x-0'">
                                            <Check v-if="item.status_aktif" :size="12" class="text-emerald-600 stroke-[4]" />
                                            <X v-else :size="12" class="text-slate-400 stroke-[4]" />
                                        </span>
                                    </button>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal(item)" class="w-9 h-9 flex items-center justify-center text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-full transition-colors" title="Edit">
                                            <Edit2 :size="18" />
                                        </button>
                                        <button @click="confirmDelete(item.id)" class="w-9 h-9 flex items-center justify-center text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-full transition-colors" title="Hapus">
                                            <Trash2 :size="18" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="teknologis.data.length === 0">
                                <td colspan="5" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-slate-50 p-6 rounded-full mb-4">
                                            <Layers :size="48" class="text-slate-300" />
                                        </div>
                                        <h3 class="text-lg font-medium text-slate-900">Tidak ada data ditemukan</h3>
                                        <p class="text-slate-500 mt-1 max-w-xs mx-auto">Coba ubah kata kunci pencarian atau filter Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
                    <div class="text-sm text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">{{ teknologis.from || 0 }}</span> - <span class="font-bold text-slate-700">{{ teknologis.to || 0 }}</span> dari <span class="font-bold text-slate-700">{{ teknologis.total }}</span> data
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
                        <Pagination :links="teknologis.links" />
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-normal text-slate-900">{{ isEditing ? 'Edit Teknologi' : 'Tambah Teknologi' }}</h2>
                    <button @click="closeModal" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-600"><X :size="24"/></button>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="nama" value="Nama Teknologi" class="mb-2" />
                            <TextInput id="nama" v-model="form.nama" type="text" class="block w-full rounded-xl bg-slate-50 border-slate-200 focus:bg-white" placeholder="Contoh: Laravel" />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="kategori" value="Jenis / Kategori" class="mb-2" />
                            <select id="kategori" v-model="form.kategori_teknologi" class="block w-full rounded-xl bg-slate-50 border-slate-200 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4 text-sm">
                                <option v-for="opt in kategoriOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                            <InputError :message="form.errors.kategori_teknologi" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="ikon_url" value="URL Icon (SVG/PNG)" class="mb-2" />
                        <div class="flex gap-4">
                            <TextInput id="ikon_url" v-model="form.ikon_url" type="url" class="block w-full rounded-xl bg-slate-50 border-slate-200 focus:bg-white font-mono text-sm" placeholder="https://..." />
                            <div class="flex-shrink-0 w-12 h-12 border border-slate-200 rounded-xl bg-white flex items-center justify-center p-2 shadow-sm">
                                <img v-if="isValidUrl" :src="form.ikon_url" class="h-full w-full object-contain" />
                                <Layers v-else :size="24" class="text-slate-300" />
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2 ml-1">Saran: Gunakan URL dari devicon.dev atau simpleicons.org.</p>
                        <InputError :message="form.errors.ikon_url" class="mt-2" />
                    </div>

                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                         <div>
                            <InputLabel for="warna" value="Warna Identitas" class="mb-2" />
                            <div class="flex items-center gap-3 bg-slate-50 p-2 rounded-xl border border-slate-200">
                                <input type="color" v-model="form.warna" class="h-10 w-14 rounded-lg cursor-pointer border-none p-0 bg-transparent">
                                <TextInput id="warna" v-model="form.warna" type="text" class="block w-full border-none bg-transparent focus:ring-0 uppercase font-mono" />
                            </div>
                        </div>
                        <div class="pt-6">
                            <label class="flex items-center cursor-pointer group p-3 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-200">
                                <div class="relative">
                                    <input type="checkbox" v-model="form.status_aktif" class="sr-only">
                                    <div class="block bg-slate-200 w-12 h-7 rounded-full transition-colors group-hover:bg-slate-300" :class="{ '!bg-emerald-200': form.status_aktif }"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition-transform duration-200 shadow-sm" :class="form.status_aktif ? 'translate-x-5' : ''"></div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-slate-900">{{ form.status_aktif ? 'Status Aktif' : 'Status Nonaktif' }}</div>
                                    <div class="text-xs text-slate-500">Teknologi dapat dipilih user</div>
                                </div>
                            </label>
                        </div>
                     </div>
                </div>

                <div class="mt-10 flex justify-end gap-3">
                    <button @click="closeModal" class="px-6 py-2.5 rounded-full text-slate-700 font-medium hover:bg-slate-100 transition-colors">Batal</button>
                    <button @click="submitForm" :disabled="form.processing" class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full font-medium shadow-md hover:shadow-lg transition-all transform active:scale-95">
                        {{ isEditing ? 'Simpan Perubahan' : 'Tambah Teknologi' }}
                    </button>
                </div>
            </div>
        </Modal>

        <ConfirmationModal :show="showDeleteModal" :title="itemToDelete ? 'Hapus Teknologi?' : 'Hapus Data Terpilih?'" :content="itemToDelete ? 'Yakin hapus teknologi ini?' : 'Yakin hapus semua yang dipilih?'" @close="showDeleteModal = false" @confirm="executeDelete" />
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1; 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; 
}
</style>