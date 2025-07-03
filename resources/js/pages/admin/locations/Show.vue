<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

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
    full_address: string;
    images: LocationImage[];
}

interface Props {
    location: Location;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { name: 'Admin', href: route('admin.index') },
    { name: 'Locations', href: route('admin.locations.index') },
    { name: props.location.name, href: route('admin.locations.show', props.location.id) },
];

const showDeleteModal = ref(false);

const deleteLocation = () => {
    // Use Inertia to delete the location
    window.location.href = route('admin.locations.destroy', props.location.id);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="location.name" />

        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold">{{ location.name }}</h1>
                <div class="flex space-x-2">
                    <Link :href="route('admin.locations.edit', location.id)">
                        <Button>Edit Location</Button>
                    </Link>
                </div>
            </div>

            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Basic Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</h3>
                            <p class="mt-1">{{ location.name }}</p>
                        </div>

                        <div v-if="location.description">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h3>
                            <p class="mt-1 whitespace-pre-line">{{ location.description }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
                            <p class="mt-1">
                                <span
                                    :class="{
                                        'px-2 py-1 rounded text-xs font-medium': true,
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': location.is_active,
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': !location.is_active,
                                    }"
                                >
                                    {{ location.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Address Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="location.full_address">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Address</h3>
                            <p class="mt-1">{{ location.full_address }}</p>
                        </div>

                        <div v-if="location.latitude && location.longitude" class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Latitude</h3>
                                <p class="mt-1">{{ location.latitude }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Longitude</h3>
                                <p class="mt-1">{{ location.longitude }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Additional Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="location.image">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Primary Image</h3>
                            <div class="mt-2">
                                <img :src="`/storage/${location.image}`" alt="Location primary image" class="max-w-xs rounded-md" />
                            </div>
                        </div>
                        <div v-else>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Primary Image</h3>
                            <p class="mt-1 text-gray-400">No primary image available</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Additional Images</h3>
                            <div v-if="location.images && location.images.length > 0" class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div v-for="image in location.images" :key="image.id" class="relative">
                                    <img :src="`/storage/${image.path}`" alt="Location image" class="w-full h-40 object-cover rounded-md" />
                                </div>
                            </div>
                            <p v-else class="mt-1 text-gray-400">No additional images available</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-between mt-6">
                <Button variant="destructive" @click="showDeleteModal = true">Delete Location</Button>
                <Link :href="route('admin.locations.index')">
                    <Button variant="outline">Back to Locations</Button>
                </Link>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-md w-full">
                <h2 class="text-xl font-semibold mb-4">Confirm Deletion</h2>
                <p class="mb-6">Are you sure you want to delete this location? This action cannot be undone.</p>
                <div class="flex justify-end space-x-3">
                    <Button variant="outline" @click="showDeleteModal = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteLocation">Delete</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
