<script setup>
import { ref, onMounted } from 'vue';

// Landing-standard 2-state toggle (spec-standard, native Vue port).
// Key `inea-theme`, `.dark` class, OS preference only on first load.
const theme = ref('light');

const applyTheme = (next) => {
    theme.value = next;
    document.documentElement.classList.toggle('dark', next === 'dark');
    try {
        localStorage.setItem('inea-theme', next);
    } catch {
        // Private mode: theme simply won't persist.
    }
};

const toggleTheme = () => {
    applyTheme(theme.value === 'light' ? 'dark' : 'light');
};

const migrateLegacyKey = () => {
    let legacy = null;
    try {
        legacy = localStorage.getItem('theme');
    } catch {
        return;
    }
    if (legacy === 'dark' || legacy === 'light') {
        applyTheme(legacy);
    } else if (legacy === 'system') {
        applyTheme(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    }
    try {
        localStorage.removeItem('theme');
    } catch {
        // Ignore cleanup failures.
    }
};

onMounted(() => {
    let stored = null;
    try {
        stored = localStorage.getItem('inea-theme');
    } catch {
        stored = null;
    }
    if (stored === 'dark' || stored === 'light') {
        theme.value = stored;
    } else {
        migrateLegacyKey();
    }
});
</script>

<template>
    <button
        type="button"
        @click="toggleTheme"
        :aria-label="theme === 'light' ? 'Switch to dark theme' : 'Switch to light theme'"
        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-brand-primary/20 dark:border-brand-cream/20 text-brand-primary dark:text-brand-cream hover:bg-brand-primary/5 dark:hover:bg-brand-cream/10 transition-colors"
    >
        <svg v-if="theme === 'light'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z" /></svg>
        <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="4" /><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" /></svg>
    </button>
</template>
