<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="mb-8 mt-4 flex justify-center sm:mb-12">
                <ApplicationLogo />
            </div>

            <div class="text-sm text-brand-primary/80 dark:text-brand-cream/80 text-left">
                Enter your email address to receive a secure password reset link.
            </div>

            <div
                v-if="status"
                class="text-sm font-medium text-green-600 text-center"
            >
                {{ status }}
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2 text-center" :message="form.errors.email" />
            </div>

            <div class="flex flex-col mt-2">
                <PrimaryButton
                    class="w-full text-center flex justify-center"
                    :loading="form.processing"
                >
                    Email Password Reset Link
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>