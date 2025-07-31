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

interface News {
    id: number;
    title: string;
    excerpt: string | null;
    content: string;
    featured_image: string | null;
    is_published: boolean;
    published_at: string | null;
    visible_to_role: string[] | null;
}

interface AvailableImage {
    path: string;
    url: string;
    name: string;
    size: number;
}

interface Props {
    news: News;
    roles: Role[];
    availableImages: AvailableImage[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'News', href: route('admin.news.index') },
    { title: props.news.title, href: route('admin.news.edit', { news: props.news.id }) },
];

// Format datetime for input field (convert from UTC to local)
const formatDateTimeForInput = (dateString: string | null) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:mm
};

const form = useForm({
    title: props.news.title,
    excerpt: props.news.excerpt || '',
    content: props.news.content,
    published_at: formatDateTimeForInput(props.news.published_at),
    is_published: props.news.is_published,
    visible_to_role: props.news.visible_to_role || [],
    featured_image: null, // Will be set when a new image is selected
    selected_image: '',
    _method: 'PUT',
});

const submit = () => {
    form.post(route('admin.news.update', { news: props.news.id }), {
        onSuccess: () => {
            // Form is automatically reset on success
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
        <Head title="Edit News Article" />
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Edit News Article" description="Update news article information" />
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

                    <!-- Current image -->
                    <div v-if="news.featured_image" class="space-y-2">
                        <Label class="text-sm font-medium">Current Image</Label>
                        <div class="border rounded-lg p-4 ">
                            <img :src="`/storage/${news.featured_image}`" alt="Current featured image" class="max-w-xs max-h-48 rounded-md mb-2" />
                            <p class="text-sm text-gray-600">{{ news.featured_image.split('/').pop() }}</p>
                        </div>
                    </div>

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
                                :class="form.selected_image === image.path ? 'border-blue-500 bg-blue-50' : (news.featured_image === image.path ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300')"
                                @click="form.selected_image = image.path; form.featured_image = null"
                            >
                                <img :src="image.url" :alt="image.name" class="w-full h-24 object-cover" />
                                <div class="absolute inset-0 bg-transparent hover:bg-gray-800/50 transition-all"></div>
                                <div v-if="form.selected_image === image.path" class="absolute top-1 right-1 bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                    ✓
                                </div>
                                <div v-else-if="news.featured_image === image.path && !form.selected_image" class="absolute top-1 right-1 bg-green-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                    ●
                                </div>
                                <div class="p-2 bg-white">
                                    <p class="text-xs text-gray-600 truncate" :title="image.name">{{ image.name }}</p>
                                    <p class="text-xs text-gray-400">{{ Math.round(image.size / 1024) }}KB</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">Green dot (●) indicates current image, blue checkmark (✓) indicates selected image</p>
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
                                :model-value="form.visible_to_role.includes(role.value)"
                                @update:model-value="toggleRole(role.value)"
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
                    <Button type="submit" :disabled="form.processing"><SaveIcon />Update News Article</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
