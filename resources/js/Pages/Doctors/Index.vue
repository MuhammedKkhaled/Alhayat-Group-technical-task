<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({ doctors: { type: Array, required: true } });
</script>

<template>
    <Head title="Doctors" />
    <AppLayout>
        <section class="max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-widest text-teal-700">Doctor booking</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Choose your doctor</h1>
            <p class="mt-3 max-w-2xl text-slate-600">Select a doctor, choose the services you need, then find an available appointment time.</p>
        </section>

        <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article v-for="doctor in doctors" :key="doctor.id" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">{{ doctor.name.charAt(0) }}</div>
                <h2 class="mt-4 text-lg font-bold">{{ doctor.name }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ doctor.code }}</p>
                <Link :href="`/doctors/${doctor.id}`" class="mt-6 inline-flex rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">View services</Link>
            </article>
        </section>
    </AppLayout>
</template>
