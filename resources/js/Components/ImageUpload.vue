<script setup>
import { ref, watch, computed } from 'vue';
import { Upload, X, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    modelValue: [File, Array, null],
    multiple: {
        type: Boolean,
        default: false
    },
    previewSize: {
        type: String,
        default: 'h-48'
    },
    preview: {
        type: String,
        default: null
    },
    maxFiles: {
        type: Number,
        default: 5
    },
    aspectRatio: {
        type: Number,
        default: null // e.g., 16/9
    }
});

const emit = defineEmits(['update:modelValue']);

const files = ref(props.multiple ? (Array.isArray(props.modelValue) ? props.modelValue : []) : null);
const previews = ref([]);
const dragActive = ref(false);
const errors = ref([]);

const remainingSlots = computed(() => {
    if (!props.multiple) return 0;
    const fileArray = files.value || [];
    return props.maxFiles - fileArray.length;
});

// Generate preview URLs
const generatePreviews = () => {
    previews.value = [];
    if (!files.value) return;

    const fileArray = props.multiple ? files.value : [files.value];

    fileArray.forEach(file => {
        if (file instanceof File) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previews.value.push(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
};

// Process Image (Auto-crop & Resize)
const processFile = async (file) => {
    // Validate Type
    if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
        errors.value.push(`File ${file.name} bukan gambar JPG/PNG.`);
        return null;
    }

    return new Promise((resolve) => {
        const img = new Image();
        img.src = URL.createObjectURL(file);
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            let width = img.width;
            let height = img.height;

            // Auto-crop if aspect ratio is set
            if (props.aspectRatio) {
                const targetRatio = props.aspectRatio;
                const currentRatio = width / height;

                let sx = 0, sy = 0, sWidth = width, sHeight = height;

                if (currentRatio > targetRatio) {
                    // Image is too wide, crop width
                    sWidth = height * targetRatio;
                    sx = (width - sWidth) / 2;
                } else {
                    // Image is too tall, crop height
                    sHeight = width / targetRatio;
                    sy = (height - sHeight) / 2;
                }

                canvas.width = sWidth;
                canvas.height = sHeight;
                ctx.drawImage(img, sx, sy, sWidth, sHeight, 0, 0, sWidth, sHeight);
            } else {
                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0);
            }

            // Compress if > 1MB (approx logic)
            // Start with 0.9 quality, reduce if needed? 
            // For now, just export as JPEG 0.8 which usually reduces size significantly
            canvas.toBlob((blob) => {
                if (blob.size > 1024 * 1024) {
                    // If still > 1MB, try aggressive compression
                    canvas.toBlob((blob2) => {
                        const newFile = new File([blob2], file.name, { type: 'image/jpeg' });
                        resolve(newFile);
                    }, 'image/jpeg', 0.6);
                } else {
                    const newFile = new File([blob], file.name, { type: file.type });
                    resolve(newFile);
                }
            }, file.type === 'image/png' ? 'image/png' : 'image/jpeg', 0.85);
        };
        img.onerror = () => {
            errors.value.push(`Gagal memproses gambar ${file.name}.`);
            resolve(null);
        };
    });
};

const handleFiles = async (newFiles) => {
    errors.value = []; // Reset errors
    const processedFiles = [];

    for (const file of newFiles) {
        if (file.size > 5 * 1024 * 1024) { // Hard limit 5MB before processing
            errors.value.push(`File ${file.name} terlalu besar (>5MB).`);
            continue;
        }

        const processed = await processFile(file);
        if (processed) {
            // Final check 1MB
            if (processed.size > 1024 * 1024) {
                errors.value.push(`File ${file.name} gagal dikompresi di bawah 1MB.`);
            } else {
                processedFiles.push(processed);
            }
        }
    }

    if (props.multiple) {
        const currentCount = files.value?.length || 0;
        const availableSlots = props.maxFiles - currentCount;
        const filesToAdd = processedFiles.slice(0, availableSlots);

        if (processedFiles.length > availableSlots) {
            errors.value.push(`Hanya ${availableSlots} file yang ditambahkan (Max ${props.maxFiles}).`);
        }

        files.value = [...(files.value || []), ...filesToAdd];
        emit('update:modelValue', files.value);
    } else {
        if (processedFiles.length > 0) {
            files.value = processedFiles[0];
            emit('update:modelValue', files.value);
        }
    }

    generatePreviews();
};

