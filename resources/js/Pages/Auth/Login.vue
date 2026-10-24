<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '' });
</script>

<template>
    <main class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-12">
        <section class="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-semibold uppercase tracking-widest text-teal-700">Alhayat Group</p>
            <h1 class="mt-3 text-2xl font-bold text-slate-900">Sign in to book a visit</h1>
            <p class="mt-2 text-sm text-slate-600">Use the account details provided by your clinic.</p>

            <form class="mt-8 space-y-5" @submit.prevent="form.post('/login')">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" v-model="form.email" type="email" autocomplete="username" required autofocus class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 shadow-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-700" role="alert">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" v-model="form.password" type="password" autocomplete="current-password" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 shadow-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-700" role="alert">{{ form.errors.password }}</p>
                </div>
                <button type="submit" :disabled="form.processing" class="w-full rounded-lg bg-teal-700 px-4 py-3 font-semibold text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-700 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
                    {{ form.processing ? 'Signing in…' : 'Sign in' }}
                </button>
            </form>
        </section>
    </main>
</template>
