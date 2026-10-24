<script setup>
defineProps({ preview: { type: Object, required: true } });
</script>

<template>
    <aside class="rounded-2xl bg-slate-900 p-6 text-white" aria-live="polite">
        <p class="text-sm font-semibold uppercase tracking-widest text-teal-300">Review booking</p>
        <h2 class="mt-2 text-xl font-bold">{{ preview.doctor.name }}</h2>
        <p class="mt-1 text-sm text-slate-300">{{ new Date(preview.starts_at).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' }) }}–{{ new Date(preview.ends_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</p>

        <ul class="mt-5 space-y-2 border-y border-slate-700 py-4 text-sm">
            <li v-for="service in preview.services" :key="service.id" class="flex justify-between gap-4">
                <span>{{ service.name }} ({{ service.duration_minutes }} min)</span>
                <span>{{ Number(service.price).toFixed(2) }}</span>
            </li>
        </ul>

        <div class="mt-4 flex justify-between font-semibold">
            <span>{{ preview.total_duration_minutes }} minutes</span>
            <span>{{ Number(preview.total_price).toFixed(2) }}</span>
        </div>
    </aside>
</template>
