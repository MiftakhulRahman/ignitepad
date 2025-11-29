<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Rocket, Trophy, Users, Layers, Cpu, 
    MoreHorizontal, GraduationCap, Briefcase, X 
} from 'lucide-vue-next';

const props = defineProps({
    isOpen: Boolean, // State dari Layout (Desktop)
    isMobileOpen: Boolean // State dari Layout (Mobile)
});

const emit = defineEmits(['closeMobileSidebar']);

const page = usePage();
const userRoles = computed(() => page.props.auth.user.perans.map(r => r.slug));

const hasRole = (roles) => roles.some(r => userRoles.value.includes(r));
const isActive = (routeName) => route().current(routeName);

// MENU CONFIGURATION
const menuGroups = computed(() => [
    {
        groupName: '', 
        items: [
            { name: 'Dashboard', route: 'dashboard', icon: LayoutDashboard, show: true },
        ]
    },
    {
        groupName: 'Menu Utama',
        items: [
            { name: 'Jelajah Proyek', route: 'proyek.index', icon: Rocket, show: hasRole(['superadmin']) },
            { name: 'Proyek Saya', route: 'proyek.saya', icon: Briefcase, show: hasRole(['mahasiswa', 'dosen']) },
            { name: 'Challenge', route: 'challenge.index', icon: Trophy, show: true },
        ].filter(item => item.show)
    },
    {
        groupName: 'Admin Area',
        items: [
            { name: 'Program Studi', route: 'prodi.index', icon: GraduationCap, show: hasRole(['superadmin']) },
            { name: 'Pengguna', route: 'pengguna.index', icon: Users, show: hasRole(['superadmin']) },
            { name: 'Kategori', route: 'kategori.index', icon: Layers, show: hasRole(['superadmin']) },
            { name: 'Teknologi', route: 'teknologi.index', icon: Cpu, show: hasRole(['superadmin']) },
        ].filter(item => item.show)
    }
].filter(group => group.items.length > 0));
</script>

<template>
    <div v-if="isMobileOpen" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm md:hidden" @click="$emit('closeMobileSidebar')"></div>

    <aside 
        class="fixed top-0 left-0 z-50 h-screen bg-white border-r border-gray-100 transition-all duration-300 ease-spring"
        :class="[
            isOpen ? 'w-[280px]' : 'w-[88px]', // Desktop widths
            isMobileOpen ? 'translate-x-0 w-[280px]' : '-translate-x-full md:translate-x-0' // Mobile translations
        ]"
    >
        <div class="h-20 flex items-center px-6 relative" :class="isOpen ? 'justify-start' : 'justify-center'">
            <Link :href="route('dashboard')" class="flex items-center gap-3 overflow-hidden">
                <div class="bg-indigo-600 text-white p-2 rounded-xl shrink-0">
                    <Rocket :size="24" />
                </div>
                <span class="text-xl font-bold text-gray-800 whitespace-nowrap transition-opacity duration-300"
                    :class="isOpen ? 'opacity-100' : 'opacity-0 w-0 hidden md:hidden'">
                    IgnitePad
                </span>
            </Link>
            
            <button @click="$emit('closeMobileSidebar')" class="absolute right-4 top-6 text-gray-500 md:hidden">
                <X :size="24" />
            </button>
        </div>

        <div class="h-[calc(100vh-80px)] overflow-y-auto px-4 pb-6 custom-scrollbar">
            <nav class="space-y-6">
                <div v-for="(group, idx) in menuGroups" :key="idx">
                    <div v-if="group.groupName" class="mb-2 px-4 transition-all duration-300"
                        :class="isOpen ? 'text-start' : 'text-center'">
                        <span v-if="isOpen" class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                            {{ group.groupName }}
                        </span>
                        <MoreHorizontal v-else :size="20" class="text-gray-300 mx-auto" />
                    </div>

                    <ul class="space-y-1">
                        <li v-for="item in group.items" :key="item.route">
                            <Link 
                                :href="route(item.route)" 
                                class="flex items-center gap-4 px-4 py-3 rounded-full transition-all duration-200 group relative"
                                :class="[
                                    isActive(item.route) ? 'bg-indigo-100 text-indigo-900 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
                                    !isOpen && 'justify-center px-0'
                                ]"
                            >
                                <component :is="item.icon" :size="24" class="shrink-0" :class="isActive(item.route) ? 'text-indigo-600' : 'text-gray-500 group-hover:text-gray-700'" />
                                
                                <span class="whitespace-nowrap transition-all duration-300"
                                    :class="isOpen ? 'opacity-100' : 'opacity-0 w-0 hidden'">
                                    {{ item.name }}
                                </span>

                                <div v-if="!isOpen" class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none md:block hidden">
                                    {{ item.name }}
                                </div>
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </aside>
</template>

<style scoped>
/* Smooth Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 20px; }
.ease-spring { transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }
</style>