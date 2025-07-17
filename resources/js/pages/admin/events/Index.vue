<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/utils';
import { CalendarIcon, FilterIcon, MapPinIcon, PencilIcon, PlusIcon, TrashIcon } from 'lucide-vue-next';

interface Organizer {
    link: string | null;
    name: string;
}

interface Event {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    location: { name: string } | null;
    status: string;
    organizer: Organizer | null;
}

interface Location {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
}

interface Organization {
    id: number;
    name: string;
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
    filters?: {
        from_date?: string;
        to_date?: string;
        location_id?: number;
        status?: string;
        organizer_type?: string;
        organizer_id?: number;
        sort_field?: string;
        sort_direction?: string;
    };
    locations?: Location[];
    users?: User[];
    organizations?: Organization[];
}

const props = defineProps<Props>();
const goto = (url: string) => {
    router.get(url, { preserveState: true, preserveScroll: true });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Events', href: route('admin.events.index') },
];

// Format date for display
const formatEventDate = (date: string) => {
    return formatDate(date, 'ddd DD/MM/YYYY HH:mm');
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

// Filter form
const showFilters = ref(false);
const form = useForm({
    from_date: props.filters?.from_date || '',
    to_date: props.filters?.to_date || '',
    location_id: props.filters?.location_id || 'all',
    status: props.filters?.status || 'all',
    organizer_type: props.filters?.organizer_type || 'all',
    organizer_id: props.filters?.organizer_id || 'all',
    sort_field: props.filters?.sort_field || 'start_date',
    sort_direction: props.filters?.sort_direction || 'desc',
});

// Available statuses
const statuses = [
    { value: 'published', label: 'Published' },
    { value: 'draft', label: 'Draft' },
    { value: 'cancelled', label: 'Cancelled' },
];

// Available sort fields
const sortFields = [
    { value: 'title', label: 'Title' },
    { value: 'start_date', label: 'Date' },
    { value: 'status', label: 'Status' },
];

// Available sort directions
const sortDirections = [
    { value: 'asc', label: 'A-Z (Ascending)' },
    { value: 'desc', label: 'Z-A (Descending)' },
];

// Watch for changes in the organizer type to reset the organizer id
watch(() => form.organizer_type, () => {
    form.organizer_id = 'all';
});

// Apply filters
const applyFilters = () => {
    // Create a copy of the form data
    const formData = { ...form };

    // Convert 'all' values to empty strings for backend processing
    if (formData.location_id === 'all') formData.location_id = '';
    if (formData.status === 'all') formData.status = '';
    if (formData.organizer_type === 'all') formData.organizer_type = '';
    if (formData.organizer_id === 'all') formData.organizer_id = '';

    router.get(route('admin.events.index'), formData, {
        preserveState: true,
        preserveScroll: true,
        only: ['events', 'filters'],
    });
};

// Reset filters
const resetFilters = () => {
    form.from_date = '';
    form.to_date = '';
    form.location_id = 'all';
    form.status = 'all';
    form.organizer_type = 'all';
    form.organizer_id = 'all';
    form.sort_field = 'start_date';
    form.sort_direction = 'desc';
    applyFilters();
};

// Toggle filters visibility
const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Manage Events" />

        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall title="Events" description="Manage your events" class="m-3" />
                <div class="flex space-x-2">
                    <Button variant="outline" @click="toggleFilters">
                        <FilterIcon class="mr-2 h-4 w-4" />
                        {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </Button>
                    <Link :href="route('admin.events.create')">
                        <Button>
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add Event
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <Card v-if="showFilters" class="mb-6">
                <CardContent class="pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Date Range -->
                        <div class="space-y-2">
                            <Label for="from_date">From Date</Label>
                            <Input
                                id="from_date"
                                v-model="form.from_date"
                                type="date"
                                placeholder="From Date"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="to_date">To Date</Label>
                            <Input
                                id="to_date"
                                v-model="form.to_date"
                                type="date"
                                placeholder="To Date"
                            />
                        </div>

                        <!-- Location -->
                        <div class="space-y-2">
                            <Label for="location_id">Location</Label>
                            <Select v-model="form.location_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select location" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Locations</SelectItem>
                                    <SelectItem
                                        v-for="location in props.locations"
                                        :key="location.id"
                                        :value="location.id"
                                    >
                                        {{ location.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Status -->
                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    <SelectItem
                                        v-for="status in statuses"
                                        :key="status.value"
                                        :value="status.value"
                                    >
                                        {{ status.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Organizer Type -->
                        <div class="space-y-2">
                            <Label for="organizer_type">Organizer Type</Label>
                            <Select v-model="form.organizer_type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select organizer type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Organizers</SelectItem>
                                    <SelectItem value="user">User</SelectItem>
                                    <SelectItem value="organization">Organization</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Organizer -->
                        <div class="space-y-2">
                            <Label for="organizer_id">Organizer</Label>
                            <Select v-model="form.organizer_id" :disabled="!form.organizer_type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select organizer" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All</SelectItem>
                                    <template v-if="form.organizer_type === 'user'">
                                        <SelectItem
                                            v-for="user in props.users"
                                            :key="user.id"
                                            :value="user.id"
                                        >
                                            {{ user.name }}
                                        </SelectItem>
                                    </template>
                                    <template v-else-if="form.organizer_type === 'organization'">
                                        <SelectItem
                                            v-for="org in props.organizations"
                                            :key="org.id"
                                            :value="org.id"
                                        >
                                            {{ org.name }}
                                        </SelectItem>
                                    </template>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Sort Field -->
                        <div class="space-y-2">
                            <Label for="sort_field">Sort By</Label>
                            <Select v-model="form.sort_field">
                                <SelectTrigger>
                                    <SelectValue placeholder="Sort by" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="field in sortFields"
                                        :key="field.value"
                                        :value="field.value"
                                    >
                                        {{ field.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Sort Direction -->
                        <div class="space-y-2">
                            <Label for="sort_direction">Sort Direction</Label>
                            <Select v-model="form.sort_direction">
                                <SelectTrigger>
                                    <SelectValue placeholder="Sort direction" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="direction in sortDirections"
                                        :key="direction.value"
                                        :value="direction.value"
                                    >
                                        {{ direction.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 mt-6">
                        <Button variant="outline" @click="resetFilters">Reset</Button>
                        <Button @click="applyFilters">Apply Filters</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Events list -->
            <div class="space-y-4">
                <div v-if="props.events.data.length === 0" class="p-4 text-center text-gray-500">No events found.</div>
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
                                    <Link
                                        :href="route('admin.events.show', { event: event.id })"
                                        class="hover:text-blue-600 dark:hover:text-blue-400"
                                    >
                                        {{ event.title }}
                                    </Link>
                                </TableCell>
                                <TableCell class="p-3">
                                    <div class="flex items-center">
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ formatEventDate(event.start_date) }}
                                    </div>
                                </TableCell>
                                <TableCell class="p-3">
                                    <div v-if="event.location" class="flex items-center">
                                        <MapPinIcon class="mr-2 h-4 w-4" />
                                        {{ event.location.name }}
                                    </div>
                                    <span v-else class="text-gray-400">-</span>
                                </TableCell>
                                <TableCell class="p-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-medium" :class="getStatusClass(event.status)">
                                        {{ event.status }}
                                    </span>
                                </TableCell>
                                <TableCell class="p-3">
                                    <div v-if="event.organizer" class="flex items-center">
                                        <div v-if="event.organizer.link">
                                            <Button variant="link" @click.prevent="goto(event.organizer.link)">
                                                {{ event.organizer.name }}
                                            </Button>
                                        </div>
                                        <div v-else>{{ event.organizer.name }}</div>
                                    </div>
                                    <span v-else class="text-gray-400">-</span>
                                </TableCell>
                                <TableCell class="p-3 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <Link :href="route('admin.events.edit', { event: event.id })">
                                            <Button variant="outline" size="sm">
                                                <PencilIcon class="mr-2 h-4 w-4" />
                                                Edit
                                            </Button>
                                        </Link>
                                        <Button variant="destructive" size="sm" @click="confirmDelete(event.id, event.title)">
                                            <TrashIcon class="mr-2 h-4 w-4" />
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
            </div>
        </div>
    </AppLayout>
</template>
