<script setup>
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import Navbar from '@/Components/Navbar.vue';

// State global layout: Sidebar terbuka atau tertutup?
// Default true (terbuka) di desktop
const isSidebarOpen = ref(true);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        
        <Sidebar :is-open="isSidebarOpen" />

        <div 
            class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out"
            :class="isSidebarOpen ? 'ml-64' : 'ml-20'"
        >
            <Navbar @toggle-sidebar="toggleSidebar" />

            <main class="flex-1 p-6 overflow-x-hidden">
                <slot />
            </main>
            
        </div>
    </div>
</template>