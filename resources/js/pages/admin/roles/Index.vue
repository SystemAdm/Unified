<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { PencilIcon, TrashIcon, PlusIcon } from 'lucide-vue-next';

interface Role {
    id: number;
    name: string;
    permissions: string[];
}

interface Props {
    roles: Role[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Roles',
        href: '/admin/roles',
    },
];

const deleteRole = (roleId: number) => {
    if (confirm('Are you sure you want to delete this role?')) {
        // Use Inertia to delete the role
        window.location.href = route('admin.roles.destroy', { role: roleId });
    }
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
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Permissions
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="role in props.roles" :key="role.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ role.name }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="permission in role.permissions"
                                        :key="permission"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                    >
                                        {{ permission }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <Link :href="route('admin.roles.edit', { role: role.id })">
                                        <Button variant="ghost" size="icon">
                                            <PencilIcon class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button variant="ghost" size="icon" @click="deleteRole(role.id)">
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
