<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'icon', // 'icon' or 'full'
    }
});

const theme = ref('system');

const updateTheme = (newTheme) => {
    theme.value = newTheme;
    localStorage.setItem('theme', newTheme);
    applyTheme(newTheme);
};

const applyTheme = (currentTheme) => {
    if (currentTheme === 'dark' || (currentTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

onMounted(() => {
    const storedTheme = localStorage.getItem('theme');
    if (storedTheme) {
        theme.value = storedTheme;
    }
    applyTheme(theme.value);

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (theme.value === 'system') {
            applyTheme('system');
        }
    });
});
</script>

<template>
    <div :class="[
        'items-center rounded-full bg-white/50 p-1 shadow-ambient backdrop-blur-md dark:bg-brand-dark-surface/50 border border-brand-primary/10 dark:border-brand-dark-border',
        variant === 'full' ? 'grid grid-cols-3 w-full gap-1' : 'inline-flex gap-1'
    ]">
        <button
            @click="updateTheme('light')"
            :class="[
                theme === 'light' ? 'bg-white text-brand-primary shadow-sm dark:bg-brand-primary dark:text-brand-cream' : 'text-brand-primary/60 hover:text-brand-primary dark:text-brand-cream/60 dark:hover:text-brand-cream',
                variant === 'full' ? 'w-full py-1.5 px-1 flex justify-center items-center gap-1.5' : 'p-2'
            ]"
            class="rounded-full text-xs font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-brand-primary/50"
            title="Light Mode"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
            <span v-if="variant === 'full'" class="truncate tracking-tight">Light</span>
        </button>
        <button
            @click="updateTheme('dark')"
            :class="[
                theme === 'dark' ? 'bg-white text-brand-primary shadow-sm dark:bg-brand-primary dark:text-brand-cream' : 'text-brand-primary/60 hover:text-brand-primary dark:text-brand-cream/60 dark:hover:text-brand-cream',
                variant === 'full' ? 'w-full py-1.5 px-1 flex justify-center items-center gap-1.5' : 'p-2'
            ]"
            class="rounded-full text-xs font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-brand-primary/50"
            title="Dark Mode"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
            <span v-if="variant === 'full'" class="truncate tracking-tight">Dark</span>
        </button>
        <button
            @click="updateTheme('system')"
            :class="[
                theme === 'system' ? 'bg-white text-brand-primary shadow-sm dark:bg-brand-primary dark:text-brand-cream' : 'text-brand-primary/60 hover:text-brand-primary dark:text-brand-cream/60 dark:hover:text-brand-cream',
                variant === 'full' ? 'w-full py-1.5 px-1 flex justify-center items-center gap-1.5' : 'p-2'
            ]"
            class="rounded-full text-xs font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-brand-primary/50"
            title="System Mode"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
            <span v-if="variant === 'full'" class="truncate tracking-tight">System</span>
        </button>
    </div>
</template>
