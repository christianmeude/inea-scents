<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    package: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.package.name,
    description: props.package.description,
    price: props.package.price,
    image_url: props.package.image_url,
});

const submit = () => {
    form.put(route('admin.packages.update', props.package.id));
};
</script>

<template>
    <Head title="Edit Package" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Package: {{ package.name }}
                </h2>
                <Link
                    :href="route('admin.packages.index')"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    Back to Packages
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                class="mt-1 block w-full border-gray-300 focus:border-burgundy-500 focus:ring-burgundy-500 rounded-md shadow-sm"
                                v-model="form.description"
                                rows="4"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div>
                            <InputLabel for="price" value="Price" />
                            <TextInput
                                id="price"
                                type="number"
                                step="0.01"
                                class="mt-1 block w-full"
                                v-model="form.price"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.price" />
                        </div>

                        <div>
                            <InputLabel for="image_url" value="Image URL" />
                            <TextInput
                                id="image_url"
                                type="url"
                                class="mt-1 block w-full"
                                v-model="form.image_url"
                            />
                            <InputError class="mt-2" :message="form.errors.image_url" />
                        </div>

                        <div class="flex items-center justify-end">
                            <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Update Package
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
