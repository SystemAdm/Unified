<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { PlusIcon, PencilIcon, TrashIcon, CalendarIcon, MapPinIcon, UserIcon } from 'lucide-vue-next';
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
    events: {
        data: Event[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        from: number;
        last_page: number;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Events',
        href: '/admin/events',
    },
];

// Format date for display
const formatEventDate = (date: string) => {
    return formatDate(date, 'MMM D, YYYY h:mm A');
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'published':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'draft':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }
};

const confirmDelete = (id: number, title: string) => {
    if (confirm(`Are you sure you want to delete the event "${title}"?`)) {
        // Use Inertia to delete the event
        window.location.href = route('admin.events.destroy', { event: id });
    }
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
        <Head title="Manage Events" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall title="Events" description="Manage your events" class="m-3" />
                <Link :href="route('admin.events.create')">
                    <Button>
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Event
                    </Button>
                </Link>
            </div>

            <!-- Events list -->
            <div class="space-y-4">
                <div v-if="props.events.data.length === 0" class="p-4 text-center text-gray-500">
                    No events found.
                </div>
                <div v-else class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Title</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Location</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Organizer</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="event in props.events.data" :key="event.id">
                                <TableCell class="p-3">
                                    <Link :href="route('admin.events.show', { event: event.id })" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ event.title }}
                                    </Link>
                                </TableCell>
                                <TableCell class="p-3">
                                    <div class="flex items-center">
                                        <CalendarIcon class="h-4 w-4 mr-2" />
                                        {{ formatEventDate(event.start_date) }}
                                    </div>
                                </TableCell>
                                <TableCell class="p-3">
                                    <div v-if="event.location" class="flex items-center">
                                        <MapPinIcon class="h-4 w-4 mr-2" />
                                        {{ event.location }}
                                    </div>
                                    <span v-else class="text-gray-400">-</span>
                                </TableCell>
                                <TableCell class="p-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getStatusClass(event.status)">
                                        {{ event.status }}
                                    </span>
                                </TableCell>
                                <TableCell class="p-3">
                                    <div v-if="event.user" class="flex items-center">
                                        <UserIcon class="h-4 w-4 mr-2" />
                                        {{ event.user.name }}
                                    </div>
                                    <span v-else class="text-gray-400">-</span>
                                </TableCell>
                                <TableCell class="p-3 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <Link :href="route('admin.events.edit', { event: event.id })">
                                            <Button variant="outline" size="sm">
                                                <PencilIcon class="h-4 w-4 mr-2" />
                                                Edit
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            @click="confirmDelete(event.id, event.title)"
                                        >
                                            <TrashIcon class="h-4 w-4 mr-2" />
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
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
                                    <PaginationItem
                                        :value="parseInt(decodeHtmlEntities(link.label))"
                                        :is-active="link.active"
                                    >
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
                                <PaginationEllipsis
                                    v-else-if="link.label === '...'"
                                />
                            </template>
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
