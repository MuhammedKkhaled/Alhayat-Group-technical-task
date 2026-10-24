<script setup>
defineProps({
    slots: { type: Array, required: true },
    modelValue: { type: String, required: true },
    loading: { type: Boolean, default: false },
    hasDate: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <section aria-labelledby="time-slots-heading">
        <h2 id="time-slots-heading" class="text-sm font-semibold text-slate-800">Available times</h2>
        <p v-if="loading" class="mt-3 text-sm text-slate-500">Checking availability…</p>
        <p v-else-if="!slots.length" class="mt-3 text-sm text-slate-500">{{ hasDate ? 'No appointment times are available on this date.' : 'Choose a date to see available times.' }}</p>
        <div v-else class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4">
            <button v-for="slot in slots" :key="slot.start_time" type="button" class="rounded-lg border px-3 py-2 text-sm font-semibold transition" :class="modelValue === slot.start_time ? 'border-teal-700 bg-teal-700 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-teal-600'" @click="$emit('update:modelValue', slot.start_time)">
                {{ slot.start_time }}
            </button>
        </div>
    </section>
</template>
