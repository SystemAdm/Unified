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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: route('admin.index'),
    },
    {
        title: 'Organizations',
        href: route('admin.organizations.index'),
    },
    {
        title: 'Create',
        href: route('admin.organizations.create'),
    },
];

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('admin.organizations.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Organization" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Create Organization" description="Add a new organization to the system" />

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
                    <Button type="submit" :disabled="form.processing">Create Organization</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
