<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: Boolean,
    packages: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    package_id: '',
    pax: 50,
    event_date: '',
    event_time: '',
    venue_address: '',
    payment_method: 'cash',
    status: 'Pending',
    total_price: '',
    notes: '',
});

const submit = () => {
    form.post(route('admin.bookings.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        }
    });
};

watch(() => props.show, (newVal) => {
    if (!newVal) form.reset();
});
</script>

<template>
    <Modal :show="show" @close="emit('close')" maxWidth="2xl">
        <div class="bg-white dark:bg-brand-dark-surface p-6 sm:p-8 rounded-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-brand-primary dark:text-brand-cream">New Booking</h2>
                <button type="button" @click="emit('close')" class="p-2 -mr-2 text-brand-primary/50 hover:text-brand-primary dark:text-brand-cream/50 dark:hover:text-brand-cream transition-colors rounded-full hover:bg-brand-primary/5 dark:hover:bg-brand-dark-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-8">
                
                <!-- Customer Details -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-brand-primary/60 dark:text-brand-cream/60 uppercase tracking-wider">Customer</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Name</label>
                            <input 
                                v-model="form.customer_name" 
                                type="text" 
                                required
                                placeholder="Jane Doe"
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors placeholder:text-brand-primary/30 dark:placeholder:text-brand-cream/30"
                            >
                            <div v-if="form.errors.customer_name" class="text-red-500 text-xs mt-1">{{ form.errors.customer_name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Email</label>
                            <input 
                                v-model="form.customer_email" 
                                type="email" 
                                placeholder="jane@example.com"
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors placeholder:text-brand-primary/30 dark:placeholder:text-brand-cream/30"
                            >
                            <div v-if="form.errors.customer_email" class="text-red-500 text-xs mt-1">{{ form.errors.customer_email }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Phone</label>
                            <input 
                                v-model="form.customer_phone" 
                                type="text" 
                                placeholder="+63 912 345 6789"
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors placeholder:text-brand-primary/30 dark:placeholder:text-brand-cream/30"
                            >
                            <div v-if="form.errors.customer_phone" class="text-red-500 text-xs mt-1">{{ form.errors.customer_phone }}</div>
                        </div>
                    </div>
                </div>

                <!-- Event Information -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-brand-primary/60 dark:text-brand-cream/60 uppercase tracking-wider">Event</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div>
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Package</label>
                            <select 
                                v-model="form.package_id"
                                required
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                            >
                                <option value="" disabled>Select a package...</option>
                                <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                    {{ pkg.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.package_id" class="text-red-500 text-xs mt-1">{{ form.errors.package_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Pax (Guests)</label>
                            <input 
                                v-model="form.pax" 
                                type="number" 
                                placeholder="50"
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors placeholder:text-brand-primary/30 dark:placeholder:text-brand-cream/30"
                            >
                            <div v-if="form.errors.pax" class="text-red-500 text-xs mt-1">{{ form.errors.pax }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Date</label>
                            <input 
                                v-model="form.event_date" 
                                type="date" 
                                required
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                            >
                            <div v-if="form.errors.event_date" class="text-red-500 text-xs mt-1">{{ form.errors.event_date }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Time</label>
                            <input 
                                v-model="form.event_time" 
                                type="time" 
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                            >
                            <div v-if="form.errors.event_time" class="text-red-500 text-xs mt-1">{{ form.errors.event_time }}</div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Venue Address</label>
                            <input 
                                v-model="form.venue_address" 
                                type="text" 
                                required
                                placeholder="123 Celebration Ave, City"
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors placeholder:text-brand-primary/30 dark:placeholder:text-brand-cream/30"
                            >
                            <div v-if="form.errors.venue_address" class="text-red-500 text-xs mt-1">{{ form.errors.venue_address }}</div>
                        </div>
                    </div>
                </div>

                <!-- Admin & Status -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-brand-primary/60 dark:text-brand-cream/60 uppercase tracking-wider">Admin</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div>
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Status</label>
                            <select 
                                v-model="form.status"
                                required
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                            >
                                <option value="Pending">Pending</option>
                                <option value="Confirmed">Confirmed</option>
                                <option value="Unavailable">Unavailable</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Payment Method</label>
                            <select 
                                v-model="form.payment_method"
                                required
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                            >
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                            <div v-if="form.errors.payment_method" class="text-red-500 text-xs mt-1">{{ form.errors.payment_method }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Total Price (₱)</label>
                            <input 
                                v-model="form.total_price" 
                                type="number" 
                                step="0.01"
                                placeholder="0.00"
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors placeholder:text-brand-primary/30 dark:placeholder:text-brand-cream/30"
                            >
                            <div v-if="form.errors.total_price" class="text-red-500 text-xs mt-1">{{ form.errors.total_price }}</div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-brand-primary dark:text-brand-cream mb-1">Internal Notes</label>
                            <textarea 
                                v-model="form.notes" 
                                rows="2"
                                placeholder="Special requests, requirements, or admin notes..."
                                class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors placeholder:text-brand-primary/30 dark:placeholder:text-brand-cream/30 resize-none"
                            ></textarea>
                            <div v-if="form.errors.notes" class="text-red-500 text-xs mt-1">{{ form.errors.notes }}</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-6 border-t border-brand-primary/10 dark:border-brand-dark-border mt-8">
                    <button 
                        type="button"
                        @click="emit('close')" 
                        class="px-5 py-2 rounded-full text-brand-primary dark:text-brand-cream hover:bg-brand-primary/5 dark:hover:bg-brand-dark-border transition-colors text-sm font-medium"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="px-6 py-2 rounded-full bg-brand-primary text-white hover:bg-brand-primary/90 transition-colors shadow-sm dark:shadow-none text-sm font-medium disabled:opacity-75"
                    >
                        {{ form.processing ? 'Saving...' : 'Create Booking' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
