<script setup>
import { ref, watch } from 'vue';
import { Search, X, UserPlus, Trash2, Shield, Users2, Lightbulb, ChevronDown, Check } from 'lucide-vue-next';
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
const inputFocused = ref(false);

// Icon mapping for roles
const roleIcons = {
    pembimbing: Shield,
    rekan: Users2,
    kontributor: Lightbulb,
    anggota: UserPlus
};

const roleColors = {
    pembimbing: 'text-indigo-600 bg-indigo-50 border-indigo-100',
    rekan: 'text-blue-600 bg-blue-50 border-blue-100',
    kontributor: 'text-emerald-600 bg-emerald-50 border-emerald-100',
    anggota: 'text-slate-600 bg-slate-100 border-slate-200'
};

// Search users with debounce
const searchUsers = debounce(async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;

    try {
        // Asumsi route() tersedia global (Laravel/Ziggy)
        const response = await fetch(route('proyek.search-users', { q: searchQuery.value }));
        const data = await response.json();

        const existingIds = props.modelValue.map(k => k.user_id || k.id);
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

const addUser = (user) => {
    const newCollaborator = {
        user_id: user.id,
        nama: user.nama,
        email: user.email,
        username: user.username,
        avatar: user.avatar,
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

const removeUser = (index) => {
    const newList = [...props.modelValue];
    newList.splice(index, 1);
    emit('update:modelValue', newList);
};

const updateRole = (index, role) => {
    const newList = [...props.modelValue];
    newList[index].peran_kolaborator = role;
    emit('update:modelValue', newList);
};

const handleBlur = () => {
    setTimeout(() => {
        inputFocused.value = false;
    }, 200);
};
</script>

<template>
    <div class="space-y-6 font-sans">
        <div class="flex items-center justify-between">
            <label class="text-base font-medium text-slate-800 flex items-center gap-2">
                <UserPlus :size="18" class="text-indigo-600" />
                Tim & Kolaborator
            </label>
            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                {{ modelValue.length }} Orang
            </span>
        </div>

        <div class="relative z-20">
            <div class="relative group transition-all duration-300"
                :class="inputFocused ? 'scale-[1.01]' : 'scale-100'">

                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <Search :size="20" class="text-slate-400 group-focus-within:text-indigo-600 transition-colors" />
                </div>

                <input v-model="searchQuery" type="text" @focus="inputFocused = true" @blur="handleBlur"
                    class="w-full pl-14 pr-12 py-4 bg-slate-50 border-0 ring-1 ring-slate-200 rounded-full text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500 focus:bg-white shadow-sm transition-all ease-out duration-200"
                    placeholder="Ketik nama, username, atau email untuk mengundang...">

                <div v-if="isSearching" class="absolute inset-y-0 right-0 pr-5 flex items-center">
                    <div class="w-5 h-5 border-2 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                </div>

                <button v-else-if="searchQuery" @click="searchQuery = ''"
                    class="absolute inset-y-0 right-0 pr-5 flex items-center text-slate-400 hover:text-slate-600">
                    <X :size="18" />
                </button>
            </div>

            <transition enter-active-class="transition duration-200 ease-out" enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in"
                leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-2 opacity-0">
                <div v-if="searchResults.length > 0 && inputFocused"
                    class="absolute w-full mt-3 bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden max-h-[320px] overflow-y-auto">

                    <div
                        class="px-4 py-3 bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 tracking-wider uppercase">
                        Hasil Pencarian
                    </div>

                    <div v-for="user in searchResults" :key="user.id" @click="addUser(user)"
                        class="group flex items-center gap-4 p-4 hover:bg-indigo-50/50 cursor-pointer transition-colors border-b border-slate-50 last:border-0">

                        <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&background=random&color=fff`"
                            :alt="user.nama"
                            class="w-10 h-10 rounded-full shadow-sm ring-2 ring-white group-hover:ring-indigo-200 transition-all">

                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-slate-900 truncate group-hover:text-indigo-700">{{ user.nama }}
                            </div>
                            <div class="text-sm text-slate-500 truncate flex items-center gap-2">
                                <span>@{{ user.username }}</span>
                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                <span class="text-xs">{{ user.nim || user.nidn || 'User' }}</span>
                            </div>
                        </div>

                        <div
                            class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 group-hover:border-indigo-200 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <UserPlus :size="16" />
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <TransitionGroup tag="div" enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in absolute w-full" leave-from-class="opacity-100"
            leave-to-class="opacity-0 scale-95" move-class="transition duration-300 ease-in-out"
            class="space-y-3 relative">
            <div v-for="(user, index) in modelValue" :key="user.user_id || user.id"
                class="bg-white rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center gap-4 border border-slate-100 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.05)] hover:shadow-md transition-shadow group">

                <div class="flex items-center gap-4 flex-1">
                    <div class="relative">
                        <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&background=random`"
                            class="w-12 h-12 rounded-full ring-2 ring-slate-50">
                        <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow-sm">
                            <component :is="roleIcons[user.peran_kolaborator]" :size="14" class="text-slate-500" />
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="font-semibold text-slate-800 truncate">{{ user.nama }}</div>
                        <div class="text-sm text-slate-500 truncate">{{ user.email }}</div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between sm:justify-end gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-50">

                    <div class="relative">
                        <select :value="user.peran_kolaborator" @change="updateRole(index, $event.target.value)"
                            class="appearance-none pl-3 pr-9 py-2 text-sm font-medium rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition-all capitalize w-full sm:w-auto"
                            :class="roleColors[user.peran_kolaborator]">
                            <option value="pembimbing">Pembimbing</option>
                            <option value="rekan">Rekan Tim</option>
                            <option value="kontributor">Kontributor</option>
                            <option value="anggota">Anggota</option>
                        </select>
                        <ChevronDown :size="14"
                            class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none opacity-60"
                            :class="roleColors[user.peran_kolaborator].split(' ')[0]" />
                    </div>

                    <button @click="removeUser(index)"
                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-all opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100"
                        title="Hapus Kolaborator">
                        <Trash2 :size="18" />
                    </button>
                </div>
            </div>
        </TransitionGroup>

        <div v-if="modelValue.length === 0"
            class="flex flex-col items-center justify-center py-10 px-4 border-2 border-dashed border-slate-200 rounded-2xl text-center bg-slate-50/50">
            <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center mb-3">
                <Users2 class="text-indigo-400" :size="24" />
            </div>
            <h3 class="text-slate-900 font-medium">Belum ada kolaborator</h3>
            <p class="text-slate-500 text-sm mt-1 max-w-xs">Gunakan kolom pencarian di atas untuk menambahkan anggota
                tim ke proyek ini.</p>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for dropdown */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>