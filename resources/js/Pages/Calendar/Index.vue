<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ViewBookingModal from '../Bookings/Partials/ViewBookingModal.vue';

const props = defineProps({
    view: { type: String, default: 'month' },
    bookings: { type: Object, default: () => ({ data: [] }) },
    blockedDates: { type: Array, default: () => [] },
    availability: { type: Object, default: () => ({}) },
    yearAvailability: { type: Object, default: () => ({}) },
    currentMonth: Number,
    currentYear: Number,
});

const isViewModalOpen = ref(false);
const selectedBooking = ref(null);

const openViewModal = (booking) => {
    selectedBooking.value = booking;
    isViewModalOpen.value = true;
};

const goMonth = (month, year) => {
    router.get(route('admin.calendar.index'), { view: 'month', month, year }, { preserveScroll: true, preserveState: true });
};

const goYear = (year) => {
    router.get(route('admin.calendar.index'), { view: 'year', year, month: props.currentMonth }, { preserveScroll: true, preserveState: true });
};

const nextMonth = () => {
    let nextM = props.currentMonth + 1;
    let nextY = props.currentYear;
    if (nextM > 12) {
        nextM = 1;
        nextY += 1;
    }
    goMonth(nextM, nextY);
};

const prevMonth = () => {
    let prevM = props.currentMonth - 1;
    let prevY = props.currentYear;
    if (prevM < 1) {
        prevM = 12;
        prevY -= 1;
    }
    goMonth(prevM, prevY);
};

