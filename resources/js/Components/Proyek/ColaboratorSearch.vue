<script setup>
import { ref, watch } from 'vue';
import { Search, X, UserPlus, Trash2, Shield, Users2, Lightbulb } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const props = defineProps({
    proyekId: [String, Number],
    existingKolaborators: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['added', 'removed']);

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const selectedUsers = ref([]);

// Icon mapping for roles
const roleIcons = {
    pembimbing: Shield,
    rekan: Users2,
    kontributor: Lightbulb,
    anggota: UserPlus
};

// Search users with debounce
const searchUsers = debounce(async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;

    try {
        const response = await fetch(route('proyek.search-users', { q: searchQuery.value }));
        const data = await response.json();

        // Filter out already added collaborators
        const existingIds = props.existingKolaborators.map(k => k.user_id);
        searchResults.value = data.filter(user => !existingIds.includes(user.id));
    } catch (error) {
        console.error('Error searching users:', error);
    } finally {
        isSearching.value = false;
    }
}, 300);

watch(searchQuery, () => {
    searchUsers();
});

// Add user to selection
const addUser = (user) => {
    const exists = selectedUsers.value.find(u => u.id === user.id);
    if (!exists) {
        selectedUsers.value.push({
            ...user,
            peran_kolaborator: 'anggota',
            bisa_edit: true,
            bisa_hapus: true
        });
    }
    searchQuery.value = '';
    searchResults.value = [];
};

// Remove from selection
const removeFromSelection = (userId) => {
    selectedUsers.value = selectedUsers.value.filter(u => u.id !== userId);
};

// Save collaborators
const saveKolaborators = () => {
    selectedUsers.value.forEach(user => {
        router.post(route('proyek.kolaborator.store', props.proyekId), {
            user_id: user.id,
            peran_kolaborator: user.peran_kolaborator,
            bisa_edit: user.bisa_edit,
            bisa_hapus: user.bisa_hapus
        }, {
            preserveScroll: true,
            onSuccess: () => {
                emit('added', user);
            }
        });
    });

    selectedUsers.value = [];
};

// Remove existing collaborator
const removeKolaborator = (kolaboratorId) => {
    if (confirm('Apakah Anda yakin ingin menghapus kolaborator ini?')) {
        router.delete(route('proyek.kolaborator.destroy', [props.proyekId, kolaboratorId]), {
            preserveScroll: true,
            onSuccess: () => {
                emit('removed', kolaboratorId);
            }
        });
    }
};

// Get role name in Indonesian
const getRoleName = (role) => {
    const names = {
        pembimbing: 'Pembimbing',
        rekan: 'Rekan',
        kontributor: 'Kontributor',
        anggota: 'Anggota'
    };
    return names[role] || 'Anggota';
};

// Get role badge color
const getRoleBadgeColor = (role) => {
    const colors = {
        pembimbing: 'bg-purple-100 text-purple-700 border-purple-200',
        rekan: 'bg-blue-100 text-blue-700 border-blue-200',
        kontributor: 'bg-green-100 text-green-700 border-green-200',
        anggota: 'bg-slate-100 text-slate-700 border-slate-200'
    };
    return colors[role] || colors.anggota;
};
</script>

