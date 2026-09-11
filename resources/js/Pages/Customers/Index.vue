<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 shadow-ambient dark:shadow-none border border-brand-primary/10 dark:border-brand-dark-border relative">
                    <div class="flex flex-wrap items-center gap-4 mb-8">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search name or email"
                            class="w-72 px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-sm text-brand-primary dark:text-brand-cream focus:outline-none focus:border-brand-primary bg-white dark:bg-brand-dark-surface placeholder-brand-muted/40"
                        >
                    </div>

                    <div class="overflow-x-auto pb-4">
                        <table class="w-full text-left border-collapse border-b border-brand-primary/10 dark:border-brand-dark-border">
                            <thead>
                                <tr class="text-brand-primary dark:text-brand-cream text-xs font-bold border-b border-brand-primary/30">
                                    <th class="py-4 px-6">Name</th>
                                    <th class="py-4 px-6">Email</th>
                                    <th class="py-4 px-6">Account</th>
                                    <th class="py-4 px-6">Bookings</th>
                                    <th class="py-4 px-6">Inquiries</th>
                                    <th class="py-4 px-6">Last activity</th>
                                    <th class="py-4 px-6">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr
                                    v-for="customer in customers.data"
                                    :key="customer.email"
                                    class="transition-colors duration-150"
                                >
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">
                                        {{ customer.name || '—' }}
                                    </td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">
                                        {{ customer.email }}
                                    </td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">
                                        {{ customer.user_id ? 'Linked' : 'Guest' }}
                                    </td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">
                                        {{ customer.bookings_count }}
                                    </td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">
                                        {{ customer.inquiries_count }}
                                    </td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">
                                        {{ formatDate(customer.last_activity_at) }}
                                    </td>
                                    <td class="py-5 px-6">
                                        <Link :href="route('admin.customers.show', customer.email)" class="text-cyan-500 font-medium hover:text-cyan-600 transition-colors whitespace-nowrap">
                                            View Details
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end" v-if="customers.links && customers.links.length > 3">
                        <div class="flex gap-1">
                            <Link
                                v-for="(link, k) in customers.links"
                                :key="k"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1 rounded-md border text-sm"
                                :class="link.active ? 'bg-brand-primary text-white border-brand-primary' : 'border-gray-200 dark:border-brand-dark-border text-gray-500 hover:bg-gray-50'"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
