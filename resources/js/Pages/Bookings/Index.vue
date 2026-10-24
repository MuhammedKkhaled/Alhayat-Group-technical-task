<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({ bookings: { type: Array, required: true } });

function formatDate(value) {
    return new Date(value).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
}
</script>

<template>
    <Head title="My bookings" />
    <AppLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-teal-700">Appointments</p>
                <h1 class="mt-2 text-3xl font-bold">My bookings</h1>
            </div>
            <Link href="/doctors" class="rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">Book another visit</Link>
        </div>

        <div v-if="!bookings.length" class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <h2 class="text-lg font-bold">No bookings yet</h2>
            <p class="mt-2 text-sm text-slate-600">Choose a doctor to book your first visit.</p>
        </div>

        <div v-else class="mt-8 space-y-4">
            <Link v-for="booking in bookings" :key="booking.id" :href="`/bookings/${booking.id}`" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-teal-500 hover:shadow">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-bold">{{ booking.doctor_name_snapshot }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ formatDate(booking.starts_at) }}</p>
                    </div>
                    <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold capitalize text-teal-800">{{ booking.status }}</span>
                </div>
                <p class="mt-4 text-sm text-slate-600">{{ booking.services.map((service) => service.pivot.service_name_snapshot).join(', ') }}</p>
                <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold text-slate-700">
                    <span>{{ booking.total_duration_minutes }} minutes</span>
                    <span>{{ Number(booking.total_price).toFixed(2) }}</span>
                </div>
            </Link>
        </div>
    </AppLayout>
</template>
