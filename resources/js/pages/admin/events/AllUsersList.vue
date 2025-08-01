<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { UserIcon, ArrowLeftIcon, ArrowRightIcon, XIcon, UserMinusIcon } from 'lucide-vue-next';

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
    registeredUsers: User[];
    attendingUsers: User[];
    insideUsers: User[];
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
    return new Date(dateString).toLocaleDateString('NO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Functions to handle user state transitions
const moveToAttending = (userId: number) => {
    router.post(route('admin.events.users.copy-to-attending', {
        event: props.event.id,
        user: userId
    }));
};

// Move user to Inside list
const moveToInside = (userId: number) => {
    router.post(route('admin.events.users.copy-to-inside', {
        event: props.event.id,
        user: userId
    }));
};

// Remove user from Inside list
const removeFromInside = (userId: number) => {
    router.delete(route('admin.events.users.remove-from-inside', {
        event: props.event.id,
        user: userId
    }));
};

const removeFromAll = (userId: number) => {
    router.delete(route('admin.events.users.remove-from-all', {
        event: props.event.id,
        user: userId
    }));
};

const getListTypeColor = (type: string) => {
    switch (type) {
        case 'registered':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        case 'attending':
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
                    :description="`User lists for event: ${props.event.title}`"
                    class="m-3"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4">
                <!-- Registered Users Column -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-medium">Registered Users</h3>
                        <span
                            class="px-3 py-1 rounded-full text-sm font-medium"
                            :class="getListTypeColor('registered')"
                        >
                            {{ props.registeredUsers.length }}
                        </span>
                    </div>
                    <div v-if="props.registeredUsers.length === 0" class="p-8 text-center">
                        <UserIcon class="h-12 w-12 mx-auto text-gray-400 mb-4" />
                        <p class="text-gray-500 dark:text-gray-400">
                            No registered users for this event.
                        </p>
                    </div>
                    <div v-else class="overflow-y-auto max-h-96">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Added On
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="user in props.registeredUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-2">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ user.given_name }}
                                                    <span v-if="user.additional_name">{{ user.additional_name }} </span>
                                                    {{ user.family_name }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    ID: {{ user.id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        {{ user.pivot?.created_at ? formatDate(user.pivot.created_at) : 'Unknown' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="text-blue-600 hover:text-blue-800"
                                                @click="moveToAttending(user.id)"
                                                title="Move to Attending"
                                            >
                                                <ArrowRightIcon class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="text-red-600 hover:text-red-800"
                                                @click="removeFromAll(user.id)"
                                                title="Remove from all lists"
                                            >
                                                <UserMinusIcon class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Attending Users Column -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-medium">Attending Users</h3>
                        <span
                            class="px-3 py-1 rounded-full text-sm font-medium"
                            :class="getListTypeColor('attending')"
                        >
                            {{ props.attendingUsers.length }}
                        </span>
                    </div>
                    <div v-if="props.attendingUsers.length === 0" class="p-8 text-center">
                        <UserIcon class="h-12 w-12 mx-auto text-gray-400 mb-4" />
                        <p class="text-gray-500 dark:text-gray-400">
                            No attending users for this event.
                        </p>
                    </div>
                    <div v-else class="overflow-y-auto max-h-96">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Added On
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="user in props.attendingUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-2">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ user.given_name }}
                                                    <span v-if="user.additional_name">{{ user.additional_name }} </span>
                                                    {{ user.family_name }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    ID: {{ user.id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        {{ user.pivot?.created_at ? formatDate(user.pivot.created_at) : 'Unknown' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="text-purple-600 hover:text-purple-800"
                                                @click="moveToInside(user.id)"
                                                title="Move to Inside"
                                            >
                                                <ArrowRightIcon class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="text-red-600 hover:text-red-800"
                                                @click="removeFromAll(user.id)"
                                                title="Remove from all lists"
                                            >
                                                <UserMinusIcon class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Inside Users Column -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-medium">Inside Users</h3>
                        <span
                            class="px-3 py-1 rounded-full text-sm font-medium"
                            :class="getListTypeColor('inside')"
                        >
                            {{ props.insideUsers.length }}
                        </span>
                    </div>
                    <div v-if="props.insideUsers.length === 0" class="p-8 text-center">
                        <UserIcon class="h-12 w-12 mx-auto text-gray-400 mb-4" />
                        <p class="text-gray-500 dark:text-gray-400">
                            No inside users for this event.
                        </p>
                    </div>
                    <div v-else class="overflow-y-auto max-h-96">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Added On
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="user in props.insideUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-2">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ user.given_name }}
                                                    <span v-if="user.additional_name">{{ user.additional_name }} </span>
                                                    {{ user.family_name }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    ID: {{ user.id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        {{ user.pivot?.created_at ? formatDate(user.pivot.created_at) : 'Unknown' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="text-gray-600 hover:text-gray-800"
                                                @click="removeFromInside(user.id)"
                                                title="Remove from Inside"
                                            >
                                                <XIcon class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="text-red-600 hover:text-red-800"
                                                @click="removeFromAll(user.id)"
                                                title="Remove from all lists"
                                            >
                                                <UserMinusIcon class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
