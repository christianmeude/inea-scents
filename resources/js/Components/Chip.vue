<script setup>
import { computed } from 'vue';

const props = defineProps({
    tone: {
        type: String,
        default: 'gray',
        validator: (value) => ['green', 'amber', 'red', 'gray', 'blue', 'cyan'].includes(value),
    },
});

const tones = {
    green: 'bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-300',
    amber: 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
    red: 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-300',
    gray: 'bg-gray-200/70 text-gray-700 dark:bg-white/10 dark:text-gray-300',
    blue: 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-300',
    cyan: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-500/15 dark:text-cyan-300',
};

const classes = computed(() => tones[props.tone] ?? tones.gray);
</script>

<template>
    <span
        class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold"
        :class="classes"
    >
        <slot />
    </span>
</template>
