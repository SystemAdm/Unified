<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Link } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Organization {
    id: number;
    name: string;
}

interface Props {
    organization: Organization;
    users: User[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Organizations',
        href: '/admin/organizations',
    },
    {
        title: 'Edit',
        href: `/admin/organizations/${props.organization.id}/edit`,
    },
];

const form = useForm({
    name: props.organization.name,
});

const submit = () => {
    form.put(route('admin.organizations.update', { organization: props.organization.id }), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit Organization" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Edit Organization" :description="`Update details for ${props.organization.name}`" />

            <form @submit.prevent="submit" class="space-y-6 max-w-xl">
                <div class="space-y-2">
                    <Label for="name">Organization Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        placeholder="Enter organization name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.organizations.index')">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">Update Organization</Button>
                </div>
            </form>

            <!-- Users in this organization -->
            <div class="mt-8">
                <HeadingSmall title="Users" description="Users in this organization" />

                <div v-if="props.users.length === 0" class="p-4 text-center text-gray-500">
                    No users in this organization.
                </div>
                <div v-else class="overflow-x-auto mt-4">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-3 text-left">Name</th>
                                <th class="p-3 text-left">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in props.users" :key="user.id" class="border-b">
                                <td class="p-3">{{ user.name }}</td>
                                <td class="p-3">{{ user.email }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
