<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
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
        href: `/admin/users/${props.user.id}/edit`,
    },
];

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    roles: [...props.user.roles],
});

const toggleRole = (roleId: number) => {
    const index = form.roles.indexOf(roleId);
    if (index === -1) {
        form.roles.push(roleId);
    } else {
        form.roles.splice(index, 1);
    }
};

const submit = () => {
    form.put(route('admin.users.update', { user: props.user.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit User" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Edit User" :description="`Update user: ${props.user.name}`" />

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
                                    :checked="form.roles.includes(role.id)"
                                    @update:checked="toggleRole(role.id)"
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
                    <Button type="submit" :disabled="form.processing">Update User</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
