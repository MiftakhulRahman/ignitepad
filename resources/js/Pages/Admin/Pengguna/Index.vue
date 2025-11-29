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
    Search, Plus, Edit2, Trash2, Filter, Download, RefreshCw, Eye,
    ChevronDown, Check, X, FileText, Sheet, User, Users, Shield, GraduationCap
} = LucideIcons;

const props = defineProps({
    users: Object,
    filters: Object,
    roles: Array,
    prodis: Array,
});

// --- STATES ---
const activeTab = ref(props.filters.tab || 'mahasiswa');
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const prodiFilter = ref(props.filters.prodi || '');
const perPage = ref(props.filters.per_page || 10);

const selectedIds = ref([]);
const selectAll = ref(false);
const toastRef = ref(null);

const showModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const itemToDelete = ref(null);
const selectedUser = ref(null);

const form = useForm({
    id: null,
    nama: '',
    email: '',
    password: '',
    peran: '',
    prodi_id: '',
    nomor_induk: '',
    status_aktif: true,
    bio: '',
    website_url: '',
});

const tabs = [
    { id: 'superadmin', label: 'Super Admin', icon: Shield },
    { id: 'dosen', label: 'Dosen', icon: Users },
    { id: 'mahasiswa', label: 'Mahasiswa', icon: User },
];

// --- WATCHERS ---
watch([search, statusFilter, prodiFilter, perPage], debounce(() => {
    fetchData();
}, 300));

watch(selectAll, (value) => {
    selectedIds.value = value ? props.users.data.map(item => item.id) : [];
});

// --- FUNCTIONS ---
const switchTab = (tabId) => {
    activeTab.value = tabId;
    search.value = '';
    prodiFilter.value = '';
    selectedIds.value = [];
    selectAll.value = false;
    fetchData();
};

const fetchData = () => {
    router.get(route('pengguna.index'), {
        tab: activeTab.value,
        search: search.value,
        status: statusFilter.value,
        prodi: prodiFilter.value,
        per_page: perPage.value
    }, { preserveState: true, replace: true, preserveScroll: true });
};

const handleRefresh = () => {
    search.value = '';
    statusFilter.value = '';
    prodiFilter.value = '';
    fetchData();
    if (toastRef.value) toastRef.value.fire('Data berhasil diperbarui', 'success');
};

const resetFilter = () => {
    statusFilter.value = '';
    prodiFilter.value = '';
};

const exportData = (type) => {
    const url = route('pengguna.export') + `?type=${type}&tab=${activeTab.value}`;
    window.location.href = url;
};

const toggleStatus = (item) => {
    router.patch(route('pengguna.toggle', item.id), {}, { preserveScroll: true });
};

// --- MODAL HANDLERS ---
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    const currentRole = props.roles.find(r => r.slug === activeTab.value);
    if (currentRole) form.peran = currentRole.id;
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    form.id = item.id;
    form.nama = item.nama;
    form.email = item.email;
    form.password = '';
    form.status_aktif = Boolean(item.status_aktif);
    form.peran = item.perans.length > 0 ? item.perans[0].id : '';
    form.prodi_id = item.prodi_id || '';
    form.nomor_induk = item.nim || item.nidn || '';
    showModal.value = true;
};

const openDetailModal = (item) => {
    selectedUser.value = item;
    showDetailModal.value = true;
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
    const action = isEditing.value ? route('pengguna.update', form.id) : route('pengguna.store');
    const method = isEditing.value ? 'put' : 'post';
    form[method](action, { onSuccess: () => closeModal() });
};

const executeDelete = () => {
    if (itemToDelete.value) {
        router.delete(route('pengguna.destroy', itemToDelete.value), {
            onSuccess: () => { showDeleteModal.value = false; itemToDelete.value = null; }
        });
    } else {
        router.post(route('pengguna.bulk-delete'), { ids: selectedIds.value }, {
            onSuccess: () => { showDeleteModal.value = false; selectedIds.value = []; selectAll.value = false; }
        });
    }
};

