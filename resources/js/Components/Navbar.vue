<script setup>
import { Menu, Bell, Search, User, LogOut, Layout, Home } from 'lucide-vue-next';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

defineEmits(['toggleSidebar']);
</script>

<template>
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 h-20 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-30 transition-all duration-300">
        
        <div class="flex items-center gap-4">
            <button 
                @click="$emit('toggleSidebar')" 
                class="p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 rounded-full transition-colors focus:outline-none"
            >
                <Menu :size="24" />
            </button>
            
            <h1 class="text-xl font-semibold text-gray-800 hidden md:block">
                Dashboard
            </h1>
        </div>

        <div class="flex items-center gap-2 sm:gap-4">
            <div class="hidden md:flex items-center px-4 py-2 bg-gray-100 rounded-full text-gray-500 gap-2 border border-transparent focus-within:border-indigo-300 focus-within:bg-white focus-within:shadow-sm transition-all">
                <Search :size="18" />
                <input type="text" placeholder="Cari sesuatu..." class="bg-transparent border-none focus:ring-0 text-sm w-48 placeholder:text-gray-400 p-0">
            </div>

            <button class="relative p-2.5 text-gray-500 hover:bg-gray-100 rounded-full transition-colors">
                <Bell :size="20" />
                <span class="absolute top-2 right-2.5 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>

            <div class="relative ml-2">
                <Dropdown align="right" width="64">
                    <template #trigger>
                        <button class="flex items-center gap-3 focus:outline-none group">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 border-2 border-white shadow-sm flex items-center justify-center text-indigo-600 overflow-hidden group-hover:ring-2 group-hover:ring-indigo-100 transition-all">
                                <img v-if="$page.props.auth.user.avatar" :src="'/storage/' + $page.props.auth.user.avatar" class="w-full h-full object-cover">
                                <User v-else :size="20" />
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-semibold text-gray-700 leading-none group-hover:text-indigo-600 transition-colors">
                                    {{ $page.props.auth.user.nama }}
                                </p>
                                <p class="text-[11px] text-gray-500 mt-1 truncate max-w-[120px]">
                                    {{ $page.props.auth.user.email }}
                                </p>
                            </div>
                        </button>
                    </template>

                    <template #content>
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Akun Saya</p>
                        </div>
                        
                        <DropdownLink :href="route('beranda')">
                            <Home :size="18" /> Halaman Publik
                        </DropdownLink>
                        
                        <DropdownLink :href="route('profile.edit')"> 
                            <User :size="18" /> Edit Profil 
                        </DropdownLink>

                        <div class="border-t border-gray-100 my-1"></div>

                        <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600 hover:bg-red-50">
                            <LogOut :size="18" /> Keluar
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </div>
    </header>
</template>