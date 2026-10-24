<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import BookingSteps from '../../Components/BookingSteps.vue';
import BookingSummary from '../../Components/BookingSummary.vue';
import DatePicker from '../../Components/DatePicker.vue';
import TimeSlotPicker from '../../Components/TimeSlotPicker.vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    doctor: { type: Object, required: true },
    selectedServiceIds: { type: Array, required: true },
});

const form = useForm({
    doctor_id: props.doctor.id,
    service_ids: props.selectedServiceIds,
    date: '',
    start_time: '',
});
const slots = ref([]);
const loadingSlots = ref(false);
const preview = ref(null);
const feedback = ref('');
const today = new Date().toISOString().slice(0, 10);
const idempotencyKey = window.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random()}`;

async function fetchSlots() {
    form.start_time = '';
    preview.value = null;
    feedback.value = '';
    slots.value = [];

    if (!form.date) {
        return;
    }

    loadingSlots.value = true;
    try {
        const response = await window.axios.get(`/doctors/${props.doctor.id}/availability`, {
            params: { date: form.date, service_ids: form.service_ids },
        });
        slots.value = response.data.slots;
    } catch (error) {
        feedback.value = error.response?.data?.message ?? 'Unable to load availability. Please try again.';
    } finally {
        loadingSlots.value = false;
    }
}

async function loadPreview() {
    if (!form.date || !form.start_time) {
        feedback.value = 'Choose a date and time before reviewing your booking.';
        return;
    }

    feedback.value = '';
    form.clearErrors();
    try {
        const response = await window.axios.post('/bookings/preview', form.data());
        preview.value = response.data;
    } catch (error) {
        preview.value = null;
        feedback.value = error.response?.data?.message ?? 'This time is no longer available.';
        await fetchSlots();
    }
}

function confirm() {
    form.post('/bookings', {
        headers: { 'Idempotency-Key': idempotencyKey },
        onError: async () => {
            preview.value = null;
            feedback.value = form.errors.start_time ?? 'Review the available times and try again.';
            await fetchSlots();
        },
    });
}

watch(() => form.date, fetchSlots);
watch(() => form.start_time, () => {
    preview.value = null;
    form.clearErrors('start_time');
});
</script>

<template>
    <Head title="Choose appointment time" />
    <AppLayout>
        <BookingSteps :current="preview ? 3 : 2" />
        <Link :href="`/doctors/${doctor.id}`" class="text-sm font-semibold text-teal-800 hover:text-teal-950">← Edit services</Link>
        <div class="mt-5 grid gap-8 lg:grid-cols-[1fr_24rem]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-widest text-teal-700">{{ doctor.name }}</p>
                <h1 class="mt-2 text-2xl font-bold">Choose a date and time</h1>
                <p class="mt-2 text-sm text-slate-600">Times are held only after you confirm the booking.</p>

                <div class="mt-7 space-y-7">
                    <DatePicker v-model="form.date" :min="today" />
                    <TimeSlotPicker v-model="form.start_time" :slots="slots" :loading="loadingSlots" :has-date="Boolean(form.date)" />
                    <p v-if="feedback || form.errors.start_time" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ form.errors.start_time || feedback }}</p>
                    <button type="button" :disabled="!form.start_time || loadingSlots" class="rounded-lg bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-50" @click="loadPreview">Review booking</button>
                </div>
            </section>

            <div class="space-y-4">
                <BookingSummary v-if="preview" :preview="preview" />
                <button v-if="preview" type="button" :disabled="form.processing" class="w-full rounded-lg bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800 disabled:cursor-wait disabled:opacity-60" @click="confirm">
                    {{ form.processing ? 'Confirming…' : 'Confirm booking' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>
