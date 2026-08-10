<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full px-4 py-2 rounded-full text-start text-base font-medium text-white bg-brand-primary focus:outline-none transition duration-150 ease-in-out dark:bg-brand-dark-accent dark:text-brand-cream'
        : 'block w-full px-4 py-2 rounded-full border-transparent text-start text-base font-medium text-brand-primary dark:text-brand-cream hover:text-brand-primary dark:text-brand-cream hover:bg-brand-primary/10 focus:outline-none focus:text-brand-primary dark:text-brand-cream focus:bg-brand-primary/10 transition duration-150 ease-in-out dark:text-brand-cream dark:hover:bg-brand-dark-accent dark:focus:bg-brand-dark-accent',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
