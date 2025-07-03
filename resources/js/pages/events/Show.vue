<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { CalendarIcon, MapPinIcon, UserIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { formatDate } from '@/utils';

interface User {
    id: number;
    name: string;
}

interface Event {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    location: string;
    status: string;
    user: User | null;
}

interface Props {
    event: Event;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Events',
        href: '/events',
    },
    {
        title: props.event.title,
        href: `/events/${props.event.id}`,
    },
];

// Format date for display
const formatEventDate = (date: string) => {
    return formatDate(date, 'MMM D, YYYY h:mm A');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.event.title" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall :title="props.event.title" description="Event details" class="m-3" />
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start mb-6">
                        <div>
                            <h1 class="text-2xl font-bold mb-4">{{ props.event.title }}</h1>

                            <div class="flex items-center text-gray-500 dark:text-gray-400 mb-2">
                                <CalendarIcon class="h-4 w-4 mr-2" />
                                <span>{{ formatEventDate(props.event.start_date) }}</span>

                                <span v-if="props.event.end_date" class="mx-2">-</span>
                                <span v-if="props.event.end_date">{{ formatEventDate(props.event.end_date) }}</span>
                            </div>

                            <div v-if="props.event.location" class="flex items-center text-gray-500 dark:text-gray-400 mb-2">
                                <MapPinIcon class="h-4 w-4 mr-2" />
                                <span>{{ props.event.location }}</span>
                            </div>

                            <div v-if="props.event.user" class="flex items-center text-gray-500 dark:text-gray-400 mb-2">
                                <UserIcon class="h-4 w-4 mr-2" />
                                <span>Organized by: {{ props.event.user.name }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="prose dark:prose-invert max-w-none">
                        <div v-if="props.event.description" class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">Description</h3>
                            <p class="whitespace-pre-line">{{ props.event.description }}</p>
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <Link :href="route('events.index')" class="text-blue-600 dark:text-blue-400 hover:underline">
                            ← Back to events
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
