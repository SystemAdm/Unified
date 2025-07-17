<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
}

interface Location {
    id: number;
    name: string;
    full_address: string;
}

interface Organization {
    id: number;
    name: string;
}

interface Props {
    users: User[];
    locations: Location[];
    organizations: Organization[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Events', href: route('admin.events.index') },
    { title: 'Create', href: route('admin.events.create') },
];

// Signup timing options
const SIGNUP_START_OPTIONS = {
    NOW: 'now',
    EVENT_START: 'event-start',
    CUSTOM: 'custom',
};

const SIGNUP_END_OPTIONS = {
    EVENT_START: 'event-start',
    EVENT_END: 'event-end',
    CUSTOM: 'custom',
};

// Form state
const signupStartOption = ref(SIGNUP_START_OPTIONS.NOW);
const signupEndOption = ref(SIGNUP_END_OPTIONS.EVENT_END);
const selectedUserIds = ref<number[]>([]);
const selectedOrgIds = ref<number[]>([]);

const form = useForm({
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    location_id: null as number | null,
    status: 'published',
    has_signup: false,
    signup_start_date: '',
    signup_end_date: '',
    seats: -1, // -1 means unlimited
    min_age: null as number | null,
    max_age: null as number | null,
    class_restriction: '',
    restriction: 'everyone',
    user_ids: [] as number[],
    organization_ids: [] as number[],
});

// Watch for changes in signup options and update dates accordingly
watch(
    [() => form.start_date, () => form.end_date, signupStartOption, signupEndOption],
    () => {
        // Update signup start date based on selected option
        if (signupStartOption.value === SIGNUP_START_OPTIONS.NOW) {
            form.signup_start_date = new Date().toISOString().slice(0, 16);
        } else if (signupStartOption.value === SIGNUP_START_OPTIONS.EVENT_START && form.start_date) {
            form.signup_start_date = form.start_date;
        }

        // Update signup end date based on selected option
        if (signupEndOption.value === SIGNUP_END_OPTIONS.EVENT_START && form.start_date) {
            form.signup_end_date = form.start_date;
        } else if (signupEndOption.value === SIGNUP_END_OPTIONS.EVENT_END && form.end_date) {
            form.signup_end_date = form.end_date;
        }
    },
    { immediate: true },
);

// Toggle user selection
const toggleUser = (userId: number) => {
    const index = selectedUserIds.value.indexOf(userId);
    if (index === -1) {
        selectedUserIds.value.push(userId);
    } else {
        selectedUserIds.value.splice(index, 1);
    }
    form.user_ids = selectedUserIds.value;
};

// Toggle organization selection
const toggleOrganization = (orgId: number) => {
    const index = selectedOrgIds.value.indexOf(orgId);
    if (index === -1) {
        selectedOrgIds.value.push(orgId);
    } else {
        selectedOrgIds.value.splice(index, 1);
    }
    form.organization_ids = selectedOrgIds.value;
};

const submit = () => {
    // Set signup dates based on options if has_signup is true
    if (form.has_signup) {
        if (signupStartOption.value === SIGNUP_START_OPTIONS.NOW) {
            form.signup_start_date = new Date().toISOString().slice(0, 16);
        } else if (signupStartOption.value === SIGNUP_START_OPTIONS.EVENT_START) {
            form.signup_start_date = form.start_date;
        }

        if (signupEndOption.value === SIGNUP_END_OPTIONS.EVENT_START) {
            form.signup_end_date = form.start_date;
        } else if (signupEndOption.value === SIGNUP_END_OPTIONS.EVENT_END) {
            form.signup_end_date = form.end_date;
        }
    } else {
        // If no signup, clear signup dates
        form.signup_start_date = null;
        form.signup_end_date = null;
    }

    form.post(route('admin.events.store'), {
        onSuccess: () => {
            // Reset form
            form.reset();
            selectedUserIds.value = [];
            selectedOrgIds.value = [];
            signupStartOption.value = SIGNUP_START_OPTIONS.NOW;
            signupEndOption.value = SIGNUP_END_OPTIONS.EVENT_END;
            form.has_signup = false;
            form.seats = -1;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Event" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Create Event" description="Add a new event to the system" />

            <form @submit.prevent="submit" class="max-w-xl space-y-6">
                <!-- Basic Event Information -->
                <div class="space-y-2">
                    <Label for="title">Event Title</Label>
                    <Input id="title" v-model="form.title" type="text" required autofocus placeholder="Enter event title" />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" placeholder="Enter event description" rows="5" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="start_date">Start Date</Label>
                        <Input id="start_date" v-model="form.start_date" type="datetime-local" required />
                        <InputError :message="form.errors.start_date" />
                    </div>

                    <div class="space-y-2">
                        <Label for="end_date">End Date</Label>
                        <Input id="end_date" v-model="form.end_date" type="datetime-local" />
                        <InputError :message="form.errors.end_date" />
                    </div>
                </div>

                <!-- Location Selection -->
                <div class="space-y-2">
                    <Label for="location_id">Location</Label>
                    <Select :model-value="form.location_id" @update:model-value="(value) => form.location_id = value">
                        <SelectTrigger>
                            <SelectValue placeholder="Select location" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="null">None</SelectItem>
                            <SelectItem v-for="location in props.locations" :key="location.id" :value="location.id">
                                {{ location.name }} - {{ location.full_address }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.location_id" />
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <Label for="status">Status</Label>
                    <Select :model-value="form.status" @update:model-value="(value) => form.status = value">
                        <SelectTrigger>
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="published">Published</SelectItem>
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.status" />
                </div>

                <!-- Signup Configuration -->
                <div class="space-y-4 rounded-md border p-4">
                    <h3 class="text-lg font-medium">Signup Configuration</h3>

                    <div class="flex items-center space-x-2">
                        <Switch id="has_signup" v-model="form.has_signup" />
                        <Label for="has_signup">Enable signup for this event</Label>
                    </div>
                    <InputError :message="form.errors.has_signup" />

                    <div v-if="form.has_signup" class="space-y-4">
                        <!-- Signup Start Date Configuration -->
                        <div class="space-y-2">
                            <Label>When should signup start?</Label>
                            <RadioGroup v-model="signupStartOption">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="signup_start_now" value="now" />
                                    <Label for="signup_start_now">Now</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="signup_start_event" value="event-start" />
                                    <Label for="signup_start_event">Same as event start</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="signup_start_custom" value="custom" />
                                    <Label for="signup_start_custom">Custom date</Label>
                                </div>
                            </RadioGroup>
                        </div>

                        <div v-if="signupStartOption === 'custom'" class="space-y-2">
                            <Label for="signup_start_date">Custom Signup Start Date</Label>
                            <Input id="signup_start_date" v-model="form.signup_start_date" type="datetime-local" />
                            <InputError :message="form.errors.signup_start_date" />
                        </div>

                        <!-- Signup End Date Configuration -->
                        <div class="space-y-2">
                            <Label>When should signup end?</Label>
                            <RadioGroup v-model="signupEndOption">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="signup_end_event_start" value="event-start" />
                                    <Label for="signup_end_event_start">Same as event start</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="signup_end_event_end" value="event-end" />
                                    <Label for="signup_end_event_end">Same as event end</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="signup_end_custom" value="custom" />
                                    <Label for="signup_end_custom">Custom date</Label>
                                </div>
                            </RadioGroup>
                        </div>

                        <div v-if="signupEndOption === 'custom'" class="space-y-2">
                            <Label for="signup_end_date">Custom Signup End Date</Label>
                            <Input id="signup_end_date" v-model="form.signup_end_date" type="datetime-local" />
                            <InputError :message="form.errors.signup_end_date" />
                        </div>

                        <!-- Seats Configuration -->
                        <div class="space-y-2">
                            <Label for="seats">Number of Seats</Label>
                            <Select :model-value="form.seats" @update:model-value="(value) => form.seats = value">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select seats availability" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="-1">Unlimited seats</SelectItem>
                                    <SelectItem :value="0">No seats available</SelectItem>
                                    <SelectItem :value="10">10 seats</SelectItem>
                                    <SelectItem :value="20">20 seats</SelectItem>
                                    <SelectItem :value="50">50 seats</SelectItem>
                                    <SelectItem :value="100">100 seats</SelectItem>
                                    <SelectItem :value="200">200 seats</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.seats" />
                        </div>
                    </div>
                </div>

                <!-- Organizers -->
                <div class="space-y-4 rounded-md border p-4">
                    <h3 class="text-lg font-medium">Organizers</h3>

                    <!-- User Organizers -->
                    <div class="space-y-2">
                        <Label>User Organizers</Label>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                            <div v-for="user in props.users" :key="user.id" class="flex items-center space-x-2">
                                <Checkbox
                                    :id="'user-' + user.id"
                                    :checked="selectedUserIds.includes(user.id)"
                                    @update:checked="toggleUser(user.id)"
                                />
                                <Label :for="'user-' + user.id">{{ user.name }}</Label>
                            </div>
                        </div>
                        <InputError :message="form.errors.user_ids" />
                    </div>

                    <!-- Organization Organizers -->
                    <div class="space-y-2">
                        <Label>Organization Organizers</Label>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                            <div v-for="org in props.organizations" :key="org.id" class="flex items-center space-x-2">
                                <Checkbox
                                    :id="'org-' + org.id"
                                    :checked="selectedOrgIds.includes(org.id)"
                                    @update:checked="toggleOrganization(org.id)"
                                />
                                <Label :for="'org-' + org.id">{{ org.name }}</Label>
                            </div>
                        </div>
                        <InputError :message="form.errors.organization_ids" />
                    </div>
                </div>

                <!-- Access Restrictions -->
                <div class="space-y-4 rounded-md border p-4">
                    <h3 class="text-lg font-medium">Access Restrictions</h3>

                    <!-- Role Restriction -->
                    <div class="space-y-2">
                        <Label for="restriction">Role Restriction</Label>
                        <Select v-model="form.restriction">
                            <SelectTrigger>
                                <SelectValue placeholder="Select restriction" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="everyone">Everyone</SelectItem>
                                <SelectItem value="members">Members Only</SelectItem>
                                <SelectItem value="crew">Crew Only</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.restriction" />
                    </div>

                    <!-- Class Restriction -->
                    <div class="space-y-2">
                        <Label for="class_restriction">Class Restriction</Label>
                        <Input id="class_restriction" v-model="form.class_restriction" type="text" placeholder="Enter class restriction (optional)" />
                        <InputError :message="form.errors.class_restriction" />
                    </div>

                    <!-- Age Limits -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="min_age">Minimum Age</Label>
                            <Input id="min_age" v-model="form.min_age" type="number" min="0" placeholder="No minimum" />
                            <InputError :message="form.errors.min_age" />
                        </div>

                        <div class="space-y-2">
                            <Label for="max_age">Maximum Age</Label>
                            <Input id="max_age" v-model="form.max_age" type="number" min="0" placeholder="No maximum" />
                            <InputError :message="form.errors.max_age" />
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.events.index')">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">Create Event</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
