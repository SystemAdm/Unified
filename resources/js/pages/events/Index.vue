<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';

import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/utils';
import { BetweenHorizontalStartIcon, CalendarIcon, ClockIcon, MapPinIcon, OctagonMinusIcon, ShieldHalfIcon, UserPlusIcon } from 'lucide-vue-next';

interface Event {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    location: { id: number; name: string } | null;
    status: string;
    organizations: [{ name: string; id: number }];
    users: [{ name: string; id: number }];
    seats: number | null;
    available_seats: number | null;
    min_age: number | null;
    max_age: number | null;
    restriction: string | null;
    class_restriction: string | null;
    is_cancelled: boolean;
    has_signup: boolean;
    signup_start_date: string | null;
    signup_end_date: string | null;
}

interface Props {
    events: {
        data: Event[];
        links: any[];
        per_page: number;
        current_page: number;
        total: number;
        from: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Events',
        href: '/events',
    },
];

// Format date for display
const formatEventDate = (date: string) => {
    return formatDate(date, 'MMM D, YYYY HH:mm');
};

// Calculate event duration in hours and minutes
const calculateDuration = (startDate: string, endDate: string) => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const durationMs = end.getTime() - start.getTime();
    const hours = Math.floor(durationMs / (1000 * 60 * 60));
    const minutes = Math.floor((durationMs % (1000 * 60 * 60)) / (1000 * 60));

    if (hours > 0 && minutes > 0) {
        return `${hours}h ${minutes}m`;
    } else if (hours > 0) {
        return `${hours}h`;
    } else {
        return `${minutes}m`;
    }
};

// Format age limits
const formatAgeLimits = (minAge: number | null, maxAge: number | null) => {
    if (minAge !== null && maxAge !== null) {
        return `${minAge}-${maxAge} years`;
    } else if (minAge !== null) {
        return `${minAge}+ years`;
    } else if (maxAge !== null) {
        return `Up to ${maxAge} years`;
    }
    return null;
};

// Decode HTML entities
const decodeHtmlEntities = (html: string) => {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = html;
    return textarea.value;
};

// Check if signup is currently open
const isSignupOpen = (event: Event) => {
    if (!event.has_signup) return false;

    const now = new Date();
    const signupStartDate = event.signup_start_date ? new Date(event.signup_start_date) : null;
    const signupEndDate = event.signup_end_date ? new Date(event.signup_end_date) : null;

    // Check if current time is between signup start and end dates
    return (!signupStartDate || now >= signupStartDate) &&
           (!signupEndDate || now <= signupEndDate);
};

// Check if event is restricted to everyone
const isRestrictedToEveryone = (event: Event) => {
    return event.restriction === 'everyone';
};

// Check if user meets role requirements
const userMeetsRoleRequirements = (event: Event) => {
    // Get the current user from Inertia shared props
    const user = usePage().props.auth.user;

    // If no user is logged in, they don't meet role requirements
    if (!user) return false;

    // If event is restricted to everyone, any logged-in user meets requirements
    if (event.restriction === 'everyone') return true;

    // Check if user has the required role
    if (event.restriction === 'members' && user.roles.includes('member')) return true;
    if (event.restriction === 'crew' && user.roles.includes('crew')) return true;

    return false;
};

