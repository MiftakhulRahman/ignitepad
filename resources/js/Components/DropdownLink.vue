<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: { type: String, required: true },
    as: { type: String, default: 'a' } // Support button untuk logout
});
</script>

<template>
    <Link
        v-if="as !== 'button'"
        :href="href"
        class="flex items-center gap-3 w-full px-4 py-3 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors duration-200"
    >
        <slot />
    </Link>
    
    <button
        v-else
        type="submit"
        class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-medium text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors duration-200"
    >
        <slot />
    </button>
</template>