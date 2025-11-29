<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, AlertCircle, X } from 'lucide-vue-next';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref('success'); // success | error

// Pantau perubahan pesan dari Backend
watch(() => page.props.flash, (flash) => {
    if (flash.success) {
        fire(flash.success, 'success');
    } else if (flash.error) {
        fire(flash.error, 'error');
    }
}, { deep: true });

const fire = (msg, msgType = 'success') => {
    message.value = msg;
    type.value = msgType;
    show.value = true;
    setTimeout(() => { show.value = false; }, 5000); // 5 detik agar tidak terlalu cepat hilang
};

defineExpose({ fire });
</script>

<template>
    <Transition enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="show"
            class="fixed top-24 right-6 z-50 flex w-full max-w-md overflow-hidden bg-white rounded-xl shadow-2xl border border-gray-100">
            <div class="flex items-center justify-center w-16"
                :class="type === 'success' ? 'bg-green-500' : 'bg-red-500'">
                <CheckCircle v-if="type === 'success'" class="w-8 h-8 text-white" />
                <AlertCircle v-else class="w-8 h-8 text-white" />
            </div>

            <div class="px-6 py-4 flex-1">
                <span class="font-bold text-lg block mb-1"
                    :class="type === 'success' ? 'text-green-600' : 'text-red-600'">
                    {{ type === 'success' ? 'Berhasil!' : 'Terjadi Kesalahan!' }}
                </span>
                <p class="text-sm text-gray-600 leading-relaxed">{{ message }}</p>
            </div>

            <button @click="show = false" class="pr-4 pl-2 text-gray-400 hover:text-gray-600 transition-colors">
                <X :size="20" />
            </button>
        </div>
    </Transition>
</template>