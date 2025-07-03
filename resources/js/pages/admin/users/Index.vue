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
import { PencilIcon, TrashIcon, PlusIcon, StarIcon, ShieldCheckIcon } from 'lucide-vue-next';

interface Email {
    id: number;
    address: string;
    is_primary: boolean;
    is_verified: boolean;
}

interface Phone {
    id: number;
    number: string;
    is_primary: boolean;
    is_verified: boolean;
}

interface User {
    id: number;
    name: string;
    email: string;
    phone: string;
    emails: Email[];
    phones: Phone[];
    roles: string[];
}

interface Props {
    users: {
        data: User[];
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
        title: 'Users',
        href: '/admin/users',
    },
];

const deleteUser = (userId: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
        // Use Inertia to delete the user
        window.location.href = route('admin.users.destroy', { user: userId });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="User Management" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall title="User Management" description="Manage users in the system" />
                <Link :href="route('admin.users.create')">
                    <Button>
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add User
                    </Button>
                </Link>
            </div>

            <!-- User list -->
            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>
                                Name
                            </TableHead>
                            <TableHead>
                                Email
                            </TableHead>
                            <TableHead>
                                Phone
                            </TableHead>
                            <TableHead>
                                Roles
                            </TableHead>
                            <TableHead class="text-right">
                                Actions
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in props.users.data" :key="user.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                {{ user.name }}
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="space-y-1">
                                    <div v-for="email in user.emails" :key="email.id" class="flex items-center space-x-1">
                                        <span>{{ email.address }}</span>
                                        <StarIcon v-if="email.is_primary" class="h-4 w-4 text-yellow-500" />
                                        <ShieldCheckIcon v-if="email.is_verified" class="h-4 w-4 text-green-500" />
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="space-y-1">
                                    <div v-for="phone in user.phones" :key="phone.id" class="flex items-center space-x-1">
                                        <span>{{ phone.number }}</span>
                                        <StarIcon v-if="phone.is_primary" class="h-4 w-4 text-yellow-500" />
                                        <ShieldCheckIcon v-if="phone.is_verified" class="h-4 w-4 text-green-500" />
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100"
                                    >
                                        {{ role }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <Link :href="route('admin.users.edit', { user: user.id })">
                                        <Button variant="ghost" size="icon">
                                            <PencilIcon class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button variant="ghost" size="icon" @click="deleteUser(user.id)">
                                        <TrashIcon class="h-4 w-4 text-red-500" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <Pagination :items-per-page="props.users.per_page" :total="props.users.total" :default-page="props.users.from">
                    <PaginationContent>
                        <a v-if="props.users.links.prev" href="#" @click.prevent="router.visit(props.users.links.prev, { preserveState: true, preserveScroll: true, only: ['users'] })">
                            <PaginationPrevious />
                        </a>

                        <template v-for="(link, index) in props.users.links" :key="index">
                            <!-- Skip previous and next links as they're handled separately -->
                            <template v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'">
                                <a v-if="!isNaN(parseInt(link.label)) && link.url" href="#" @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['users'] })">
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

                        <a v-if="props.users.links.next" href="#" @click.prevent="router.visit(props.users.links.next, { preserveState: true, preserveScroll: true, only: ['users'] })">
                            <PaginationNext />
                        </a>
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
    </AppLayout>
</template>
