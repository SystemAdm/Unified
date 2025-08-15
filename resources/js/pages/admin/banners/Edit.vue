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

interface Banner {
    id: number;
    title: string;
    description: string;
    type: string;
    from_datetime: string;
    to_datetime: string;
    is_published: boolean;
    is_recurring?: boolean;
    is_active?: boolean;
    relative_day?: string | null;
    visible_to_access: string[] | null;
    visible_to_role: string[] | null;
    link_norwegian: string | null;
    link_english: string | null;
}

interface Props {
    banner: Banner;
    types: AnnouncementType[];
    accessTypes: Access[];
    roles: Role[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Banners', href: route('admin.banners.index') },
    { title: props.banner.title, href: route('admin.banners.edit', { banner: props.banner.id }) },
];

// Format datetime for input field (convert from UTC to local)
const formatDateTimeForInput = (dateString: string) => {
    const date = new Date(dateString);
    return date.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:mm
};

const form = useForm({
    title: props.banner.title,
    description: props.banner.description,
    type: props.banner.type,
    from_datetime: formatDateTimeForInput(props.banner.from_datetime),
    to_datetime: formatDateTimeForInput(props.banner.to_datetime),
    is_published: props.banner.is_published,
    is_recurring: (props.banner as any).is_recurring ?? false,
    visible_to_access: props.banner.visible_to_access || [],
    visible_to_role: props.banner.visible_to_role || [],
    link_norwegian: props.banner.link_norwegian || '',
    link_english: props.banner.link_english || '',
    _method: 'PUT',
});

const submit = () => {
    form.post(route('admin.banners.update', { banner: props.banner.id }), {
        onSuccess: () => {
            // Form is automatically reset on success
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
        <Head title="Edit Banner" />
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Edit Banner" description="Update banner information" />
            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="title">Title</Label>
                    <Input id="title" v-model="form.title" type="text" required autofocus placeholder="Enter banner title" />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" required placeholder="Enter banner description" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="space-y-2">
                    <Label for="type">Type</Label>
                    <Select id="type" :model-value="form.type" @update:model-value="form.type = $event ?? ''">
                        <SelectTrigger>
                            <SelectValue placeholder="Select banner type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Banner Types</SelectLabel>
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
                    <Label for="is_published">Publish banner</Label>
                    <InputError :message="form.errors.is_published" />
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox id="is_recurring" :checked="(form as any).is_recurring" @update:checked="(form as any).is_recurring = $event" />
                    <Label for="is_recurring">Recurring annually</Label>
                    <InputError :message="(form as any).errors?.is_recurring" />
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

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="link_norwegian">Norwegian Link (Optional)</Label>
                        <Input id="link_norwegian" v-model="form.link_norwegian" type="url" placeholder="https://example.no" />
                        <InputError :message="form.errors.link_norwegian" />
                    </div>

                    <div class="space-y-2">
                        <Label for="link_english">English Link (Optional)</Label>
                        <Input id="link_english" v-model="form.link_english" type="url" placeholder="https://example.com" />
                        <InputError :message="form.errors.link_english" />
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.banners.index')">
                        <Button type="button" variant="destructive"><BanIcon />Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing"><SaveIcon />Update Banner</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
