<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    inquiry: Object,
});

const statusOrder = { new: 0, contacted: 1, booked: 2, closed: 3 };
const nextStatuses = ['contacted', 'closed'].filter(
    (s) => statusOrder[s] > statusOrder[props.inquiry.status]
);

const form = useForm({
    status: props.inquiry.status,
    archived: !!props.inquiry.archived,
});

const submit = () => {
    form.put(route('admin.inquiries.update', props.inquiry.id));
};

const formatDate = (dateString) => {
    if (!dateString) return 'No date given';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
};
</script>

<template>
    <Head title="Inquiry Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-end justify-between border-b border-brand-primary/10 dark:border-brand-dark-border pb-4 mb-4">
                <div>
                    <h2 class="text-3xl font-semibold text-brand-primary dark:text-brand-cream">
                        {{ inquiry.name }}
                    </h2>
                    <p class="text-brand-muted dark:text-brand-cream/70 text-sm mt-1">
                        {{ inquiry.email }} · {{ inquiry.phone }}
                    </p>
                </div>
                <Link :href="route('admin.inquiries.index')" class="text-sm text-cyan-500 font-medium hover:text-cyan-600">
                    ← Back to Inquiries
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-3xl">
                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 shadow-ambient dark:shadow-none border border-brand-primary/10 dark:border-brand-dark-border">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm mb-8">
                        <div>
                            <dt class="font-bold text-brand-primary dark:text-brand-cream text-xs uppercase">Event Date</dt>
                            <dd class="mt-1 text-brand-primary dark:text-brand-cream">{{ formatDate(inquiry.event_date) }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold text-brand-primary dark:text-brand-cream text-xs uppercase">Status</dt>
                            <dd class="mt-1 text-brand-primary dark:text-brand-cream capitalize">{{ inquiry.status }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="font-bold text-brand-primary dark:text-brand-cream text-xs uppercase">Message</dt>
                            <dd class="mt-1 text-brand-primary dark:text-brand-cream whitespace-pre-wrap">{{ inquiry.message }}</dd>
                        </div>
                    </dl>

                    <form @submit.prevent="submit" class="flex flex-wrap items-end gap-4">
                        <div v-if="nextStatuses.length">
                            <label class="block text-xs font-bold uppercase text-brand-primary dark:text-brand-cream mb-1">Move status</label>
                            <select
                                v-model="form.status"
                                class="px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-sm bg-white dark:bg-brand-dark-surface text-brand-primary dark:text-brand-cream focus:outline-none focus:border-brand-primary"
                            >
                                <option :value="inquiry.status">{{ inquiry.status }} (current)</option>
                                <option v-for="s in nextStatuses" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                        </div>
                        <p v-else-if="!nextStatuses.length" class="text-sm text-brand-muted dark:text-brand-cream/70 pb-2">
                            Terminal state — status locked, archive toggle still available.
                        </p>
                        <label class="inline-flex items-center gap-2 text-sm text-brand-primary dark:text-brand-cream pb-2">
                            <input type="checkbox" v-model="form.archived" class="rounded">
                            Archived
                        </label>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-full bg-brand-primary px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-primary/90 disabled:opacity-50 transition-all"
                        >
                            Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
