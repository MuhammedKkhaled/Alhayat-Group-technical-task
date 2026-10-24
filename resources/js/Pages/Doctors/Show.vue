<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ServiceSelector from '../../Components/ServiceSelector.vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({ doctor: { type: Object, required: true } });
const selectedServiceIds = ref([]);
const message = ref('');

function continueBooking() {
    if (!selectedServiceIds.value.length) {
        message.value = 'Select at least one service to continue.';
        return;
    }

    router.get('/bookings/create', {
        doctor_id: props.doctor.id,
        service_ids: selectedServiceIds.value,
    });
}
</script>

<template>
    <Head :title="doctor.name" />
    <AppLayout>
        <Link href="/doctors" class="text-sm font-semibold text-teal-800 hover:text-teal-950">← All doctors</Link>
        <div class="mt-5 grid gap-8 lg:grid-cols-[1fr_22rem]">
            <section>
                <p class="text-sm font-semibold uppercase tracking-widest text-teal-700">Choose services</p>
                <h1 class="mt-2 text-3xl font-bold">{{ doctor.name }}</h1>
                <p class="mt-2 text-slate-600">Select every service you want in this appointment.</p>
                <div class="mt-7">
                    <ServiceSelector v-model="selectedServiceIds" :services="doctor.services" />
                </div>
            </section>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-6">
                <h2 class="font-bold">Your selection</h2>
                <p class="mt-2 text-sm text-slate-600">{{ selectedServiceIds.length }} service{{ selectedServiceIds.length === 1 ? '' : 's' }} selected</p>
                <p v-if="message" class="mt-3 text-sm text-red-700" role="alert">{{ message }}</p>
                <button type="button" class="mt-6 w-full rounded-lg bg-teal-700 px-4 py-3 font-semibold text-white hover:bg-teal-800" @click="continueBooking">Continue to date & time</button>
            </aside>
        </div>
    </AppLayout>
</template>
