<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    bookings: Object,
    events: Object,
    alerts: Object,
    revenue: Number,
    filters: Object,
});

const method = ref(props.filters.method || '');
const status = ref(props.filters.status || '');
const search = ref(props.filters.search || '');

watch([method, status, search], ([methodValue, statusValue, searchValue]) => {
    router.get(
        route('admin.payments.index'),
        { method: methodValue, status: statusValue, search: searchValue },
        { preserveState: true, preserveScroll: true, replace: true }
    );
});

const formatDate = (dateString) => {
    if (!dateString) return '—';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
};

const formatMoney = (value) => {
    if (value === null || value === undefined) return '—';
    return '₱' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2 });
};
</script>

<template>
    <Head title="Payments" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-end justify-between border-b border-brand-primary/10 dark:border-brand-dark-border pb-4 mb-4">
                <div>
                    <h2 class="text-3xl font-semibold text-brand-primary dark:text-brand-cream">
                        Payments
                    </h2>
                    <p class="text-brand-muted dark:text-brand-cream/70 text-sm mt-1">
                        Read-only ledger over bookings and webhook events. Confirmed revenue: {{ formatMoney(revenue) }}.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl space-y-6">
                <div v-if="alerts.rejectedCount > 0 || alerts.ignoredCount > 0 || alerts.unmatchedCount > 0" class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-3xl p-6 text-sm text-red-800 dark:text-red-200">
                    <p class="font-bold mb-1">Webhook attention needed</p>
                    <ul class="list-disc ml-5 space-y-1">
                        <li v-if="alerts.rejectedCount > 0">Rejected signatures: {{ alerts.rejectedCount }} (latest {{ alerts.latestRejectedAt || '—' }})</li>
                        <li v-if="alerts.ignoredCount > 0">Ignored event types: {{ alerts.ignoredCount }} (latest {{ alerts.latestIgnoredAt || '—' }})</li>
                        <li v-if="alerts.unmatchedCount > 0">Paid with no matching booking: {{ alerts.unmatchedCount }} (latest {{ alerts.latestUnmatchedAt || '—' }})</li>
                    </ul>
                </div>

                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 shadow-ambient dark:shadow-none border border-brand-primary/10 dark:border-brand-dark-border relative">
                    <h3 class="text-xl font-semibold text-brand-primary dark:text-brand-cream mb-4">Bookings</h3>
                    <div class="flex flex-wrap items-center gap-4 mb-8">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search reference or name"
                            class="w-72 px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-sm text-brand-primary dark:text-brand-cream focus:outline-none focus:border-brand-primary bg-white dark:bg-brand-dark-surface placeholder-brand-muted/40"
                        >
                        <select
                            v-model="method"
                            class="px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-sm text-brand-primary dark:text-brand-cream bg-white dark:bg-brand-dark-surface focus:outline-none focus:border-brand-primary"
                        >
                            <option value="">All methods</option>
                            <option value="credit_card">Credit card</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank transfer</option>
                        </select>
                        <select
                            v-model="status"
                            class="px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-sm text-brand-primary dark:text-brand-cream bg-white dark:bg-brand-dark-surface focus:outline-none focus:border-brand-primary"
                        >
                            <option value="">All statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto pb-4">
                        <table class="w-full text-left border-collapse border-b border-brand-primary/10 dark:border-brand-dark-border">
                            <thead>
                                <tr class="text-brand-primary dark:text-brand-cream text-xs font-bold border-b border-brand-primary/30">
                                    <th class="py-4 px-6">Reference</th>
                                    <th class="py-4 px-6">Customer</th>
                                    <th class="py-4 px-6">Method</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6">Total</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="booking in bookings.data" :key="booking.id" class="transition-colors duration-150">
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ booking.booking_reference }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ booking.customer_name }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ booking.payment_method || '—' }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ booking.status }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">{{ formatMoney(booking.total_price) }}</td>
                                </tr>
                                <tr v-if="!bookings.data.length">
                                    <td colspan="5" class="py-5 px-6 text-brand-muted dark:text-brand-cream/70">No bookings match.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end" v-if="bookings.links && bookings.links.length > 3">
                        <div class="flex gap-1">
                            <Link
                                v-for="(link, k) in bookings.links"
                                :key="k"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1 rounded-md border text-sm"
                                :class="link.active ? 'bg-brand-primary text-white border-brand-primary' : 'border-gray-200 dark:border-brand-dark-border text-gray-500 hover:bg-gray-50'"
                            />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 shadow-ambient dark:shadow-none border border-brand-primary/10 dark:border-brand-dark-border relative">
                    <h3 class="text-xl font-semibold text-brand-primary dark:text-brand-cream mb-4">Webhook events</h3>
                    <div class="overflow-x-auto pb-4">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="text-brand-primary dark:text-brand-cream text-xs font-bold border-b border-brand-primary/30">
                                    <th class="py-4 px-6">Event</th>
                                    <th class="py-4 px-6">Type</th>
                                    <th class="py-4 px-6">Booking ref</th>
                                    <th class="py-4 px-6">Received</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="event in events.data" :key="event.id">
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ event.event_id.slice(0, 12) }}…</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ event.event_type }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">{{ event.booking_reference || '—' }}</td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">{{ formatDate(event.created_at) }}</td>
                                </tr>
                                <tr v-if="!events.data.length">
                                    <td colspan="4" class="py-5 px-6 text-brand-muted dark:text-brand-cream/70">No webhook events yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
