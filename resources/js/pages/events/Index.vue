<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import AppLayout from '@/layouts/AppLayout.vue';
import EventCard from '@/components/EventCard.vue';
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

        <div class="mb-10 flex items-center">
            <CalendarIcon class="mr-3 h-8 w-8 text-muted-foreground" />
            <h2 class="text-3xl font-bold">Upcoming Events</h2>
        </div>

        <!-- Events list -->
        <div v-if="props.events.data.length === 0" class="p-4 text-center text-gray-500">No events found.</div>
        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 content-between">
            <EventCard
                v-for="event in props.events.data"
                :key="event.id"
                :event="event"
                :showDetailedView="true"
            />
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <Pagination :items-per-page="props.events.per_page" :total="props.events.total" :default-page="props.events.from">
                <PaginationContent>
                    <template v-for="(link, i) in props.events.links" :key="i">
                        <!-- Previous link -->
                        <a
                            v-if="link.label === '&laquo; Previous' && link.url"
                            href="#"
                            @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['events'] })"
                        >
                            <PaginationPrevious />
                        </a>

                        <!-- Page numbers -->
                        <a
                            v-else-if="!isNaN(parseInt(decodeHtmlEntities(link.label))) && link.url"
                            href="#"
                            @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['events'] })"
                        >
                            <PaginationItem :value="parseInt(decodeHtmlEntities(link.label))" :is-active="link.active">
                                {{ decodeHtmlEntities(link.label) }}
                            </PaginationItem>
                        </a>
                        <PaginationItem
                            v-else-if="!isNaN(parseInt(decodeHtmlEntities(link.label)))"
                            :value="parseInt(decodeHtmlEntities(link.label))"
                            :is-active="link.active"
                        >
                            {{ decodeHtmlEntities(link.label) }}
                        </PaginationItem>

                        <!-- Next link -->
                        <a
                            v-else-if="link.label === 'Next &raquo;' && link.url"
                            href="#"
                            @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['events'] })"
                        >
                            <PaginationNext />
                        </a>

                        <!-- Ellipsis -->
                        <PaginationEllipsis v-else-if="link.label === '...'" />
                    </template>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
