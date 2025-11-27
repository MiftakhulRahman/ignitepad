<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronRight, Home } from 'lucide-vue-next';

defineProps({
    items: Array // Format: [{ label: 'Name', url: '...' }]
});
</script>

<template>
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <Link :href="route('dashboard')" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                    <Home :size="16" class="mr-2" />
                    Dashboard
                </Link>
            </li>
            <li v-for="(item, index) in items" :key="index">
                <div class="flex items-center">
                    <ChevronRight :size="16" class="text-gray-400" />
                    <span v-if="!item.url" class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ item.label }}</span>
                    <Link v-else :href="item.url" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2">
                        {{ item.label }}
                    </Link>
                </div>
            </li>
        </ol>
    </nav>
</template>