<script setup>
import { Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    Rocket, 
    Trophy, 
    Users, 
    Layers, 
    Cpu, 
    MoreHorizontal 
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
        <div class="flex h-16 items-center border-b border-gray-200 shrink-0 transition-all duration-300"
            :class="isOpen ? 'px-6 justify-start' : 'justify-center px-0'"
        >
            <Link :href="route('dashboard')" class="flex items-center gap-2 overflow-hidden">
                <div class="bg-indigo-600 text-white p-1.5 rounded-lg shrink-0">
                    <Rocket :size="24" />
                </div>
                <span 
                    class="text-xl font-bold text-gray-800 transition-opacity duration-300 whitespace-nowrap"
                    :class="isOpen ? 'opacity-100' : 'opacity-0 w-0'"
                >
                    IgnitePad
                </span>
            </Link>
        </div>

        <div class="flex-1 overflow-y-auto py-4 overflow-x-hidden hover:overflow-y-auto">
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
                                    class="font-medium whitespace-nowrap transition-all duration-300 origin-left"
                                    :class="isOpen ? 'opacity-100 w-auto' : 'opacity-0 w-0 absolute left-full ml-2 bg-gray-800 text-white text-xs px-2 py-1 rounded hidden group-hover:block z-50'"
                                >
                                    {{ item.name }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>

            </nav>
        </div>
    </aside>
</template>