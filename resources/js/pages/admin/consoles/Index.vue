<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon, PlusIcon, TrashIcon, BanIcon, ExternalLinkIcon } from 'lucide-vue-next';
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

interface Console {
    id: number;
    name: string;
    cpu: string | null;
    gpu: string | null;
    psu: string | null;
    ram: string | null;
    hdd: string | null;
    ssd: string | null;
    description: string | null;
    is_active: boolean;
}
interface Props {
    consoles: {
        data: Console[];
        links: {
            label: string;
            url: string | null;
            active: boolean;
            first: string;
            last: string;
            prev: string | null;
            next: string | null;
        };
        current_page: number;
        from: number;
        last_page: number;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}
defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Consoles', href: route('admin.consoles.index') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Consoles" />

        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall title="Consoles" description="Manage consoles in the system" />
                <div class="flex space-x-2">
                    <Link href="/consoles">
                        <Button variant="outline" class="gap-2">
                            <ExternalLinkIcon class="h-4 w-4" />
                            View Public Consoles
                        </Button>
                    </Link>
                    <Link :href="route('admin.consoles.create')">
                        <Button>
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add Console
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>CPU</TableHead>
                            <TableHead>GPU</TableHead>
                            <TableHead>RAM</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="console in consoles.data" :key="console.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ console.name }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ console.cpu || 'N/A' }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ console.gpu || 'N/A' }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ console.ram || 'N/A' }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <span :class="console.is_active ? 'text-green-600' : 'text-red-600'">
                                    {{ console.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.consoles.show', console.id)">
                                        <Button variant="secondary" size="sm"><EyeIcon class="h-4 w-4" /></Button>
                                    </Link>
                                    <Link :href="route('admin.consoles.edit', console.id)">
                                        <Button variant="outline" size="sm"><PencilIcon class="h-4 w-4" /></Button>
                                    </Link>
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                            >
                                                <TrashIcon class="h-4 w-4" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Are you sure you want to delete this console?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the console from the system.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel><BanIcon />Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="router.delete(route('admin.consoles.destroy', console.id))">
                                                    <TrashIcon />Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="consoles.data.length === 0">
                            <TableCell colspan="6" class="py-4 text-center">No consoles found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination :items-per-page="consoles.per_page" :total="consoles.total" :default-page="consoles.from">
                <PaginationContent>
                    <a
                        v-if="consoles.links.prev"
                        href="#"
                        @click.prevent="router.visit(consoles.links.prev, { preserveState: true, preserveScroll: true, only: ['consoles'] })"
                    >
                        <PaginationPrevious />
                    </a>

                    <template v-for="(link, index) in consoles.links" :key="index">
                        <!-- Skip previous and next links as they're handled separately -->
                        <template v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'">
                            <a
                                v-if="!isNaN(parseInt(link.label)) && link.url"
                                href="#"
                                @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['consoles'] })"
                            >
                                <PaginationItem :value="parseInt(link.label)" :is-active="link.active">
                                    {{ link.label }}
                                </PaginationItem>
                            </a>
                            <PaginationItem v-else-if="!isNaN(parseInt(link.label))" :value="parseInt(link.label)" :is-active="link.active">
                                {{ link.label }}
                            </PaginationItem>
                            <PaginationEllipsis v-else-if="link.label === '...'" />
                        </template>
                    </template>

                    <a
                        v-if="consoles.links.next"
                        href="#"
                        @click.prevent="router.visit(consoles.links.next, { preserveState: true, preserveScroll: true, only: ['consoles'] })"
                    >
                        <PaginationNext />
                    </a>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
