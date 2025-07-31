<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import LaravelPaginator from '@/components/LaravelPaginator.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { PencilIcon, TrashIcon, PlusIcon, StarIcon, ShieldCheckIcon } from 'lucide-vue-next';
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
        href: route('admin.index'),
    },
    {
        title: 'Users',
        href: route('admin.users.index'),
    },
];

// No longer need deleteUser function as we're using AlertDialog
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
                                        <Button variant="outline" size="sm">
                                            <PencilIcon class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button variant="destructive" size="sm">
                                                <TrashIcon class="h-4 w-4" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the user from the system.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="window.location.href = route('admin.users.destroy', { user: user.id })">
                                                    Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <LaravelPaginator
                v-if="props.users.data.length > 0"
                :pagination="props.users"
                onlyKey="users"
            />
        </div>
    </AppLayout>
</template>
