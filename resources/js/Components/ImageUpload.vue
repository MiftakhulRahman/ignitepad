<script setup>
import { ref } from 'vue';
import { UploadCloud, X, Image as ImageIcon } from 'lucide-vue-next';

const props = defineProps({
    modelValue: [File, Array, null], // Bisa single file atau array file
    multiple: { type: Boolean, default: false },
    label: { type: String, default: 'Upload Gambar' },
    previewSize: { type: String, default: 'h-48' } // Tailwind class
});

const emit = defineEmits(['update:modelValue', 'change']);
const previews = ref([]);
const isDragging = ref(false);

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    processFiles(files);
};

const onDrop = (event) => {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer.files);
    processFiles(files);
};

const processFiles = (files) => {
    // Validasi gambar
    const validFiles = files.filter(file => file.type.startsWith('image/'));
    
    if (validFiles.length === 0) return;

    // Generate Preview URL
    const newPreviews = validFiles.map(file => ({
        file,
        url: URL.createObjectURL(file)
    }));

    if (props.multiple) {
        // Jika multiple, gabung dengan yang sudah ada
        previews.value = [...previews.value, ...newPreviews];
        // Emit array file asli
        emit('update:modelValue', previews.value.map(p => p.file));
    } else {
        // Jika single, replace
        previews.value = [newPreviews[0]]; // Ambil 1 aja
        emit('update:modelValue', newPreviews[0].file);
    }
    
    emit('change');
};

const removeFile = (index) => {
    previews.value.splice(index, 1);
    if (props.multiple) {
        emit('update:modelValue', previews.value.map(p => p.file));
    } else {
        emit('update:modelValue', null);
    }
};
</script>

<template>
    <div>
        <div 
            class="relative border-2 border-dashed rounded-xl p-6 text-center transition-all cursor-pointer group"
            :class="[
                isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-indigo-400 hover:bg-gray-50',
                previews.length > 0 && !multiple ? 'hidden' : 'block' // Sembunyikan dropzone jika single file sudah ada isinya
            ]"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
            @click="$refs.fileInput.click()"
        >
            <input 
                type="file" 
                ref="fileInput" 
                class="hidden" 
                accept="image/*" 
                :multiple="multiple"
                @change="handleFileSelect"
            >
            
            <div class="flex flex-col items-center justify-center py-4">
                <div class="p-3 bg-indigo-100 text-indigo-600 rounded-full mb-3 group-hover:scale-110 transition-transform">
                    <UploadCloud :size="24" />
                </div>
                <p class="text-sm font-medium text-gray-900">
                    <span class="text-indigo-600">Klik untuk upload</span> atau drag and drop
                </p>
                <p class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP (Max. 2MB)</p>
            </div>
        </div>

        <div v-if="previews.length > 0" :class="multiple ? 'grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4' : 'mt-0'">
            <div 
                v-for="(item, index) in previews" 
                :key="index" 
                class="relative group rounded-xl overflow-hidden border border-gray-200 shadow-sm"
                :class="multiple ? 'aspect-square' : `w-full ${previewSize} mt-4`"
            >
                <img :src="item.url" class="w-full h-full object-cover" />
                
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <button 
                        @click.stop="removeFile(index)" 
                        type="button"
                        class="p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-transform hover:scale-110"
                        title="Hapus Gambar"
                    >
                        <X :size="18" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>