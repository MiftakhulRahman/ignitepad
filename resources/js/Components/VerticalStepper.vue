<script setup>
import { Check } from 'lucide-vue-next';

const props = defineProps({
    steps: {
        type: Array,
        required: true,
        // Format: [{ step: 1, title: 'Personal Details', description: 'Name and email' }]
    },
    currentStep: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['goToStep']);

const handleClick = (stepNumber) => {
    // Validasi logic bisa ditambahkan di parent sebelum mengubah step
    emit('goToStep', stepNumber);
};
</script>

<template>
    <nav aria-label="Progress" class="max-w-2xl mx-auto py-4">
        <ol role="list" class="overflow-hidden">
            <li v-for="(step, stepIdx) in steps" :key="step.step" class="relative pb-12 last:pb-0">
                
                <div v-if="stepIdx !== steps.length - 1" 
                     class="absolute top-5 left-5 -ml-px h-full w-0.5 transition-colors duration-500 ease-in-out"
                     :class="step.step < currentStep ? 'bg-blue-600' : 'bg-slate-200'" 
                     aria-hidden="true">
                </div>

                <div class="relative flex items-start group cursor-pointer select-none" @click="handleClick(step.step)">
                    
                    <span class="h-10 flex items-center">
                        <span v-if="step.step < currentStep" 
                              class="relative z-10 w-10 h-10 flex items-center justify-center bg-blue-600 rounded-full shadow-sm group-hover:bg-blue-700 transition-all duration-300">
                            <Check class="w-6 h-6 text-white" />
                        </span>

                        <span v-else-if="step.step === currentStep" 
                              class="relative z-10 w-10 h-10 flex items-center justify-center bg-blue-100 border-2 border-blue-600 rounded-full shadow-md ring-4 ring-blue-50 transition-all duration-300">
                            <span class="h-3 w-3 bg-blue-600 rounded-full"></span>
                        </span>

                        <span v-else 
                              class="relative z-10 w-10 h-10 flex items-center justify-center bg-white border border-slate-300 rounded-full group-hover:border-slate-400 group-hover:bg-slate-50 transition-all duration-300">
                           <span class="text-sm font-medium text-slate-500">{{ step.step }}</span>
                        </span>
                    </span>

                    <span class="ml-4 min-w-0 flex flex-col pt-0.5">
                        <span class="text-base font-medium tracking-tight transition-colors duration-300"
                              :class="step.step === currentStep ? 'text-blue-700' : (step.step < currentStep ? 'text-slate-900' : 'text-slate-500')">
                            {{ step.title }}
                        </span>
                        
                        <span class="text-sm mt-0.5 transition-colors duration-300"
                            :class="step.step === currentStep ? 'text-blue-600/80' : 'text-slate-500'">
                            {{ step.description }}
                        </span>
                    </span>
                </div>
            </li>
        </ol>
    </nav>
</template>