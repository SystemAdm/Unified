<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import LaravelPaginator from '@/components/LaravelPaginator.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { MapPinIcon, UsersIcon } from 'lucide-vue-next';

interface Location {
    id: number;
    name: string;
    description: string | null;
    full_address: string;
    capacity: string | null;
    is_active: boolean;
}

interface Props {
    locations: {
        data: Location[];
        links: any[];
        meta: any;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Locations', href: route('locations.index') },
];

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Locations" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall title="Locations" description="Browse locations" class="m-3" />
            </div>

            <!-- Locations list -->
            <div class="space-y-4">
                <div v-if="props.locations.data.length === 0" class="p-4 text-center text-gray-500">
                    No locations found.
                </div>
                <div v-else>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="location in props.locations.data" :key="location.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden" :class="{ 'opacity-60 grayscale': !location.is_active }">
                            <div class="p-6">
                                <div v-if="!location.is_active" class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">(Inactive)</div>
                                <h3 class="text-xl font-semibold mb-2">
                                    <Link :href="route('locations.show', location.id)" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ location.name }}
                                    </Link>
                                </h3>
                                <div v-if="location.full_address" class="flex items-center text-gray-500 dark:text-gray-400 mb-2">
                                    <MapPinIcon class="h-4 w-4 mr-2" />
                                    <span>{{ location.full_address }}</span>
                                </div>
                                <div v-if="location.capacity" class="flex items-center text-gray-500 dark:text-gray-400 mb-2">
                                    <UsersIcon class="h-4 w-4 mr-2" />
                                    <span>Capacity: {{ location.capacity }}</span>
                                </div>
                                <p v-if="location.description" class="text-gray-600 dark:text-gray-300 mt-3 line-clamp-3">
                                    {{ location.description }}
                                </p>
                                <div class="mt-4">
                                    <Link :href="route('locations.show', location.id)" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        View details →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6" v-if="props.locations.meta && props.locations.links">
                        <LaravelPaginator
                            :pagination="{
                                data: props.locations.data,
                                links: props.locations.links,
                                current_page: props.locations.meta.current_page,
                                from: props.locations.meta.from,
                                last_page: props.locations.meta.last_page,
                                path: props.locations.meta.path,
                                per_page: props.locations.meta.per_page,
                                to: props.locations.meta.to,
                                total: props.locations.meta.total
                            }"
                            onlyKey="locations"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
