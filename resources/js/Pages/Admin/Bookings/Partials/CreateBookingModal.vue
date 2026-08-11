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
    pax: '',
    event_date: '',
    event_time: '',
    venue_address: '',
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

<<template>
    <Modal :show="show" @close="emit('close')" maxWidth="3xl">
        <div class="bg-white dark:bg-brand-dark-surface p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8 border-b border-brand-primary/10 dark:border-brand-dark-border pb-4">
                <h2 class="text-2xl font-bold text-brand-primary dark:text-brand-cream">New Booking</h2>
                <button @click="emit('close')" class="text-brand-primary dark:text-brand-cream hover:opacity-70 transition-opacity">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Name -->
                    <div>
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Customer Name</label>
                        <input 
                            v-model="form.customer_name" 
                            type="text" 
                            required
                            class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                        >
                        <div v-if="form.errors.customer_name" class="text-red-500 text-xs mt-1">{{ form.errors.customer_name }}</div>
                    </div>

                    <!-- Customer Email -->
                    <div>
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Email</label>
                        <input 
                            v-model="form.customer_email" 
                            type="email" 
                            class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                        >
                        <div v-if="form.errors.customer_email" class="text-red-500 text-xs mt-1">{{ form.errors.customer_email }}</div>
                    </div>

                    <!-- Customer Phone -->
                    <div>
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Phone</label>
                        <input 
                            v-model="form.customer_phone" 
                            type="text" 
                            class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                        >
                        <div v-if="form.errors.customer_phone" class="text-red-500 text-xs mt-1">{{ form.errors.customer_phone }}</div>
                    </div>

                    <!-- Package -->
                    <div>
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Package</label>
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

                    <!-- Pax -->
                    <div>
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Pax (No. of people)</label>
                        <input 
                            v-model="form.pax" 
                            type="number" 
                            class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                        >
                        <div v-if="form.errors.pax" class="text-red-500 text-xs mt-1">{{ form.errors.pax }}</div>
                    </div>

                    <!-- Event Date -->
                    <div>
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Event Date</label>
                        <input 
                            v-model="form.event_date" 
                            type="date" 
                            required
                            class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                        >
                        <div v-if="form.errors.event_date" class="text-red-500 text-xs mt-1">{{ form.errors.event_date }}</div>
                    </div>

                    <!-- Event Time -->
                    <div>
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Event Time</label>
                        <input 
                            v-model="form.event_time" 
                            type="time" 
                            class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                        >
                        <div v-if="form.errors.event_time" class="text-red-500 text-xs mt-1">{{ form.errors.event_time }}</div>
                    </div>

                    <!-- Venue Address -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Venue Address</label>
                        <input 
                            v-model="form.venue_address" 
                            type="text" 
                            required
                            class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                        >
                        <div v-if="form.errors.venue_address" class="text-red-500 text-xs mt-1">{{ form.errors.venue_address }}</div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Status</label>
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

                    <!-- Total Price -->
                    <div>
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Total Price (Php)</label>
                        <input 
                            v-model="form.total_price" 
                            type="number" 
                            step="0.01"
                            class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                        >
                        <div v-if="form.errors.total_price" class="text-red-500 text-xs mt-1">{{ form.errors.total_price }}</div>
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-brand-primary dark:text-brand-cream mb-1">Notes</label>
                        <textarea 
                            v-model="form.notes" 
                            rows="3"
                            class="bg-white dark:bg-brand-dark-base w-full px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-brand-primary dark:text-brand-cream focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                        ></textarea>
                        <div v-if="form.errors.notes" class="text-red-500 text-xs mt-1">{{ form.errors.notes }}</div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-brand-primary/10 dark:border-brand-dark-border">
                    <button 
                        type="button"
                        @click="emit('close')" 
                        class="px-6 py-2.5 rounded-full text-brand-primary dark:text-brand-cream hover:bg-brand-cream dark:bg-brand-dark-base transition-colors text-sm font-medium"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="px-8 py-2.5 rounded-full bg-brand-primary text-white hover:bg-brand-primary/90 transition-colors shadow-ambient dark:shadow-none text-sm font-medium disabled:opacity-75"
                    >
                        {{ form.processing ? 'Saving...' : 'Create Booking' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
