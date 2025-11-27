<script setup>
import { Menu, Bell, ChevronDown, User } from 'lucide-vue-next';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// Terima event toggle dari parent
const emit = defineEmits(['toggleSidebar']);
</script>

<template>
    <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 sticky top-0 z-30">
        
        <div class="flex items-center gap-4">
            <button 
                @click="$emit('toggleSidebar')" 
                class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors focus:outline-none"
            >
                <Menu :size="24" />
            </button>
            
            <h1 class="text-lg font-semibold text-gray-800 hidden md:block">
                Dashboard
            </h1>
        </div>

        <div class="flex items-center gap-4">
            
            <button class="relative p-2 text-gray-500 hover:bg-gray-100 rounded-full transition-colors">
                <Bell :size="20" />
                <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>

            <div class="relative">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button class="flex items-center gap-2 focus:outline-none">
                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                <User :size="18" />
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-700 leading-none">
                                    {{ $page.props.auth.user.nama }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $page.props.auth.user.email }}
                                </p>
                            </div>
                            <ChevronDown :size="16" class="text-gray-400" />
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </div>
    </header>
</template>