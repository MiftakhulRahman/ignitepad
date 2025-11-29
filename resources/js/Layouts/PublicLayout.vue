<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Rocket, User, Menu, X, ChevronRight, LogIn, LayoutDashboard, LogOut } from 'lucide-vue-next';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const page = usePage();
const user = page.props.auth.user;
const showingNavigationDropdown = ref(false);
const isScrolled = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));
</script>

<template>
    <div class="min-h-screen bg-white flex flex-col font-sans">
        
        <nav 
            class="fixed top-0 w-full z-50 transition-all duration-300 border-b border-transparent"
            :class="[
                isScrolled || showingNavigationDropdown 
                    ? 'bg-white/90 backdrop-blur-lg border-gray-100 shadow-sm py-2' 
                    : 'bg-transparent py-4'
            ]"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    
                    <div class="flex items-center">
                        <Link :href="route('beranda')" class="flex items-center gap-3 group">
                            <div class="bg-indigo-600 text-white p-2 rounded-xl transition-transform group-hover:scale-110 duration-300 shadow-lg shadow-indigo-200">
                                <Rocket :size="24" />
                            </div>
                            <span class="text-2xl font-bold text-gray-900 tracking-tight">IgnitePad</span>
                        </Link>
                        
                        <div class="hidden md:ml-12 md:flex md:space-x-8">
                            <Link :href="route('proyek.index')" 
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium transition-colors duration-200"
                                :class="route().current('proyek.index') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900 border-b-2 border-transparent'"
                            >
                                Jelajah Proyek
                            </Link>
                            <Link :href="route('challenge.index')" 
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium transition-colors duration-200"
                                :class="route().current('challenge.index') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900 border-b-2 border-transparent'"
                            >
                                Challenge
                            </Link>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="hidden md:flex items-center gap-4">
                            <div v-if="user" class="relative">
                                <Dropdown align="right" width="64">
                                    <template #trigger>
                                        <button class="flex items-center gap-3 py-1 pl-3 pr-2 rounded-full border border-gray-200 hover:border-indigo-300 hover:shadow-md hover:bg-white transition-all duration-300 bg-white/50 backdrop-blur-sm">
                                            <span class="text-sm font-semibold text-gray-700">{{ user.nama }}</span>
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 overflow-hidden ring-2 ring-white">
                                                <img v-if="user.avatar" :src="'/storage/' + user.avatar" class="w-full h-full object-cover">
                                                <User v-else :size="16" />
                                            </div>
                                        </button>
                                    </template>
                                    <template #content>
                                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 rounded-t-[20px]">
                                            <p class="text-xs text-gray-500 uppercase tracking-wider font-bold">Halo, {{ user.nama }}</p>
                                        </div>
                                        <DropdownLink :href="route('dashboard')">
                                            <LayoutDashboard :size="18"/> Dashboard
                                        </DropdownLink>
                                        <DropdownLink :href="route('profile.edit')">
                                            <User :size="18"/> Profil Saya
                                        </DropdownLink>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600 hover:bg-red-50">
                                            <LogOut :size="18"/> Keluar
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>

                            <div v-else class="flex items-center gap-3">
                                <Link :href="route('login')" class="text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors">
                                    Masuk
                                </Link>
                                <Link :href="route('register')" class="inline-flex items-center px-5 py-2.5 bg-gray-900 text-white rounded-full font-semibold text-sm hover:bg-gray-800 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                                    Daftar Sekarang <ChevronRight :size="16" class="ml-1" />
                                </Link>
                            </div>
                        </div>

                        <div class="flex items-center md:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" 
                                class="p-2 rounded-full text-gray-500 hover:bg-gray-100 focus:outline-none transition-colors">
                                <Menu v-if="!showingNavigationDropdown" :size="24" />
                                <X v-else :size="24" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform translate-x-full opacity-0"
                enter-to-class="transform translate-x-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="transform translate-x-0 opacity-100"
                leave-to-class="transform translate-x-full opacity-0"
            >
                <div v-show="showingNavigationDropdown" class="md:hidden fixed inset-0 z-40 bg-white pt-24 px-6 overflow-y-auto">
                    <div class="space-y-2 mb-8">
                        <ResponsiveNavLink :href="route('proyek.index')" :active="route().current('proyek.index')">
                            Jelajah Proyek
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('challenge.index')" :active="route().current('challenge.index')">
                            Challenge
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-gray-100 pt-6">
                        <div v-if="user" class="space-y-4">
                            <div class="flex items-center gap-3 px-3">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-lg font-bold">
                                    {{ user.nama.charAt(0) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">{{ user.nama }}</div>
                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <ResponsiveNavLink :href="route('dashboard')">Dashboard</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('profile.edit')">Profil</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-red-600">
                                    Keluar
                                </ResponsiveNavLink>
                            </div>
                        </div>
                        <div v-else class="grid gap-3">
                            <Link :href="route('login')" class="w-full py-3 text-center border border-gray-200 rounded-full font-bold text-gray-700">
                                Masuk
                            </Link>
                            <Link :href="route('register')" class="w-full py-3 text-center bg-gray-900 text-white rounded-full font-bold">
                                Daftar Sekarang
                            </Link>
                        </div>
                    </div>
                </div>
            </Transition>
        </nav>

        <main class="flex-1 pt-20">
            <slot />
        </main>

        <footer class="bg-white border-t border-gray-100 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-100 p-1.5 rounded-lg text-indigo-700">
                        <Rocket :size="20" />
                    </div>
                    <span class="font-bold text-gray-900 text-lg">IgnitePad</span>
                </div>
                <div class="text-gray-500 text-sm text-center md:text-right">
                    <p>&copy; {{ new Date().getFullYear() }} IgnitePad. Universitas Nurul Huda.</p>
                    <p class="mt-1">Dibuat dengan ❤️ oleh Mahasiswa Informatika.</p>
                </div>
            </div>
        </footer>
    </div>
</template>