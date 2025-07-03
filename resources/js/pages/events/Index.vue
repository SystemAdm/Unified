<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';
import { type BreadcrumbItem } from '@/types';
import { CalendarIcon, MapPinIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { formatDate } from '@/utils';

interface Event {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    location: string;
    status: string;
    user: {
        name: string;
    };
}

interface Props {
    events: {
        data: Event[];
        links: any[];
        meta: any;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Events',
        href: '/events',
    },
];

// Format date for display
const formatEventDate = (date: string) => {
    return formatDate(date, 'MMM D, YYYY h:mm A');
};

// Decode HTML entities
const decodeHtmlEntities = (html: string) => {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = html;
    return textarea.value;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Events" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall title="Upcoming Events" description="Browse and register for upcoming events" class="m-3" />
            </div>

            <!-- Events list -->
            <div class="space-y-4">
                <div v-if="props.events.data.length === 0" class="p-4 text-center text-gray-500">
                    No events found.
                </div>
                <div v-else>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="event in props.events.data" :key="event.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2">
                                    <Link :href="route('events.show', { event: event.id })" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ event.title }}
                                    </Link>
                                </h3>
                                <div class="flex items-center text-gray-500 dark:text-gray-400 mb-2">
                                    <CalendarIcon class="h-4 w-4 mr-2" />
                                    <span>{{ formatEventDate(event.start_date) }}</span>
                                </div>
                                <div v-if="event.location" class="flex items-center text-gray-500 dark:text-gray-400 mb-2">
                                    <MapPinIcon class="h-4 w-4 mr-2" />
                                    <span>{{ event.location }}</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mt-3 line-clamp-3">
                                    {{ event.description }}
                                </p>
                                <div class="mt-4">
                                    <Link :href="route('events.show', { event: event.id })" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        View details →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        <Pagination :items-per-page="props.events.meta.per_page" :total="props.events.meta.total" :default-page="props.events.meta.current_page">
                            <PaginationContent>
                                <template v-for="(link, i) in props.events.links" :key="i">
                                    <!-- Previous link -->
                                    <PaginationPrevious
                                        v-if="link.label === '&laquo; Previous'"
                                        :href="link.url"
                                    />

                                    <!-- Page numbers -->
                                    <PaginationItem
                                        v-else-if="!isNaN(parseInt(decodeHtmlEntities(link.label)))"
                                        :value="parseInt(decodeHtmlEntities(link.label))"
                                        :is-active="link.active"
                                        :href="link.url"
                                    >
                                        {{ decodeHtmlEntities(link.label) }}
                                    </PaginationItem>

                                    <!-- Next link -->
                                    <PaginationNext
                                        v-else-if="link.label === 'Next &raquo;'"
                                        :href="link.url"
                                    />

                                    <!-- Ellipsis -->
                                    <PaginationEllipsis
                                        v-else-if="link.label === '...'"
                                    />
                                </template>
                            </PaginationContent>
                        </Pagination>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
