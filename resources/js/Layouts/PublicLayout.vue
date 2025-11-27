<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { Rocket, ChevronDown, User } from 'lucide-vue-next';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// Cek apakah user sedang login
const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    
                    <div class="flex items-center">
                        <Link :href="route('beranda')" class="flex items-center gap-2">
                            <div class="bg-indigo-600 text-white p-1.5 rounded-lg">
                                <Rocket :size="24" />
                            </div>
                            <span class="text-xl font-bold text-gray-900">IgnitePad</span>
                        </Link>
                        
                        <div class="hidden sm:ml-10 sm:flex sm:space-x-8">
                            <Link :href="route('proyek.index')" class="text-gray-500 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors">
                                Jelajah Proyek
                            </Link>
                            <Link :href="route('challenge.index')" class="text-gray-500 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors">
                                Challenge
                            </Link>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div v-if="user">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="flex items-center gap-2 focus:outline-none hover:bg-gray-50 p-1 rounded-lg transition-colors">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                            <User :size="18" />
                                        </div>
                                        <div class="hidden md:block text-left">
                                            <p class="text-sm font-medium text-gray-700 leading-none">
                                                {{ user.nama }}
                                            </p>
                                        </div>
                                        <ChevronDown :size="16" class="text-gray-400" />
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('dashboard')">
                                        Dashboard
                                    </DropdownLink>
                                    <DropdownLink :href="route('profile.edit')"> 
                                        Profile 
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        Log Out
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                        <div v-else class="flex items-center gap-2">
                            <Link :href="route('login')" class="text-gray-600 hover:text-gray-900 font-medium text-sm px-3 py-2">
                                Masuk
                            </Link>
                            <Link :href="route('register')" class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 transition-colors">
                                Daftar Sekarang
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center gap-2 mb-4 md:mb-0">
                        <Rocket :size="20" class="text-indigo-600" />
                        <span class="text-gray-900 font-bold">IgnitePad</span>
                    </div>
                    <p class="text-gray-500 text-sm">
                        &copy; {{ new Date().getFullYear() }} IgnitePad. Wadah Inovasi Kampus.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
