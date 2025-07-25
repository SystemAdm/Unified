<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CalendarIcon, ClockIcon, MapPinIcon, OctagonMinusIcon, ShieldHalfIcon, UserPlusIcon, BetweenHorizontalStartIcon } from 'lucide-vue-next';

interface Location {
    id: number;
    name: string;
    address?: string;
}

interface Organization {
    id: number;
    name: string;
}

interface EventProps {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    location: Location | null;
    status?: string;
    organizations?: Organization[];
    users?: { name: string; id: number }[];
    seats?: number | null;
    available_seats?: number | null;
    min_age?: number | null;
    max_age?: number | null;
    restriction?: string | null;
    class_restriction?: string | null;
    is_cancelled?: boolean;
    has_signup?: boolean;
    signup_start_date?: string | null;
    signup_end_date?: string | null;
}

defineProps<{
    event: EventProps;
    showDetailedView?: boolean;
}>();

// Format date for display
const formatEventDate = (date: string) => {
    const eventDate = new Date(date);
    return eventDate.toLocaleDateString('NO', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

// Format time for display
const formatEventTime = (date: string) => {
    const eventDate = new Date(date);
    return eventDate.toLocaleTimeString('NO', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: false,
    });
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
const formatAgeLimits = (minAge: number | null | undefined, maxAge: number | null) => {
    if (minAge !== undefined && minAge !== null && maxAge !== null) {
        return `${minAge}-${maxAge} years`;
    } else if (minAge !== null && minAge !== undefined) {
        return `${minAge}+ years`;
    } else if (maxAge !== null) {
        return `Up to ${maxAge} years`;
    }
    return null;
};

// Check if signup is currently open
const isSignupOpen = (event: EventProps) => {
    if (!event.has_signup) return false;

    const now = new Date();
    const signupStartDate = event.signup_start_date ? new Date(event.signup_start_date) : null;
    const signupEndDate = event.signup_end_date ? new Date(event.signup_end_date) : null;

    // Check if current time is between signup start and end dates
    return (!signupStartDate || now >= signupStartDate) &&
           (!signupEndDate || now <= signupEndDate);
};

// Check if event is restricted to everyone
const isRestrictedToEveryone = (event: EventProps) => {
    return event.restriction === 'everyone';
};

// Check if user meets role requirements
const userMeetsRoleRequirements = (event: EventProps) => {
    // Get the current user from Inertia shared props
    const user = usePage().props.auth?.user;

    // If no user is logged in, they don't meet role requirements
    if (!user) return false;

    // If event is restricted to everyone, any logged-in user meets requirements
    if (event.restriction === 'everyone') return true;

    // Check if user has the required role
    if (event.restriction === 'members' && user.roles?.includes('member')) return true;
    if (event.restriction === 'crew' && user.roles?.includes('crew')) return true;

    return false;
};

// Check if user meets age requirements
const userMeetsAgeRequirements = (event: EventProps) => {
    // Get the current user from Inertia shared props
    const user = usePage().props.auth?.user;

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
    if (event.min_age != null && age < event.min_age) return false;
    return !(event.max_age != null && age > event.max_age);
};
</script>

<template>
    <Card class="overflow-hidden h-full">
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
                    <span class="mr-2">Event Start:</span>
                    <strong class="text-white">{{ formatEventDate(event.start_date) }} @ {{ formatEventTime(event.start_date) }}</strong>
                </div>
                <!-- Event Duration (only in detailed view) -->
                <div v-if="showDetailedView" class="mb-2 flex items-center text-muted-foreground">
                    <ClockIcon class="mr-2 h-4 w-4" />
                    <span class="mr-2">Duration:</span>
                    <strong class="text-white">{{ calculateDuration(event.start_date, event.end_date) }}</strong>
                </div>
                <div v-if="event.location" class="mb-2 flex items-center text-muted-foreground">
                    <MapPinIcon class="mr-2 h-4 w-4" />
                    <span class="mr-2">Location:</span>
                    <template v-if="showDetailedView">
                        <Link :href="route('locations.show', { id: event.location.id })" class="text-white fw-bold">
                            {{ event.location.name }}
                        </Link>
                    </template>
                    <template v-else>
                        <strong class="text-white">{{ event.location.name }}</strong>
                    </template>
                </div>
                <!-- Available Seats (only in detailed view) -->
                <div v-if="showDetailedView && event.available_seats !== null" class="mb-2 flex items-center text-muted-foreground">
                    <BetweenHorizontalStartIcon class="mr-2 h-4 w-4" />
                    <span class="mr-2">Available Seats:</span>
                    <strong class="text-white">{{ event.available_seats }}</strong>
                </div>
                <!-- Restrictions (only in detailed view) -->
                <div v-if="showDetailedView && event.restriction" class="mb-2 flex items-center text-muted-foreground">
                    <OctagonMinusIcon class="mr-2 h-4 w-4" />
                    <span class="mr-2">Restriction:</span>
                    <strong class="text-white">{{ event.restriction }}</strong>
                </div>
                <!-- Age Limits (only in detailed view) -->
                <div v-if="showDetailedView && (event.min_age != null || event.max_age != null)" class="mb-2 flex items-center text-muted-foreground">
                    <ShieldHalfIcon class="mr-2 h-4 w-4" />
                    <span class="mr-2">Age Limit:</span>
                    <strong class="text-white">{{ formatAgeLimits(event.min_age, event.max_age) }}</strong>
                </div>
                <!-- Signup Requirement (only in detailed view) -->
                <div v-if="showDetailedView" class="mb-2 flex items-center text-muted-foreground">
                    <UserPlusIcon class="mr-2 h-4 w-4" />
                    <span class="mr-2">Signup:</span>
                    <strong class="text-white">{{ event.has_signup ? 'Required' : 'Not Required' }}</strong>
                </div>
                <p class="mt-3 line-clamp-3 text-muted-foreground">
                    {{ event.description }}
                </p>
                <p v-if="showDetailedView && event.organizations && event.organizations.length > 0" class="mt-3 line-clamp-3 text-muted-foreground">
                    In association with:
                    <Link v-for="{ name, id } in event.organizations" :key="id" class="text-white" :href="route('locations.show', { id: id })"
                        >{{ name }}
                    </Link>
                </p>
            </div>
            <div class="mt-4 sticky bottom-0 pb-4">
                <div v-if="showDetailedView && !event.is_cancelled && isSignupOpen(event) && (isRestrictedToEveryone(event) || (userMeetsRoleRequirements(event) && userMeetsAgeRequirements(event)))" class="mb-2">
                    <Link :href="route('events.signup', { event: event.id })" method="post" class="w-full block">
                        <button class="h-10 w-full rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 font-bold text-lg shadow-lg">
                            Signup to event
                        </button>
                    </Link>
                </div>
                <Link :href="route('events.show', { event: event.id })" class="w-full block">
                    <Button class="w-full">View Details</Button>
                </Link>
            </div>
        </div>
    </Card>
</template>
