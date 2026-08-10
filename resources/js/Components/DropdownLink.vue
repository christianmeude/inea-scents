<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-sm leading-5 text-brand-primary dark:text-brand-cream transition duration-150 ease-in-out hover:bg-brand-primary/5 dark:hover:bg-brand-dark-accent focus:bg-brand-primary/5 dark:focus:bg-brand-dark-accent focus:outline-none"
    >
        <slot />
    </Link>
</template>
