<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const form = useForm({
    name: '',
    description: '',
    price: '',
    inclusions: [''],
    pax_options: [''],
    freebies: [''],
    images: [], // File objects
});

const imagePreviews = ref(['', '', '']);

const handleImageUpload = (index, event) => {
    const file = event.target.files[0];
    if (file) {
        form.images[index] = file;
        imagePreviews.value[index] = URL.createObjectURL(file);
    }
};

const addField = (field) => {
    form[field].push('');
};

const removeField = (field, index) => {
    form[field].splice(index, 1);
};

const submit = () => {
    form.post(route('admin.packages.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Create Package" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-brand-primary dark:text-brand-cream">
                    Packages
                </h2>
                <Link
                    :href="route('admin.packages.index')"
                    class="text-sm font-medium text-brand-muted dark:text-brand-cream/70 hover:text-brand-primary dark:text-brand-cream"
                >
                    Back to Packages
                </Link>
            </div>
            <p class="text-sm text-brand-muted dark:text-brand-cream/70">Manages the perfume-bar packages.</p>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-brand-dark-surface shadow-sm rounded-3xl overflow-hidden p-6 sm:p-10 border border-[#c4acac]/30 flex flex-col lg:flex-row gap-10">
                    
                    <!-- Left Column: Form -->
                    <div class="flex-1">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Images -->
                            <div>
                                <InputLabel value="Add Image" class="text-brand-primary dark:text-brand-cream" />
                                <div class="flex gap-4 mt-2">
                                    <div v-for="(preview, index) in imagePreviews" :key="index" class="relative w-32 h-24 bg-[#fdf4f5] border border-[#c4acac] rounded-lg overflow-hidden flex items-center justify-center">
                                        <input type="file" @change="e => handleImageUpload(index, e)" accept="image/*" class="bg-white dark:bg-brand-dark-base absolute inset-0 opacity-0 cursor-pointer" />
                                        <img alt="Image" v-if="preview" :src="preview" class="w-full h-full object-cover" />
                                        <div v-else class="text-[#c4acac] text-2xl">+</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Name -->
                            <div>
                                <InputLabel for="name" value="Package Name:" class="text-brand-primary dark:text-brand-cream" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full border-brand-primary rounded-lg text-brand-primary dark:text-brand-cream"
                                    v-model="form.name"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <!-- Description -->
                            <div>
                                <InputLabel for="description" value="Package Description:" class="text-brand-primary dark:text-brand-cream" />
                                <textarea
                                    id="description"
                                    class="bg-white dark:bg-brand-dark-base mt-1 block w-full border-brand-primary focus:border-brand-primary focus:ring-brand-primary rounded-lg shadow-sm text-brand-primary dark:text-brand-cream"
                                    v-model="form.description"
                                    rows="3"
                                    maxlength="200"
                                ></textarea>
                                <div class="text-right text-xs text-brand-muted dark:text-brand-cream/70 mt-1">200 Char</div>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <!-- Inclusions -->
                            <div>
                                <InputLabel value="Add Inclusion/s:" class="text-brand-primary dark:text-brand-cream" />
                                <div v-for="(inclusion, index) in form.inclusions" :key="`inc-${index}`" class="flex gap-2 mt-1">
                                    <TextInput
                                        type="text"
                                        class="block w-full border-brand-primary rounded-lg text-brand-primary dark:text-brand-cream"
                                        v-model="form.inclusions[index]"
                                    />
                                    <button type="button" @click="removeField('inclusions', index)" v-if="form.inclusions.length > 1" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                </div>
                                <div class="text-right mt-1">
                                    <button type="button" @click="addField('inclusions')" class="text-sm text-brand-primary dark:text-brand-cream hover:underline">Add More +</button>
                                </div>
                                <InputError class="mt-2" :message="form.errors.inclusions" />
                            </div>

                            <!-- Pax -->
                            <div>
                                <InputLabel value="Add Pax:" class="text-brand-primary dark:text-brand-cream" />
                                <div v-for="(pax, index) in form.pax_options" :key="`pax-${index}`" class="flex gap-2 mt-1">
                                    <TextInput
                                        type="text"
                                        class="block w-full border-brand-primary rounded-lg text-brand-primary dark:text-brand-cream"
                                        v-model="form.pax_options[index]"
                                    />
                                    <button type="button" @click="removeField('pax_options', index)" v-if="form.pax_options.length > 1" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                </div>
                                <div class="text-right mt-1">
                                    <button type="button" @click="addField('pax_options')" class="text-sm text-brand-primary dark:text-brand-cream hover:underline">Add More +</button>
                                </div>
                                <InputError class="mt-2" :message="form.errors.pax_options" />
                            </div>

                            <!-- Freebies -->
                            <div>
                                <InputLabel value="Freebie/s:" class="text-brand-primary dark:text-brand-cream" />
                                <div v-for="(freebie, index) in form.freebies" :key="`freebie-${index}`" class="flex gap-2 mt-1">
                                    <TextInput
                                        type="text"
                                        class="block w-full border-brand-primary rounded-lg text-brand-primary dark:text-brand-cream"
                                        v-model="form.freebies[index]"
                                    />
                                    <button type="button" @click="removeField('freebies', index)" v-if="form.freebies.length > 1" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                </div>
                                <div class="text-right mt-1">
                                    <button type="button" @click="addField('freebies')" class="text-sm text-brand-primary dark:text-brand-cream hover:underline">Add More +</button>
                                </div>
                                <InputError class="mt-2" :message="form.errors.freebies" />
                            </div>

                            <!-- Price -->
                            <div>
                                <InputLabel for="price" value="Price" class="text-brand-primary dark:text-brand-cream" />
                                <TextInput
                                    id="price"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full border-brand-primary rounded-lg text-brand-primary dark:text-brand-cream"
                                    v-model="form.price"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.price" />
                            </div>

                            <div class="flex items-center justify-end mt-8">
                                <PrimaryButton class="ms-4 bg-brand-primary hover:bg-brand-muted rounded-full px-8 py-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Save Package
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Mobile App Preview -->
                    <div class="hidden lg:block w-80 relative flex-shrink-0">
                        <h3 class="text-sm font-medium text-brand-primary dark:text-brand-cream mb-4 text-center">Preview on Mobile App</h3>
                        
                        <div class="relative w-full h-[600px] bg-black rounded-[40px] p-2 shadow-xl border-4 border-gray-800 flex flex-col overflow-hidden">
                            <!-- Dynamic Island notch -->
                            <div class="absolute top-0 inset-x-0 h-6 flex justify-center z-20">
                                <div class="w-24 h-5 bg-black rounded-b-xl"></div>
                            </div>

                            <!-- App UI Content -->
                            <div class="flex-1 bg-white dark:bg-brand-dark-surface rounded-[32px] overflow-hidden flex flex-col text-sm pb-16 relative">
                                
                                <!-- App Header -->
                                <div class="pt-8 pb-3 px-4 flex items-center justify-between border-b border-gray-100">
                                    <div class="text-gray-400">&larr;</div>
                                    <div class="flex-1 px-3">
                                        <div class="bg-gray-100 rounded-full h-8 flex items-center px-3">
                                            <span class="text-gray-400 text-xs text-center w-full">Search "Perfume" here</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 text-gray-400">
                                        <span class="text-xs">&#128172;</span>
                                        <span class="text-xs">&#128197;</span>
                                    </div>
                                </div>

                                <!-- Scrollable App Content -->
                                <div class="flex-1 overflow-y-auto pb-6 relative">
                                    
                                    <!-- Image Preview Slider -->
                                    <div class="relative h-48 bg-[#fdf4f5] w-full flex items-center justify-center overflow-hidden">
                                        <!-- Top tags -->
                                        <div class="absolute top-3 left-3 bg-brand-primary text-white text-xs px-2 py-1 rounded-full z-10">Massage</div>
                                        <div class="absolute top-3 right-3 bg-white dark:bg-brand-dark-surface/80 text-brand-primary dark:text-brand-cream text-xs px-2 py-1 rounded-full z-10 backdrop-blur-sm">Add to Checklist</div>

                                        <img alt="Image" v-if="imagePreviews[0]" :src="imagePreviews[0]" class="w-full h-full object-cover" />
                                        <div v-else class="text-[#c4acac] text-xs">No image</div>
                                        
                                        <!-- Indicators -->
                                        <div class="absolute bottom-3 inset-x-0 flex justify-center gap-1">
                                            <div v-for="(_, i) in imagePreviews" :key="i" class="w-1.5 h-1.5 rounded-full" :class="imagePreviews[i] ? 'bg-white dark:bg-brand-dark-surface' : 'bg-white dark:bg-brand-dark-surface/50'"></div>
                                        </div>
                                    </div>

                                    <!-- Content Details -->
                                    <div class="p-4 text-brand-primary dark:text-brand-cream">
                                        <h4 class="font-bold text-lg mb-1">{{ form.name || 'Package Name' }}</h4>
                                        <div class="flex items-center text-xs text-brand-muted dark:text-brand-cream/70 mb-3">
                                            <span class="text-yellow-400 mr-1">&#9733;</span> 4.5 <span class="ml-1">(232 reviews)</span>
                                        </div>

                                        <p class="text-xs text-brand-muted dark:text-brand-cream/70 mb-4 min-h-[40px]">
                                            {{ form.description || 'Description of the package will appear here. It offers a premium perfume experience.' }}
                                        </p>

                                        <div class="mb-4">
                                            <strong class="text-xs mb-1 block">Includes:</strong>
                                            <ul class="list-disc pl-4 text-xs text-brand-muted dark:text-brand-cream/70">
                                                <li v-for="(inc, i) in form.inclusions.filter(Boolean)" :key="i">{{ inc }}</li>
                                                <li v-if="!form.inclusions.filter(Boolean).length">No inclusions listed</li>
                                            </ul>
                                        </div>

                                        <div class="mb-4">
                                            <strong class="text-xs mb-1 block">Pax Options:</strong>
                                            <ul class="list-disc pl-4 text-xs text-brand-muted dark:text-brand-cream/70">
                                                <li v-for="(pax, i) in form.pax_options.filter(Boolean)" :key="i">{{ pax }} pax</li>
                                            </ul>
                                        </div>

                                        <div class="mb-4" v-if="form.freebies.filter(Boolean).length">
                                            <strong class="text-xs mb-1 block">Freebies:</strong>
                                            <ul class="list-disc pl-4 text-xs text-brand-muted dark:text-brand-cream/70">
                                                <li v-for="(fb, i) in form.freebies.filter(Boolean)" :key="i">{{ fb }}</li>
                                            </ul>
                                        </div>

                                        <div class="flex items-center justify-between mt-6">
                                            <div>
                                                <span class="text-xs text-brand-muted dark:text-brand-cream/70">Starting at</span>
                                                <div class="font-bold">&#8369;{{ form.price || '0.00' }}</div>
                                            </div>
                                            <button class="bg-brand-primary text-white text-xs px-4 py-2 rounded-full">Book Now</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- App Bottom Nav -->
                                <div class="absolute bottom-0 inset-x-0 h-16 bg-brand-primary/90 backdrop-blur text-white flex justify-around items-center text-xs rounded-b-[32px]">
                                    <div class="flex flex-col items-center opacity-50">
                                        <span class="text-lg mb-0.5">&#8962;</span>
                                        <span>HOME</span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <span class="text-lg mb-0.5">&#127873;</span>
                                        <span>PACKAGES</span>
                                    </div>
                                    <div class="flex flex-col items-center opacity-50">
                                        <span class="text-lg mb-0.5">&#128197;</span>
                                        <span>BOOKINGS</span>
                                    </div>
                                    <div class="flex flex-col items-center opacity-50">
                                        <span class="text-lg mb-0.5">&#128100;</span>
                                        <span>PROFILE</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
