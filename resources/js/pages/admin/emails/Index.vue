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
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { PencilIcon, TrashIcon, PlusIcon, StarIcon, ShieldCheckIcon } from 'lucide-vue-next';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

interface User {
    id: number;
    name: string;
    pivot: {
        is_primary: boolean;
        verified_at: string | null;
    };
}

interface Email {
    id: number;
    address: string;
    users: User[];
}

interface Props {
    emails: {
        data: Email[];
        links: {
            first: string;
            last: string;
            prev: string | null;
            next: string | null;
        };
        current_page: number;
        from: number;
        last_page: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: route('admin.index'),
    },
    {
        title: 'Emails',
        href: route('admin.emails.index'),
    },
];

const deleteEmail = (emailId: number) => {
    // Use Inertia to delete the email
    window.location.href = route('admin.emails.destroy', { email: emailId });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Email Management" />

        <div class="flex flex-col space-y-6">
            <div class="flex justify-between items-center">
                <HeadingSmall title="Email Management" description="Manage email addresses in the system" />
                <Link :href="route('admin.emails.create')">
                    <Button>
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Email
                    </Button>
                </Link>
            </div>

            <!-- Email list -->
            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>
                                Email Address
                            </TableHead>
                            <TableHead>
                                Users
                            </TableHead>
                            <TableHead class="text-right">
                                Actions
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="email in props.emails.data" :key="email.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                {{ email.address }}
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="space-y-1">
                                    <div v-for="user in email.users" :key="user.id" class="flex items-center space-x-1">
                                        <span>{{ user.name }}</span>
                                        <StarIcon v-if="user.pivot.is_primary" class="h-4 w-4 text-yellow-500" title="Primary" />
                                        <ShieldCheckIcon v-if="user.pivot.verified_at" class="h-4 w-4 text-green-500" title="Verified" />
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <Link :href="route('admin.emails.edit', { email: email.id })">
                                        <Button variant="ghost" size="icon">
                                            <PencilIcon class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button variant="ghost" size="icon">
                                                <TrashIcon class="h-4 w-4 text-red-500" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the email address from the system.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="deleteEmail(email.id)">
                                                    Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <Pagination :items-per-page="props.emails.per_page" :total="props.emails.total" :default-page="props.emails.from">
                    <PaginationContent>
                        <a v-if="props.emails.links.prev" href="#" @click.prevent="router.visit(props.emails.links.prev, { preserveState: true, preserveScroll: true, only: ['emails'] })">
                            <PaginationPrevious />
                        </a>

                        <template v-for="(link, index) in props.emails.links" :key="index">
                            <!-- Skip previous and next links as they're handled separately -->
                            <template v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'">
                                <a v-if="!isNaN(parseInt(link.label)) && link.url" href="#" @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['emails'] })">
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

                        <a v-if="props.emails.links.next" href="#" @click.prevent="router.visit(props.emails.links.next, { preserveState: true, preserveScroll: true, only: ['emails'] })">
                            <PaginationNext />
                        </a>
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
    </AppLayout>
</template>
