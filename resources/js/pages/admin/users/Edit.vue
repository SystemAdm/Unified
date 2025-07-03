<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { type DateValue, getLocalTimeZone, parseDate, today } from '@internationalized/date';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { DatePicker } from '@/components/ui/datepicker';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Role {
    id: number;
    name: string;
}

interface Email {
    id: number;
    address: string;
    is_primary: boolean;
    is_verified: boolean;
}

interface Phone {
    id: number;
    number: string;
    is_primary: boolean;
    is_verified: boolean;
}

interface User {
    id: number;
    name: string;
    email: string;
    phone: string;
    birthday?: string;
    emails: Email[];
    phones: Phone[];
    roles: number[];
}

interface Props {
    user: User;
    roles: Role[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Users',
        href: '/admin/users',
    },
    {
        title: 'Edit User',
        href: `/admin/users/${props.user?.id || 0}/edit`,
    },
];

// Ensure props.user and props.user.roles are defined and roles is an array
const userRoles = props.user && Array.isArray(props.user.roles) ? props.user.roles : [];

// Debug role selection
console.log('User roles:', userRoles);
console.log('Available roles:', props.roles);
console.log('User roles as strings:', userRoles.map(id => String(id)));
console.log('Available role IDs as strings:', props.roles.map(role => String(role.id)));

// Convert all IDs to strings for comparison to avoid type mismatches
const userRolesStr = userRoles.map(id => String(id));

// Initialize selectedDate with the user's birthday if available, or today's date if not
let initialDate: DateValue;
if (props.user?.birthday) {
    // Parse the birthday string (YYYY-MM-DD) into a DateValue
    const [year, month, day] = props.user.birthday.split('-').map(Number);
    initialDate = parseDate(`${year}-${month}-${day}`);
} else {
    initialDate = today(getLocalTimeZone());
}
const selectedDate = ref<DateValue>(initialDate);

// Additional debugging for role selection
console.log('Initial selectedRoles:', props.roles.filter(role => userRolesStr.includes(String(role.id))));
const selectedRoles = ref<Role[]>(
    props.roles.filter(role => userRolesStr.includes(String(role.id)))
);

const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    phone: props.user?.phone || '',
    birthday: props.user?.birthday || '',
    password: '',
    roles: [...userRoles], // This will be updated by the watch function when selectedRoles changes
});

// Update form.birthday when selectedDate changes
watch(selectedDate, (newDate) => {
    if (newDate) {
        // Convert the DateValue to YYYY-MM-DD format for the form
        const year = newDate.year;
        const month = newDate.month.toString().padStart(2, '0');
        const day = newDate.day.toString().padStart(2, '0');
        form.birthday = `${year}-${month}-${day}`;
    } else {
        form.birthday = '';
    }
});

// Update form.roles when selectedRoles changes
watch(selectedRoles, (newRoles) => {
    form.roles = newRoles.map(role => role.id);
}, { deep: true, immediate: true });

const toggleRole = (role: Role) => {
    // Use string comparison to avoid type mismatches
    const index = selectedRoles.value.findIndex(r => String(r.id) === String(role.id));
    if (index === -1) {
        selectedRoles.value.push(role);
    } else {
        selectedRoles.value.splice(index, 1);
    }
};

const submit = () => {
    // Ensure form.roles is updated with the latest selectedRoles
    form.roles = selectedRoles.value.map(role => role.id);

    // Check if at least one role is selected
    if (form.roles.length === 0) {
        form.setError('roles', 'The roles field is required.');
        return;
    }

    if (!props.user?.id) {
        console.error('User ID is undefined');
        return;
    }

    form.put(route('admin.users.update', { user: props.user.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit User" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Edit User" :description="`Update user: ${props.user?.name || 'User'}`" />

            <form class="space-y-6 p-6 border rounded-lg">
                <div class="grid gap-4">
                    <!-- Name -->
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Emails -->
                    <div class="grid gap-2">
                        <Label for="email">Emails</Label>
                        <div class="space-y-2">
                            <div v-if="!props.user.emails || props.user.emails.length === 0" class="flex items-center space-x-2">
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                />
                            </div>
                            <div v-for="email in props.user.emails || []" :key="email.id" class="flex items-center space-x-2">
                                <div class="px-3 py-2 border border-input rounded-md bg-muted text-muted-foreground" :class="{ 'border-green-500': email.is_verified && !email.is_primary,'border-yellow-500': email.is_primary }">
                                    {{ email.address }}
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span v-if="email.is_primary" class="text-xs text-yellow-500">Primary</span>
                                    <span v-if="email.is_verified" class="text-xs text-green-500">Verified</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Primary email cannot be changed. To manage multiple emails, please use the user profile page.
                        </p>
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Phones -->
                    <div class="grid gap-2">
                        <Label for="phone">Phone Numbers</Label>
                        <div class="space-y-2">
                            <div v-if="!props.user.phones || props.user.phones.length === 0" class="flex items-center space-x-2">
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    autocomplete="tel"
                                />
                            </div>
                            <div v-for="phone in props.user.phones || []" :key="phone.id" class="flex items-center space-x-2">
                                <div class="px-3 py-2 border border-input rounded-md bg-muted text-muted-foreground" :class="{ 'border-yellow-500': phone.is_primary }">
                                    {{ phone.number }}
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span v-if="phone.is_primary" class="text-xs text-yellow-500">Primary</span>
                                    <span v-if="phone.is_verified" class="text-xs text-green-500">Verified</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Primary phone cannot be changed. To manage multiple phone numbers, please use the user profile page.
                        </p>
                        <InputError :message="form.errors.phone" />
                    </div>

                    <!-- Birthday -->
                    <div class="grid gap-2">
                        <Label for="birthday">Birthday</Label>
                        <DatePicker
                            id="birthday"
                            v-model="selectedDate"
                            :placeholder="selectedDate"
                        />
                        <InputError :message="form.errors.birthday" />
                    </div>

                    <!-- Password -->
                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Leave blank to keep current password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Roles -->
                    <div class="grid gap-2">
                        <Label>Roles</Label>
                        <div class="grid gap-2">
                            <div v-for="role in props.roles" :key="role.id" class="flex items-center space-x-2">
                                <Checkbox
                                    :id="`role-${role.id}`"
                                    :model-value="selectedRoles.some(r => String(r.id) === String(role.id))"
                                    @update:model-value="toggleRole(role)"
                                />
                                <Label :for="`role-${role.id}`" class="cursor-pointer">{{ role.name }}</Label>
                            </div>
                        </div>
                        <InputError :message="form.errors.roles" />
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.users.index')">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="button" :disabled="form.processing" @click="submit">Update User</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
