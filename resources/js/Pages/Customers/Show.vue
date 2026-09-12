<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BackLink from '@/Components/BackLink.vue';

const props = defineProps({
    customer: Object,
});

const linkForm = useForm({
    booking_id: '',
});

const unlinkForm = useForm({
    booking_id: '',
});

const link = (bookingId) => {
    linkForm.booking_id = bookingId;
    linkForm.post(route('admin.customers.link'));
};

const unlink = (bookingId) => {
    unlinkForm.booking_id = bookingId;
    unlinkForm.post(route('admin.customers.unlink'));
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
};
</script>

<template>
    <Head title="Customer Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-end justify-between border-b border-brand-primary/10 dark:border-brand-dark-border pb-4 mb-4">
                <div>
                    <h2 class="text-3xl font-semibold text-brand-primary dark:text-brand-cream">
                        {{ customer.name }}
                    </h2>
                    <p class="text-brand-muted dark:text-brand-cream/70 text-sm mt-1">
                        {{ customer.email }} · {{ customer.user ? 'Linked account' : 'No account' }}
                    </p>
                </div>
                <BackLink :href="route('admin.customers.index')" label="Customers" />
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-5xl space-y-6">
                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 shadow-ambient dark:shadow-none border border-brand-primary/10 dark:border-brand-dark-border">
                    <h3 class="text-xl font-semibold text-brand-primary dark:text-brand-cream mb-4">Bookings ({{ customer.bookings.length }})</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="text-brand-primary dark:text-brand-cream text-xs font-bold border-b border-brand-primary/30">
                                    <th class="py-4 px-6">Reference</th>
                                    <th class="py-4 px-6">Package</th>
                                    <th class="py-4 px-6">Event date</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6">Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="booking in customer.bookings" :key="booking.id">
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ booking.booking_reference }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ booking.package?.name ?? '—' }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">{{ formatDate(booking.event_date) }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ booking.status }}</td>
                                    <td class="py-5 px-6">
                                        <button
                                            v-if="booking.user_id"
                                            @click="unlink(booking.id)"
                                            :disabled="unlinkForm.processing"
                                            class="text-cyan-500 font-medium hover:text-cyan-600 transition-colors whitespace-nowrap disabled:opacity-50"
                                        >
                                            Unlink
                                        </button>
                                        <button
                                            v-else-if="customer.user"
                                            @click="link(booking.id)"
                                            :disabled="linkForm.processing"
                                            class="text-cyan-500 font-medium hover:text-cyan-600 transition-colors whitespace-nowrap disabled:opacity-50"
                                        >
                                            Link
                                        </button>
                                        <span v-else class="text-brand-muted dark:text-brand-cream/50">—</span>
                                    </td>
                                </tr>
                                <tr v-if="!customer.bookings.length">
                                    <td colspan="5" class="py-5 px-6 text-brand-muted dark:text-brand-cream/70">No bookings yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="linkForm.errors.booking_id" class="text-red-500 text-xs mt-2">{{ linkForm.errors.booking_id }}</div>
                </div>

                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 shadow-ambient dark:shadow-none border border-brand-primary/10 dark:border-brand-dark-border">
                    <h3 class="text-xl font-semibold text-brand-primary dark:text-brand-cream mb-4">Inquiries ({{ customer.inquiries.length }})</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="text-brand-primary dark:text-brand-cream text-xs font-bold border-b border-brand-primary/30">
                                    <th class="py-4 px-6">Name</th>
                                    <th class="py-4 px-6">Event date</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="inquiry in customer.inquiries" :key="inquiry.id">
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ inquiry.name }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">{{ formatDate(inquiry.event_date) }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream capitalize">{{ inquiry.status }}</td>
                                    <td class="py-5 px-6">
                                        <Link :href="route('admin.inquiries.show', inquiry.id)" class="text-cyan-500 font-medium hover:text-cyan-600 transition-colors whitespace-nowrap">
                                            View Details
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!customer.inquiries.length">
                                    <td colspan="4" class="py-5 px-6 text-brand-muted dark:text-brand-cream/70">No inquiries yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
