<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { UsersIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Organization {
    id: number;
    name: string;
    users: User[];
}

interface Props {
    organization: Organization;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Organizations',
        href: '/organizations',
    },
    {
        title: props.organization.name,
        href: `/organizations/${props.organization.id}`,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.organization.name" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall :title="props.organization.name" description="Organization details" class="m-3" />
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start mb-6">
                        <div>
                            <h1 class="text-2xl font-bold mb-4">{{ props.organization.name }}</h1>

                            <div class="flex items-center text-gray-500 dark:text-gray-400 mb-2">
                                <UsersIcon class="h-4 w-4 mr-2" />
                                <span>{{ props.organization.users.length }} members</span>
                            </div>
                        </div>
                    </div>

                    <div class="prose dark:prose-invert max-w-none">
                        <div v-if="props.organization.users.length > 0" class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">Members</h3>
                            <ul class="list-disc pl-5">
                                <li v-for="user in props.organization.users" :key="user.id" class="mb-1">
                                    {{ user.name }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <Link :href="route('organizations.index')" class="text-blue-600 dark:text-blue-400 hover:underline">
                            ← Back to organizations
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
