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
import { PlusIcon, PencilIcon, TrashIcon, UsersIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface Organization {
    id: number;
    name: string;
    users_count: number;
}

interface Props {
    organizations: {
        data: Organization[];
        links: {
            first: string;
            last: string;
            prev: string | null;
            next: string | null;
        };
        current_page: number;
        from: number;
        last_page: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
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
        title: 'Organizations',
        href: '/admin/organizations',
    },
];

const confirmDelete = (id: number, name: string) => {
    if (confirm(`Are you sure you want to delete the organization "${name}"?`)) {
        // Use Inertia to delete the organization
        window.location.href = route('admin.organizations.destroy', { organization: id });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Manage Organizations" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall title="Organizations" description="Manage your organizations" class="m-3" />
                <Link :href="route('admin.organizations.create')">
                    <Button>
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Organization
                    </Button>
                </Link>
            </div>

            <!-- Organizations list -->
            <div class="space-y-4">
                <div v-if="props.organizations.data.length === 0" class="p-4 text-center text-gray-500">
                    No organizations found.
                </div>
                <div v-else class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Users</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="org in props.organizations.data" :key="org.id">
                                <TableCell class="p-3">{{ org.name }}</TableCell>
                                <TableCell class="p-3">
                                    <div class="flex items-center">
                                        <UsersIcon class="h-4 w-4 mr-2" />
                                        {{ org.users_count }}
                                    </div>
                                </TableCell>
                                <TableCell class="p-3 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <Link :href="route('admin.organizations.edit', { organization: org.id })">
                                            <Button variant="outline" size="sm">
                                                <PencilIcon class="h-4 w-4 mr-2" />
                                                Edit
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            @click="confirmDelete(org.id, org.name)"
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
                <div v-if="props.organizations.last_page > 1" class="mt-4">
                    <Pagination :items-per-page="props.organizations.per_page" :total="props.organizations.total" :default-page="props.organizations.from">
                        <PaginationContent>
                            <a v-if="props.organizations.links.prev" href="#" @click.prevent="router.visit(props.organizations.links.prev, { preserveState: true, preserveScroll: true, only: ['organizations'] })">
                                <PaginationPrevious />
                            </a>

                            <template v-for="(link, index) in props.organizations.links" :key="index">
                                <!-- Skip previous and next links as they're handled separately -->
                                <template v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'">
                                    <a v-if="!isNaN(parseInt(link.label)) && link.url" href="#" @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['organizations'] })">
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

                            <a v-if="props.organizations.links.next" href="#" @click.prevent="router.visit(props.organizations.links.next, { preserveState: true, preserveScroll: true, only: ['organizations'] })">
                                <PaginationNext />
                            </a>
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
