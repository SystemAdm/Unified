<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import EventCard from '@/components/EventCard.vue';
import LaravelPaginator from '@/components/LaravelPaginator.vue';
import { type BreadcrumbItem } from '@/types';
import { CalendarIcon } from 'lucide-vue-next';

interface Event {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    location: { id: number; name: string } | null;
    status: string;
    organizations: [{ name: string; id: number }];
    users: [{ name: string; id: number }];
    seats: number | null;
    available_seats: number | null;
    min_age: number | null;
    max_age: number | null;
    restriction: string | null;
    class_restriction: string | null;
    is_cancelled: boolean;
    has_signup: boolean;
    signup_start_date: string | null;
    signup_end_date: string | null;
}

interface Props {
    events: {
        data: Event[];
        links: any[];
        per_page: number;
        current_page: number;
        total: number;
        from: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Events',
        href: '/events',
    },
];

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Events" />

        <div class="mb-10 flex items-center">
            <CalendarIcon class="mr-3 h-8 w-8 text-muted-foreground" />
            <h2 class="text-3xl font-bold">Upcoming Events</h2>
        </div>

        <!-- Events list -->
        <div v-if="props.events.data.length === 0" class="p-4 text-center text-gray-500">No events found.</div>
        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3 content-between">
            <EventCard
                v-for="event in props.events.data"
                :key="event.id"
                :event="event"
                :showDetailedView="true"
            />
        </div>

        <!-- Pagination -->
        <LaravelPaginator
            v-if="props.events.data.length > 0"
            :pagination="props.events"
            onlyKey="events"
        />
    </AppLayout>
</template>
