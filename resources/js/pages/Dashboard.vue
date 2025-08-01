<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/utils';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarIcon, MapPinIcon, ShieldIcon, ShieldCheckIcon, ShieldQuestionIcon } from 'lucide-vue-next';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import ToastTest from '../components/ToastTest.vue';
import SonnerExample from '../components/SonnerExample.vue';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    user: any;
    events: any[];
    guardianStatus: {
        isAdult: boolean;
        hasGuardians: boolean;
        guardians: Array<{
            id: number;
            name: string;
            relation: string;
            isVerified: boolean;
            verifiedBy: number | null;
            verifiedAt: string | null;
        }>;
    } | null;
    guardedUsers: Array<{
        id: number;
        name: string;
        relation: string;
        isVerified: boolean;
        verifiedBy: number | null;
        verifiedAt: string | null;
    }> | null;
}>();

const formatEventDate = (date: string) => {
    return formatDate(date, 'MMM D, YYYY HH:mm');
};

// Filter events based on user roles and event restrictions
// Note: we only have one membership tier
const filteredEvents = computed(() => {
    if (!props.events || !props.user || !props.user.roles) {
        return [];
    }

    // Get user roles
    const userRoles = props.user.roles.map(role => role.name.toLowerCase());

    // Check if user has any of these roles
    const isOwner = userRoles.includes('owner');
    const isAdmin = userRoles.includes('admin');
    const isModerator = userRoles.includes('moderator');
    const isMember = userRoles.includes('member');
    const isGuardian = userRoles.includes('guardian');
    const isGuest = userRoles.includes('guest');
    const isCrew = userRoles.includes('crew'); // In case 'crew' is a valid role

    // Higher level roles (owner, admin, moderator, crew) can see all events
    if (isOwner || isAdmin || isModerator || isCrew) {
        return props.events;
    }

    // Members can see events for everyone and for members
    if (isMember) {
        return props.events.filter(event =>
            event.restriction === 'everyone' ||
            event.restriction === 'members'
        );
    }

    // Guests and guardians can only see events for everyone
    if (isGuest || isGuardian) {
        return props.events.filter(event =>
            event.restriction === 'everyone'
        );
    }

    // Default: if no role matches or no roles, show no events
    return [];
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl">
            <div class="flex flex-col gap-4">
                <!-- Guardian Status Icons -->
                <div v-if="props.guardianStatus" class="mb-4 p-3 bg-card rounded-lg shadow-sm">
                    <div v-if="props.guardianStatus.isAdult" class="flex items-center">
                        <div v-if="props.guardianStatus.hasGuardians" class="flex items-center">
                            <ShieldIcon class="h-6 w-6 text-primary mr-2" />
                            <span class="text-sm font-medium">Guardian information available</span>
                        </div>
                        <div v-else class="flex items-center">
                            <ShieldQuestionIcon class="h-6 w-6 text-amber-500 mr-2" />
                            <span class="text-sm font-medium">No guardian information provided</span>
                        </div>
                    </div>
                    <div v-else-if="props.guardianStatus.hasGuardians" class="flex flex-col">
                        <div class="flex items-center mb-2">
                            <ShieldIcon class="h-6 w-6 text-primary mr-2" />
                            <span class="text-sm font-medium">Guardians:</span>
                        </div>
                        <ul class="ml-8 space-y-2">
                            <li v-for="guardian in props.guardianStatus.guardians" :key="guardian.id" class="flex items-center">
                                <ShieldCheckIcon v-if="guardian.isVerified" class="h-5 w-5 text-green-500 mr-2" />
                                <ShieldIcon v-else class="h-5 w-5 text-gray-400 mr-2" />
                                <span>{{ guardian.name }} ({{ guardian.relation }})</span>
                            </li>
                        </ul>
                    </div>
                    <div v-else class="flex items-center">
                        <ShieldQuestionIcon class="h-6 w-6 text-red-500 mr-2" />
                        <span class="text-sm font-medium">No guardians registered</span>
                    </div>
                </div>

                <!-- Guarded Users Section -->
                <div v-if="props.guardedUsers && props.guardedUsers.length > 0" class="mb-4 p-3 bg-card rounded-lg shadow-sm">
                    <div class="flex flex-col">
                        <div class="flex items-center mb-2">
                            <ShieldIcon class="h-6 w-6 text-primary mr-2" />
                            <span class="text-sm font-medium">Users you are guarding:</span>
                        </div>
                        <ul class="ml-8 space-y-2">
                            <li v-for="user in props.guardedUsers" :key="user.id" class="flex items-center">
                                <ShieldCheckIcon v-if="user.isVerified" class="h-5 w-5 text-green-500 mr-2" />
                                <ShieldIcon v-else class="h-5 w-5 text-gray-400 mr-2" />
                                <span>{{ user.name }} ({{ user.relation }})</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Events section - visible based on user role -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 content-between">
                <div v-for="event in filteredEvents" :key="event.id" class="overflow-hidden rounded-lg border shadow-md dark:border-gray-700 h-full">
                    <div class="rounded-b-lg bg-card p-6 shadow-sm h-full flex flex-col">
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
                            <div v-if="event.location" class="mb-2 flex items-center text-muted-foreground">
                                <MapPinIcon class="mr-2 h-4 w-4" />
                                <Link :href="route('locations.show', { id: event.location.id })" class="text-white">
                                    {{ event.location.name }}
                                </Link>
                            </div>
                            <p class="mt-3 line-clamp-3 text-muted-foreground" v-if="event.organizations.length > 0">
                                In association with:
                                <Link
                                    v-for="{ name, id } in event.organizations"
                                    :key="id"
                                    class="text-white"
                                    :href="route('locations.show', { id: id })"
                                    >{{ name }}
                                </Link>
                            </p>
                            </div>
                            <div class="sticky bottom-0 mt-4 ">
                                <Link :href="route('events.show', { event: event.id })" class="block w-full">
                                    <button
                                        class="h-10 w-full rounded-md bg-primary px-4 py-2 font-bold text-primary-foreground shadow-lg hover:bg-primary/90"
                                    >
                                        View Details
                                    </button>
                                </Link>
                            </div>
                        </div>
                </div>
            </div>

            <!-- Message for when no events are available -->
            <div v-if="filteredEvents.length === 0" class="p-6 bg-card rounded-lg shadow-sm text-center">
                <p class="text-muted-foreground">No upcoming events available.</p>
            </div>

            <!-- Toast Test Component -->
            <ToastTest class="mt-4" />

            <!-- Sonner Example Component -->
            <div class="mt-4 p-4 bg-card rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold mb-4">Sonner Toast Example</h3>
                <SonnerExample />
            </div>
            <!-- Placeholder pattern - visible to all users who can see events -->
            <div v-if="filteredEvents.length > 0" class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
