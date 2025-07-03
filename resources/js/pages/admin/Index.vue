<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { PlusIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin Dashboard" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall title="Admin Dashboard" description="Manage your application" class="m-3" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Events Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-4">Events</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                            Manage events, registrations, and attendees.
                        </p>
                        <div class="flex space-x-2">
                            <Link :href="route('admin.events.index')">
                                <Button variant="outline">
                                    View Events
                                </Button>
                            </Link>
                            <Link :href="route('admin.events.create')">
                                <Button>
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Create Event
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Users Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-4">Users</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                            Manage users, roles, and permissions.
                        </p>
                        <div class="flex space-x-2">
                            <Link :href="route('admin.users.index')">
                                <Button variant="outline">
                                    View Users
                                </Button>
                            </Link>
                            <Link :href="route('admin.users.create')">
                                <Button>
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Create User
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Organizations Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-4">Organizations</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                            Manage organizations and their members.
                        </p>
                        <div class="flex space-x-2">
                            <Link :href="route('admin.organizations.index')">
                                <Button variant="outline">
                                    View Organizations
                                </Button>
                            </Link>
                            <Link :href="route('admin.organizations.create')">
                                <Button>
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Create Organization
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
