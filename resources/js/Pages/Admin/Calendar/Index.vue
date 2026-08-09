<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ViewBookingModal from '../Bookings/Partials/ViewBookingModal.vue';

const props = defineProps({
    bookings: Array,
    blockedDates: Array,
    currentMonth: Number,
    currentYear: Number,
});

const isViewModalOpen = ref(false);
const selectedBooking = ref(null);

const openViewModal = (booking) => {
    selectedBooking.value = booking;
    isViewModalOpen.value = true;
};

const nextMonth = () => {
    let nextM = props.currentMonth + 1;
    let nextY = props.currentYear;
    if (nextM > 12) {
        nextM = 1;
        nextY += 1;
    }
    router.get(route('admin.calendar.index', { month: nextM, year: nextY }), {}, { preserveScroll: true, preserveState: true });
};

const prevMonth = () => {
    let prevM = props.currentMonth - 1;
    let prevY = props.currentYear;
    if (prevM < 1) {
        prevM = 12;
        prevY -= 1;
    }
    router.get(route('admin.calendar.index', { month: prevM, year: prevY }), {}, { preserveScroll: true, preserveState: true });
};

const toggleBlockDate = (dateString) => {
    // Make sure we don't block days that have bookings
    if (getBookingsForDate(dateString).length > 0) return;

    router.post(route('admin.calendar.toggle-block'), { date: dateString }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const monthName = computed(() => {
    const date = new Date(props.currentYear, props.currentMonth - 1, 1);
    return date.toLocaleString('default', { month: 'long', year: 'numeric' });
});

// Generate calendar grid
const calendarDays = computed(() => {
    const days = [];
    const firstDay = new Date(props.currentYear, props.currentMonth - 1, 1);
    const lastDay = new Date(props.currentYear, props.currentMonth, 0);
    
    // Day of the week for the 1st of the month (0 = Sunday, 1 = Monday, etc.)
    // But Figma shows Monday as first day of week.
    let startDayOfWeek = firstDay.getDay(); 
    // Shift so Monday is 0, Sunday is 6
    startDayOfWeek = startDayOfWeek === 0 ? 6 : startDayOfWeek - 1;

    // Fill previous month days
    for (let i = 0; i < startDayOfWeek; i++) {
        days.push(null);
    }

    for (let i = 1; i <= lastDay.getDate(); i++) {
        // Format YYYY-MM-DD
        const monthStr = String(props.currentMonth).padStart(2, '0');
        const dayStr = String(i).padStart(2, '0');
        days.push({
            day: i,
            dateString: `${props.currentYear}-${monthStr}-${dayStr}`,
        });
    }

    // Fill next month days to complete the grid
    const remaining = days.length % 7;
    if (remaining > 0) {
        for (let i = 0; i < (7 - remaining); i++) {
            days.push(null);
        }
    }

    return days;
});

const getBookingsForDate = (dateString) => {
    return props.bookings.filter(b => {
        // event_date might have time or be an ISO string, extract just the date part.
        const datePart = b.event_date.split('T')[0];
        return datePart === dateString;
    });
};

const isDateBlocked = (dateString) => {
    return props.blockedDates.includes(dateString);
};
</script>

<template>
    <Head title="Calendar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-end justify-between border-b border-brand-primary/10 pb-4 mb-4">
                <div>
                    <h2 class="text-3xl font-semibold text-brand-primary">
                        Calendar
                    </h2>
                    <p class="text-brand-muted text-sm mt-1">
                        Manage available dates and event schedules.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl">
                <!-- Calendar Controls -->
                <div class="flex justify-center items-center mb-6">
                    <button @click="prevMonth" class="text-brand-primary font-bold px-4 py-2 hover:bg-brand-primary/10 rounded-lg transition-colors">
                        &lt;
                    </button>
                    <h3 class="text-xl font-semibold text-brand-primary mx-4 min-w-[150px] text-center uppercase tracking-wide">
                        {{ monthName }}
                    </h3>
                    <button @click="nextMonth" class="text-brand-primary font-bold px-4 py-2 hover:bg-brand-primary/10 rounded-lg transition-colors">
                        &gt;
                    </button>
                </div>

                <!-- Calendar Grid -->
                <div class="bg-white rounded-xl shadow-ambient border border-brand-primary/10 overflow-hidden relative">
                    <div class="grid grid-cols-7 border-b border-brand-primary/20 bg-white">
                        <div v-for="day in ['MON', 'TUE', 'WED', 'THUR', 'FRI', 'SAT', 'SUN']" :key="day" class="py-4 px-4 text-xs font-semibold text-brand-muted uppercase tracking-wider border-r border-brand-primary/10 last:border-r-0">
                            {{ day }}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-7 auto-rows-[160px]">
                        <div v-for="(dayObj, index) in calendarDays" :key="index" 
                             class="p-3 border-b border-r border-brand-primary/10 flex flex-col transition-colors relative group"
                             :class="{ 'bg-brand-primary/5': !dayObj, 'hover:bg-brand-cream/20 cursor-pointer': dayObj && getBookingsForDate(dayObj.dateString).length === 0, 'border-r-0': (index + 1) % 7 === 0 }"
                             @click="dayObj && toggleBlockDate(dayObj.dateString)"
                             >
                            
                            <template v-if="dayObj">
                                <span class="text-sm font-semibold text-brand-primary mb-2 px-1">{{ dayObj.day }}</span>
                                
                                <div class="mt-auto space-y-1">
                                    <div v-if="getBookingsForDate(dayObj.dateString).length > 0" class="flex flex-col gap-1.5">
                                        <button v-for="booking in getBookingsForDate(dayObj.dateString)" :key="booking.id"
                                            @click.stop="openViewModal(booking)"
                                            class="w-full text-center px-2 py-1.5 bg-brand-primary/10 hover:bg-brand-primary/20 text-brand-primary text-xs font-bold rounded-lg transition-colors truncate"
                                        >
                                            View Booking
                                        </button>
                                        <span class="w-full text-center px-2 py-1 bg-green-200 text-green-700 text-[10px] font-bold rounded-lg block">
                                            Booked
                                        </span>
                                    </div>
                                    <div v-else-if="isDateBlocked(dayObj.dateString)">
                                        <span class="w-full text-center px-2 py-1 bg-red-200 text-red-700 text-[10px] font-bold rounded-lg block">
                                            Marked as Unavailable
                                        </span>
                                    </div>
                                    <div v-else>
                                        <span class="w-full text-center px-2 py-1 bg-cyan-100 text-cyan-600 text-[10px] font-bold rounded-lg block">
                                            Available
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ViewBookingModal 
            :show="isViewModalOpen" 
            :booking="selectedBooking" 
            @close="isViewModalOpen = false" 
        />
    </AuthenticatedLayout>
</template>
