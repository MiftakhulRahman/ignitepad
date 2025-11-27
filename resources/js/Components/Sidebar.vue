<script setup>
import { Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    Rocket, 
    Trophy, 
    Users, 
    Layers, 
    Cpu, 
    MoreHorizontal,
    GraduationCap // Import Icon Baru
} from 'lucide-vue-next';

const props = defineProps({
    isOpen: Boolean,
});

const menuGroups = [
    {
        groupName: '', 
        items: [
            { name: 'Dashboard', route: 'dashboard', icon: LayoutDashboard },
        ]
    },
    {
        groupName: 'Menu Utama',
        items: [
            { name: 'Jelajah Proyek', route: 'proyek.index', icon: Rocket },
            { name: 'Challenge', route: 'challenge.index', icon: Trophy },
        ]
    },
    {
        groupName: 'Master Data',
        items: [
            // Menu Baru: Program Studi
            { name: 'Program Studi', route: 'prodi.index', icon: GraduationCap },
            { name: 'Pengguna', route: 'pengguna.index', icon: Users },
            { name: 'Kategori', route: 'kategori.index', icon: Layers },
            { name: 'Teknologi', route: 'teknologi.index', icon: Cpu },
        ]
    }
];

const isActive = (routeName) => route().current(routeName);
</script>

<template>
    <aside 
        class="fixed left-0 top-0 z-40 h-screen bg-white border-r border-gray-200 transition-all duration-300 ease-in-out flex flex-col"
        :class="isOpen ? 'w-64' : 'w-20'"
    >
        <div class="h-16 flex items-center border-b border-gray-200 shrink-0 transition-all duration-300"
            :class="isOpen ? 'px-6 justify-start' : 'justify-center px-0'"
        >
            <Link :href="route('dashboard')" class="flex items-center gap-2 overflow-hidden w-full"
                :class="isOpen ? '' : 'justify-center'"
            >
                <div class="bg-indigo-600 text-white p-1.5 rounded-lg shrink-0 flex items-center justify-center transition-all duration-300">
                    <Rocket :size="24" />
                </div>
                
                <span 
                    class="text-xl font-bold text-gray-800 transition-opacity duration-300 whitespace-nowrap"
                    :class="isOpen ? 'opacity-100' : 'opacity-0 w-0 hidden'"
                >
                    IgnitePad
                </span>
            </Link>
        </div>

        <div class="flex-1 overflow-y-auto py-4 overflow-x-hidden hover:overflow-y-auto custom-scrollbar">
            <nav class="space-y-2 px-3">
                
                <div v-for="(group, index) in menuGroups" :key="index">
                    
                    <div class="min-h-[20px] mb-2 mt-4 first:mt-0 flex items-center transition-all duration-300"
                        :class="isOpen ? 'justify-start px-3' : 'justify-center'"
                        v-if="group.groupName"
                    >
                        <span v-if="isOpen" class="text-xs font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">
                            {{ group.groupName }}
                        </span>
                        <MoreHorizontal v-else :size="20" class="text-gray-300" />
                    </div>

                    <ul class="space-y-1">
                        <li v-for="item in group.items" :key="item.route">
                            <Link 
                                :href="route(item.route)" 
                                class="flex items-center rounded-lg py-2.5 transition-all duration-300 group relative"
                                :class="[
                                    isActive(item.route) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                                    isOpen ? 'px-3 gap-3 justify-start' : 'justify-center px-0'
                                ]"
                            >
                                <component :is="item.icon" :size="22" class="shrink-0" />
                                
                                <span 
                                    class="font-medium whitespace-nowrap transition-all duration-300"
                                    :class="isOpen ? 'opacity-100 w-auto' : 'opacity-0 w-0 hidden'"
                                >
                                    {{ item.name }}
                                </span>

                                <div v-if="!isOpen" 
                                    class="absolute left-full ml-6 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap z-50 transition-opacity"
                                >
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
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 20px;
}
</style>