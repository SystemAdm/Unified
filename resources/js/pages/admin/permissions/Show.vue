<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
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
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Permissions',
        href: '/admin/permissions',
    },
    {
        title: props.permission.name,
        href: `/admin/permissions/${props.permission.id}`,
    },
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
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="role in props.permission.roles" :key="role.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ role.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('admin.roles.edit', { role: role.id })">
                                            <Button variant="outline" size="sm">
                                                Edit Role
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="props.permission.roles.length === 0">
                                    <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No roles have this permission
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Users with this permission -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Users with this permission</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in props.permission.users" :key="user.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ user.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ user.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('admin.users.edit', { user: user.id })">
                                            <Button variant="outline" size="sm">
                                                Edit User
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="props.permission.users.length === 0">
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No users have this permission
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
