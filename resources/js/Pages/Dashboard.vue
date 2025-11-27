<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-gray-500 text-sm font-medium">Total Proyek</h3>
                <p class="text-3xl font-bold text-gray-800 mt-2">12</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-gray-500 text-sm font-medium">Challenge Aktif</h3>
                <p class="text-3xl font-bold text-gray-800 mt-2">3</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-gray-500 text-sm font-medium">Total Poin</h3>
                <p class="text-3xl font-bold text-gray-800 mt-2">1,240</p>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-semibold mb-4">Selamat Datang di IgnitePad!</h3>
                <p class="text-gray-600">
                    Sistem sudah siap. Coba klik tombol hamburger di pojok kiri atas untuk melihat sidebar melipat dan membuka.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>