const toggleBlockDate = (dateString, isPast) => {
    if (isPast || getBookingsForDate(dateString).length > 0) return;

    router.post(route('admin.calendar.toggle-block'), { date: dateString }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const monthName = computed(() => {
    const date = new Date(props.currentYear, props.currentMonth - 1, 1);
    return date.toLocaleString('default', { month: 'long', year: 'numeric' });
});

const todayString = () => {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
};

// Generate calendar grid
const calendarDays = computed(() => {
    const days = [];
    const firstDay = new Date(props.currentYear, props.currentMonth - 1, 1);
    const lastDay = new Date(props.currentYear, props.currentMonth, 0);

    // Monday-first: Sunday (0) becomes 6, else shift down by one.
    let startDayOfWeek = firstDay.getDay();
    startDayOfWeek = startDayOfWeek === 0 ? 6 : startDayOfWeek - 1;

    for (let i = 0; i < startDayOfWeek; i++) {
        days.push(null);
    }

    for (let i = 1; i <= lastDay.getDate(); i++) {
        const monthStr = String(props.currentMonth).padStart(2, '0');
        const dayStr = String(i).padStart(2, '0');
        const dateString = `${props.currentYear}-${monthStr}-${dayStr}`;
        days.push({
            day: i,
            dateString,
            isPast: dateString < todayString(),
            state: props.availability[dateString] ?? 'Available',
        });
    }

    const remaining = days.length % 7;
    if (remaining > 0) {
        for (let i = 0; i < (7 - remaining); i++) {
            days.push(null);
        }
    }

    return days;
});

const rows = computed(() => {
    const items = props.bookings?.data ?? [];
    return Array.isArray(items) ? items : Object.values(items);
});

const getBookingsForDate = (dateString) => {
    return rows.value.filter(b => {
        const datePart = String(b.event_date).split('T')[0];
        return datePart === dateString;
    });
};

const formatTime = (booking) => {
    if (booking.event_time) {
        const [h, m] = String(booking.event_time).split(':');
        const hour = Number(h);
        const suffix = hour >= 12 ? 'PM' : 'AM';
        const display = hour % 12 === 0 ? 12 : hour % 12;
        return `${display}:${String(m).padStart(2, '0')} ${suffix}`;
    }
    return '';
};

const isDateBlocked = (dateString) => {
    return props.blockedDates.includes(dateString);
};

const yearMonths = computed(() => {
    return Array.from({ length: 12 }, (_, i) => {
        const month = i + 1;
        const days = props.yearAvailability[month] ?? props.yearAvailability[String(month)] ?? [];
        const booked = new Set(days.filter((d) => d.status === 'Booked').map((d) => d.date));
        const first = new Date(props.currentYear, i, 1);
        let lead = first.getDay();
        lead = lead === 0 ? 6 : lead - 1;
        const count = new Date(props.currentYear, month, 0).getDate();
        return { month, booked, lead, count };
    });
});

const yearMonthName = (month) => {
    return new Date(props.currentYear, month - 1, 1).toLocaleString('default', { month: 'short' });
};
</script>

<template>
    <Head title="Calendar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-end justify-between border-b border-brand-primary/10 dark:border-brand-dark-border pb-4 mb-4">
                <div>
                    <h2 class="text-3xl font-semibold text-brand-primary dark:text-brand-cream">
                        Calendar
                    </h2>
                    <p class="text-brand-muted dark:text-brand-cream/70 text-sm mt-1">
                        Manage available dates and event schedules.
                    </p>
                </div>
                <div class="inline-flex rounded-full border border-brand-primary/20 dark:border-brand-dark-border p-1 text-sm font-medium">
                    <button
                        @click="goMonth(currentMonth, currentYear)"
                        class="px-4 py-1.5 rounded-full transition-colors"
                        :class="view !== 'year' ? 'bg-brand-primary text-white' : 'text-brand-primary dark:text-brand-cream'"
                    >
                        Month
                    </button>
                    <button
                        @click="goYear(currentYear)"
                        class="px-4 py-1.5 rounded-full transition-colors"
                        :class="view === 'year' ? 'bg-brand-primary text-white' : 'text-brand-primary dark:text-brand-cream'"
                    >
                        Year
                    </button>
                </div>
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl">
                <template v-if="view === 'year'">
                    <div class="flex justify-center items-center mb-6">
                        <button @click="goYear(currentYear - 1)" class="text-brand-primary dark:text-brand-cream font-bold px-4 py-2 hover:bg-brand-primary/10 rounded-lg transition-colors">
                            &lt;
                        </button>
                        <h3 class="text-xl font-semibold text-brand-primary dark:text-brand-cream mx-4 min-w-[150px] text-center tracking-wide">
                            {{ currentYear }}
                        </h3>
                        <button @click="goYear(currentYear + 1)" class="text-brand-primary dark:text-brand-cream font-bold px-4 py-2 hover:bg-brand-primary/10 rounded-lg transition-colors">
                            &gt;
                        </button>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <button
                            v-for="mini in yearMonths"
                            :key="mini.month"
                            @click="goMonth(mini.month, currentYear)"
                            class="bg-white dark:bg-brand-dark-surface rounded-2xl border border-brand-primary/10 dark:border-brand-dark-border p-4 text-left hover:border-brand-primary/40 transition-colors"
                        >
                            <p class="text-sm font-semibold text-brand-primary dark:text-brand-cream mb-2">{{ yearMonthName(mini.month) }}</p>
                            <div class="grid grid-cols-7 gap-1">
                                <span v-for="n in mini.lead" :key="'l' + n" />
                                <span
                                    v-for="day in mini.count"
                                    :key="day"
                                    class="aspect-square rounded-full"
                                    :class="mini.booked.has(`${currentYear}-${String(mini.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`) ? 'bg-brand-primary' : 'bg-brand-primary/10 dark:bg-white/10'"
                                />
                            </div>
                        </button>
                    </div>
                </template>

                <template v-else>
                    <!-- Calendar Controls -->
                    <div class="flex justify-center items-center mb-6">
                        <button @click="prevMonth" class="text-brand-primary dark:text-brand-cream font-bold px-4 py-2 hover:bg-brand-primary/10 rounded-lg transition-colors">
                            &lt;
                        </button>
                        <h3 class="text-xl font-semibold text-brand-primary dark:text-brand-cream mx-4 min-w-[150px] text-center uppercase tracking-wide">
                            {{ monthName }}
                        </h3>
                        <button @click="nextMonth" class="text-brand-primary dark:text-brand-cream font-bold px-4 py-2 hover:bg-brand-primary/10 rounded-lg transition-colors">
                            &gt;
                        </button>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="bg-white dark:bg-brand-dark-surface rounded-xl border border-brand-primary/10 dark:border-brand-dark-border overflow-hidden">
                        <div class="grid grid-cols-7 border-b border-brand-primary/20 dark:border-brand-dark-border bg-white dark:bg-brand-dark-surface">
                            <div v-for="day in ['MON', 'TUE', 'WED', 'THUR', 'FRI', 'SAT', 'SUN']" :key="day" class="py-3 px-4 text-xs font-semibold text-brand-muted dark:text-brand-cream/70 uppercase tracking-wider border-r border-brand-primary/10 dark:border-brand-dark-border last:border-r-0">
                                {{ day }}
                            </div>
                        </div>

                        <div class="grid grid-cols-7 auto-rows-fr">
                            <div v-for="(dayObj, index) in calendarDays" :key="index"
                                 class="p-2 min-h-24 border-b border-r border-brand-primary/10 dark:border-brand-dark-border flex flex-col transition-colors"
                                 :class="{
                                     'bg-brand-primary/[0.03]': !dayObj,
                                     'bg-black/[0.03] dark:bg-white/[0.03]': dayObj?.isPast,
                                     'hover:bg-brand-primary/5 cursor-pointer': dayObj && !dayObj.isPast && getBookingsForDate(dayObj.dateString).length === 0 && dayObj.state === 'Available',
                                     'border-r-0': (index + 1) % 7 === 0,
                                 }"
                                 @click="dayObj && toggleBlockDate(dayObj.dateString, dayObj.isPast)"
                                 >

                                <template v-if="dayObj">
                                    <span
                                        class="text-sm font-semibold mb-1 px-1"
                                        :class="dayObj.isPast ? 'text-brand-muted/50 dark:text-brand-cream/30' : 'text-brand-primary dark:text-brand-cream'"
                                    >
                                        {{ dayObj.day }}
                                    </span>

                                    <div class="space-y-1">
                                        <template v-if="getBookingsForDate(dayObj.dateString).length > 0">
                                            <button v-for="booking in getBookingsForDate(dayObj.dateString)" :key="booking.id"
                                                @click.stop="openViewModal(booking)"
                                                class="w-full text-left px-2 py-1.5 bg-brand-primary text-white text-xs rounded-lg transition-opacity hover:opacity-90"
                                            >
                                                <span class="block font-bold truncate">{{ formatTime(booking) }} · {{ booking.customer_name }}</span>
                                                <span class="block opacity-80 truncate">{{ booking.pax ? booking.pax + ' pax · ' : '' }}{{ booking.package?.name }} · {{ booking.status }}</span>
                                            </button>
                                        </template>
                                        <span v-else-if="isDateBlocked(dayObj.dateString)" class="block w-full text-center px-2 py-1 bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 text-xs font-semibold rounded-lg">
                                            Blocked
                                        </span>
                                        <span v-else-if="dayObj.state === 'Booked'" class="block w-full text-center px-2 py-1 bg-amber-100 dark:bg-amber-500/10 text-amber-800 dark:text-amber-300 text-xs font-semibold rounded-lg">
                                            Booked
                                        </span>
                                        <span v-else class="block w-full text-center px-2 py-1 text-cyan-600 dark:text-cyan-400/70 text-xs font-medium">
                                            Available
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <ViewBookingModal
            :show="isViewModalOpen"
            :booking="selectedBooking"
            @close="isViewModalOpen = false"
        />
    </AuthenticatedLayout>
</template>
