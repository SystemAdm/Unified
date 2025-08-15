<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { PencilIcon, ArrowLeftIcon } from 'lucide-vue-next';
import BannerTypeBadge from '@/components/BannerTypeBadge.vue';
import { formatDate } from '@/utils';

interface Banner {
    id: number;
    title: string;
    description: string;
    type: string;
    from_datetime: string;
    to_datetime: string;
    is_published: boolean;
    is_recurring?: boolean;
    is_active?: boolean;
    relative_day?: string | null;
    visible_to_access: string[] | null;
    visible_to_role: string[] | null;
    link_norwegian: string | null;
    link_english: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    banner: Banner;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Banners', href: route('admin.banners.index') },
    { title: props.banner.title, href: route('admin.banners.show', { banner: props.banner.id }) },
];

// Using standardized date format

const isActive = () => {
    const banner: any = props.banner as any;
    if (typeof banner.is_active !== 'undefined') {
        return !!banner.is_active;
    }
    const now = new Date();
    const from = new Date(props.banner.from_datetime);
    const to = new Date(props.banner.to_datetime);
    return (props.banner as any).is_published && from <= now && to >= now;
};

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="'Banner: ' + banner.title" />
        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall :title="banner.title" description="Banner Details" />
                <div class="flex space-x-2">
                    <Link :href="route('admin.banners.edit', { banner: banner.id })">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="h-4 w-4" /> Edit banner
                        </Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Banner Information</CardTitle>
                    <CardDescription>Detailed information about this banner</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">Title</h3>
                            <p class="mt-1 text-lg font-semibold">{{ banner.title }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">Description</h3>
                            <p class="mt-1">{{ banner.description }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Type</h3>
                            <BannerTypeBadge :type="banner.type" class="mt-1" />
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1" :class="isActive() ? 'text-green-600' : 'text-red-600'">
                                {{ isActive() ? 'Active' : 'Inactive' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">From Date & Time</h3>
                            <p class="mt-1">{{ formatDate(banner.from_datetime, 'm') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">To Date & Time</h3>
                            <p class="mt-1">{{ formatDate(banner.to_datetime, 'm') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Published</h3>
                            <p class="mt-1" :class="banner.is_published ? 'text-green-600' : 'text-red-600'">
                                {{ banner.is_published ? 'Yes' : 'No' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Recurring</h3>
                            <p class="mt-1">{{ (banner as any).is_recurring ? 'Yes' : 'No' }}</p>
                        </div>

                        <div v-if="(banner as any).relative_day">
                            <h3 class="text-sm font-medium text-gray-500">Relative Day</h3>
                            <p class="mt-1">
                                <Badge variant="secondary">{{ (banner as any).relative_day }}</Badge>
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Visible to Access Types</h3>
                            <div class="mt-1">
                                <span v-if="!banner.visible_to_access || banner.visible_to_access.length === 0" class="text-gray-400">
                                    No restrictions
                                </span>
                                <div v-else class="flex flex-wrap gap-1">
                                    <Badge v-for="access in banner.visible_to_access" :key="access" variant="outline">
                                        {{ access }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Visible to Roles</h3>
                            <div class="mt-1">
                                <span v-if="!banner.visible_to_role || banner.visible_to_role.length === 0" class="text-gray-400">
                                    No restrictions
                                </span>
                                <div v-else class="flex flex-wrap gap-1">
                                    <Badge v-for="role in banner.visible_to_role" :key="role" variant="outline">
                                        {{ role }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <div v-if="banner.link_norwegian">
                            <h3 class="text-sm font-medium text-gray-500">Norwegian Link</h3>
                            <a :href="banner.link_norwegian" target="_blank" class="mt-1 text-blue-600 hover:text-blue-800 underline">
                                {{ banner.link_norwegian }}
                            </a>
                        </div>

                        <div v-if="banner.link_english">
                            <h3 class="text-sm font-medium text-gray-500">English Link</h3>
                            <a :href="banner.link_english" target="_blank" class="mt-1 text-blue-600 hover:text-blue-800 underline">
                                {{ banner.link_english }}
                            </a>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                            <p class="mt-1">{{ formatDate(banner.created_at, 'd') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                            <p class="mt-1">{{ formatDate(banner.updated_at, 'd') }}</p>
                        </div>
                    </div>
                </CardContent>
                <CardFooter>
                    <div class="flex justify-end space-x-2">
                        <Link :href="route('admin.banners.index')">
                            <Button variant="outline"><ArrowLeftIcon /> Back to Banners</Button>
                        </Link>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
