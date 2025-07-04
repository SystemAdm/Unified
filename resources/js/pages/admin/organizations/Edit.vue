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
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { TrashIcon } from 'lucide-vue-next';

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
const unassignUser = (userId: number) => {
    console.log(userId);
}
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
                    <Table class="w-full border-collapse">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in props.users" :key="user.id" class="border-b">
                                <TableCell>{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell class="px-6 whitespace-nowrap text-right text-sm font-medium">
                                    <Button variant="destructive" size="sm" @click="unassignUser(user.id)">
                                        <TrashIcon class="h-4 w-4 mr-2" />
                                        Delete
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
