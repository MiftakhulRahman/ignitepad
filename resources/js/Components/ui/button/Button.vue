<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
    },
    size: {
        type: String,
        default: 'default',
    },
    class: {
        type: String,
        default: '',
    },
});

const classes = computed(() => {
    const base = 'inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';
    
    const variants = {
        default: 'bg-indigo-600 text-white hover:bg-indigo-700',
        destructive: 'bg-red-500 text-white hover:bg-red-600',
        outline: 'border border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900',
        secondary: 'bg-slate-100 text-slate-900 hover:bg-slate-100/80',
        ghost: 'hover:bg-slate-100 hover:text-slate-900',
        link: 'text-indigo-600 underline-offset-4 hover:underline',
    };

    const sizes = {
        default: 'h-10 px-4 py-2',
        sm: 'h-9 rounded-md px-3',
        lg: 'h-11 rounded-md px-8',
        icon: 'h-10 w-10',
    };

    return [
        base,
        variants[props.variant] || variants.default,
        sizes[props.size] || sizes.default,
        props.class
    ].join(' ');
});
</script>

<template>
    <button :class="classes">
        <slot />
    </button>
</template>
