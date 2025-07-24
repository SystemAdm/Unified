<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Select, SelectItem, SelectTrigger, SelectValue, SelectGroup, SelectContent, SelectLabel } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { BanIcon, SaveIcon } from 'lucide-vue-next';

interface AnnouncementType {
    name: string;
    value: string;
}

interface Access {
    name: string;
    value: string;
}

interface Role {
    name: string;
    value: string;
}

interface Props {
    types: AnnouncementType[];
    accessTypes: Access[];
    roles: Role[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Announcements', href: route('admin.announcements.index') },
    { title: 'Add', href: route('admin.announcements.create') },
];

const form = useForm({
    title: '',
    description: '',
    type: '',
    from_datetime: '',
    to_datetime: '',
    is_published: false,
    visible_to_access: [] as string[],
    visible_to_role: [] as string[],
});

const submit = () => {
    form.post(route('admin.announcements.store'), {
        onSuccess: () => {
            form.reset();
        }
    });
};

const toggleAccess = (accessValue: string) => {
    const index = form.visible_to_access.indexOf(accessValue);
    if (index > -1) {
        form.visible_to_access.splice(index, 1);
    } else {
        form.visible_to_access.push(accessValue);
    }
};

const toggleRole = (roleValue: string) => {
    const index = form.visible_to_role.indexOf(roleValue);
    if (index > -1) {
        form.visible_to_role.splice(index, 1);
    } else {
        form.visible_to_role.push(roleValue);
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Add Announcement" />
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Add Announcement" description="Add a new announcement to the system" />
            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="title">Title</Label>
                    <Input id="title" v-model="form.title" type="text" required autofocus placeholder="Enter announcement title" />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" required placeholder="Enter announcement description" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="space-y-2">
                    <Label for="type">Type</Label>
                    <Select id="type" :model-value="form.type" @update:model-value="form.type = $event ?? ''">
                        <SelectTrigger>
                            <SelectValue placeholder="Select announcement type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Announcement Types</SelectLabel>
                                <SelectItem v-for="type in props.types" :value="type.value" :key="type.value">{{ type.name }}</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.type" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="from_datetime">From Date & Time</Label>
                        <Input id="from_datetime" v-model="form.from_datetime" type="datetime-local" required />
                        <InputError :message="form.errors.from_datetime" />
                    </div>

                    <div class="space-y-2">
                        <Label for="to_datetime">To Date & Time</Label>
                        <Input id="to_datetime" v-model="form.to_datetime" type="datetime-local" required />
                        <InputError :message="form.errors.to_datetime" />
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox id="is_published" :checked="form.is_published" @update:checked="form.is_published = $event" />
                    <Label for="is_published">Publish announcement</Label>
                    <InputError :message="form.errors.is_published" />
                </div>

                <div class="space-y-2">
                    <Label>Visible to Access Types</Label>
                    <div class="grid grid-cols-3 gap-2">
                        <div v-for="access in props.accessTypes" :key="access.value" class="flex items-center space-x-2">
                            <Checkbox
                                :id="`access-${access.value}`"
                                :checked="form.visible_to_access.includes(access.value)"
                                @update:checked="toggleAccess(access.value)"
                            />
                            <Label :for="`access-${access.value}`">{{ access.name }}</Label>
                        </div>
                    </div>
                    <InputError :message="form.errors.visible_to_access" />
                </div>

                <div class="space-y-2">
                    <Label>Visible to Roles</Label>
                    <div class="grid grid-cols-3 gap-2">
                        <div v-for="role in props.roles" :key="role.value" class="flex items-center space-x-2">
                            <Checkbox
                                :id="`role-${role.value}`"
                                :checked="form.visible_to_role.includes(role.value)"
                                @update:checked="toggleRole(role.value)"
                            />
                            <Label :for="`role-${role.value}`">{{ role.name }}</Label>
                        </div>
                    </div>
                    <InputError :message="form.errors.visible_to_role" />
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.announcements.index')">
                        <Button type="button" variant="destructive"><BanIcon />Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing"><SaveIcon />Add Announcement</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
