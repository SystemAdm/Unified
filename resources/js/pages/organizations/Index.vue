<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import LaravelPaginator from '@/components/LaravelPaginator.vue';
import { type BreadcrumbItem } from '@/types';
import { UsersIcon } from 'lucide-vue-next';

interface Organization {
    id: number;
    name: string;
    users_count: number;
}

interface Props {
    organizations: {
        data: Organization[];
        links: any[];
        meta: any;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Organizations', href: route('organizations.index') },
];

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Organizations" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall title="Organizations" description="Browse organizations" class="m-3" />
            </div>

            <!-- Organizations list -->
            <div class="space-y-4">
                <div v-if="props.organizations.data.length === 0" class="p-4 text-center text-gray-500">
                    No organizations found.
                </div>
                <div v-else>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="org in props.organizations.data" :key="org.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2">
                                    <Link :href="route('organizations.show', { organization: org.id })" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ org.name }}
                                    </Link>
                                </h3>
                                <div class="flex items-center text-gray-500 dark:text-gray-400 mb-2">
                                    <UsersIcon class="h-4 w-4 mr-2" />
                                    <span>{{ org.users_count }} members</span>
                                </div>
                                <div class="mt-4">
                                    <Link :href="route('organizations.show', { organization: org.id })" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        View details →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6" v-if="props.organizations.meta && props.organizations.links">
                        <LaravelPaginator
                            :pagination="{
                                data: props.organizations.data,
                                links: props.organizations.links,
                                current_page: props.organizations.meta.current_page,
                                from: props.organizations.meta.from,
                                last_page: props.organizations.meta.last_page,
                                path: props.organizations.meta.path,
                                per_page: props.organizations.meta.per_page,
                                to: props.organizations.meta.to,
                                total: props.organizations.meta.total
                            }"
                            onlyKey="organizations"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
