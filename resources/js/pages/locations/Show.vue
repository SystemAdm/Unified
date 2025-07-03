<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Event {
    id: number;
    title: string;
    description: string | null;
    start_date: string;
    end_date: string | null;
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
    capacity: string | null;
    is_active: boolean;
    full_address: string;
    events: Event[];
}

interface Props {
    location: Location;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Locations', href: '/locations' },
    { title: 'Location Details', href: '#' },
];

// Format date for display
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="location.name" />

        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center mb-6">
                    <h1 class="text-3xl font-bold">{{ location.name }}</h1>
                    <span v-if="!location.is_active" class="ml-3 px-3 py-1 text-sm font-medium bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-full">Inactive</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Location Info -->
                    <div class="md:col-span-2">
                        <Card class="mb-6">
                            <CardHeader>
                                <CardTitle>About this Location</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div v-if="location.description" class="mb-4">
                                    <p class="whitespace-pre-line">{{ location.description }}</p>
                                </div>
                                <div v-if="location.capacity" class="mb-4">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Capacity</h3>
                                    <p>{{ location.capacity }}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Upcoming Events -->
                        <Card v-if="location.events && location.events.length > 0">
                            <CardHeader>
                                <CardTitle>Upcoming Events</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div v-for="event in location.events" :key="event.id" class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0">
                                        <h3 class="font-semibold text-lg mb-1">{{ event.title }}</h3>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-2">{{ formatDate(event.start_date) }}</p>
                                        <p v-if="event.description" class="text-gray-600 dark:text-gray-300 text-sm mb-2 line-clamp-2">
                                            {{ event.description }}
                                        </p>
                                        <Link :href="route('events.show', event.id)" class="text-blue-600 dark:text-blue-400 text-sm hover:underline">
                                            View Event
                                        </Link>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Location Details -->
                    <div>
                        <Card class="mb-6">
                            <CardHeader>
                                <CardTitle>Location Details</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div v-if="location.full_address" class="mb-4">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</h3>
                                    <p class="whitespace-pre-line">{{ location.full_address }}</p>
                                </div>

                                <div v-if="location.latitude && location.longitude" class="mb-4">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Coordinates</h3>
                                    <p>{{ location.latitude }}, {{ location.longitude }}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Map placeholder -->
                        <div v-if="location.latitude && location.longitude" class="bg-gray-200 dark:bg-gray-700 h-48 rounded-lg flex items-center justify-center">
                            <p class="text-gray-500 dark:text-gray-400">Map would be displayed here</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center">
                    <Link :href="route('locations.index')">
                        <Button variant="outline">Back to Locations</Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
