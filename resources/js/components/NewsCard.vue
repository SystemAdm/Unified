<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Calendar, User, MoreHorizontal } from 'lucide-vue-next';

interface NewsArticle {
    id: number;
    title: string;
    excerpt: string | null;
    content: string;
    author: {
        id?: number;
        name: string;
    } | string | null;
    featured_image: string | null;
    published_at: string | null;
    is_published: boolean;
    created_at: string;
    updated_at: string;
}

defineProps<{
    article: NewsArticle;
    useCardComponents?: boolean;
}>();

// Format date for display
const formatNewsDate = (date: string) => {
    const newsDate = new Date(date);
    return newsDate.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

// Get excerpt or truncated content
const getExcerpt = (article: NewsArticle) => {
    if (article.excerpt) {
        return article.excerpt;
    }
    return article.content.substring(0, 150) + (article.content.length > 150 ? '...' : '');
};

// Get author name
const getAuthorName = (author: NewsArticle['author']) => {
    if (!author) return null;

    if (typeof author === 'string') {
        return author;
    }

    return author.name;
};
</script>

<template>
    <!-- Card using UI components (for Welcome.vue) -->
    <Card v-if="useCardComponents" class="overflow-hidden h-full min-h-[400px]">
        <img
            :src="article.featured_image
                ? `/storage/${article.featured_image}`
                : `https://placehold.co/400x200/0f0f0f/ffffff?text=${encodeURIComponent(article.title)}`"
            :alt="article.title"
            class="h-48 w-full object-cover"
        />
        <CardHeader>
            <CardTitle class="text-lg">{{ article.title }}</CardTitle>
            <CardDescription>
                <span v-if="getAuthorName(article.author)">By {{ getAuthorName(article.author) }} • </span>
                {{ article.published_at ? formatNewsDate(article.published_at) : formatNewsDate(article.created_at) }}
            </CardDescription>
        </CardHeader>
        <CardContent>
            <p class="text-muted-foreground">
                {{ getExcerpt(article) }}
            </p>
        </CardContent>
        <CardFooter class="pt-2">
            <Link :href="route('news.show', { news: article.id })">
                <Button class="w-full">
                    <MoreHorizontal class="mr-2 h-4" />
                    Read More
                </Button>
            </Link>
        </CardFooter>
    </Card>

    <!-- Card using div structure (for News/Index.vue) -->
    <div v-else class="overflow-hidden rounded-lg border shadow-md dark:border-gray-700 min-h-[400px] flex flex-col">
        <img
            :src="article.featured_image
                ? `/storage/${article.featured_image}`
                : `https://placehold.co/400x200/0f0f0f/ffffff?text=${encodeURIComponent(article.title)}`"
            :alt="article.title"
            class="h-48 w-full object-cover"
        />
        <div class="rounded-b-lg bg-card p-6 shadow-sm flex-grow flex flex-col">
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

                <div v-if="getAuthorName(article.author)" class="mb-2 flex items-center text-muted-foreground">
                    <User class="mr-2 h-4 w-4" />
                    <span>By {{ getAuthorName(article.author) }}</span>
                </div>

                <p class="mb-4 text-muted-foreground">
                    {{ getExcerpt(article) }}
                </p>
            </div>

            <div class="mt-4 pt-2 sticky bottom-0">
                <Link
                    :href="route('news.show', { news: article.id })"
                    class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                >
                    <MoreHorizontal class="mr-2 h-4" />
                    Read More
                </Link>
            </div>
        </div>
    </div>
</template>
