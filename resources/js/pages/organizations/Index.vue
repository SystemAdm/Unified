<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';
import { type BreadcrumbItem } from '@/types';
import { UsersIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

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
    {
        title: 'Organizations',
        href: '/organizations',
    },
];

// Decode HTML entities for pagination
const decodeHtmlEntities = (html: string) => {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = html;
    return textarea.value;
};
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
                    <div class="mt-6">
                        <Pagination :items-per-page="10" :total="100" :default-page="1">
                            <PaginationContent>
                                <template v-for="(link, i) in props.organizations.links" :key="i">
                                    <!-- Previous link -->
                                    <PaginationPrevious
                                        v-if="link.label === '&laquo; Previous'"
                                        :href="link.url"
                                    />

                                    <!-- Page numbers -->
                                    <PaginationItem
                                        v-else-if="!isNaN(parseInt(decodeHtmlEntities(link.label)))"
                                        :value="parseInt(decodeHtmlEntities(link.label))"
                                        :is-active="link.active"
                                        :href="link.url"
                                    >
                                        {{ decodeHtmlEntities(link.label) }}
                                    </PaginationItem>

                                    <!-- Next link -->
                                    <PaginationNext
                                        v-else-if="link.label === 'Next &raquo;'"
                                        :href="link.url"
                                    />

                                    <!-- Ellipsis -->
                                    <PaginationEllipsis
                                        v-else-if="link.label === '...'"
                                    />
                                </template>
                            </PaginationContent>
                        </Pagination>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
