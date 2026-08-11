<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="mb-8 mt-4 flex justify-center sm:mb-12">
                <!-- Translate left to visually balance the 'Scents' overhang -->
                <ApplicationLogo />
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
                    :disabled="form.processing"
                />
                <InputError class="mt-2 text-center" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Password" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="rounded-md text-sm text-brand-primary dark:text-brand-cream/80 hover:text-brand-primary/80 dark:hover:text-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 dark:focus:ring-offset-brand-dark-base transition-colors"
                    >
                        Forgot password?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    :disabled="form.processing"
                />
                <InputError class="mt-2 text-center" :message="form.errors.password" />
            </div>

            <div class="mt-2">
                <label class="inline-flex items-center cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember" class="transition-colors group-hover:border-brand-primary" />
                    <span class="ml-2 text-sm text-brand-primary dark:text-brand-cream/80 group-hover:text-brand-primary dark:group-hover:text-brand-cream transition-colors">Remember me</span>
                </label>
            </div>

            <div class="flex flex-col gap-4 mt-2">
                <PrimaryButton
                    class="w-full text-center flex justify-center"
                    :loading="form.processing"
                >
                    Log In
                </PrimaryButton>
                
                <div class="text-center text-sm text-brand-primary dark:text-brand-cream/80">
                    Don't have an account? 
                    <Link
                        :href="route('register')"
                        class="font-semibold underline hover:text-brand-primary/80 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 dark:focus:ring-offset-brand-dark-base"
                    >
                        Register
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