// Check if user meets age requirements
const userMeetsAgeRequirements = (event: Event) => {
    // Get the current user from Inertia shared props
    const user = usePage().props.auth.user;

    // If no user is logged in, they don't meet age requirements
    if (!user) return false;

    // If no age limits are set, any user meets requirements
    if (event.min_age === null && event.max_age === null) return true;

    // Calculate user's age
    if (!user.birthday) return false; // No birthday, can't verify age

    const birthday = new Date(user.birthday);
    const today = new Date();
    let age = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();

    // Adjust age if birthday hasn't occurred yet this year
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
        age--;
    }

    // Check if user's age is within limits
    if (event.min_age !== null && age < event.min_age) return false;
    return !(event.max_age !== null && age > event.max_age);


};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Events" />

        <div class="mb-10 flex items-center">
            <CalendarIcon class="mr-3 h-8 w-8 text-muted-foreground" />
            <h2 class="text-3xl font-bold">Upcoming Events</h2>
        </div>

        <!-- Events list -->
        <div v-if="props.events.data.length === 0" class="p-4 text-center text-gray-500">No events found.</div>
        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 content-between">
            <div
                v-for="event in props.events.data"
                :key="event.id"
                class="overflow-hidden rounded-lg border shadow-md dark:border-gray-700 h-full"
            >
                <img
                    :src="`https://placehold.co/300x200/0f0f0f/ffffff?text=${encodeURIComponent(event.title)}`"
                    :alt="event.title"
                    class="h-48 w-full object-cover"
                />
                <div class="rounded-b-lg bg-card p-6 shadow-sm h-full flex flex-col" :class="{ 'bg-red-900': event.is_cancelled || event.status === 'cancelled' }">
                    <div class="flex-grow">
                        <h3 class="mb-2 text-xl font-semibold">
                            <Link :href="route('events.show', { event: event.id })" class="hover:text-primary">
                                {{ event.title }}
                            </Link>
                        </h3>
                        <div class="mb-2 flex items-center text-muted-foreground">
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            <span>{{ formatEventDate(event.start_date) }}</span>
                        </div>
                        <!-- Event Duration -->
                        <div class="mb-2 flex items-center text-muted-foreground">
                            <ClockIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Duration:</span>
                            <strong class="text-white">{{ calculateDuration(event.start_date, event.end_date) }}</strong>
                        </div>
                        <div v-if="event.location" class="mb-2 flex items-center text-muted-foreground">
                            <MapPinIcon class="mr-2 h-4 w-4" />
                            <Link :href="route('locations.show', { id: event.location.id })" class="text-white">
                                {{ event.location.name }}
                            </Link>
                        </div>
                        <!-- Available Seats -->
                        <div v-if="event.available_seats !== null" class="mb-2 flex items-center text-muted-foreground">
                            <BetweenHorizontalStartIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Available Seats:</span>
                            <strong class="text-white">{{ event.available_seats }}</strong>
                        </div>
                        <!-- Restrictions -->
                        <div v-if="event.restriction" class="mb-2 flex items-center text-muted-foreground">
                            <OctagonMinusIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Restriction:</span>
                            <strong class="text-white">{{ event.restriction }}</strong>
                        </div>
                        <!-- Age Limits -->
                        <div v-if="event.min_age !== null || event.max_age !== null" class="mb-2 flex items-center text-muted-foreground">
                            <ShieldHalfIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Age Limit:</span>
                            <strong class="text-white">{{ formatAgeLimits(event.min_age, event.max_age) }}</strong>
                        </div>
                        <!-- Signup Requirement -->
                        <div class="mb-2 flex items-center text-muted-foreground">
                            <UserPlusIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Signup:</span>
                            <strong class="text-white">{{ event.has_signup ? 'Required' : 'Not Required' }}</strong>
                        </div>
                        <p class="mt-3 line-clamp-3 text-muted-foreground">
                            {{ event.description }}
                        </p>
                        <p class="mt-3 line-clamp-3 text-muted-foreground" v-if="event.organizations.length > 0">
                            In association with:
                            <Link v-for="{ name, id } in event.organizations" :key="id" class="text-white" :href="route('locations.show', { id: id })"
                                >{{ name }}
                            </Link>
                        </p>
                    </div>
                    <div class="mt-4 sticky bottom-0 pb-4">
                        <div v-if="!event.is_cancelled && isSignupOpen(event) && (isRestrictedToEveryone(event) || (userMeetsRoleRequirements(event) && userMeetsAgeRequirements(event)))" class="mb-2">
                            <Link :href="route('events.signup', { event: event.id })" method="post" class="w-full block">
                                <button class="h-10 w-full rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 font-bold text-lg shadow-lg">
                                    Signup to event
                                </button>
                            </Link>
                        </div>
                        <Link :href="route('events.show', { event: event.id })" class="w-full block">
                            <button class="h-10 w-full rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 font-bold text-lg shadow-lg">
                                View Details
                            </button>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <Pagination :items-per-page="props.events.per_page" :total="props.events.total" :default-page="props.events.from">
                <PaginationContent>
                    <template v-for="(link, i) in props.events.links" :key="i">
                        <!-- Previous link -->
                        <a
                            v-if="link.label === '&laquo; Previous' && link.url"
                            href="#"
                            @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['events'] })"
                        >
                            <PaginationPrevious />
                        </a>

                        <!-- Page numbers -->
                        <a
                            v-else-if="!isNaN(parseInt(decodeHtmlEntities(link.label))) && link.url"
                            href="#"
                            @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['events'] })"
                        >
                            <PaginationItem :value="parseInt(decodeHtmlEntities(link.label))" :is-active="link.active">
                                {{ decodeHtmlEntities(link.label) }}
                            </PaginationItem>
                        </a>
                        <PaginationItem
                            v-else-if="!isNaN(parseInt(decodeHtmlEntities(link.label)))"
                            :value="parseInt(decodeHtmlEntities(link.label))"
                            :is-active="link.active"
                        >
                            {{ decodeHtmlEntities(link.label) }}
                        </PaginationItem>

                        <!-- Next link -->
                        <a
                            v-else-if="link.label === 'Next &raquo;' && link.url"
                            href="#"
                            @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['events'] })"
                        >
                            <PaginationNext />
                        </a>

                        <!-- Ellipsis -->
                        <PaginationEllipsis v-else-if="link.label === '...'" />
                    </template>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
