<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Page not found" />
    <main class="flex min-h-screen items-center justify-center bg-slate-50 px-6 text-center text-slate-900">
        <section class="max-w-lg">
            <p class="text-sm font-bold uppercase tracking-[0.3em] text-teal-700">404</p>
            <h1 class="mt-4 text-4xl font-bold tracking-tight">Page not found</h1>
            <p class="mt-4 text-slate-600">The address may be incorrect, or the page is no longer available.</p>
            <Link href="/doctors" class="mt-8 inline-flex rounded-lg bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Browse doctors</Link>
        </section>
    </main>
</template>
