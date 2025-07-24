<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { UserIcon, ArrowLeftIcon } from 'lucide-vue-next';

interface User {
    id: number;
    given_name: string;
    family_name: string;
    additional_name?: string;
    birthday?: string;
    pivot?: {
        created_at: string;
        updated_at: string;
    };
}

interface Event {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    location: string;
    status: string;
}

interface Props {
    event: Event;
    users: User[];
    listType: 'registered' | 'visited' | 'inside';
    title: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Events', href: route('admin.events.index') },
    { title: props.event.title, href: route('admin.events.show', { event: props.event.id }) },
    { title: props.title, href: '#' },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getListTypeColor = (type: string) => {
    switch (type) {
        case 'registered':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        case 'visited':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'inside':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${props.title} - ${props.event.title}`" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall
                    :title="props.title"
                    :description="`${props.users.length} users for event: ${props.event.title}`"
                    class="m-3"
                />
                <div class="flex items-center space-x-2">
                    <span
                        class="px-3 py-1 rounded-full text-sm font-medium"
                        :class="getListTypeColor(props.listType)"
                    >
                        {{ props.listType.charAt(0).toUpperCase() + props.listType.slice(1) }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ props.users.length }} {{ props.users.length === 1 ? 'user' : 'users' }}
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div v-if="props.users.length === 0" class="p-8 text-center">
                    <UserIcon class="h-12 w-12 mx-auto text-gray-400 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                        No {{ props.listType }} users
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        There are currently no users in the {{ props.listType }} list for this event.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Full Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Birthday
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Added On
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="user in props.users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                <UserIcon class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                ID: {{ user.id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ user.given_name }}
                                        <span v-if="user.additional_name">{{ user.additional_name }} </span>
                                        {{ user.family_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ user.birthday ? formatDate(user.birthday) : 'Not provided' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ user.pivot?.created_at ? formatDate(user.pivot.created_at) : 'Unknown' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Back to Event Button -->
            <div class="flex justify-start">
                <Link :href="route('admin.events.show', { event: props.event.id })">
                    <Button variant="outline">
                        <ArrowLeftIcon class="h-4 w-4 mr-2" />
                        Back to Event
                    </Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