<template>
    <div class="space-y-6">

        <!-- Search Box -->
        <div class="relative">
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                <UserPlus :size="16" class="inline mr-2" />
                Cari dan Tambah Kolaborator
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <Search :size="18" class="text-slate-400" />
                </div>
                <input v-model="searchQuery" type="text"
                    class="w-full pl-11 pr-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    placeholder="Cari berdasarkan nama, username, email, NIM, atau NIDN...">
                <div v-if="isSearching" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                    <div class="animate-spin h-5 w-5 border-2 border-indigo-600 border-t-transparent rounded-full">
                    </div>
                </div>
            </div>

            <!-- Search Results Dropdown -->
            <div v-if="searchResults.length > 0"
                class="absolute z-10 w-full mt-2 bg-white border-2 border-slate-200 rounded-xl shadow-xl max-h-80 overflow-y-auto">
                <div v-for="user in searchResults" :key="user.id" @click="addUser(user)"
                    class="flex items-center gap-3 p-4 hover:bg-indigo-50 cursor-pointer transition-colors border-b border-slate-100 last:border-0">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&background=random`"
                        :alt="user.nama" class="w-12 h-12 rounded-full">
                    <div class="flex-1">
                        <div class="font-semibold text-slate-900">{{ user.nama }}</div>
                        <div class="text-sm text-slate-500">@{{ user.username }}</div>
                        <div class="text-xs text-slate-400">{{ user.email }}</div>
                    </div>
                    <div class="text-xs text-slate-500">
                        {{ user.nim || user.nidn || 'Pengguna' }}
                    </div>
                </div>
            </div>

            <p v-if="searchQuery.length > 0 && searchResults.length === 0 && !isSearching"
                class="mt-2 text-sm text-slate-500">
                Tidak ada pengguna ditemukan
            </p>
        </div>

        <!-- Selected Users to Add -->
        <div v-if="selectedUsers.length > 0" class="bg-indigo-50 border-2 border-indigo-200 rounded-xl p-5">
            <h4 class="font-semibold text-slate-900 mb-4">Kolaborator Yang Akan Ditambahkan</h4>
            <div class="space-y-3">
                <div v-for="user in selectedUsers" :key="user.id"
                    class="bg-white rounded-xl p-4 flex items-center gap-4 border border-slate-200">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&background=random`"
                        :alt="user.nama" class="w-12 h-12 rounded-full">

                    <div class="flex-1">
                        <div class="font-semibold text-slate-900">{{ user.nama }}</div>
                        <div class="text-sm text-slate-500">{{ user.email }}</div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Role Selector -->
                        <select v-model="user.peran_kolaborator"
                            class="px-3 py-2 border border-slate-300 rounded-lg text-sm font-medium focus:ring-2 focus:ring-indigo-500">
                            <option value="pembimbing">Pembimbing</option>
                            <option value="rekan">Rekan</option>
                            <option value="kontributor">Kontributor</option>
                            <option value="anggota">Anggota</option>
                        </select>



                        <!-- Remove Button -->
                        <button @click="removeFromSelection(user.id)"
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                            <X :size="18" />
                        </button>
                    </div>
                </div>
            </div>

            <button @click="saveKolaborators"
                class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors">
                Simpan Kolaborator
            </button>
        </div>

        <!-- Existing Collaborators -->
        <div v-if="existingKolaborators.length > 0">
            <h4 class="font-semibold text-slate-900 mb-4">Kolaborator Saat Ini</h4>
            <div class="space-y-3">
                <div v-for="kolaborator in existingKolaborators" :key="kolaborator.id"
                    class="bg-white rounded-xl p-4 flex items-center gap-4 border-2 border-slate-200 hover:border-indigo-300 transition-colors">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(kolaborator.user?.nama || 'User')}&background=random`"
                        :alt="kolaborator.user?.nama" class="w-12 h-12 rounded-full">

                    <div class="flex-1">
                        <div class="font-semibold text-slate-900">{{ kolaborator.user?.nama }}</div>
                        <div class="text-sm text-slate-500">{{ kolaborator.user?.email }}</div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Role Badge -->
                        <span :class="getRoleBadgeColor(kolaborator.peran_kolaborator)"
                            class="px-3 py-1 rounded-lg text-sm font-semibold border flex items-center gap-1.5">
                            <component :is="roleIcons[kolaborator.peran_kolaborator]" :size="14" />
                            {{ getRoleName(kolaborator.peran_kolaborator) }}
                        </span>



                        <!-- Remove Button -->
                        <button @click="removeKolaborator(kolaborator.id)"
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            title="Hapus Kolaborator">
                            <Trash2 :size="18" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <p v-else class="text-center py-8 text-slate-500">
            Belum ada kolaborator ditambahkan
        </p>

    </div>
</template>
