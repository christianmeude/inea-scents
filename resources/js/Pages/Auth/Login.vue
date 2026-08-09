<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-4">
            <div class="mb-12 mt-4 flex justify-center sm:mb-16">
                <!-- Translate left to visually balance the 'Scents' overhang -->
                <ApplicationLogo />
            </div>

            <div class="relative">
                <input
                    id="email"
                    type="email"
                    class="block w-full rounded-full border-none bg-brand-muted px-6 py-4 text-white placeholder-white/80 shadow-sm transition-all duration-300 ease-in-out hover:bg-brand-muted/90 focus:bg-brand-muted/95 focus:outline-none focus:ring-2 focus:ring-brand-primary/40 focus:ring-offset-2 focus:ring-offset-brand-cream disabled:cursor-not-allowed disabled:opacity-50"
                    :class="{ 'opacity-50': form.processing }"
                    v-model="form.email"
                    placeholder="Email:"
                    required
                    autofocus
                    autocomplete="username"
                    :disabled="form.processing"
                />
                <InputError class="mt-2 text-center" :message="form.errors.email" />
            </div>

            <div class="relative">
                <input
                    id="password"
                    type="password"
                    class="block w-full rounded-full border-none bg-brand-muted px-6 py-4 text-white placeholder-white/80 shadow-sm transition-all duration-300 ease-in-out hover:bg-brand-muted/90 focus:bg-brand-muted/95 focus:outline-none focus:ring-2 focus:ring-brand-primary/40 focus:ring-offset-2 focus:ring-offset-brand-cream disabled:cursor-not-allowed disabled:opacity-50"
                    :class="{ 'opacity-50': form.processing }"
                    v-model="form.password"
                    placeholder="Password:"
                    required
                    autocomplete="current-password"
                    :disabled="form.processing"
                />
                <InputError class="mt-2 text-center" :message="form.errors.password" />
            </div>

            <button 
                type="submit" 
                class="sr-only"
                :disabled="form.processing"
            >
                Log In
            </button>
        </form>
    </GuestLayout>
</template>
