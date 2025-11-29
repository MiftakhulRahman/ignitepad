<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    active: { type: Boolean },
    icon: { type: Object, required: false } // Lucide Icon component
});

const classes = computed(() =>
    props.active
        ? 'bg-indigo-100 text-indigo-900 font-bold'
        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 font-medium'
);
</script>

<template>
    <Link
        :href="href"
        class="flex items-center gap-3 px-4 py-3 rounded-full transition-all duration-300 group relative overflow-hidden"
        :class="classes"
    >
        <div v-if="$slots.icon || icon" class="shrink-0 transition-colors duration-300" :class="active ? 'text-indigo-700' : 'text-gray-500 group-hover:text-gray-700'">
            <component :is="icon" :size="22" v-if="icon" />
            <slot name="icon" v-else />
        </div>
        
        <span class="truncate">
            <slot />
        </span>
    </Link>
</template>