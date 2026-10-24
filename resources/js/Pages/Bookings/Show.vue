<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({ booking: { type: Object, required: true } });

function formatDate(value) {
    return new Date(value).toLocaleString([], { dateStyle: 'full', timeStyle: 'short' });
}
</script>

<template>
    <Head title="Booking details" />
    <AppLayout>
        <Link href="/my-bookings" class="text-sm font-semibold text-teal-800 hover:text-teal-950">← My bookings</Link>
        <article class="mt-5 max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-widest text-teal-700">Booking #{{ booking.id }}</p>
                    <h1 class="mt-2 text-2xl font-bold">{{ booking.doctor_name_snapshot }}</h1>
                </div>
                <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold capitalize text-teal-800">{{ booking.status }}</span>
            </div>

            <dl class="mt-8 grid gap-5 border-y border-slate-200 py-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500">Appointment time</dt>
                    <dd class="mt-1 font-semibold">{{ formatDate(booking.starts_at) }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500">Duration</dt>
                    <dd class="mt-1 font-semibold">{{ booking.total_duration_minutes }} minutes</dd>
                </div>
            </dl>

            <h2 class="mt-7 text-lg font-bold">Services</h2>
            <ul class="mt-3 divide-y divide-slate-200">
                <li v-for="service in booking.services" :key="service.id" class="flex items-center justify-between gap-4 py-3">
                    <span><span class="block font-medium">{{ service.pivot.service_name_snapshot }}</span><span class="text-sm text-slate-500">{{ service.pivot.duration_minutes_snapshot }} minutes</span></span>
                    <span class="font-semibold">{{ Number(service.pivot.price_snapshot).toFixed(2) }}</span>
                </li>
            </ul>

            <div class="mt-6 flex justify-between rounded-xl bg-slate-50 p-4 text-lg font-bold">
                <span>Total</span>
                <span>{{ Number(booking.total_price).toFixed(2) }}</span>
            </div>
        </article>
    </AppLayout>
</template>
