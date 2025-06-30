<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
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
    organizations: Organization[];
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
                <HeadingSmall title="Organizations" description="Manage your organizations" />
                <Link :href="route('admin.organizations.create')">
                    <Button>
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Organization
                    </Button>
                </Link>
            </div>

            <!-- Organizations list -->
            <div class="space-y-4">
                <div v-if="props.organizations.length === 0" class="p-4 text-center text-gray-500">
                    No organizations found.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-3 text-left">Name</th>
                                <th class="p-3 text-left">Users</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="org in props.organizations" :key="org.id" class="border-b">
                                <td class="p-3">{{ org.name }}</td>
                                <td class="p-3">
                                    <div class="flex items-center">
                                        <UsersIcon class="h-4 w-4 mr-2" />
                                        {{ org.users_count }}
                                    </div>
                                </td>
                                <td class="p-3 text-right">
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
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
