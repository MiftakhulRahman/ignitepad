<script setup>
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import Navbar from '@/Components/Navbar.vue';

// State Layout
const isDesktopSidebarOpen = ref(true); // Default open di desktop
const isMobileSidebarOpen = ref(false); // Default close di mobile

// Toggle Logic
const toggleSidebar = () => {
    if (window.innerWidth >= 768) {
        isDesktopSidebarOpen.value = !isDesktopSidebarOpen.value;
    } else {
        isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
    }
};
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] flex font-sans text-gray-900">
        
        <Sidebar 
            :is-open="isDesktopSidebarOpen" 
            :is-mobile-open="isMobileSidebarOpen"
            @close-mobile-sidebar="isMobileSidebarOpen = false" 
        />

        <div 
            class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out"
            :class="isDesktopSidebarOpen ? 'md:ml-[280px]' : 'md:ml-[88px]'"
        >
            <Navbar @toggle-sidebar="toggleSidebar" />

            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-x-hidden">
                <div class="w-full mx-auto animate-fade-in-up">
                    <slot />
                </div>
            </main>
            
        </div>
    </div>
</template>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
}
</style>