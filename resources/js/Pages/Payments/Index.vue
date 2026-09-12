<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Chip from '@/Components/Chip.vue';
import DataTable from '@/Components/DataTable.vue';
import { methodTone, statusTone } from '@/Components/tones.js';
import { Head, router } from '@inertiajs/vue3';
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

const bookingColumns = [
    { key: 'reference', label: 'Reference' },
    { key: 'customer', label: 'Customer' },
    { key: 'method', label: 'Method' },
    { key: 'status', label: 'Status' },
    { key: 'total', label: 'Total' },
];

const eventColumns = [
    { key: 'event', label: 'Event' },
    { key: 'type', label: 'Type' },
    { key: 'bookingRef', label: 'Booking ref' },
    { key: 'received', label: 'Received' },
];
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
                        Read-only ledger over bookings and webhook events. Confirmed revenue (all time): {{ formatMoney(revenue) }}.
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

                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 border border-brand-primary/10 dark:border-brand-dark-border">
                    <h3 class="text-xl font-semibold text-brand-primary dark:text-brand-cream mb-4">Bookings</h3>
                    <DataTable
                        :columns="bookingColumns"
                        :items="bookings.data"
                        :links="bookings.links"
                        empty-text="No bookings match."
                    >
                        <template #filters>
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
                                <option value="online">Online</option>
                                <option value="cash">Cash</option>
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
                        </template>

                        <template #row="{ item: booking }">
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">{{ booking.booking_reference }}</td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">{{ booking.customer_name }}</td>
                            <td class="py-4 px-4">
                                <Chip :tone="methodTone(booking.payment_method)" class="uppercase">
                                    {{ booking.payment_method || '—' }}
                                </Chip>
                            </td>
                            <td class="py-4 px-4">
                                <Chip :tone="statusTone(booking.status)">
                                    {{ booking.status }}
                                </Chip>
                            </td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">{{ formatMoney(booking.total_price) }}</td>
                        </template>
                    </DataTable>
                </div>

                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 border border-brand-primary/10 dark:border-brand-dark-border">
                    <h3 class="text-xl font-semibold text-brand-primary dark:text-brand-cream mb-1">Webhook events</h3>
                    <p class="text-xs text-brand-muted dark:text-brand-cream/60 mb-4">Only verified PayMongo payments land here — rejected probes never create rows.</p>
                    <DataTable
                        :columns="eventColumns"
                        :items="events.data"
                        empty-text="No webhook events yet."
                    >
                        <template #row="{ item: event }">
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">{{ event.event_id.slice(0, 12) }}…</td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">{{ event.event_type }}</td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">{{ event.booking_reference || '—' }}</td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">{{ formatDate(event.created_at) }}</td>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
