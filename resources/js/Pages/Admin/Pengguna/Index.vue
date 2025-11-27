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
const activeTab = ref(props.filters.tab || 'mahasiswa'); // Default tab
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const prodiFilter = ref(props.filters.prodi || '');
const perPage = ref(props.filters.per_page || 10);

const selectedIds = ref([]);
const selectAll = ref(false);
const toastRef = ref(null);

const showModal = ref(false);
const showDetailModal = ref(false); // Modal Lihat Detail
const showDeleteModal = ref(false);
const isEditing = ref(false);
const itemToDelete = ref(null);
const selectedUser = ref(null); // Data user untuk detail view

const form = useForm({
    id: null,
    nama: '',
    email: '',
    password: '',
    peran: '',
    prodi_id: '',
    nomor_induk: '',
    status_aktif: true,
    bio: '',         // Untuk detail view saja (read only di form ini sementara)
    website_url: '',
});

// --- TABS CONFIG ---
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

const exportData = (type) => {
    // Export mengikuti Tab yang aktif
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

    // Auto-select Role based on Active Tab
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
            <Breadcrumb :items="[{ label: 'Pengguna' }, { label: tabs.find(t => t.id === activeTab).label }]" />

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola data {{tabs.find(t => t.id === activeTab).label}}
                        dalam sistem.</p>
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
                        <Plus :size="16" /> Tambah {{tabs.find(t => t.id === activeTab).label}}
                    </PrimaryButton>
                </div>
            </div>

            <div class="bg-gray-100 rounded-lg p-1.5 inline-flex gap-1">
                <button v-for="tab in tabs" :key="tab.id" @click="switchTab(tab.id)"
                    class="group inline-flex items-center justify-center px-4 py-2.5 rounded-md font-medium text-sm transition-all duration-200"
                    :class="activeTab === tab.id
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'">
                    <component :is="tab.icon" class="mr-2 h-4 w-4"
                        :class="activeTab === tab.id ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-700'" />
                    {{ tab.label }}
                </button>
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
                            <TextInput v-model="search" type="text"
                                :placeholder="`Cari ${labelNomorInduk}, nama, email...`"
                                class="pl-9 pr-8 block w-full text-sm rounded-lg" />
                            <button v-if="search" @click="search = ''"
                                class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer">
                                <X :size="14" class="bg-gray-200 rounded-full p-0.5 w-5 h-5" />
                            </button>
                        </div>

                        <div class="relative">
                            <Dropdown align="left" width="64">
                                <template #trigger>
                                    <button
                                        class="flex items-center gap-2 px-3 py-2.5 text-sm font-medium border rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                        :class="isFilterActive ? 'text-indigo-600 bg-indigo-50 border-indigo-200' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50'">
                                        <Filter :size="16"
                                            :class="isFilterActive ? 'text-indigo-600' : 'text-gray-500'" />
                                        <span>Filter</span>
                                        <ChevronDown :size="14" class="text-gray-400" />
                                    </button>
                                </template>
                                <template #content>
                                    <div class="p-3 w-64">
                                        <div v-if="activeTab !== 'superadmin'" class="mb-4">
                                            <p class="text-xs text-gray-400 mb-1.5 px-1">Program Studi</p>
                                            <div class="space-y-1 max-h-32 overflow-y-auto">
                                                <button v-for="prodi in prodis" :key="prodi.id"
                                                    @click="prodiFilter = prodiFilter === prodi.id ? '' : prodi.id"
                                                    class="w-full flex items-center justify-between px-2 py-1.5 text-sm rounded-md hover:bg-gray-100"
                                                    :class="prodiFilter === prodi.id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700'">
                                                    <span>{{ prodi.kode }}</span>
                                                    <Check v-if="prodiFilter === prodi.id" :size="14" />
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <p class="text-xs text-gray-400 mb-1.5 px-1">Status</p>
                                            <div class="flex gap-2">
                                                <button @click="statusFilter = statusFilter === '1' ? '' : '1'"
                                                    class="flex-1 py-1.5 text-xs border rounded text-center hover:bg-gray-50"
                                                    :class="statusFilter === '1' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600'">Aktif</button>
                                                <button @click="statusFilter = '0'"
                                                    class="flex-1 py-1.5 text-xs border rounded text-center hover:bg-gray-50"
                                                    :class="statusFilter === '0' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600'">Nonaktif</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Dropdown>
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
                                    Identitas
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Detail</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id"
                                class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap align-middle"><input type="checkbox"
                                        :value="user.id" v-model="selectedIds"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img :src="user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&background=random`"
                                            class="h-10 w-10 rounded-full object-cover border border-gray-200" alt="">
                                        <div>
                                            <div class="text-sm font-bold text-gray-900">{{ user.nama }}</div>
                                            <div class="text-xs text-gray-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-start gap-1.5">
                                        <div
                                            class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-200">
                                            <span v-if="activeTab === 'superadmin'">ID: {{ user.id }}</span>
                                            <span v-else-if="activeTab === 'dosen'">NIDN: {{ user.nidn || '-' }}</span>
                                            <span v-else>NIM: {{ user.nim || '-' }}</span>
                                        </div>

                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border capitalize"
                                            :class="activeTab === 'superadmin' ? 'bg-red-50 text-red-700 border-red-100' : (activeTab === 'dosen' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100')">
                                            {{ user.perans[0]?.nama }}
                                        </span>

                                        <div v-if="user.prodi" class="flex items-center gap-1 text-xs text-gray-600">
                                            <GraduationCap :size="12" /> {{ user.prodi.nama }}
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button @click="toggleStatus(user)"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                        :class="user.status_aktif ? 'bg-green-500' : 'bg-gray-200'">
                                        <span
                                            class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out flex items-center justify-center"
                                            :class="user.status_aktif ? 'translate-x-5' : 'translate-x-0'">
                                            <Check v-if="user.status_aktif" :size="10"
                                                class="text-green-600 stroke-[3]" />
                                            <X v-else :size="10" class="text-gray-400 stroke-[3]" />
                                        </span>
                                    </button>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openDetailModal(user)"
                                            class="p-2 text-blue-600 bg-white hover:bg-blue-50 border border-gray-200 rounded-lg transition-all shadow-sm"
                                            title="Lihat Detail">
                                            <Eye :size="16" />
                                        </button>
                                        <button @click="openEditModal(user)"
                                            class="p-2 text-indigo-600 bg-white hover:bg-indigo-50 border border-gray-200 rounded-lg transition-all shadow-sm">
                                            <Edit2 :size="16" />
                                        </button>
                                        <button @click="confirmDelete(user.id)"
                                            class="p-2 text-red-600 bg-white hover:bg-red-50 border border-gray-200 rounded-lg transition-all shadow-sm">
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-24 text-center text-gray-500 bg-gray-50/30">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-gray-100 p-4 rounded-full mb-3">
                                            <component :is="tabs.find(t => t.id === activeTab).icon" :size="32"
                                                class="text-gray-300" />
                                        </div>
                                        <p class="text-base font-medium text-gray-900">Belum ada data {{tabs.find(t =>
                                            t.id ===
                                            activeTab).label }}</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
                    <div class="text-sm text-gray-500">Menampilkan {{ users.from || 0 }} - {{ users.to || 0 }} dari {{
                        users.total }}
                        data</div>
                    <div class="flex items-center gap-4">
                        <select v-model="perPage"
                            class="text-xs border-gray-300 rounded-lg focus:ring-indigo-500 py-1.5 pl-2 pr-7 cursor-pointer">
                            <option :value="10">10 / hal</option>
                            <option :value="25">25 / hal</option>
                            <option :value="50">50 / hal</option>
                        </select>
                        <Pagination :links="users.links" />
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showDetailModal" @close="showDetailModal = false">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Detail Pengguna</h2>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600">
                        <X :size="24" />
                    </button>
                </div>

                <div v-if="selectedUser" class="space-y-6">
                    <div class="flex items-center gap-4 pb-6 border-b border-gray-100">
                        <img :src="selectedUser.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(selectedUser.nama)}&background=random`"
                            class="h-16 w-16 rounded-full object-cover border-2 border-white shadow-md">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ selectedUser.nama }}</h3>
                            <p class="text-gray-500">{{ selectedUser.email }}</p>
                            <div class="flex gap-2 mt-2">
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium border bg-indigo-50 text-indigo-700 border-indigo-100 capitalize">{{
                                    selectedUser.perans[0]?.nama }}</span>
                                <span v-if="selectedUser.status_aktif"
                                    class="px-3 py-1 rounded-full text-xs font-medium border bg-green-50 text-green-700 border-green-100">Aktif</span>
                                <span v-else
                                    class="px-3 py-1 rounded-full text-xs font-medium border bg-gray-50 text-gray-700 border-gray-100">Nonaktif</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 text-xs uppercase tracking-wide font-semibold mb-1">Identitas ({{
                                labelNomorInduk }})</p>
                            <p class="font-mono text-gray-900">{{ selectedUser.nim || selectedUser.nidn || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase tracking-wide font-semibold mb-1">Program Studi
                            </p>
                            <p class="text-gray-900">{{ selectedUser.prodi?.nama || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase tracking-wide font-semibold mb-1">Bergabung Sejak
                            </p>
                            <p class="text-gray-900">{{ new Date(selectedUser.created_at).toLocaleDateString('id-ID', {
                                year:
                                'numeric', month: 'long', day: 'numeric' }) }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-gray-500 text-xs uppercase tracking-wide font-semibold mb-1">Bio</p>
                            <p class="text-gray-900 italic bg-gray-50 p-3 rounded-lg border border-gray-100">{{
                                selectedUser.bio
                                || 'Belum ada bio.' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="showDetailModal = false">Tutup</SecondaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">{{ isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru'
                        }}</h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <X :size="24" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="nama" value="Nama Lengkap" />
                            <TextInput id="nama" v-model="form.nama" type="text" class="mt-1 block w-full"
                                placeholder="John Doe" />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="email" value="Alamat Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                                placeholder="john@example.com" />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="password" :value="isEditing ? 'Password (Opsional)' : 'Password'" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                                placeholder="*******" />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="peran" value="Peran Pengguna" />
                        <div class="grid grid-cols-3 gap-3 mt-1">
                            <button v-for="role in roles" :key="role.id" type="button" @click="form.peran = role.id"
                                class="flex flex-col items-center justify-center p-3 border rounded-lg transition-all"
                                :class="form.peran === role.id ? 'bg-indigo-50 border-indigo-500 text-indigo-700 ring-1 ring-indigo-500' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'">
                                <Shield v-if="role.slug === 'superadmin'" :size="20" class="mb-1" />
                                <Users v-if="role.slug === 'dosen'" :size="20" class="mb-1" />
                                <User v-if="role.slug === 'mahasiswa'" :size="20" class="mb-1" />
                                <span class="text-xs font-semibold capitalize">{{ role.nama }}</span>
                            </button>
                        </div>
                        <InputError :message="form.errors.peran" class="mt-2" />
                    </div>

                    <div v-if="needsAcademicData" class="p-4 bg-gray-50 rounded-lg border border-gray-200 space-y-4">
                        <div
                            class="flex items-center gap-2 text-sm font-semibold text-gray-700 pb-2 border-b border-gray-200">
                            <GraduationCap :size="16" /> Data Akademik
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="nomor_induk"
                                    :value="labelNomorInduk === 'ID' ? 'Nomor Induk' : labelNomorInduk" />
                                <TextInput id="nomor_induk" v-model="form.nomor_induk" type="number"
                                    class="mt-1 block w-full" />
                                <InputError :message="form.errors.nomor_induk" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="prodi" value="Program Studi" />
                                <select id="prodi" v-model="form.prodi_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 rounded-lg shadow-sm">
                                    <option value="">Pilih Prodi</option>
                                    <option v-for="prodi in prodis" :key="prodi.id" :value="prodi.id">{{ prodi.nama }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.prodi_id" class="mt-2" />
                            </div>
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

        <ConfirmationModal :show="showDeleteModal" :title="'Hapus Pengguna?'"
            :content="'Data yang dihapus tidak bisa dikembalikan.'" @close="showDeleteModal = false"
            @confirm="executeDelete" />
    </AuthenticatedLayout>
</template>