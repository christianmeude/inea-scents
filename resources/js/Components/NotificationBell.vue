<script setup>
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const POLL_MS = 30000;
const MAX_BACKOFF_MS = 300000; // 5 minutes

const unreadCount = ref(0);
const notifications = ref([]);
const open = ref(false);
let timer = null;
let currentInterval = POLL_MS;
let isMounted = false;

const scheduleNextPoll = () => {
    if (timer) clearTimeout(timer);
    if (!isMounted || document.visibilityState === 'hidden') return;
    
    timer = setTimeout(() => {
        fetchNotifications();
    }, currentInterval);
};

const fetchNotifications = async () => {
    if (!isMounted) return;
    try {
        const response = await fetch(route('admin.notifications.index'), {
            headers: { Accept: 'application/json' },
        });
        
        if (!isMounted) return;
        
        if (!response.ok) {
            currentInterval = Math.min(currentInterval * 2, MAX_BACKOFF_MS);
            scheduleNextPoll();
            return;
        }
        
        const data = await response.json();
        unreadCount.value = data.unread_count ?? 0;
        notifications.value = data.notifications ?? [];
        
        currentInterval = POLL_MS;
        scheduleNextPoll();
    } catch {
        if (!isMounted) return;
        currentInterval = Math.min(currentInterval * 2, MAX_BACKOFF_MS);
        scheduleNextPoll();
    }
};

const markRead = async (ids) => {
    try {
        await fetch(route('admin.notifications.read'), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({ ids }),
        });
        await fetchNotifications();
    } catch {
        // Ignore; next poll refreshes.
    }
};

const openItem = (notification) => {
    if (!notification.read_at) {
        markRead([notification.id]);
    }
    open.value = false;
    if (notification.link) {
        router.visit(notification.link);
    }
};

const onVisibilityChange = () => {
    if (document.visibilityState === 'visible') {
        currentInterval = POLL_MS;
        if (timer) clearTimeout(timer);
        fetchNotifications();
    } else {
        if (timer) clearTimeout(timer);
    }
};

onMounted(() => {
    isMounted = true;
    fetchNotifications();
    document.addEventListener('visibilitychange', onVisibilityChange);
});

onUnmounted(() => {
    isMounted = false;
    if (timer) clearTimeout(timer);
    document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>

<template>
    <div class="relative">
        <button
            type="button"
            @click="open = !open"
            aria-label="Notifications"
            class="relative inline-flex items-center justify-center w-10 h-10 rounded-full text-brand-primary dark:text-brand-cream hover:bg-brand-primary/5 dark:hover:bg-white/5 focus:outline-none transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute top-0.5 right-0.5 inline-flex items-center justify-center min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-xs font-bold leading-none"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 mt-2 w-80 max-w-[90vw] rounded-2xl border border-brand-primary/10 dark:border-brand-dark-border bg-white dark:bg-brand-dark-surface shadow-xl z-30 overflow-hidden"
        >
            <div class="flex items-center justify-between px-4 py-3 border-b border-brand-primary/10 dark:border-brand-dark-border">
                <p class="text-sm font-semibold text-brand-primary dark:text-brand-cream">Notifications</p>
                <button
                    v-if="unreadCount > 0"
                    @click="markRead([])"
                    class="text-xs font-medium text-cyan-600 hover:text-cyan-700 transition-colors"
                >
                    Mark all read
                </button>
            </div>
            <div class="max-h-80 overflow-y-auto">
                <p v-if="!notifications.length" class="px-4 py-6 text-sm text-brand-muted dark:text-brand-cream/60 text-center">
                    You're all caught up.
                </p>
                <button
                    v-for="notification in notifications"
                    :key="notification.id"
                    type="button"
                    @click="openItem(notification)"
                    class="block w-full text-left px-4 py-3 border-b border-brand-primary/5 dark:border-white/5 last:border-0 hover:bg-brand-primary/5 dark:hover:bg-white/5 transition-colors"
                    :class="{ 'bg-brand-primary/[0.03] dark:bg-white/[0.03]': !notification.read_at }"
                >
                    <p class="text-sm font-semibold text-brand-primary dark:text-brand-cream">{{ notification.title }}</p>
                    <p class="text-xs text-brand-muted dark:text-brand-cream/70 mt-0.5">{{ notification.body }}</p>
                </button>
            </div>
        </div>
    </div>
</template>
