<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, AlertCircle, X } from 'lucide-vue-next'; // Gunakan CheckCircle2 untuk varian yang lebih clean

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
    
    // Reset timer setiap kali fire dipanggil agar tidak hilang tiba-tiba jika di-spam
    if (window.toastTimeout) clearTimeout(window.toastTimeout);
    window.toastTimeout = setTimeout(() => { show.value = false; }, 5000);
};

defineExpose({ fire });
</script>

<template>
    <Transition 
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" 
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100" 
        leave-to-class="opacity-0 translate-x-2">
        
        <div v-if="show"
             class="fixed top-6 right-6 z-50 w-full max-w-sm bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden pointer-events-auto ring-1 ring-black/5">
            
            <div class="p-4 flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center ring-1 ring-inset"
                         :class="type === 'success' 
                            ? 'bg-green-50 text-green-600 ring-green-500/10' 
                            : 'bg-red-50 text-red-600 ring-red-500/10'">
                        
                        <CheckCircle2 v-if="type === 'success'" class="w-5 h-5" />
                        <AlertCircle v-else class="w-5 h-5" />
                    </div>
                </div>

                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-slate-900">
                        {{ type === 'success' ? 'Berhasil' : 'Gagal' }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500 leading-relaxed">
                        {{ message }}
                    </p>
                </div>

                <div class="ml-4 flex flex-shrink-0">
                    <button @click="show = false" 
                            class="inline-flex rounded-md bg-white text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                        <span class="sr-only">Close</span>
                        <X class="w-5 h-5" aria-hidden="true" />
                    </button>
                </div>
            </div>
            
            </div>
    </Transition>
</template>