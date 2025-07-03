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

interface Permission {
    id: number;
    name: string;
}

interface Props {
    permissions: Permission[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Roles',
        href: '/admin/roles',
    },
    {
        title: 'Create Role',
        href: '/admin/roles/create',
    },
];

const form = useForm({
    name: '',
    permissions: [] as number[],
});

const togglePermission = (permissionId: number) => {
    const index = form.permissions.indexOf(permissionId);
    if (index === -1) {
        // Create a new array with the new permission added
        form.permissions = [...form.permissions, permissionId];
    } else {
        // Create a new array without the permission
        form.permissions = form.permissions.filter(id => id !== permissionId);
    }
};

const submit = () => {
    form.post(route('admin.roles.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Role" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Create Role" description="Add a new role to the system" />

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
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Permissions -->
                    <div class="grid gap-2">
                        <Label>Permissions</Label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            <div v-for="permission in props.permissions" :key="permission.id" class="flex items-center space-x-2">
                                <Checkbox
                                    :id="`permission-${permission.id}`"
                                    :model-value="form.permissions.includes(permission.id)"
                                    @update:model-value="togglePermission(permission.id)"
                                />
                                <Label :for="`permission-${permission.id}`" class="cursor-pointer">{{ permission.name }}</Label>
                            </div>
                        </div>
                        <InputError :message="form.errors.permissions" />
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.roles.index')">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">Create Role</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
