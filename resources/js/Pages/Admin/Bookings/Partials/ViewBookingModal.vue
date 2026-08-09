<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    booking: Object
});

const emit = defineEmits(['close']);

const formattedDate = computed(() => {
    if (!props.booking?.event_date) return '';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(props.booking.event_date).toLocaleDateString('en-US', options);
});

const formattedPrice = computed(() => {
    if (props.booking?.total_price == null) return 'Php. 0.00';
    return `Php. ${parseFloat(props.booking.total_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
});
</script>

<template>
    <!-- Background overlay -->
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>

        <!-- Modal Panel -->
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden transform transition-all">
            <div class="p-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-bold text-brand-primary">Details</h2>
                    <button @click="emit('close')" class="text-brand-primary hover:opacity-70 transition-opacity">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Content: Alternating Key-Value Rows -->
                <div class="flex flex-col text-lg text-brand-primary" v-if="booking">
                    <div class="px-4 py-3 bg-white">
                        Booking ID: {{ booking.booking_reference }}
                    </div>
                    <div class="px-4 py-3 bg-brand-cream/30 rounded-lg">
                        Customer: {{ booking.customer_name }}
                    </div>
                    <div class="px-4 py-3 bg-white">
                        Package: {{ booking.package?.name || 'N/A' }}
                    </div>
                    <div class="px-4 py-3 bg-brand-cream/30 rounded-lg">
                        Pax: {{ booking.pax || 'N/A' }}
                    </div>
                    <div class="px-4 py-3 bg-white">
                        Date: {{ formattedDate }}
                    </div>
                    <div class="px-4 py-3 bg-brand-cream/30 rounded-lg">
                        Address/Venue: {{ booking.venue_address || 'N/A' }}
                    </div>
                    <div class="px-4 py-3 bg-white">
                        Total Paid: {{ formattedPrice }}
                    </div>
                    <div class="px-4 py-3 bg-brand-cream/30 rounded-lg">
                        Status: {{ booking.status }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
