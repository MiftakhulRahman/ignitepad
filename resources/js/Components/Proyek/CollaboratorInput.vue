<script setup>
import { ref, watch } from 'vue';
import { Search, X, UserPlus, Trash2, Shield, Users2, Lightbulb } from 'lucide-vue-next';
import { debounce } from 'lodash';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);

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
        const existingIds = props.modelValue.map(k => k.user_id || k.id); // Handle both existing (user_id) and new (id)
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
    const newCollaborator = {
        user_id: user.id, // Standardize on user_id
        nama: user.nama,
        email: user.email,
        username: user.username,
        avatar: user.avatar, // If available
        peran_kolaborator: 'anggota',
        access_level: 'full_access',
        bisa_edit: true,
        bisa_hapus: true
    };

    const newList = [...props.modelValue, newCollaborator];
    emit('update:modelValue', newList);

    searchQuery.value = '';
    searchResults.value = [];
};

// Remove from selection
const removeUser = (index) => {
    const newList = [...props.modelValue];
    newList.splice(index, 1);
    emit('update:modelValue', newList);
};



// Update role
const updateRole = (index, role) => {
    const newList = [...props.modelValue];
    newList[index].peran_kolaborator = role;
    emit('update:modelValue', newList);
};

</script>

<template>
    <div class="space-y-6">
        <!-- Search Box -->
        <div class="relative">
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                <UserPlus :size="16" class="inline mr-2" />
                Tambah Kolaborator
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <Search :size="18" class="text-slate-400" />
                </div>
                <input v-model="searchQuery" type="text"
                    class="w-full pl-11 pr-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    placeholder="Cari nama, username, atau email...">
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
                    </div>
                    <div class="text-xs text-slate-500">
                        {{ user.nim || user.nidn || 'Pengguna' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- List of Added Collaborators -->
        <div v-if="modelValue.length > 0" class="space-y-3">
            <div v-for="(user, index) in modelValue" :key="index"
                class="bg-white rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-4 border border-slate-200 shadow-sm">

                <!-- User Info -->
                <div class="flex items-center gap-3 flex-1">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&background=random`"
                        class="w-10 h-10 rounded-full">
                    <div>
                        <div class="font-semibold text-slate-900">{{ user.nama }}</div>
                        <div class="text-xs text-slate-500">{{ user.email }}</div>
                    </div>
                </div>

                <!-- Controls -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Role Selector -->
                    <select :value="user.peran_kolaborator" @change="updateRole(index, $event.target.value)"
                        class="px-3 py-2 border border-slate-300 rounded-lg text-sm font-medium focus:ring-2 focus:ring-indigo-500 bg-slate-50">
                        <option value="pembimbing">Pembimbing</option>
                        <option value="rekan">Rekan</option>
                        <option value="kontributor">Kontributor</option>
                        <option value="anggota">Anggota</option>
                    </select>



                    <!-- Remove Button -->
                    <button @click="removeUser(index)"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                        <Trash2 :size="18" />
                    </button>
                </div>
            </div>
        </div>

        <p v-else class="text-sm text-slate-500 text-center py-4 border-2 border-dashed border-slate-200 rounded-xl">
            Belum ada kolaborator yang ditambahkan.
        </p>
    </div>
</template>
