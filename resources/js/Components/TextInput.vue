<script setup>
import { onMounted, ref, useAttrs } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const props = defineProps({
    type: {
        type: String,
        default: 'text',
    }
});

const input = ref(null);
const showPassword = ref(false);
const attrs = useAttrs();

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>
<script>
export default {
    inheritAttrs: false
}
</script>

<template>
    <div class="relative w-full" :class="attrs.class">
        <input
            v-bind="{ ...$attrs, class: undefined }"
            :type="props.type === 'password' ? (showPassword ? 'text' : 'password') : props.type"
            :class="['w-full rounded-full border border-white/20 bg-[#8B5D76]/70 py-3 text-white shadow-ambient backdrop-blur-md transition-all duration-300 placeholder:text-white/70 focus:border-white focus:bg-[#8B5D76]/90 focus:outline-none focus:ring-2 focus:ring-white/30 dark:border-white/10 dark:bg-brand-primary/40 dark:text-brand-cream dark:placeholder:text-brand-cream/60 dark:focus:border-brand-primary dark:focus:bg-brand-primary/60 dark:focus:ring-brand-primary/40', props.type === 'password' ? 'pl-5 pr-12' : 'px-5']"
            v-model="model"
            ref="input"
        />
        <button
            v-if="props.type === 'password'"
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-white/70 hover:text-white dark:text-brand-cream/60 dark:hover:text-brand-cream focus:outline-none rounded-full transition-colors"
        >
            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
        </button>
    </div>
</template>
