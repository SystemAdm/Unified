<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { BanIcon, SaveIcon } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';

interface AvailableImage {
    path: string;
    url: string;
    name: string;
    size: number;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface SelfHostedApp {
    id: number;
    name: string;
    description: string | null;
    public_link: string | null;
    admin_link: string | null;
    demo_username: string | null;
    demo_password: string | null;
    image: string | null;
    status: 'published' | 'draft';
    visibility: 'admin' | 'user';
    created_at: string;
    updated_at: string;
    managers: User[];
}

interface Props {
    app: SelfHostedApp;
    availableImages: AvailableImage[];
    users: User[];
    managerIds: number[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Self-Hosted Apps', href: route('admin.selfhostedapps.index') },
    { title: 'Edit', href: route('admin.selfhostedapps.edit', props.app.id) },
];

const form = useForm({
    name: props.app.name,
    description: props.app.description || '',
    public_link: props.app.public_link || '',
    admin_link: props.app.admin_link || '',
    demo_username: props.app.demo_username || '',
    demo_password: props.app.demo_password || '',
    status: props.app.status,
    visibility: props.app.visibility,
    image: null,
    selected_image: props.app.image || '',
    manager_ids: props.managerIds,
});

const submit = () => {
    form.post(route('admin.selfhostedapps.update', props.app.id), {
        method: 'put',
        onSuccess: () => {
            form.reset();
        }
    });
};

const toggleManager = (userId: number) => {
    const index = form.manager_ids.indexOf(userId);
    if (index === -1) {
        form.manager_ids.push(userId);
    } else {
        form.manager_ids.splice(index, 1);
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit Self-Hosted App" />
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Edit Self-Hosted App" :description="`Editing: ${app.name}`" />
            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" type="text" required autofocus placeholder="Enter app name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description (Optional)</Label>
                    <Textarea id="description" v-model="form.description" placeholder="Enter a description of the app" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="public_link">Public Link (Optional)</Label>
                        <Input id="public_link" v-model="form.public_link" type="url" placeholder="Enter public URL" />
                        <InputError :message="form.errors.public_link" />
                    </div>

                    <div class="space-y-2">
                        <Label for="admin_link">Admin Link (Optional)</Label>
                        <Input id="admin_link" v-model="form.admin_link" type="url" placeholder="Enter admin URL" />
                        <InputError :message="form.errors.admin_link" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="demo_username">Demo Username (Optional)</Label>
                        <Input id="demo_username" v-model="form.demo_username" type="text" placeholder="Enter demo username" />
                        <InputError :message="form.errors.demo_username" />
                    </div>

                    <div class="space-y-2">
                        <Label for="demo_password">Demo Password (Optional)</Label>
                        <Input id="demo_password" v-model="form.demo_password" type="text" placeholder="Enter demo password" />
                        <InputError :message="form.errors.demo_password" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="draft">Draft</SelectItem>
                                <SelectItem value="published">Published</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.status" />
                    </div>

                    <div class="space-y-2">
                        <Label for="visibility">Visibility</Label>
                        <Select v-model="form.visibility">
                            <SelectTrigger>
                                <SelectValue placeholder="Select visibility" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="admin">Admin Only</SelectItem>
                                <SelectItem value="user">All Users</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.visibility" />
                    </div>
                </div>

                <div class="space-y-4">
                    <Label>Image (Optional)</Label>

                    <!-- Current image preview -->
                    <div v-if="app.image" class="space-y-2">
                        <Label class="text-sm font-medium">Current Image</Label>
                        <div class="border rounded-lg p-4 flex items-center space-x-4">
                            <img
                                :src="app.image.startsWith('http') ? app.image : `/storage/${app.image}`"
                                alt="Current app image"
                                class="w-24 h-24 object-cover rounded-md"
                            />
                            <div>
                                <p class="text-sm font-medium">{{ app.image.split('/').pop() }}</p>
                                <p class="text-xs text-gray-500">Upload a new image or select from existing ones to replace this image.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload new image -->
                    <div class="space-y-2">
                        <Label for="image" class="text-sm font-medium">Upload New Image</Label>
                        <Input id="image" type="file" @input="form.image = $event.target.files[0]; form.selected_image = ''" accept="image/*" />
                        <p class="text-sm text-gray-500">Upload an image for the app. Max size: 2MB.</p>
                        <InputError :message="form.errors.image" />
                    </div>

                    <!-- Select from existing images -->
                    <div v-if="props.availableImages.length > 0" class="space-y-2">
                        <Label class="text-sm font-medium">Or Select from Previously Uploaded Images</Label>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-64 overflow-y-auto border rounded-lg p-4">
                            <div
                                v-for="image in props.availableImages"
                                :key="image.path"
                                class="relative cursor-pointer border-2 rounded-lg overflow-hidden transition-all hover:shadow-md"
                                :class="form.selected_image === image.path ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                                @click="form.selected_image = image.path; form.image = null"
                            >
                                <img :src="image.url" :alt="image.name" class="w-full h-24 object-cover" />
                                <div class="absolute inset-0 bg-transparent hover:bg-gray-800/50 transition-all"></div>
                                <div v-if="form.selected_image === image.path" class="absolute top-1 right-1 bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                    ✓
                                </div>
                                <div class="p-2 bg-white">
                                    <p class="text-xs text-gray-600 truncate" :title="image.name">{{ image.name }}</p>
                                    <p class="text-xs text-gray-400">{{ Math.round(image.size / 1024) }}KB</p>
                                </div>
                            </div>
                        </div>
                        <InputError :message="form.errors.selected_image" />
                    </div>

                    <div v-else class="text-sm text-gray-500 italic">
                        No previously uploaded images available.
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>App Managers</Label>
                    <div class="border rounded-lg p-4 max-h-64 overflow-y-auto">
                        <div v-for="user in props.users" :key="user.id" class="flex items-center space-x-2 py-2 border-b last:border-b-0">
                            <Checkbox
                                :id="`user-${user.id}`"
                                :checked="form.manager_ids.includes(user.id)"
                                @update:checked="toggleManager(user.id)"
                            />
                            <Label :for="`user-${user.id}`" class="cursor-pointer flex-1">
                                <div class="font-medium">{{ user.name }}</div>
                                <div class="text-sm text-gray-500">{{ user.email }}</div>
                            </Label>
                        </div>
                        <div v-if="props.users.length === 0" class="text-sm text-gray-500 italic py-2">
                            No users available to assign as managers.
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Select at least one user to manage this app.</p>
                    <InputError :message="form.errors.manager_ids" />
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.selfhostedapps.index')">
                        <Button type="button" variant="destructive"><BanIcon />Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing"><SaveIcon />Update Self-Hosted App</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
