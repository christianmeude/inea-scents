<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="bg-white dark:bg-brand-dark-base rounded-lg border-brand-primary/20 dark:border-brand-dark-border text-brand-primary dark:text-brand-cream shadow-sm min-h-[44px] focus:border-brand-primary focus:ring-brand-primary"
        v-model="model"
        ref="input"
    />
</template>
