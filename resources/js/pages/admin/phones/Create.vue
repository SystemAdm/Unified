<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
}

interface Props {
    users: User[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Phones',
        href: '/admin/phones',
    },
    {
        title: 'Create Phone',
        href: '/admin/phones/create',
    },
];

const form = useForm({
    phone_number: '',
    user_id: '',
    is_primary: false,
    is_verified: false,
});

const submit = () => {
    form.post(route('admin.phones.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Phone" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Create Phone" description="Add a new phone number to the system" />

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-4">
                    <!-- Phone Number -->
                    <div class="grid gap-2">
                        <Label for="phone_number">Phone Number</Label>
                        <Input
                            id="phone_number"
                            v-model="form.phone_number"
                            type="tel"
                            required
                            autofocus
                            autocomplete="tel"
                            placeholder="+1 123 456 7890"
                        />
                        <InputError :message="form.errors.phone_number" />
                    </div>

                    <!-- User -->
                    <div class="grid gap-2">
                        <Label for="user_id">User</Label>
                        <Select :model-value="form.user_id" @update:model-value="(value) => form.user_id = value">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a user" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="user in props.users" :key="user.id" :value="user.id.toString()">
                                    {{ user.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.user_id" />
                    </div>

                    <!-- Is Primary -->
                    <div class="flex items-center space-x-2">
                        <Checkbox id="is_primary" v-model="form.is_primary" />
                        <Label for="is_primary">Set as primary phone number for this user</Label>
                        <InputError :message="form.errors.is_primary" />
                    </div>

                    <!-- Is Verified -->
                    <div class="flex items-center space-x-2">
                        <Checkbox id="is_verified" v-model="form.is_verified" />
                        <Label for="is_verified">Mark as verified</Label>
                        <InputError :message="form.errors.is_verified" />
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.phones.index')">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">Create Phone</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
