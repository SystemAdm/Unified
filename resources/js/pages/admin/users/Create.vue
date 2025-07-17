<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { type DateValue, getLocalTimeZone, today } from '@internationalized/date';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { DatePicker } from '@/components/ui/datepicker';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader } from '@/components/ui/card';

interface Role {
    id: number;
    name: string;
}

interface Props {
    roles: Role[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: route('admin.index'),
    },
    {
        title: 'Users',
        href: route('admin.users.index'),
    },
    {
        title: 'Create User',
        href: route('admin.users.create'),
    },
];

// Create a ref to track selected birthday, initialized with today's date
const selectedDate = ref<DateValue>(today(getLocalTimeZone()));

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    birthday: '',
    roles: [] as number[],
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

const toggleRole = (roleId: number) => {
    console.log('toggleRole called with roleId:', roleId);
    console.log('Before toggle - form.roles:', form.roles);

    const index = form.roles.indexOf(roleId);
    if (index === -1) {
        // Create a new array with the new role added
        form.roles = [...form.roles, roleId];
    } else {
        // Create a new array without the role
        form.roles = form.roles.filter(id => id !== roleId);
    }

    console.log('After toggle - form.roles:', form.roles);
};

const submit = () => {
    console.log('Submitting with roles:', form.roles);
    console.log('Form data:', form);

    // Check if at least one role is selected
    if (form.roles.length === 0) {
        console.log('No roles selected, setting error');
        form.setError('roles', 'The roles field is required.');
        return;
    }

    console.log('Posting form data to server');
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Form submitted successfully');
        },
        onError: (errors) => {
            console.log('Form submission failed with errors:', errors);
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create User" />

        <Card class="flex flex-col space-y-6 w-[350px]">
            <CardHeader>
            <HeadingSmall title="Create User" description="Add a new user to the system" />
            </CardHeader>
            <CardContent>
            <form @submit.prevent="submit" class="space-y-6">
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

                    <!-- Email -->
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Phone -->
                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            autocomplete="tel"
                        />
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
                            required
                            autocomplete="new-password"
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
                                    :model-value="form.roles.includes(role.id)"
                                    @update:model-value="toggleRole(role.id)"
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
                    <Button type="submit" :disabled="form.processing">Create User</Button>
                </div>
            </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
