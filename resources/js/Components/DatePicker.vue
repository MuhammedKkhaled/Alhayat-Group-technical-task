<script setup>
defineProps({
    modelValue: { type: String, required: true },
    min: { type: String, required: true },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <label for="booking-date" class="block text-sm font-semibold text-slate-800">Choose a date</label>
        <input id="booking-date" :value="modelValue" :min="min" type="date" required class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20" @input="$emit('update:modelValue', $event.target.value)">
    </div>
</template>
