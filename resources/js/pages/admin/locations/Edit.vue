<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader } from '@/components/ui/card';

interface LocationImage {
    id: number;
    location_id: number;
    path: string;
    created_at: string;
    updated_at: string;
}

interface Location {
    id: number;
    name: string;
    description: string | null;
    address: string | null;
    city: string | null;
    state: string | null;
    country: string | null;
    postal_code: string | null;
    latitude: number | null;
    longitude: number | null;
    image: string | null;
    is_active: boolean;
    images: LocationImage[];
}

interface Props {
    location: Location;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.location.name,
    description: props.location.description || '',
    address: props.location.address || '',
    city: props.location.city || '',
    state: props.location.state || '',
    country: props.location.country || '',
    postal_code: props.location.postal_code || '',
    latitude: props.location.latitude,
    longitude: props.location.longitude,
    image: null as File | null,
    images: [] as File[],
    delete_images: [] as number[],
    _method: 'PUT', // For method spoofing when using FormData
    is_active: props.location.is_active,
});

const breadcrumbs: BreadcrumbItem[] = [
    { name: 'Admin', href: route('admin.index') },
    { name: 'Locations', href: route('admin.locations.index') },
    { name: 'Edit', href: route('admin.locations.edit', props.location.id) },
];

const submit = () => {
    form.post(route('admin.locations.update', props.location.id), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit Location" />

        <Card class="flex flex-col xl:max-w-1/2 mx-auto">
            <CardHeader>
            <HeadingSmall title="Edit Location" />
            </CardHeader>
            <CardContent>
            <form @submit.prevent="submit" class="space-y-6">

                    <div class="space-y-6">
                        <!-- Basic Information -->
                        <HeadingSmall title="Basic Information" />

                        <div class="space-y-4">
                            <div>
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <Label for="description">Description</Label>
                                <Textarea id="description" v-model="form.description" class="mt-1 block w-full" rows="3" />
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>
                        </div>

                        <!-- Address Information -->
                        <HeadingSmall>Address Information</HeadingSmall>

                        <div class="space-y-4">
                            <div>
                                <Label for="address">Address</Label>
                                <Input id="address" v-model="form.address" type="text" class="mt-1 block w-full" />
                                <InputError :message="form.errors.address" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="city">City</Label>
                                    <Input id="city" v-model="form.city" type="text" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.city" class="mt-2" />
                                </div>

                                <div>
                                    <Label for="state">State/Province</Label>
                                    <Input id="state" v-model="form.state" type="text" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.state" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="postal_code">Postal Code</Label>
                                    <Input id="postal_code" v-model="form.postal_code" type="text" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.postal_code" class="mt-2" />
                                </div>

                                <div>
                                    <Label for="country">Country</Label>
                                    <Input id="country" v-model="form.country" type="text" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.country" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <HeadingSmall>Additional Information</HeadingSmall>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="latitude">Latitude</Label>
                                    <Input id="latitude" v-model="form.latitude" type="number" step="0.0000001" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.latitude" class="mt-2" />
                                </div>

                                <div>
                                    <Label for="longitude">Longitude</Label>
                                    <Input id="longitude" v-model="form.longitude" type="number" step="0.0000001" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.longitude" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <Label for="image">Primary Image (Background/Heading)</Label>
                                <div v-if="props.location.image" class="mb-2">
                                    <img :src="`/storage/${props.location.image}`" alt="Location primary image" class="max-w-xs rounded-md" />
                                </div>
                                <Input id="image" type="file" accept="image/*" class="mt-1 block w-full" @input="form.image = $event.target.files[0]" />
                                <p class="text-sm text-gray-500 mt-1">This image will be used as the main image for the location.</p>
                                <InputError :message="form.errors.image" class="mt-2" />
                            </div>

                            <div>
                                <Label>Additional Images</Label>
                                <div v-if="props.location.images && props.location.images.length > 0" class="grid grid-cols-2 gap-4 mb-4">
                                    <div v-for="image in props.location.images" :key="image.id" class="relative">
                                        <img :src="`/storage/${image.path}`" alt="Location image" class="w-full h-40 object-cover rounded-md" />
                                        <button
                                            type="button"
                                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                            @click="form.delete_images.includes(image.id)
                                                ? form.delete_images = form.delete_images.filter(id => id !== image.id)
                                                : form.delete_images.push(image.id)"
                                        >
                                            <span v-if="form.delete_images.includes(image.id)" class="text-xs px-1">Undo</span>
                                            <span v-else class="text-xs px-1">Delete</span>
                                        </button>
                                        <div v-if="form.delete_images.includes(image.id)" class="absolute inset-0 bg-red-500 bg-opacity-30 flex items-center justify-center">
                                            <span class="text-white font-bold">Marked for deletion</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="mb-4 text-gray-500">No additional images uploaded yet.</div>

                                <Label for="additional-images">Upload New Images</Label>
                                <Input id="additional-images" type="file" accept="image/*" multiple class="mt-1 block w-full" @input="form.images = Array.from($event.target.files)" />
                                <p class="text-sm text-gray-500 mt-1">You can select multiple images to upload.</p>
                                <InputError :message="form.errors.images" class="mt-2" />
                            </div>

                            <div class="flex items-center space-x-2">
                                <Switch id="is_active" v-model:checked="form.is_active" />
                                <Label for="is_active">Active</Label>
                                <InputError :message="form.errors.is_active" class="mt-2" />
                            </div>
                        </div>
                    </div>

                <div class="flex justify-end space-x-3">
                    <Button type="button" variant="outline" :href="route('admin.locations.index')">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Update Location</Button>
                </div>
            </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
