<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/utils';
import { Newspaper, Calendar, User } from 'lucide-vue-next';

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
    news: NewsArticle[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'News',
        href: '/news',
    },
];

// Format date for display
const formatNewsDate = (date: string) => {
    return formatDate(date, 'MMM D, YYYY');
};

// Get excerpt or truncated content
const getExcerpt = (article: NewsArticle) => {
    if (article.excerpt) {
        return article.excerpt;
    }
    return article.content.substring(0, 150) + '...';
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="News" />

        <div class="mb-10 flex items-center">
            <Newspaper class="mr-3 h-8 w-8 text-muted-foreground" />
            <h2 class="text-3xl font-bold">Latest News</h2>
        </div>

        <!-- News list -->
        <div v-if="props.news.length === 0" class="p-4 text-center text-gray-500">No news articles found.</div>
        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="article in props.news"
                :key="article.id"
                class="overflow-hidden rounded-lg border shadow-md dark:border-gray-700 h-full"
            >
                <img
                    :src="article.featured_image
                        ? article.featured_image
                        : `https://placehold.co/400x200/0f0f0f/ffffff?text=${encodeURIComponent(article.title)}`"
                    :alt="article.title"
                    class="h-48 w-full object-cover"
                />
                <div class="rounded-b-lg bg-card p-6 shadow-sm h-full flex flex-col">
                    <div class="flex-grow">
                        <h3 class="mb-2 text-xl font-semibold">
                            <Link :href="route('news.show', { news: article.id })" class="hover:text-primary">
                                {{ article.title }}
                            </Link>
                        </h3>

                        <div class="mb-2 flex items-center text-muted-foreground">
                            <Calendar class="mr-2 h-4 w-4" />
                            <span>{{ formatNewsDate(article.published_at || article.created_at) }}</span>
                        </div>

                        <div v-if="article.author" class="mb-2 flex items-center text-muted-foreground">
                            <User class="mr-2 h-4 w-4" />
                            <span>By {{ article.author.name }}</span>
                        </div>

                        <p class="mb-4 text-muted-foreground">
                            {{ getExcerpt(article) }}
                        </p>
                    </div>

                    <div class="mt-auto">
                        <Link
                            :href="route('news.show', { news: article.id })"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            Read More
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
