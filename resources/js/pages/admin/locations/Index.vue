<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

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
import { PencilIcon, EyeIcon, PlusIcon, TrashIcon } from 'lucide-vue-next';

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
    locations: {
        data: Location[];
        links: {
            first: string;
            last: string;
            prev: string | null;
            next: string | null;
        };
        current_page: number;
        from: number;
        last_page: number;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Locations', href: '/admin/locations' },
];

const deleteLocation = (id: number) => {
    if (confirm('Are you sure you want to delete this location?')) {
        router.delete(route('admin.locations.destroy', id));
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Locations" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall title="Locations" description="Manage locations in the system" />
                <Link :href="route('admin.locations.create')">
                    <Button>
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Location
                    </Button>
                </Link>
            </div>

            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Address</TableHead>
                            <TableHead>Image</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="location in locations.data" :key="location.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ location.name }}</TableCell>
                            <TableCell class="px-6 py-4">{{ location.full_address }}</TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <div>
                                        <img v-if="location.image" :src="`/storage/${location.image}`" alt="Location primary image" class="h-10 w-10 object-cover rounded-md" />
                                        <div v-else class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-md flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">No image</span>
                                        </div>
                                    </div>
                                    <div v-if="location.images && location.images.length > 0" class="text-xs text-gray-500">
                                        +{{ location.images.length }} additional
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <span
                                    :class="{
                                        'px-2 py-1 rounded text-xs font-medium': true,
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': location.is_active,
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': !location.is_active,
                                    }"
                                >
                                    {{ location.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <Link :href="route('admin.locations.show', location.id)">
                                        <Button variant="ghost" size="icon">
                                            <EyeIcon class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Link :href="route('admin.locations.edit', location.id)">
                                        <Button variant="ghost" size="icon">
                                            <PencilIcon class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button variant="ghost" size="icon" @click="deleteLocation(location.id)">
                                        <TrashIcon class="h-4 w-4 text-red-500" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="locations.data.length === 0">
                            <TableCell colspan="5" class="text-center py-4">No locations found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination :items-per-page="locations.per_page" :total="locations.total" :default-page="locations.from">
                <PaginationContent>
                    <a v-if="locations.links.prev" href="#" @click.prevent="router.visit(locations.links.prev, { preserveState: true, preserveScroll: true, only: ['locations'] })">
                        <PaginationPrevious />
                    </a>

                    <template v-for="(link, index) in locations.links" :key="index">
                        <!-- Skip previous and next links as they're handled separately -->
                        <template v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'">
                            <a v-if="!isNaN(parseInt(link.label)) && link.url" href="#" @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['locations'] })">
                                <PaginationItem
                                    :value="parseInt(link.label)"
                                    :is-active="link.active"
                                >
                                    {{ link.label }}
                                </PaginationItem>
                            </a>
                            <PaginationItem
                                v-else-if="!isNaN(parseInt(link.label))"
                                :value="parseInt(link.label)"
                                :is-active="link.active"
                            >
                                {{ link.label }}
                            </PaginationItem>
                            <PaginationEllipsis v-else-if="link.label === '...'" />
                        </template>
                    </template>

                    <a v-if="locations.links.next" href="#" @click.prevent="router.visit(locations.links.next, { preserveState: true, preserveScroll: true, only: ['locations'] })">
                        <PaginationNext />
                    </a>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
