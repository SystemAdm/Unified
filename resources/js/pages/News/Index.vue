<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Newspaper } from 'lucide-vue-next';
import NewsCard from '@/components/NewsCard.vue';
import LaravelPaginator from '@/components/LaravelPaginator.vue';

interface NewsArticle {
    id: number;
    title: string;
    excerpt: string | null;
    content: string;
    author: {
        id: number;
        name: string;
    } | null;
    featured_image: string | null;
    published_at: string | null;
    is_published: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    news: {
        data: NewsArticle[];
        current_page: number;
        from: number;
        last_page: number;
        links: any[];
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'News',
        href: '/news',
    },
];

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="News" />

        <div class="mb-10 flex items-center justify-between">
            <div class="flex items-center">
                <Newspaper class="mr-3 h-8 w-8 text-muted-foreground" />
                <h2 class="text-3xl font-bold">Latest News</h2>
            </div>
            <Link :href="route('admin.news.index')" class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80">
                Admin
            </Link>
        </div>

        <!-- News list -->
        <div v-if="props.news.data.length === 0" class="p-4 text-center text-gray-500">No news articles found.</div>
        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            <NewsCard
                v-for="article in props.news.data"
                :key="article.id"
                :article="article"
                :useCardComponents="false"
            />
        </div>

        <!-- Pagination -->
        <LaravelPaginator
            v-if="props.news.data.length > 0"
            :pagination="props.news"
            onlyKey="news"
        />
    </AppLayout>
</template>
