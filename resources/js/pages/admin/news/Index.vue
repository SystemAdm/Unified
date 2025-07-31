<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import LaravelPaginator from '@/components/LaravelPaginator.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon, PlusIcon, TrashIcon, BanIcon } from 'lucide-vue-next';
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

interface News {
    id: number;
    title: string;
    excerpt: string | null;
    content: string;
    author: {
        id: number;
        name: string;
    } | null;
    featured_image: string | null;
    is_published: boolean;
    published_at: string | null;
    visible_to_role: string[] | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    news: {
        data: News[];
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
    { title: 'News', href: route('admin.news.index') },
];

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const isPublished = (newsItem: News) => {
    if (!newsItem.is_published) return false;
    if (!newsItem.published_at) return true;
    return new Date(newsItem.published_at) <= new Date();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="News" />

        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall title="News" description="Manage news articles in the system" />
                <div class="flex space-x-2">
                    <Link :href="route('news.index')">
                        <Button variant="secondary">
                            Public View
                        </Button>
                    </Link>
                    <Link :href="route('admin.news.create')">
                        <Button>
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add News Article
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Image</TableHead>
                            <TableHead>Title</TableHead>
                            <TableHead>Author</TableHead>
                            <TableHead>Published</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="newsItem in news.data" :key="newsItem.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <img v-if="newsItem.featured_image" :src="`/storage/${newsItem.featured_image}`" alt="Featured image" class="w-16 h-16 object-cover rounded-md" />
                                <div v-else class="w-16 h-16 bg-gray-200 rounded-md flex items-center justify-center">
                                    <span class="text-gray-500 text-xs">No image</span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div>
                                    <div class="font-medium">{{ newsItem.title }}</div>
                                    <div class="text-sm text-gray-500 truncate max-w-xs">{{ newsItem.excerpt || 'No excerpt' }}</div>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ newsItem.author ? newsItem.author.name : 'No author' }}
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ formatDate(newsItem.published_at) }}
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <span :class="isPublished(newsItem) ? 'text-green-600' : 'text-red-600'">
                                    {{ isPublished(newsItem) ? 'Published' : 'Draft' }}
                                </span>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.news.show', newsItem.id)">
                                        <Button variant="secondary" size="sm"><EyeIcon class="h-4 w-4" /></Button>
                                    </Link>
                                    <Link :href="route('admin.news.edit', newsItem.id)">
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
                                                <AlertDialogTitle>Are you sure you want to delete this news article?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the news article from the system.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel><BanIcon />Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="router.delete(route('admin.news.destroy', newsItem.id))">
                                                    <TrashIcon />Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="news.data.length === 0">
                            <TableCell colspan="6" class="py-4 text-center">No news articles found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Pagination -->
        <LaravelPaginator
            v-if="news.data.length > 0"
            :pagination="news"
            onlyKey="news"
        />
    </AppLayout>
</template>
