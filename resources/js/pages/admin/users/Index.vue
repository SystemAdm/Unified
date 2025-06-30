<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { PencilIcon, TrashIcon, PlusIcon } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

interface Props {
    users: User[];
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
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Roles
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="user in props.users" :key="user.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ user.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ user.email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        {{ role }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
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
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
