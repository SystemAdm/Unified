<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/utils';
import { Calendar, User, ArrowLeft } from 'lucide-vue-next';

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
    news: NewsArticle;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'News', href: route('news.index') },
    { title: props.news.title, href: route('news.show', { news: props.news.id }) },
];

// Format date for display
const formatNewsDate = (date: string) => {
    return formatDate(date, 'MMMM D, YYYY');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.news.title" />

        <!-- Back to News button -->
        <div class="mb-6 flex justify-between">
            <Link
                :href="route('news.index')"
                class="inline-flex items-center text-muted-foreground hover:text-foreground"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to News
            </Link>
            <Link
                :href="route('admin.news.show', { news: props.news.id })"
                class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80"
            >
                Admin View
            </Link>
        </div>

        <!-- News Article -->
        <article class="mx-auto max-w-4xl">
            <!-- Featured Image -->
            <div v-if="props.news.featured_image" class="mb-8">
                <img
                    :src="props.news.featured_image"
                    :alt="props.news.title"
                    class="w-full rounded-lg object-cover"
                    style="max-height: 400px;"
                />
            </div>

            <!-- Article Header -->
            <header class="mb-8">
                <h1 class="mb-4 text-4xl font-bold">{{ props.news.title }}</h1>

                <div class="flex flex-wrap items-center gap-4 text-muted-foreground">
                    <div class="flex items-center">
                        <Calendar class="mr-2 h-4 w-4" />
                        <span>{{ formatNewsDate(props.news.published_at || props.news.created_at) }}</span>
                    </div>

                    <div v-if="props.news.author" class="flex items-center">
                        <User class="mr-2 h-4 w-4" />
                        <span>By {{ props.news.author.name }}</span>
                    </div>
                </div>
            </header>

            <!-- Article Content -->
            <div class="prose prose-lg max-w-none dark:prose-invert">
                <div v-html="props.news.content"></div>
            </div>
        </article>

        <!-- Back to News button (bottom) -->
        <div class="mt-12 text-center space-x-4">
            <Link
                :href="route('news.index')"
                class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-3 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to All News
            </Link>
            <Link
                :href="route('admin.news.index')"
                class="inline-flex items-center justify-center rounded-md bg-secondary px-6 py-3 text-sm font-medium text-secondary-foreground hover:bg-secondary/80 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                Admin Index
            </Link>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Ensure content styling for HTML content */
.prose {
    color: inherit;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4,
.prose h5,
.prose h6 {
    color: inherit;
}

.prose p {
    margin-bottom: 1rem;
    line-height: 1.7;
}

.prose img {
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}
</style>
