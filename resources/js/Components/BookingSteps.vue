<script setup>
defineProps({ current: { type: Number, required: true } });

const steps = ['Services', 'Date & time', 'Review'];
</script>

<template>
    <ol class="mb-8 grid grid-cols-3 gap-2" aria-label="Booking steps">
        <li v-for="(step, index) in steps" :key="step" class="flex items-center gap-2 text-sm" :class="index + 1 <= current ? 'text-teal-800' : 'text-slate-400'">
            <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold" :class="index + 1 <= current ? 'bg-teal-700 text-white' : 'bg-slate-200'">{{ index + 1 }}</span>
            <span class="hidden font-medium sm:inline">{{ step }}</span>
        </li>
    </ol>
</template>
