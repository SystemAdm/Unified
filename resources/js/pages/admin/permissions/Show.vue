<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Permission {
    id: number;
    name: string;
    roles: Role[];
    users: User[];
}

interface Props {
    permission: Permission;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Permissions', href: route('admin.permissions.index') },
    { title: props.permission.name, href: route('admin.permissions.show', { permission: props.permission.id }) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Permission: ${props.permission.name}`" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall :title="`Permission: ${props.permission.name}`" description="View permission details" />

            <div class="grid gap-6">
                <!-- Roles with this permission -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Roles with this permission</h3>
                    <div class="overflow-x-auto">
                        <Table class="min-w-full">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        Name
                                    </TableHead>
                                    <TableHead class="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="role in props.permission.roles" :key="role.id">
                                    <TableCell>
                                        {{ role.name }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Link :href="route('admin.roles.edit', { role: role.id })">
                                            <Button variant="outline" size="sm">
                                                Edit Role
                                            </Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.permission.roles.length === 0">
                                    <TableCell colspan="2" class="text-center text-sm text-gray-500">
                                        No roles have this permission
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>

                <!-- Users with this permission -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Users with this permission</h3>
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
                                    <TableHead class="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="user in props.permission.users" :key="user.id">
                                    <TableCell>
                                        {{ user.name }}
                                    </TableCell>
                                    <TableCell>
                                        {{ user.email }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Link :href="route('admin.users.edit', { user: user.id })">
                                            <Button variant="outline" size="sm">
                                                Edit User
                                            </Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.permission.users.length === 0">
                                    <TableCell colspan="3" class="text-center text-sm text-gray-500">
                                        No users have this permission
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <Link :href="route('admin.permissions.index')">
                    <Button variant="outline">Back to Permissions</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
