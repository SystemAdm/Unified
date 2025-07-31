<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { PencilIcon, ArrowLeftIcon } from 'lucide-vue-next';

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
    news: News;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'News', href: route('admin.news.index') },
    { title: props.news.title, href: route('admin.news.show', { news: props.news.id }) },
];

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const isPublished = () => {
    if (!props.news.is_published) return false;
    if (!props.news.published_at) return true;
    return new Date(props.news.published_at) <= new Date();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="'News: ' + news.title" />
        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall :title="news.title" description="News Article Details" />
                <div class="flex space-x-2">
                    <Link :href="route('news.show', { news: news.id })">
                        <Button variant="secondary" size="sm">
                            Public View
                        </Button>
                    </Link>
                    <Link :href="route('admin.news.edit', { news: news.id })">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="h-4 w-4" /> Edit article
                        </Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>News Article Information</CardTitle>
                    <CardDescription>Detailed information about this news article</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="news.featured_image" class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Featured Image</h3>
                        <img :src="`/storage/${news.featured_image}`" alt="Featured image" class="max-w-md rounded-md shadow-md" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">Title</h3>
                            <p class="mt-1 text-lg font-semibold">{{ news.title }}</p>
                        </div>

                        <div class="md:col-span-2" v-if="news.excerpt">
                            <h3 class="text-sm font-medium text-gray-500">Excerpt</h3>
                            <p class="mt-1 text-gray-700">{{ news.excerpt }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">Content</h3>
                            <div class="mt-1 prose prose-sm max-w-none">
                                <p class="whitespace-pre-wrap">{{ news.content }}</p>
                            </div>
                        </div>

                        <div v-if="news.author">
                            <h3 class="text-sm font-medium text-gray-500">Author</h3>
                            <p class="mt-1">{{ news.author.name }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Publication Status</h3>
                            <p class="mt-1" :class="isPublished() ? 'text-green-600' : 'text-red-600'">
                                {{ isPublished() ? 'Published' : 'Draft' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Published At</h3>
                            <p class="mt-1">{{ formatDate(news.published_at) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Is Published</h3>
                            <p class="mt-1" :class="news.is_published ? 'text-green-600' : 'text-red-600'">
                                {{ news.is_published ? 'Yes' : 'No' }}
                            </p>
                        </div>


                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Visible to Roles</h3>
                            <div class="mt-1">
                                <span v-if="!news.visible_to_role || news.visible_to_role.length === 0" class="text-gray-400">
                                    No restrictions
                                </span>
                                <div v-else class="flex flex-wrap gap-1">
                                    <Badge v-for="role in news.visible_to_role" :key="role" variant="outline">
                                        {{ role }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                            <p class="mt-1">{{ formatDate(news.created_at) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                            <p class="mt-1">{{ formatDate(news.updated_at) }}</p>
                        </div>
                    </div>
                </CardContent>
                <CardFooter>
                    <div class="flex justify-end space-x-2">
                        <Link :href="route('news.index')">
                            <Button variant="secondary">Public Index</Button>
                        </Link>
                        <Link :href="route('admin.news.index')">
                            <Button variant="outline"><ArrowLeftIcon /> Back to News</Button>
                        </Link>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