const handleFileSelect = (event) => {
    const selectedFiles = Array.from(event.target.files);
    handleFiles(selectedFiles);
};

const handleDrop = (event) => {
    dragActive.value = false;
    const droppedFiles = Array.from(event.dataTransfer.files);
    handleFiles(droppedFiles);
};

const removeFile = (index) => {
    if (props.multiple) {
        files.value.splice(index, 1);
        previews.value.splice(index, 1);
        emit('update:modelValue', files.value);
    } else {
        files.value = null;
        previews.value = [];
        emit('update:modelValue', null);
    }
};

watch(() => props.modelValue, (newVal) => {
    if (props.multiple) {
        files.value = Array.isArray(newVal) ? newVal : [];
        generatePreviews();
    } else {
        files.value = newVal;
        if (newVal) {
            generatePreviews();
        } else {
            previews.value = [];
        }
    }
}, { immediate: true });

</script>

<template>
    <div class="space-y-3">

        <!-- Upload Area -->
        <div v-if="!multiple || remainingSlots > 0" @drop.prevent="handleDrop" @dragover.prevent="dragActive = true"
            @dragleave="dragActive = false" :class="[
                'relative border-2 border-dashed rounded-xl transition-all cursor-pointer overflow-hidden',
                dragActive ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300 hover:border-indigo-400 bg-slate-50',
                multiple ? 'p-4' : 'aspect-video'
            ]">
            <input type="file" :accept="'image/jpeg,image/jpg,image/png'" :multiple="multiple"
                @change="handleFileSelect" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

            <!-- Empty State -->
            <div v-if="previews.length === 0 && !preview"
                class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center">
                <Upload :size="32" class="text-slate-400 mb-2" />
                <p class="text-sm font-medium text-slate-700">
                    {{ multiple ? 'Upload Gambar' : 'Upload Thumbnail' }}
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    JPG, PNG (Max 1MB{{ aspectRatio ? ', Auto-crop 16:9' : '' }})
                </p>
                <p v-if="multiple" class="text-xs text-indigo-600 font-medium mt-2">Max {{ maxFiles }} gambar</p>
            </div>

            <!-- Existing Preview (dari props) -->
            <div v-else-if="preview && previews.length === 0" class="relative w-full h-full">
                <img :src="preview" class="w-full h-full object-cover rounded-lg">
                <div
                    class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
                    <p class="text-white text-sm font-medium">Klik untuk ganti</p>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        <div v-if="errors.length > 0" class="space-y-1">
            <div v-for="(err, i) in errors" :key="i"
                class="flex items-center gap-2 text-xs text-red-600 bg-red-50 p-2 rounded-lg">
                <AlertCircle :size="14" />
                {{ err }}
            </div>
        </div>

        <!-- Preview Grid -->
        <div v-if="previews.length > 0" :class="multiple ? 'grid grid-cols-3 gap-3' : ''">
            <div v-for="(previewUrl, index) in previews" :key="index" class="relative group">
                <!-- Aspect ratio container -->
                <div class="relative w-full aspect-video overflow-hidden rounded-xl border-2 border-slate-200">
                    <img :src="previewUrl" class="absolute inset-0 w-full h-full object-cover">

                    <!-- Remove Button -->
                    <button type="button" @click.stop="removeFile(index)"
                        class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        <X :size="16" />
                    </button>

                    <!-- Size indicator -->
                    <div v-if="files && (multiple ? files[index] : files)"
                        class="absolute bottom-2 left-2 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded">
                        {{ ((multiple ? files[index].size : files.size) / 1024).toFixed(0) }} KB
                    </div>
                </div>
            </div>
        </div>

        <!-- File count info for multiple -->
        <div v-if="multiple && files && files.length > 0"
            class="flex items-center justify-between text-xs text-slate-600 bg-slate-100 px-3 py-2 rounded-lg">
            <span class="font-medium">{{ files.length }} dari {{ maxFiles }} gambar</span>
            <span v-if="remainingSlots > 0" class="text-indigo-600">{{ remainingSlots }} slot tersisa</span>
            <span v-else class="text-amber-600">Maksimal tercapai</span>
        </div>

    </div>
</template>