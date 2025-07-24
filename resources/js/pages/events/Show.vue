<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/utils';
import { BetweenHorizontalStartIcon, CalendarIcon, ClockIcon, MapPinIcon, OctagonMinusIcon, ShieldHalfIcon, UserIcon } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
}

interface Event {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    location: { id: number; name: string } | null;
    status: string;
    user: User | null;
    organizations: [{ name: string; id: number }];
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
    event: Event;
    isSignedUp: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Events', href: route('events.index') },
    { title: props.event.title, href: route('events.show', { event: props.event.id }) },
];

// Format date for display
const formatEventDate = (date: string) => {
    return formatDate(date, 'MMM D, YYYY h:mm A');
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

// Check if signup is currently open
const isSignupOpen = (event: Event) => {
    if (!event.has_signup) return false;

    const now = new Date();
    const signupStartDate = event.signup_start_date ? new Date(event.signup_start_date) : null;
    const signupEndDate = event.signup_end_date ? new Date(event.signup_end_date) : null;

    // Check if current time is between signup start and end dates
    return (!signupStartDate || now >= signupStartDate) && (!signupEndDate || now <= signupEndDate);
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
    if (event.restriction === 'members' && user.roles?.includes('member')) return true;
    return !!(event.restriction === 'crew' && user.roles?.includes('crew'));
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
        <Head :title="props.event.title" />

        <div class="mb-10 flex items-center">
            <CalendarIcon class="mr-3 h-8 w-8 text-muted-foreground" />
            <h2 class="text-3xl font-bold">Event Details</h2>
        </div>

        <div class="overflow-hidden rounded-lg border shadow-md dark:border-gray-700">
            <img
                :src="`https://placehold.co/1200x300/0f0f0f/ffffff?text=${encodeURIComponent(props.event.title)}`"
                :alt="props.event.title"
                class="h-64 w-full object-cover"
            />
            <div class="rounded-b-lg bg-card p-6 shadow-sm" :class="{ 'bg-red-900': props.event.is_cancelled || props.event.status === 'cancelled' }">
                <div class="mb-6 flex flex-col md:flex-row md:items-start md:justify-between">
                    <div class="w-full">
                        <h1 class="mb-4 text-2xl font-bold">{{ props.event.title }}</h1>

                        <div class="mb-2 flex items-center text-muted-foreground">
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            <span>Event Start: {{ formatEventDate(props.event.start_date) }}</span>
                        </div>

                        <div class="mb-2 flex items-center text-muted-foreground">
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            <span>Event End: {{ formatEventDate(props.event.end_date) }}</span>
                        </div>

                        <!-- Event Duration -->
                        <div class="mb-2 flex items-center text-muted-foreground">
                            <ClockIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Duration:</span>
                            <strong class="text-white">{{ calculateDuration(props.event.start_date, props.event.end_date) }}</strong>
                        </div>

                        <div v-if="props.event.location" class="mb-2 flex items-center text-muted-foreground">
                            <MapPinIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Location:</span>
                            <Link v-if="props.event.location.id" :href="route('locations.show', { id: props.event.location.id })" class="text-white">
                                {{ props.event.location.name }}
                            </Link>
                            <span v-else class="text-white">{{ props.event.location.name }}</span>
                        </div>

                        <div v-if="props.event.user" class="mb-2 flex items-center text-muted-foreground">
                            <UserIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Organized by:</span>
                            <strong class="text-white">{{ props.event.user.name }}</strong>
                        </div>

                        <!-- Available Seats -->
                        <div v-if="props.event.seats !== null" class="mb-2 flex items-center text-muted-foreground">
                            <BetweenHorizontalStartIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Available Seats:</span>
                            <strong class="text-white">{{ props.event.seats }}</strong>
                            <strong class="text-white">{{ props.event.available_seats }}</strong>
                        </div>

                        <!-- Restrictions -->
                        <div v-if="props.event.restriction" class="mb-2 flex items-center text-muted-foreground">
                            <OctagonMinusIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Restriction:</span>
                            <strong class="text-white">{{ props.event.restriction }}</strong>
                        </div>

                        <!-- Age Limits -->
                        <div v-if="props.event.min_age !== null || props.event.max_age !== null" class="mb-2 flex items-center text-muted-foreground">
                            <ShieldHalfIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Age Limit:</span>
                            <strong class="text-white">{{ formatAgeLimits(props.event.min_age, props.event.max_age) }}</strong>
                        </div>

                        <!-- Signup Period -->
                        <div v-if="!props.event.has_signup" class="mb-2 flex items-center text-muted-foreground">
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Signup:</span>
                            <strong class="text-white">No signup required</strong>
                        </div>

                        <div v-if="props.event.has_signup && props.event.signup_start_date" class="mb-2 flex items-center text-muted-foreground">
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Signup Start:</span>
                            <strong class="text-white">{{ formatEventDate(props.event.signup_start_date) }}</strong>
                        </div>

                        <div v-if="props.event.has_signup && props.event.signup_end_date" class="mb-2 flex items-center text-muted-foreground">
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            <span class="mr-2">Signup End:</span>
                            <strong class="text-white">{{ formatEventDate(props.event.signup_end_date) }}</strong>
                        </div>

                        <div v-if="props.event.description" class="mt-6">
                            <h3 class="mb-2 text-lg font-semibold">Description</h3>
                            <p class="whitespace-pre-line text-muted-foreground">{{ props.event.description }}</p>
                        </div>

                        <p class="mt-3 text-muted-foreground" v-if="props.event.organizations && props.event.organizations.length > 0">
                            In association with:
                            <Link
                                v-for="{ name, id } in props.event.organizations"
                                :key="id"
                                class="text-white"
                                :href="route('locations.show', { id: id })"
                            >
                                {{ name }}
                            </Link>
                        </p>
                    </div>
                </div>

                <div class="mt-8 border-t pt-6">
                    <!-- Show feedback if user is already signed up -->
                    <div v-if="props.isSignedUp" class="mb-4">
                        <div class="w-full items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-lg font-bold text-white shadow-lg">
                            ✓ You are already signed up for this event
                        </div>
                        <!-- Sign down button - only show if signup end date hasn't passed -->
                        <div v-if="!props.event.signup_end_date || new Date() <= new Date(props.event.signup_end_date)" class="mt-2">
                            <Link :href="route('events.remove-signup', { event: props.event.id })" method="delete" class="block w-full">
                                <button class="h-10 w-full rounded-md bg-red-600 px-4 py-2 text-lg font-bold text-white shadow-lg hover:bg-red-700">
                                    Cancel my signup
                                </button>
                            </Link>
                        </div>
                    </div>
                    <!-- Show signup button if user is not signed up and meets all requirements -->
                    <div
                        v-else-if="
                            !props.event.is_cancelled &&
                            isSignupOpen(props.event) &&
                            (isRestrictedToEveryone(props.event) || (userMeetsRoleRequirements(props.event) && userMeetsAgeRequirements(props.event)))
                        "
                        class="mb-4"
                    >
                        <Link :href="route('events.signup', { event: props.event.id })" method="post" class="block w-full">
                            <button class="h-10 w-full rounded-md bg-green-600 px-4 py-2 text-lg font-bold text-white shadow-lg hover:bg-green-700">
                                Signup to event
                            </button>
                        </Link>
                    </div>
                    <Link :href="route('events.index')" class="block w-full">
                        <button
                            class="h-10 w-full rounded-md bg-primary px-4 py-2 text-lg font-bold text-primary-foreground shadow-lg hover:bg-primary/90"
                        >
                            ← Back to events
                        </button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
