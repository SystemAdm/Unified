<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/utils';
import { Megaphone, Calendar } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

interface Announcement {
    id: number;
    title: string;
    description: string;
    type: string;
    from_datetime: string;
    to_datetime: string;
    is_published: boolean;
    visible_to_access: string[] | null;
    visible_to_role: string[] | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    announcements: Announcement[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Announcements',
        href: '/announcements',
    },
];

// Format date for display
const formatAnnouncementDate = (date: string) => {
    return formatDate(date, 'MMM D, YYYY');
};

// Get badge variant based on announcement type
const getBadgeVariant = (type: string) => {
    switch (type.toLowerCase()) {
        case 'warning':
        case 'danger':
            return 'destructive';
        case 'info':
            return 'secondary';
        case 'primary':
            return 'default';
        default:
            return 'outline';
    }
};

// Get excerpt from description
const getExcerpt = (announcement: Announcement) => {
    if (announcement.description.length <= 150) {
        return announcement.description;
    }
    return announcement.description.substring(0, 150) + '...';
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Announcements" />

        <div class="mb-10 flex items-center">
            <Megaphone class="mr-3 h-8 w-8 text-muted-foreground" />
            <h2 class="text-3xl font-bold">Announcements</h2>
        </div>

        <!-- Announcements list -->
        <div v-if="props.announcements.length === 0" class="p-4 text-center text-gray-500">No announcements found.</div>
        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="announcement in props.announcements"
                :key="announcement.id"
                class="overflow-hidden rounded-lg border shadow-md dark:border-gray-700 h-full"
            >
                <div class="rounded-lg bg-card p-6 shadow-sm h-full flex flex-col">
                    <div class="flex-grow">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xl font-semibold">
                                <Link :href="route('announcements.show', { announcement: announcement.id })" class="hover:text-primary">
                                    {{ announcement.title }}
                                </Link>
                            </h3>
                            <Badge :variant="getBadgeVariant(announcement.type)">
                                {{ announcement.type.toUpperCase() }}
                            </Badge>
                        </div>

                        <div class="mb-2 flex items-center text-muted-foreground">
                            <Calendar class="mr-2 h-4 w-4" />
                            <span>{{ formatAnnouncementDate(announcement.from_datetime) }}</span>
                            <span v-if="announcement.to_datetime !== announcement.from_datetime">
                                - {{ formatAnnouncementDate(announcement.to_datetime) }}
                            </span>
                        </div>

                        <p class="mb-4 text-muted-foreground">
                            {{ getExcerpt(announcement) }}
                        </p>
                    </div>

                    <div class="mt-auto">
                        <Link
                            :href="route('announcements.show', { announcement: announcement.id })"
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
