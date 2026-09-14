<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Chip from '@/Components/Chip.vue';
import DataTable from '@/Components/DataTable.vue';
import { statusTone } from '@/Components/tones.js';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    inquiries: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const archived = ref(props.filters.archived || '');

watch([search, status, archived], ([searchValue, statusValue, archivedValue]) => {
    router.get(
        route('admin.inquiries.index'),
        { search: searchValue, status: statusValue, archived: archivedValue },
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
    { key: 'eventDate', label: 'Event Date' },
    { key: 'status', label: 'Status' },
    { key: 'action', label: 'Action' },
];
</script>

<template>
    <Head title="Inquiries" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-end justify-between border-b border-brand-primary/10 dark:border-brand-dark-border pb-4 mb-4">
                <div>
                    <h2 class="text-3xl font-semibold text-brand-primary dark:text-brand-cream">
                        Inquiries
                    </h2>
                    <p class="text-brand-muted dark:text-brand-cream/70 text-sm mt-1">
                        Triage event consultation requests from the landing page.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl">
                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 border border-brand-primary/10 dark:border-brand-dark-border">
                    <DataTable
                        :columns="columns"
                        :items="inquiries.data"
                        :links="inquiries.links"
                        empty-text="No inquiries found."
                    >
                        <template #filters>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search name or email"
                                class="w-72 px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-sm text-brand-primary dark:text-brand-cream focus:outline-none focus:border-brand-primary bg-white dark:bg-brand-dark-surface placeholder-brand-muted/40"
                            >
                            <select
                                v-model="status"
                                class="pl-4 pr-10 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-sm text-brand-primary dark:text-brand-cream bg-white dark:bg-brand-dark-surface focus:outline-none focus:border-brand-primary"
                            >
                                <option value="">All statuses</option>
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="booked">Booked</option>
                                <option value="closed">Closed</option>
                            </select>
                            <label class="inline-flex items-center gap-2 text-sm text-brand-primary dark:text-brand-cream">
                                <input type="checkbox" v-model="archived" true-value="1" false-value="" class="rounded">
                                Show archived
                            </label>
                        </template>

                        <template #row="{ item: inquiry }">
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">
                                {{ inquiry.name }}
                            </td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream hidden md:table-cell">
                                {{ inquiry.email }}
                            </td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">
                                {{ formatDate(inquiry.event_date) }}
                            </td>
                            <td class="py-4 px-4">
                                <Chip :tone="statusTone(inquiry.status)" class="capitalize">
                                    {{ inquiry.status }}
                                </Chip>
                            </td>
                            <td class="py-4 px-4">
                                <Link :href="route('admin.inquiries.show', inquiry.id)" class="text-cyan-500 font-medium hover:text-cyan-600 transition-colors whitespace-nowrap">
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
