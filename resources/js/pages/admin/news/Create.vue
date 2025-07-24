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
import { Checkbox } from '@/components/ui/checkbox';
import { BanIcon, SaveIcon } from 'lucide-vue-next';

interface Role {
    name: string;
    value: string;
}

interface AvailableImage {
    path: string;
    url: string;
    name: string;
    size: number;
}

interface Props {
    roles: Role[];
    availableImages: AvailableImage[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'News', href: route('admin.news.index') },
    { title: 'Add', href: route('admin.news.create') },
];

const form = useForm({
    title: '',
    excerpt: '',
    content: '',
    published_at: '',
    is_published: false,
    visible_to_role: [] as string[],
    featured_image: null,
    selected_image: '',
});

const submit = () => {
    form.post(route('admin.news.store'), {
        onSuccess: () => {
            form.reset();
        }
    });
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
        <Head title="Add News Article" />
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Add News Article" description="Add a new news article to the system" />
            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="title">Title</Label>
                    <Input id="title" v-model="form.title" type="text" required autofocus placeholder="Enter news article title" />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="space-y-2">
                    <Label for="excerpt">Excerpt (Optional)</Label>
                    <Textarea id="excerpt" v-model="form.excerpt" placeholder="Enter a brief excerpt or summary" />
                    <InputError :message="form.errors.excerpt" />
                </div>

                <div class="space-y-2">
                    <Label for="content">Content</Label>
                    <Textarea id="content" v-model="form.content" required placeholder="Enter the full article content" rows="10" />
                    <InputError :message="form.errors.content" />
                </div>

                <div class="space-y-2">
                    <Label for="published_at">Publish Date & Time (Optional)</Label>
                    <Input id="published_at" v-model="form.published_at" type="datetime-local" />
                    <p class="text-sm text-gray-500">Leave empty to publish immediately when activated</p>
                    <InputError :message="form.errors.published_at" />
                </div>

                <div class="space-y-4">
                    <Label>Featured Image (Optional)</Label>

                    <!-- Upload new image -->
                    <div class="space-y-2">
                        <Label for="featured_image" class="text-sm font-medium">Upload New Image</Label>
                        <Input id="featured_image" type="file" @input="form.featured_image = $event.target.files[0]; form.selected_image = ''" accept="image/*" />
                        <p class="text-sm text-gray-500">Upload a new featured image for the article. Max size: 2MB.</p>
                        <InputError :message="form.errors.featured_image" />
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
                                @click="form.selected_image = image.path; form.featured_image = null"
                            >
                                <img :src="image.url" :alt="image.name" class="w-full h-24 object-cover" />
                                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition-all"></div>
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

                <div class="flex items-center space-x-2">
                    <Checkbox id="is_published" :checked="form.is_published" @update:checked="form.is_published = $event" />
                    <Label for="is_published">Publish article</Label>
                    <InputError :message="form.errors.is_published" />
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
                    <Link :href="route('admin.news.index')">
                        <Button type="button" variant="destructive"><BanIcon />Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing"><SaveIcon />Add News Article</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
