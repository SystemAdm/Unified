<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon, PlusIcon, TrashIcon, BanIcon } from 'lucide-vue-next';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

interface Announcement {
    id: number;
    title: string;
    description: string;
    type: string;
    from_datetime: string;
    to_datetime: string;
    is_published: boolean;
    visible_to_access: string[] | null;
    visible_to_role: string[] | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    announcements: {
        data: Announcement[];
        links: {
            label: string;
            url: string | null;
            active: boolean;
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
    { title: 'Admin', href: route('admin.index') },
    { title: 'Announcements', href: route('admin.announcements.index') },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const isActive = (announcement: Announcement) => {
    const now = new Date();
    const from = new Date(announcement.from_datetime);
    const to = new Date(announcement.to_datetime);
    return announcement.is_published && from <= now && to >= now;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Announcements" />

        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall title="Announcements" description="Manage announcements in the system" />
                <Link :href="route('admin.announcements.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Add Announcement
                    </Button>
                </Link>
            </div>

            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Title</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead>From</TableHead>
                            <TableHead>To</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="announcement in announcements.data" :key="announcement.id">
                            <TableCell class="px-6 py-4">
                                <div>
                                    <div class="font-medium">{{ announcement.title }}</div>
                                    <div class="text-sm text-gray-500 truncate max-w-xs">{{ announcement.description }}</div>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="{
                                          'bg-red-100 text-red-800': announcement.type === 'danger',
                                          'bg-yellow-100 text-yellow-800': announcement.type === 'warning',
                                          'bg-blue-100 text-blue-800': announcement.type === 'info',
                                          'bg-green-100 text-green-800': announcement.type === 'primary',
                                          'bg-gray-100 text-gray-800': announcement.type === 'secondary' || announcement.type === 'default'
                                      }">
                                    {{ announcement.type }}
                                </span>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ formatDate(announcement.from_datetime) }}
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ formatDate(announcement.to_datetime) }}
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <span :class="isActive(announcement) ? 'text-green-600' : 'text-red-600'">
                                    {{ isActive(announcement) ? 'Active' : 'Inactive' }}
                                </span>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.announcements.show', announcement.id)">
                                        <Button variant="secondary" size="sm"><EyeIcon class="h-4 w-4" /></Button>
                                    </Link>
                                    <Link :href="route('admin.announcements.edit', announcement.id)">
                                        <Button variant="outline" size="sm"><PencilIcon class="h-4 w-4" /></Button>
                                    </Link>
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                            >
                                                <TrashIcon class="h-4 w-4" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Are you sure you want to delete this announcement?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the announcement from the system.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel><BanIcon />Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="router.delete(route('admin.announcements.destroy', announcement.id))">
                                                    <TrashIcon />Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="announcements.data.length === 0">
                            <TableCell colspan="6" class="py-4 text-center">No announcements found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination :items-per-page="announcements.per_page" :total="announcements.total" :default-page="announcements.from">
                <PaginationContent>
                    <a
                        v-if="announcements.links.prev"
                        href="#"
                        @click.prevent="router.visit(announcements.links.prev, { preserveState: true, preserveScroll: true, only: ['announcements'] })"
                    >
                        <PaginationPrevious />
                    </a>

                    <template v-for="(link, index) in announcements.links" :key="index">
                        <!-- Skip previous and next links as they're handled separately -->
                        <template v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'">
                            <a
                                v-if="!isNaN(parseInt(link.label)) && link.url"
                                href="#"
                                @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['announcements'] })"
                            >
                                <PaginationItem :value="parseInt(link.label)" :is-active="link.active">
                                    {{ link.label }}
                                </PaginationItem>
                            </a>
                            <PaginationItem v-else-if="!isNaN(parseInt(link.label))" :value="parseInt(link.label)" :is-active="link.active">
                                {{ link.label }}
                            </PaginationItem>
                            <PaginationEllipsis v-else-if="link.label === '...'" />
                        </template>
                    </template>

                    <a
                        v-if="announcements.links.next"
                        href="#"
                        @click.prevent="router.visit(announcements.links.next, { preserveState: true, preserveScroll: true, only: ['announcements'] })"
                    >
                        <PaginationNext />
                    </a>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
