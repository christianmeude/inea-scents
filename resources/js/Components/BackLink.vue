<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: { type: String, required: true },
    label: { type: String, required: true },
});
</script>

<template>
    <Link :href="href" class="text-sm text-cyan-500 font-medium hover:text-cyan-600">
        ← Back to {{ label }}
    </Link>
</template>
