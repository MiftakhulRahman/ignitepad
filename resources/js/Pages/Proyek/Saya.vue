<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import Pagination from '@/Components/Pagination.vue';
import Toast from '@/Components/Toast.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import ProyekCardDashboard from '@/Components/Proyek/ProyekCardDashboard.vue';

const props = defineProps({
    proyeks: Object,
    isAdminMode: Boolean,
    filters: Object,
});

const handleDelete = (proyek) => {
    if (confirm(`Apakah Anda yakin ingin menghapus proyek "${proyek.judul}"? Tindakan ini tidak dapat dibatalkan.`)) {
        router.delete(route('proyek.destroy', proyek.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Success message will be shown via Toast
            }
        });
    }
};
</script>

<template>

    <Head :title="isAdminMode ? 'Kelola Proyek' : 'Proyek Saya'" />
    <Toast />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <Breadcrumb :items="[
                { label: isAdminMode ? 'Kelola Proyek' : 'Proyek Saya' }
            ]" />

            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900">
                        <template v-if="isAdminMode">Kelola Proyek</template>
                        <template v-else>Proyek Saya</template>
                    </h2>
                    <p class="text-slate-600 mt-2">
                        <template v-if="isAdminMode">Kelola semua proyek mahasiswa dan dosen</template>
                        <template v-else>Kelola portofolio proyek yang telah Anda buat</template>
                    </p>
                </div>
                <Link v-if="!isAdminMode" :href="route('proyek.create')"
                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg shadow-indigo-600/30 hover:shadow-xl hover:shadow-indigo-600/40 transition-all">
                <Plus :size="20" />
                Upload Proyek Baru
                </Link>
            </div>

            <!-- Projects Grid -->
            <div v-if="proyeks.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <ProyekCardDashboard v-for="proyek in proyeks.data" :key="proyek.id" :proyek="proyek"
                    @delete="handleDelete" />
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-2xl border-2 border-dashed border-slate-300 p-16 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 mx-auto mb-6 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Belum ada proyek</h3>
                    <p class="text-slate-600 mb-8">Mulai bangun portofolio Anda dengan membuat proyek pertama</p>
                    <Link :href="route('proyek.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-lg transition-colors">
                    <Plus :size="18" />
                    Buat Proyek Pertama
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="proyeks.links.length > 3" class="mt-8">
                <Pagination :links="proyeks.links" />
            </div>

        </div>
    </AuthenticatedLayout>
</template>