<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { PencilIcon, ArrowLeftIcon } from 'lucide-vue-next';

interface Announcement {
    id: number;
    title: string;
    description: string;
    type: string;
    from_datetime: string;
    to_datetime: string;
    activating: boolean;
    visible_to_access: string[] | null;
    visible_to_role: string[] | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    announcement: Announcement;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Announcements', href: route('admin.announcements.index') },
    { title: props.announcement.title, href: route('admin.announcements.show', { announcement: props.announcement.id }) },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const isActive = () => {
    const now = new Date();
    const from = new Date(props.announcement.from_datetime);
    const to = new Date(props.announcement.to_datetime);
    return props.announcement.activating && from <= now && to >= now;
};

const getTypeColor = (type: string) => {
    switch (type) {
        case 'danger': return 'bg-red-100 text-red-800';
        case 'warning': return 'bg-yellow-100 text-yellow-800';
        case 'info': return 'bg-blue-100 text-blue-800';
        case 'primary': return 'bg-green-100 text-green-800';
        case 'secondary':
        case 'default':
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="'Announcement: ' + announcement.title" />
        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall :title="announcement.title" description="Announcement Details" />
                <div class="flex space-x-2">
                    <Link :href="route('admin.announcements.edit', { announcement: announcement.id })">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="h-4 w-4" /> Edit announcement
                        </Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Announcement Information</CardTitle>
                    <CardDescription>Detailed information about this announcement</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">Title</h3>
                            <p class="mt-1 text-lg font-semibold">{{ announcement.title }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">Description</h3>
                            <p class="mt-1">{{ announcement.description }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Type</h3>
                            <Badge :class="getTypeColor(announcement.type)" class="mt-1">
                                {{ announcement.type }}
                            </Badge>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1" :class="isActive() ? 'text-green-600' : 'text-red-600'">
                                {{ isActive() ? 'Active' : 'Inactive' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">From Date & Time</h3>
                            <p class="mt-1">{{ formatDate(announcement.from_datetime) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">To Date & Time</h3>
                            <p class="mt-1">{{ formatDate(announcement.to_datetime) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Activating</h3>
                            <p class="mt-1" :class="announcement.activating ? 'text-green-600' : 'text-red-600'">
                                {{ announcement.activating ? 'Yes' : 'No' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Visible to Access Types</h3>
                            <div class="mt-1">
                                <span v-if="!announcement.visible_to_access || announcement.visible_to_access.length === 0" class="text-gray-400">
                                    No restrictions
                                </span>
                                <div v-else class="flex flex-wrap gap-1">
                                    <Badge v-for="access in announcement.visible_to_access" :key="access" variant="outline">
                                        {{ access }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Visible to Roles</h3>
                            <div class="mt-1">
                                <span v-if="!announcement.visible_to_role || announcement.visible_to_role.length === 0" class="text-gray-400">
                                    No restrictions
                                </span>
                                <div v-else class="flex flex-wrap gap-1">
                                    <Badge v-for="role in announcement.visible_to_role" :key="role" variant="outline">
                                        {{ role }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                            <p class="mt-1">{{ formatDate(announcement.created_at) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                            <p class="mt-1">{{ formatDate(announcement.updated_at) }}</p>
                        </div>
                    </div>
                </CardContent>
                <CardFooter>
                    <div class="flex justify-end space-x-2">
                        <Link :href="route('admin.announcements.index')">
                            <Button variant="outline"><ArrowLeftIcon /> Back to Announcements</Button>
                        </Link>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
