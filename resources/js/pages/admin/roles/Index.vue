<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import LaravelPaginator from '@/components/LaravelPaginator.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { PencilIcon, TrashIcon, PlusIcon } from 'lucide-vue-next';
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

interface Role {
    id: number;
    name: string;
    permissions: string[];
}

interface Props {
    roles: {
        data: Role[];
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
        title: 'Roles',
        href: route('admin.roles.index'),
    },
];

const deleteRole = (roleId: number) => {
    // Use Inertia to delete the role
    window.location.href = route('admin.roles.destroy', { role: roleId });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Role Management" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall title="Role Management" description="Manage roles in the system" />
                <Link :href="route('admin.roles.create')">
                    <Button>
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Role
                    </Button>
                </Link>
            </div>

            <!-- Role list -->
            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>
                                Name
                            </TableHead>
                            <TableHead>
                                Permissions
                            </TableHead>
                            <TableHead class="text-right">
                                Actions
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="role in props.roles.data" :key="role.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                {{ role.name }}
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="permission in role.permissions"
                                        :key="permission"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium dark:bg-green-800 dark:text-green-100 bg-green-100 text-green-800"
                                    >
                                        {{ permission }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <Link :href="route('admin.roles.edit', { role: role.id })">
                                        <Button variant="ghost" size="icon">
                                            <PencilIcon class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button variant="ghost" size="icon">
                                                <TrashIcon class="h-4 w-4 text-red-500" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the role from the system.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="deleteRole(role.id)">
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
                v-if="props.roles.data.length > 0"
                :pagination="props.roles"
                onlyKey="roles"
            />
        </div>
    </AppLayout>
</template>
