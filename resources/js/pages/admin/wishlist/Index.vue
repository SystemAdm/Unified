<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import LaravelPaginator from '@/components/LaravelPaginator.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon, PlusIcon, TrashIcon, BanIcon, LinkIcon } from 'lucide-vue-next';
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

interface Wishlist {
    id: number;
    name: string;
    image: string | null;
    description: string | null;
    link: string | null;
    cost_per_unit: number;
    count: number;
    deadline: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    wishlists: {
        data: Wishlist[];
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
    { title: 'Wishlist', href: route('admin.wishlists.index') },
];

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const isExpired = (wishlist: Wishlist) => {
    if (!wishlist.deadline) return false;
    return new Date(wishlist.deadline) < new Date();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Wishlist" />

        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall title="Wishlist" description="Manage wishlist items in the system" />
                <div class="flex space-x-2">
                    <Link :href="route('wishlists.index')">
                        <Button variant="secondary">
                            Public View
                        </Button>
                    </Link>
                    <Link :href="route('admin.wishlists.create')">
                        <Button>
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add Wishlist Item
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Image</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Cost</TableHead>
                            <TableHead>Count</TableHead>
                            <TableHead>Deadline</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="wishlist in wishlists.data" :key="wishlist.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <img v-if="wishlist.image" :src="wishlist.image.startsWith('http') ? wishlist.image : `/storage/${wishlist.image}`" alt="Wishlist image" class="w-16 h-16 object-cover rounded-md" />
                                <div v-else class="w-16 h-16 bg-gray-200 rounded-md flex items-center justify-center">
                                    <span class="text-gray-500 text-xs">No image</span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div>
                                    <div class="font-medium">{{ wishlist.name }}</div>
                                    <div class="text-sm text-gray-500 truncate max-w-xs">{{ wishlist.description || 'No description' }}</div>
                                    <div v-if="wishlist.link" class="text-sm text-blue-500 flex items-center mt-1">
                                        <LinkIcon class="h-3 w-3 mr-1" />
                                        <a :href="wishlist.link" target="_blank" class="truncate max-w-xs">{{ wishlist.link }}</a>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ formatCurrency(wishlist.cost_per_unit) }} per unit
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ wishlist.count }}
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-sm">
                                <span :class="isExpired(wishlist) ? 'text-red-600' : 'text-green-600'">
                                    {{ formatDate(wishlist.deadline) }}
                                </span>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.wishlists.show', wishlist.id)">
                                        <Button variant="secondary" size="sm"><EyeIcon class="h-4 w-4" /></Button>
                                    </Link>
                                    <Link :href="route('admin.wishlists.edit', wishlist.id)">
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
                                                <AlertDialogTitle>Are you sure you want to delete this wishlist item?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the wishlist item from the system.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel><BanIcon />Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="router.delete(route('admin.wishlists.destroy', wishlist.id))">
                                                    <TrashIcon />Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="wishlists.data.length === 0">
                            <TableCell colspan="6" class="py-4 text-center">No wishlist items found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Pagination -->
        <LaravelPaginator
            v-if="wishlists.data.length > 0"
            :pagination="wishlists"
            onlyKey="wishlists"
        />
    </AppLayout>
</template>
