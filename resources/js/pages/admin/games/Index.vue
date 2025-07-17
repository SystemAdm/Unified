<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon, PlusIcon, TrashIcon,BanIcon } from 'lucide-vue-next';
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

interface Game {
    id: number;
    name: string;
    version: string;
    console: string;
    image: string | null;
    is_active: boolean;
}
interface Props {
    games: {
        data: Game[];
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
    { title: 'Games', href: route('admin.games.index') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Games" />

        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall title="Games" description="Manage games in the system" />
                <Link :href="route('admin.games.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Add Game
                    </Button>
                </Link>
            </div>

            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Image</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Version</TableHead>
                            <TableHead>Console</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="game in games.data" :key="game.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <img v-if="game.image" :src="`/storage/${game.image}`" alt="Game thumbnail" class="w-16 h-16 object-cover rounded-md" />
                                <div v-else class="w-16 h-16 bg-gray-200 rounded-md flex items-center justify-center">
                                    <span class="text-gray-500 text-xs">No image</span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ game.name }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ game.version }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ game.console }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <span :class="game.is_active ? 'text-green-600' : 'text-red-600'">
                                    {{ game.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.games.show', game.id)">
                                        <Button variant="secondary" size="sm"><EyeIcon class="h-4 w-4" /></Button>
                                    </Link>
                                    <Link :href="route('admin.games.edit', game.id)">
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
                                                <AlertDialogTitle>Are you sure you want to delete this game?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the game from the system.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel><BanIcon />Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="router.delete(route('admin.games.destroy', game.id))">
                                                    <TrashIcon />Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="games.data.length === 0">
                            <TableCell colspan="6" class="py-4 text-center">No games found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination :items-per-page="games.per_page" :total="games.total" :default-page="games.from">
                <PaginationContent>
                    <a
                        v-if="games.links.prev"
                        href="#"
                        @click.prevent="router.visit(games.links.prev, { preserveState: true, preserveScroll: true, only: ['games'] })"
                    >
                        <PaginationPrevious />
                    </a>

                    <template v-for="(link, index) in games.links" :key="index">
                        <!-- Skip previous and next links as they're handled separately -->
                        <template v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'">
                            <a
                                v-if="!isNaN(parseInt(link.label)) && link.url"
                                href="#"
                                @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['games'] })"
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
                        v-if="games.links.next"
                        href="#"
                        @click.prevent="router.visit(games.links.next, { preserveState: true, preserveScroll: true, only: ['games'] })"
                    >
                        <PaginationNext />
                    </a>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
