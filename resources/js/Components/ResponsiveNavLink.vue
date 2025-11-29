<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    active: { type: Boolean },
});

const classes = computed(() =>
    props.active
        ? 'block w-full pl-3 pr-4 py-3 bg-indigo-50 border-l-4 border-indigo-500 text-indigo-700 font-medium text-base transition duration-150 ease-in-out'
        : 'block w-full pl-3 pr-4 py-3 border-l-4 border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 font-medium text-base transition duration-150 ease-in-out'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>