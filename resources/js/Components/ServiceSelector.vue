<script setup>
const props = defineProps({
    services: { type: Array, required: true },
    modelValue: { type: Array, required: true },
});

const emit = defineEmits(['update:modelValue']);

function toggle(serviceId, selected) {
    const values = new Set(props.modelValue);
    selected ? values.add(serviceId) : values.delete(serviceId);
    emit('update:modelValue', [...values]);
}
</script>

<template>
    <fieldset class="space-y-3">
        <legend class="text-sm font-semibold text-slate-800">Select one or more services</legend>
        <label v-for="service in services" :key="service.id" class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 p-4 transition hover:border-teal-500">
            <span class="flex items-center gap-3">
                <input type="checkbox" :checked="modelValue.includes(service.id)" class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-700" @change="toggle(service.id, $event.target.checked)">
                <span>
                    <span class="block font-semibold text-slate-900">{{ service.name }}</span>
                    <span class="text-sm text-slate-500">{{ service.duration_minutes }} minutes</span>
                </span>
            </span>
            <span class="font-semibold text-teal-800">{{ Number(service.price).toFixed(2) }}</span>
        </label>
    </fieldset>
</template>
