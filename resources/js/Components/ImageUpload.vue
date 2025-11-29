<script setup>
import { ref, watch, computed } from 'vue';
import { Upload, X, AlertCircle, Crop } from 'lucide-vue-next';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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

// Cropper State
const showCropper = ref(false);
const cropperImage = ref(null);
const currentFile = ref(null);
const cropperRef = ref(null);

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

const handleFileSelect = (event) => {
    const selectedFiles = Array.from(event.target.files);
    handleFiles(selectedFiles);
    // Reset input to allow selecting same file again
    event.target.value = '';
};

const handleDrop = (event) => {
    dragActive.value = false;
    const droppedFiles = Array.from(event.dataTransfer.files);
    handleFiles(droppedFiles);
};

const handleFiles = async (newFiles) => {
    errors.value = []; // Reset errors

    for (const file of newFiles) {
        // Strict 1MB check for initial file
        if (file.size > 1024 * 1024) {
            errors.value.push(`File ${file.name} melebihi 1MB.`);
            continue;
        }

        // Validate Type
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
            errors.value.push(`File ${file.name} bukan gambar JPG/PNG.`);
            continue;
        }

        // If cropping is enabled (aspectRatio set) and single file mode (usually for thumbnail)
        // Open cropper modal
        if (props.aspectRatio && !props.multiple) {
            currentFile.value = file;
            cropperImage.value = URL.createObjectURL(file);
            showCropper.value = true;
            return; // Stop processing other files if any (should be single)
        }

        // If no cropping needed or multiple files, just add them
        addFile(file);
    }
};

const addFile = (file) => {
    if (props.multiple) {
        const currentCount = files.value?.length || 0;
        if (currentCount >= props.maxFiles) {
            errors.value.push(`Maksimal ${props.maxFiles} file.`);
            return;
        }
        files.value = [...(files.value || []), file];
        emit('update:modelValue', files.value);
    } else {
        files.value = file;
        emit('update:modelValue', files.value);
    }
    generatePreviews();
};

const cropImage = () => {
    const { canvas } = cropperRef.value.getResult();
    if (canvas) {
        canvas.toBlob((blob) => {
            if (blob.size > 1024 * 1024) {
                // If cropped result is still > 1MB, try to compress
                canvas.toBlob((blob2) => {
                    if (blob2.size > 1024 * 1024) {
                        errors.value.push('Hasil crop masih melebihi 1MB. Silakan gunakan gambar yang lebih kecil.');
                        showCropper.value = false;
                    } else {
                        const newFile = new File([blob2], currentFile.value.name, { type: 'image/jpeg' });
                        addFile(newFile);
                        showCropper.value = false;
                    }
                }, 'image/jpeg', 0.8);
            } else {
                const newFile = new File([blob], currentFile.value.name, { type: currentFile.value.type });
                addFile(newFile);
                showCropper.value = false;
            }
        }, currentFile.value.type);
    }
};

const cancelCrop = () => {
    showCropper.value = false;
    cropperImage.value = null;
    currentFile.value = null;
};

const fileInputRef = ref(null);

const removeFile = (index = 0) => {
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

const triggerFileInput = () => {
    fileInputRef.value?.click();
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

        <!-- Hidden File Input -->
        <input ref="fileInputRef" type="file" :accept="'image/jpeg,image/jpg,image/png'" :multiple="multiple"
            @change="handleFileSelect" class="hidden">

        <!-- Upload Area -->
        <div @drop.prevent="handleDrop" @dragover.prevent="dragActive = true" @dragleave="dragActive = false"
            @click="(!multiple && previews.length === 0 && !preview) ? triggerFileInput() : null" :class="[
                'relative border-2 rounded-xl transition-all overflow-hidden',
                dragActive ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300 bg-slate-50',
                multiple ? 'p-4 border-dashed' : 'aspect-video',
                (!multiple && (previews.length > 0 || preview)) ? 'border-solid hover:border-indigo-400 cursor-default' : 'border-dashed hover:border-indigo-400 cursor-pointer'
            ]">
            <!-- Empty State (No preview/file) -->
            <div v-if="previews.length === 0 && !preview"
                class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center pointer-events-none">
                <div class="w-14 h-14 bg-white rounded-full shadow-sm flex items-center justify-center mb-3">
                    <Upload :size="28" class="text-indigo-600" />
                </div>
                <p class="text-sm font-semibold text-slate-800">
                    {{ multiple ? 'Upload Gambar' : 'Upload Thumbnail' }}
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    JPG, PNG (Max 1MB{{ aspectRatio ? ', 16:9' : '' }})
                </p>
                <p v-if="multiple" class="text-xs text-indigo-600 font-medium mt-2">Max {{ maxFiles }} gambar</p>
            </div>

            <!-- Single File Preview (Inside Upload Box) -->
            <div v-else-if="!multiple && (previews.length > 0 || preview)" class="relative w-full h-full group">
                <img :src="previews[0] || preview" class="w-full h-full object-cover" />

                <!-- Hover Overlay with Actions -->
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center gap-3">
                    <button type="button" @click.stop="triggerFileInput"
                        class="flex items-center gap-2 px-4 py-2.5 bg-white text-slate-900 rounded-lg text-sm font-semibold hover:bg-slate-100 hover:scale-105 transition-all shadow-lg">
                        <Upload :size="16" /> Ganti
                    </button>
                    <button type="button" @click.stop="removeFile(0)"
                        class="flex items-center gap-2 px-4 py-2.5 bg-red-500 text-white rounded-lg text-sm font-semibold hover:bg-red-600 hover:scale-105 transition-all shadow-lg">
                        <X :size="16" /> Hapus
                    </button>
                </div>
            </div>

            <!-- Multiple Mode: Click to Add -->
            <div v-else-if="multiple && remainingSlots > 0" @click="triggerFileInput"
                class="text-center py-4 cursor-pointer">
                <p class="text-sm text-slate-600">Klik atau drag file di sini</p>
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

        <!-- Preview Grid (Only for Multiple Mode) -->
        <div v-if="multiple && previews.length > 0" class="grid grid-cols-3 gap-3">
            <div v-for="(previewUrl, index) in previews" :key="index" class="relative group">
                <div class="relative w-full aspect-video overflow-hidden rounded-xl border-2 border-slate-200">
                    <img :src="previewUrl" class="absolute inset-0 w-full h-full object-cover">

                    <!-- Remove Button -->
                    <button type="button" @click.stop="removeFile(index)"
                        class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-20">
                        <X :size="16" />
                    </button>

                    <!-- Size indicator -->
                    <div v-if="files && files[index]"
                        class="absolute bottom-2 left-2 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded">
                        {{ (files[index].size / 1024).toFixed(0) }} KB
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

        <!-- Cropper Modal -->
        <Modal :show="showCropper" @close="cancelCrop">
            <div class="p-6">
                <h2 class="text-lg font-medium text-slate-900 mb-4">Sesuaikan Gambar</h2>
                <div class="relative w-full h-96 bg-slate-100 rounded-lg overflow-hidden mb-6">
                    <Cropper ref="cropperRef" class="h-full" :src="cropperImage"
                        :stencil-props="{ aspectRatio: aspectRatio || 16 / 9 }" />
                </div>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="cancelCrop">Batal</SecondaryButton>
                    <PrimaryButton @click="cropImage">Simpan</PrimaryButton>
                </div>
            </div>
        </Modal>

    </div>
</template>