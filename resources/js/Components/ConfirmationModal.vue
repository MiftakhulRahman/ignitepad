<script setup>
import Modal from './Modal.vue';
import { AlertTriangle } from 'lucide-vue-next';

const emit = defineEmits(['close', 'confirm']);

defineProps({
    show: Boolean,
    title: String,
    content: String,
});
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                <AlertTriangle class="w-6 h-6 text-red-600" />
            </div>
            <div class="mt-3 text-center sm:mt-5">
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ title }}</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500">{{ content }}</p>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button
                    type="button"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:col-start-2 sm:text-sm"
                    @click="emit('confirm')"
                >
                    Ya, Hapus
                </button>
                <button
                    type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm"
                    @click="emit('close')"
                >
                    Batal
                </button>
            </div>
        </div>
    </Modal>
</template>