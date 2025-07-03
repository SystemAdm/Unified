<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { EyeIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Permission {
    id: number;
    name: string;
    roles: string[];
}

interface Props {
    permissions: {
        current_page: number;
        data: Permission[];
        first_page_url: string;
        from: number;
        last_page: number;
        last_page_url: string;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        next_page_url: string | null;
        path: string;
        per_page: number;
        prev_page_url: string | null;
        to: number;
        total: number;
    };
    actions: string[];
    models: string[];
    filters: {
        action: string | null;
        model: string | null;
    };
}

const props = defineProps<Props>();

const action = ref(props.filters.action || 'all');
const model = ref(props.filters.model || 'all');

watch([action, model], ([newAction, newModel]) => {
    router.visit(
        route('admin.permissions.index', {
            action: newAction === 'all' ? null : newAction,
            model: newModel === 'all' ? null : newModel
        }),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['permissions']
        }
    );
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Permissions',
        href: '/admin/permissions',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Permission Management" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Permission Management" description="View permissions in the system" />

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label for="action-filter">Filter by Action</Label>
                    <Select :model-value="action" @update:model-value="(value) => action = value">
                        <SelectTrigger>
                            <SelectValue placeholder="Select an action" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Actions</SelectLabel>
                                <SelectItem value="all">All Actions</SelectItem>
                                <SelectItem v-for="actionItem in props.actions" :key="actionItem" :value="actionItem.toString()">
                                    {{ actionItem }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-2">
                    <Label for="model-filter">Filter by Model</Label>
                    <Select :model-value="model" @update:model-value="(value) => model = value">
                        <SelectTrigger>
                            <SelectValue placeholder="Select a model" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Models</SelectLabel>
                                <SelectItem value="all">All Models</SelectItem>
                                <SelectItem v-for="modelItem in props.models" :key="modelItem" :value="modelItem.toString()">
                                    {{ modelItem }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Permission list -->
            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>
                                Name
                            </TableHead>
                            <TableHead>
                                Assigned Roles
                            </TableHead>
                            <TableHead class="text-right">
                                Actions
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="permission in props.permissions.data" :key="permission.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                {{ permission.name }}
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="role in permission.roles"
                                        :key="role"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100"
                                    >
                                        {{ role }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <Link :href="route('admin.permissions.show', { permission: permission.id })">
                                        <Button variant="ghost" size="icon">
                                            <EyeIcon class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <Pagination :items-per-page="props.permissions.per_page" :total="props.permissions.total" :default-page="props.permissions.from">
                    <PaginationContent>
                        <a v-if="props.permissions.prev_page_url" href="#" @click.prevent="router.visit(props.permissions.prev_page_url, { preserveState: true, preserveScroll: true, only: ['permissions'] })">
                            <PaginationPrevious />
                        </a>

                        <template v-for="(link, index) in props.permissions.links" :key="index">
                            <!-- Skip previous and next links as they're handled separately -->
                            <template v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'">
                                <a v-if="!isNaN(parseInt(link.label)) && link.url" href="#" @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['permissions'] })">
                                    <PaginationItem
                                        :value="parseInt(link.label)"
                                        :is-active="link.active"
                                    >
                                        {{ link.label }}
                                    </PaginationItem>
                                </a>
                                <PaginationItem
                                    v-else-if="!isNaN(parseInt(link.label))"
                                    :value="parseInt(link.label)"
                                    :is-active="link.active"
                                >
                                    {{ link.label }}
                                </PaginationItem>
                                <PaginationEllipsis v-else-if="link.label === '...'" />
                            </template>
                        </template>

                        <a v-if="props.permissions.next_page_url" href="#" @click.prevent="router.visit(props.permissions.next_page_url, { preserveState: true, preserveScroll: true, only: ['permissions'] })">
                            <PaginationNext />
                        </a>
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
    </AppLayout>
</template>
