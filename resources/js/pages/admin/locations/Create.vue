<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const form = useForm({
    name: '',
    description: '',
    address: '',
    city: '',
    state: '',
    country: '',
    postal_code: '',
    latitude: null as number | null,
    longitude: null as number | null,
    image: null as File | null,
    images: [] as File[],
    is_active: true,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Locations', href: '/admin/locations' },
    { title: 'Create', href: '/admin/locations/create' },
];

const submit = () => {
    form.post(route('admin.locations.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Location" />

        <Card class="flex flex-col space-y-6 xl:max-w-1/2 mx-auto">
            <CardHeader>
                <HeadingSmall title="Create Location" description="Add a new location to the system" />
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">

                        <!-- Basic Information -->
                        <HeadingSmall title="Basic Information"></HeadingSmall>

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
                                <Input id="image" type="file" accept="image/*" class="mt-1 block w-full" @input="form.image = $event.target.files[0]" />
                                <p class="text-sm text-gray-500 mt-1">This image will be used as the main image for the location.</p>
                                <InputError :message="form.errors.image" class="mt-2" />
                            </div>

                            <div>
                                <Label for="additional-images">Additional Images</Label>
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

                    <div class="flex justify-end space-x-3">
                        <Button type="button" variant="outline" :href="route('admin.locations.index')">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">Create Location</Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
