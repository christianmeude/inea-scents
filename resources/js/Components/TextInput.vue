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
        class="rounded-lg border-brand-primary/20 text-brand-primary shadow-sm focus:border-brand-primary focus:ring-brand-primary"
        v-model="model"
        ref="input"
    />
</template>
