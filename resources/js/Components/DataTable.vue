<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    columns: {
        type: Array,
        required: true,
    },
    items: {
        type: Array,
        default: () => [],
    },
    links: {
        type: Array,
        default: () => [],
    },
    emptyText: {
        type: String,
        default: 'No records found.',
    },
    rowKey: {
        type: String,
        default: 'id',
    },
});
</script>

<template>
    <div>
        <div v-if="$slots.filters" class="flex flex-wrap items-center gap-3 mb-6">
            <slot name="filters" />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs font-semibold uppercase tracking-wide text-brand-muted dark:text-brand-cream/60 border-b border-brand-primary/15 dark:border-brand-dark-border">
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="py-3 px-4 first:pl-1 last:pr-1"
                            :class="column.hideBelow ?? ''"
                        >
                            {{ column.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-brand-primary/10 dark:divide-white/10">
                    <tr
                        v-for="item in items"
                        :key="item[rowKey]"
                        class="transition-colors duration-150 hover:bg-brand-primary/5 dark:hover:bg-white/5"
                    >
                        <slot name="row" :item="item" />
                    </tr>
                    <tr v-if="!items.length">
                        <td :colspan="columns.length" class="py-6 px-4 first:pl-1 text-brand-muted dark:text-brand-cream/60">
                            {{ emptyText }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end" v-if="links && links.length > 3">
            <div class="flex gap-1">
                <template v-for="(link, k) in links" :key="k">
                    <span
                        v-if="!link.url"
                        v-html="link.label"
                        aria-disabled="true"
                        class="px-3 py-1 rounded-md border text-sm border-gray-200 dark:border-brand-dark-border text-gray-500 opacity-50 cursor-not-allowed"
                    />
                    <Link
                        v-else
                        :href="link.url"
                        v-html="link.label"
                        class="px-3 py-1 rounded-md border text-sm"
                        :class="link.active ? 'bg-brand-primary text-white border-brand-primary' : 'border-gray-200 dark:border-brand-dark-border text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
