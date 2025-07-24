<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/utils';
import { Calendar, ArrowLeft, AlertTriangle } from 'lucide-vue-next';
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
    announcement: Announcement;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Announcements', href: route('announcements.index') },
    { title: props.announcement.title, href: route('announcements.show', { announcement: props.announcement.id }) },
];

// Format date for display
const formatAnnouncementDate = (date: string) => {
    return formatDate(date, 'MMMM D, YYYY h:mm A');
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

// Get icon based on announcement type
const getTypeIcon = (type: string) => {
    switch (type.toLowerCase()) {
        case 'warning':
        case 'danger':
            return AlertTriangle;
        default:
            return Calendar;
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.announcement.title" />

        <!-- Back to Announcements button -->
        <div class="mb-6">
            <Link
                :href="route('announcements.index')"
                class="inline-flex items-center text-muted-foreground hover:text-foreground"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Announcements
            </Link>
        </div>

        <!-- Announcement -->
        <article class="mx-auto max-w-4xl">
            <!-- Announcement Header -->
            <header class="mb-8">
                <div class="flex items-start justify-between mb-4">
                    <h1 class="text-4xl font-bold flex-1 mr-4">{{ props.announcement.title }}</h1>
                    <Badge :variant="getBadgeVariant(props.announcement.type)" class="text-sm">
                        {{ props.announcement.type.toUpperCase() }}
                    </Badge>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-muted-foreground">
                    <div class="flex items-center">
                        <component :is="getTypeIcon(props.announcement.type)" class="mr-2 h-4 w-4" />
                        <span>{{ formatAnnouncementDate(props.announcement.from_datetime) }}</span>
                        <span v-if="props.announcement.to_datetime !== props.announcement.from_datetime">
                            - {{ formatAnnouncementDate(props.announcement.to_datetime) }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Announcement Content -->
            <div class="prose prose-lg max-w-none dark:prose-invert">
                <div class="bg-muted/50 rounded-lg p-6 border-l-4"
                     :class="{
                         'border-l-red-500': props.announcement.type === 'danger' || props.announcement.type === 'warning',
                         'border-l-blue-500': props.announcement.type === 'info',
                         'border-l-green-500': props.announcement.type === 'success',
                         'border-l-gray-500': props.announcement.type === 'primary'
                     }">
                    <p class="text-lg leading-relaxed whitespace-pre-wrap">{{ props.announcement.description }}</p>
                </div>
            </div>
        </article>

        <!-- Back to Announcements button (bottom) -->
        <div class="mt-12 text-center">
            <Link
                :href="route('announcements.index')"
                class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-3 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to All Announcements
            </Link>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Ensure content styling */
.prose {
    color: inherit;
}

.prose p {
    margin-bottom: 1rem;
    line-height: 1.7;
}
</style>