const closeModal = () => { showModal.value = false; showDetailModal.value = false; form.reset(); };

// --- HELPERS ---
const isFilterActive = computed(() => statusFilter.value !== '' || prodiFilter.value !== '');
const needsAcademicData = computed(() => {
    const selectedRole = props.roles.find(r => r.id === form.peran);
    return selectedRole && (selectedRole.slug === 'mahasiswa' || selectedRole.slug === 'dosen');
});
const labelNomorInduk = computed(() => {
    if (activeTab.value === 'mahasiswa') return 'NIM';
    if (activeTab.value === 'dosen') return 'NIDN';
    return 'ID';
});
</script>

<template>
    <Head title="Kelola Pengguna" />
    <Toast ref="toastRef" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <Breadcrumb :items="[{ label: 'Pengguna' }, { label: tabs.find(t => t.id === activeTab).label }]" />
                    <h2 class="text-[28px] leading-9 font-normal text-slate-900">Manajemen Pengguna</h2>
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
                        <span>Tambah {{tabs.find(t => t.id === activeTab).label}}</span>
                    </button>
                </div>
            </div>

            <div class="border-b border-slate-200">
                <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                    <button v-for="tab in tabs" :key="tab.id" @click="switchTab(tab.id)"
                        class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 whitespace-nowrap"
                        :class="activeTab === tab.id
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
                        <div class="relative w-full sm:w-72 group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Search :size="18" class="text-slate-400 group-focus-within:text-indigo-600 transition-colors" />
                            </div>
                            <input v-model="search" type="text"
                                :placeholder="`Cari ${labelNomorInduk}, nama...`"
                                class="pl-11 pr-10 block w-full py-2.5 bg-slate-100 border-none rounded-full text-sm focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-slate-500" />
                            <button v-if="search" @click="search = ''"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
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

                                        <div v-if="activeTab !== 'superadmin'" class="mb-4">
                                            <p class="text-xs text-slate-400 mb-2">Program Studi</p>
                                            <div class="max-h-40 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                                <button v-for="prodi in prodis" :key="prodi.id"
                                                    @click="prodiFilter = prodiFilter === prodi.id ? '' : prodi.id"
                                                    class="w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg text-left transition-colors"
                                                    :class="prodiFilter === prodi.id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'hover:bg-slate-50 text-slate-700'">
                                                    <span class="truncate">{{ prodi.nama }}</span>
                                                    <Check v-if="prodiFilter === prodi.id" :size="14" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <p class="text-xs text-slate-400 mb-2">Status Akun</p>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button @click="statusFilter = statusFilter === '1' ? '' : '1'"
                                                    class="px-3 py-2 text-xs border rounded-lg text-center transition-colors flex flex-col items-center justify-center gap-1"
                                                    :class="statusFilter === '1' ? 'border-indigo-200 bg-indigo-50 text-indigo-700 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                                    <Check :size="14" v-if="statusFilter === '1'" />
                                                    Aktif
                                                </button>
                                                <button @click="statusFilter = '0'"
                                                    class="px-3 py-2 text-xs border rounded-lg text-center transition-colors flex flex-col items-center justify-center gap-1"
                                                    :class="statusFilter === '0' ? 'border-indigo-200 bg-indigo-50 text-indigo-700 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                                    <X :size="14" v-if="statusFilter === '0'" />
                                                    Nonaktif
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <button @click="handleRefresh"
                            class="p-2.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-colors" title="Refresh Data">
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
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Identitas</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Detail</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="user in users.data" :key="user.id"
                                class="hover:bg-slate-50 transition-colors group">
                                <td class="pl-6 pr-3 py-4 align-middle">
                                    <input type="checkbox" :value="user.id" v-model="selectedIds" class="rounded-[4px] border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500/50 cursor-pointer w-4 h-4">
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img :src="user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&background=random`"
                                            class="h-12 w-12 rounded-full object-cover border-2 border-white shadow-sm" alt="">
                                        <div>
                                            <div class="text-base font-semibold text-slate-900">{{ user.nama }}</div>
                                            <div class="text-sm text-slate-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-start gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border capitalize"
                                                :class="activeTab === 'superadmin' ? 'bg-rose-50 text-rose-700 border-rose-100' : (activeTab === 'dosen' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100')">
                                                {{ user.perans[0]?.nama }}
                                            </span>
                                            <span class="text-xs font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                                <span v-if="activeTab === 'superadmin'">ID: {{ user.id }}</span>
                                                <span v-else-if="activeTab === 'dosen'">{{ user.nidn || '-' }}</span>
                                                <span v-else>{{ user.nim || '-' }}</span>
                                            </span>
                                        </div>

                                        <div v-if="user.prodi" class="flex items-center gap-1.5 text-xs text-slate-600">
                                            <GraduationCap :size="14" class="text-slate-400" /> {{ user.prodi.nama }}
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button @click="toggleStatus(user)"
                                        class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                                        :class="user.status_aktif ? 'bg-emerald-500' : 'bg-slate-200'">
                                        <span class="pointer-events-none relative inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out flex items-center justify-center"
                                            :class="user.status_aktif ? 'translate-x-5' : 'translate-x-0'">
                                            <Check v-if="user.status_aktif" :size="12" class="text-emerald-600 stroke-[4]" />
                                            <X v-else :size="12" class="text-slate-400 stroke-[4]" />
                                        </span>
                                    </button>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openDetailModal(user)"
                                            class="w-9 h-9 flex items-center justify-center text-sky-600 bg-sky-50 hover:bg-sky-100 rounded-full transition-colors"
                                            title="Detail">
                                            <Eye :size="18" />
                                        </button>
                                        <button @click="openEditModal(user)"
                                            class="w-9 h-9 flex items-center justify-center text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-full transition-colors">
                                            <Edit2 :size="18" />
                                        </button>
                                        <button @click="confirmDelete(user.id)"
                                            class="w-9 h-9 flex items-center justify-center text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-full transition-colors">
                                            <Trash2 :size="18" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-slate-50 p-6 rounded-full mb-4">
                                            <component :is="tabs.find(t => t.id === activeTab).icon" :size="48" class="text-slate-300" />
                                        </div>
                                        <h3 class="text-lg font-medium text-slate-900">Belum ada data {{tabs.find(t => t.id === activeTab).label }}</h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
                    <div class="text-sm text-slate-500">
                         Menampilkan <span class="font-bold text-slate-700">{{ users.from || 0 }}</span> - <span class="font-bold text-slate-700">{{ users.to || 0 }}</span> dari <span class="font-bold text-slate-700">{{ users.total }}</span> data
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
                        <Pagination :links="users.links" />
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showDetailModal" @close="showDetailModal = false">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-normal text-slate-900">Detail Pengguna</h2>
                    <button @click="showDetailModal = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-600"><X :size="24"/></button>
                </div>

                <div v-if="selectedUser" class="space-y-8">
                    <div class="flex flex-col items-center text-center">
                         <img :src="selectedUser.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(selectedUser.nama)}&background=random`"
                            class="h-24 w-24 rounded-full object-cover border-4 border-white shadow-lg mb-4">
                         <h3 class="text-2xl font-bold text-slate-900">{{ selectedUser.nama }}</h3>
                         <p class="text-slate-500 mb-3">{{ selectedUser.email }}</p>
                         <div class="flex gap-2">
                             <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                                 {{ selectedUser.perans[0]?.nama }}
                             </span>
                             <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider" 
                                :class="selectedUser.status_aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                 {{ selectedUser.status_aktif ? 'Aktif' : 'Nonaktif' }}
                             </span>
                         </div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
                        <div class="grid grid-cols-2 gap-6 text-sm">
                            <div>
                                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Identitas ({{ labelNomorInduk }})</p>
                                <p class="font-mono text-slate-900 text-base">{{ selectedUser.nim || selectedUser.nidn || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Program Studi</p>
                                <p class="text-slate-900 text-base">{{ selectedUser.prodi?.nama || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Bergabung Sejak</p>
                                <p class="text-slate-900">{{ new Date(selectedUser.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                            </div>
                            <div class="col-span-2 pt-2">
                                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Bio Singkat</p>
                                <p class="text-slate-700 italic leading-relaxed">{{ selectedUser.bio || 'Pengguna ini belum menambahkan bio.' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button @click="showDetailModal = false" class="px-6 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-full font-medium transition-colors">Tutup</button>
                    </div>
                </div>
            </div>
        </Modal>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-normal text-slate-900">{{ isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h2>
                    <button @click="closeModal" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-600"><X :size="24"/></button>
                </div>

                <div class="space-y-6">
                    <div>
                        <InputLabel for="nama" value="Nama Lengkap" class="mb-2" />
                        <TextInput id="nama" v-model="form.nama" type="text" class="block w-full rounded-xl bg-slate-50 border-slate-200 focus:bg-white" placeholder="John Doe" />
                        <InputError :message="form.errors.nama" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="email" value="Alamat Email" class="mb-2" />
                            <TextInput id="email" v-model="form.email" type="email" class="block w-full rounded-xl bg-slate-50 border-slate-200 focus:bg-white" placeholder="john@example.com" />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="password" :value="isEditing ? 'Password (Opsional)' : 'Password'" class="mb-2" />
                            <TextInput id="password" v-model="form.password" type="password" class="block w-full rounded-xl bg-slate-50 border-slate-200 focus:bg-white" placeholder="*******" />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="peran" value="Peran Pengguna" class="mb-2" />
                        <div class="grid grid-cols-3 gap-4">
                            <button v-for="role in roles" :key="role.id" type="button" @click="form.peran = role.id"
                                class="flex flex-col items-center justify-center p-4 border rounded-xl transition-all duration-200"
                                :class="form.peran === role.id ? 'bg-indigo-50 border-indigo-500 text-indigo-700 ring-1 ring-indigo-500' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'">
                                <Shield v-if="role.slug === 'superadmin'" :size="24" class="mb-2" />
                                <Users v-if="role.slug === 'dosen'" :size="24" class="mb-2" />
                                <User v-if="role.slug === 'mahasiswa'" :size="24" class="mb-2" />
                                <span class="text-sm font-semibold capitalize">{{ role.nama }}</span>
                            </button>
                        </div>
                        <InputError :message="form.errors.peran" class="mt-2" />
                    </div>

                    <div v-if="needsAcademicData" class="p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
                        <div class="flex items-center gap-2 text-sm font-bold text-slate-800 pb-2 border-b border-slate-200">
                            <GraduationCap :size="18" /> Data Akademik
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="nomor_induk" :value="labelNomorInduk === 'ID' ? 'Nomor Induk' : labelNomorInduk" class="mb-2" />
                                <TextInput id="nomor_induk" v-model="form.nomor_induk" type="number" class="block w-full rounded-xl bg-white border-slate-200 focus:border-indigo-500" />
                                <InputError :message="form.errors.nomor_induk" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="prodi" value="Program Studi" class="mb-2" />
                                <select id="prodi" v-model="form.prodi_id" class="block w-full rounded-xl bg-white border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4 text-sm">
                                    <option value="">Pilih Prodi</option>
                                    <option v-for="prodi in prodis" :key="prodi.id" :value="prodi.id">{{ prodi.nama }}</option>
                                </select>
                                <InputError :message="form.errors.prodi_id" class="mt-2" />
                            </div>
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

        <ConfirmationModal :show="showDeleteModal" :title="'Hapus Pengguna?'"
            :content="'Data yang dihapus tidak bisa dikembalikan.'" @close="showDeleteModal = false"
            @confirm="executeDelete" />
    </AuthenticatedLayout>
</template>