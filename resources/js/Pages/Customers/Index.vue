<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Chip from '@/Components/Chip.vue';
import DataTable from '@/Components/DataTable.vue';
import { accountTone } from '@/Components/tones.js';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (searchValue) => {
    router.get(
        route('admin.customers.index'),
        { search: searchValue },
        { preserveState: true, preserveScroll: true, replace: true }
    );
});

const formatDate = (dateString) => {
    if (!dateString) return '—';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
};

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email', hideBelow: 'hidden md:table-cell' },
    { key: 'account', label: 'Account' },
    { key: 'bookings', label: 'Bookings' },
    { key: 'inquiries', label: 'Inquiries' },
    { key: 'activity', label: 'Last activity' },
    { key: 'action', label: 'Action' },
];
</script>

<template>
    <Head title="Customers" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-end justify-between border-b border-brand-primary/10 dark:border-brand-dark-border pb-4 mb-4">
                <div>
                    <h2 class="text-3xl font-semibold text-brand-primary dark:text-brand-cream">
                        Customers
                    </h2>
                    <p class="text-brand-muted dark:text-brand-cream/70 text-sm mt-1">
                        Customer directory by email, with booking and inquiry history.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl">
                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 border border-brand-primary/10 dark:border-brand-dark-border">
                    <DataTable
                        :columns="columns"
                        :items="customers.data"
                        :links="customers.links"
                        row-key="email"
                        empty-text="No customers found."
                    >
                        <template #filters>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search name or email"
                                class="w-72 px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-sm text-brand-primary dark:text-brand-cream focus:outline-none focus:border-brand-primary bg-white dark:bg-brand-dark-surface placeholder-brand-muted/40"
                            >
                        </template>

                        <template #row="{ item: customer }">
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">
                                {{ customer.name || '—' }}
                            </td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream hidden md:table-cell">
                                {{ customer.email }}
                            </td>
                            <td class="py-4 px-4">
                                <Chip :tone="accountTone(customer.user_id)">
                                    {{ customer.user_id ? 'USER' : 'GUEST' }}
                                </Chip>
                            </td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">
                                {{ customer.bookings_count }}
                            </td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">
                                {{ customer.inquiries_count }}
                            </td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">
                                {{ formatDate(customer.last_activity_at) }}
                            </td>
                            <td class="py-4 px-4">
                                <Link :href="route('admin.customers.show', customer.email)" class="text-cyan-500 font-medium hover:text-cyan-600 transition-colors whitespace-nowrap">
                                    View Details
                                </Link>
                            </td>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
