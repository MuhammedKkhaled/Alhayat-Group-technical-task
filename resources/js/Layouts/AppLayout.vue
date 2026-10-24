<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const success = computed(() => page.props.flash.success);

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <Link href="/doctors" class="text-lg font-bold tracking-tight text-teal-800">Alhayat Group</Link>
                <div class="flex items-center gap-4 text-sm font-medium">
                    <Link href="/doctors" class="text-slate-600 hover:text-teal-800">Doctors</Link>
                    <Link v-if="user" href="/my-bookings" class="text-slate-600 hover:text-teal-800">My bookings</Link>
                    <Link v-if="!user" href="/login" class="rounded-lg bg-teal-700 px-3 py-2 text-white hover:bg-teal-800">Sign in</Link>
                    <button v-else type="button" class="text-slate-600 hover:text-teal-800" @click="logout">Sign out</button>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">
            <div v-if="success" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ success }}
            </div>
            <slot />
        </main>
    </div>
</template>
