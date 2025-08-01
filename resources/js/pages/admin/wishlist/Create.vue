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

interface AvailableImage {
    path: string;
    url: string;
    name: string;
    size: number;
}

interface Props {
    availableImages: AvailableImage[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Wishlist', href: route('admin.wishlists.index') },
    { title: 'Add', href: route('admin.wishlists.create') },
];

const form = useForm({
    name: '',
    description: '',
    link: '',
    cost_per_unit: 0,
    count: 1,
    deadline: '',
    image: null,
    selected_image: '',
});

const submit = () => {
    form.post(route('admin.wishlists.store'), {
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Add Wishlist Item" />
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Add Wishlist Item" description="Add a new item to the wishlist" />
            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" type="text" required autofocus placeholder="Enter wishlist item name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description (Optional)</Label>
                    <Textarea id="description" v-model="form.description" placeholder="Enter a description of the item" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="space-y-2">
                    <Label for="link">Link (Optional)</Label>
                    <Input id="link" v-model="form.link" type="url" placeholder="Enter a link to the item" />
                    <InputError :message="form.errors.link" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="cost_per_unit">Cost Per Unit</Label>
                        <Input id="cost_per_unit" v-model="form.cost_per_unit" type="number" step="0.01" min="0" required placeholder="Enter cost per unit" />
                        <InputError :message="form.errors.cost_per_unit" />
                    </div>

                    <div class="space-y-2">
                        <Label for="count">Count</Label>
                        <Input id="count" v-model="form.count" type="number" min="1" required placeholder="Enter number of units needed" />
                        <InputError :message="form.errors.count" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="deadline">Deadline (Optional)</Label>
                    <Input id="deadline" v-model="form.deadline" type="date" />
                    <p class="text-sm text-gray-500">Leave empty if there's no deadline</p>
                    <InputError :message="form.errors.deadline" />
                </div>

                <div class="space-y-4">
                    <Label>Image (Optional)</Label>

                    <!-- Upload new image -->
                    <div class="space-y-2">
                        <Label for="image" class="text-sm font-medium">Upload New Image</Label>
                        <Input id="image" type="file" @input="form.image = $event.target.files[0]; form.selected_image = ''" accept="image/*" />
                        <p class="text-sm text-gray-500">Upload an image for the wishlist item. Max size: 2MB.</p>
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

                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.wishlists.index')">
                        <Button type="button" variant="destructive"><BanIcon />Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing"><SaveIcon />Add Wishlist Item</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
