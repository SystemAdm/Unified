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
import { Switch } from '@/components/ui/switch';
import { BanIcon,SaveIcon } from 'lucide-vue-next';

interface Equipment {
    name: string;
    value: string;
}

interface Game {
    id: number;
    name: string;
    version: string;
    console: string;
    image: string | null;
    is_active: boolean;
}

interface Props {
    game: Game;
    types: Equipment[];
    currentTypeIndex: number;
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Games', href: route('admin.games.index') },
    { title: props.game.name, href: route('admin.games.edit', { game: props.game.id }) },
];

const form = useForm({
    name: props.game.name,
    type: props.currentTypeIndex,
    version: props.game.version,
    is_active: props.game.is_active,
    image: null, // Will be set when a new image is selected
    _method: 'PUT', // For proper method spoofing with file uploads
});

const submit = () => {
    // Use post with _method: 'PUT' for file uploads
    form.post(route('admin.games.update', { game: props.game.id }), {
        onSuccess: () => {
            // Form is automatically reset on success
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit Game" />
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Edit Game" description="Update game information" />
            <form @submit.prevent="submit" class="max-w-xl space-y-6">
                <div class="space-y-2">
                    <Label for="name">Game Name</Label>
                    <Input id="name" v-model="form.name" type="text" required autofocus placeholder="Enter game name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="space-y-2">
                    <Label for="version">Version</Label>
                    <Input id="version" v-model="form.version" type="text" required placeholder="Enter game version" />
                    <InputError :message="form.errors.version" />
                </div>
                <div class="space-y-2">
                    <Label for="equipment">Equipment</Label>
                    <Select id="equipment" :model-value="form.type" @update:model-value="form.type = $event">
                        <SelectTrigger>
                            <SelectValue placeholder="Select equipment" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Equipment</SelectLabel>
                                <SelectItem v-for="(type, index) in props.types" :value="index" :key="index">{{ type }}</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.type" />
                </div>
                <div class="flex items-center space-x-2">
                    <Switch id="is_active" v-model="form.is_active" />
                    <Label for="is_active">Active</Label>
                    <InputError :message="form.errors.is_active" />
                </div>
                <div class="space-y-2">
                    <Label for="image">Game Image</Label>
                    <div v-if="game.image" class="mb-2">
                        <p class="text-sm text-gray-500 mb-1">Current image:</p>
                        <img :src="`/storage/${game.image}`" alt="Game image" class="max-w-xs max-h-48 rounded-md" />
                    </div>
                    <Input id="image" type="file" @input="form.image = $event.target.files[0]" accept="image/*" />
                    <p class="text-sm text-gray-500">Upload a new image for the game (optional). Max size: 2MB.</p>
                    <InputError :message="form.errors.image" />
                </div>
                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.games.index')">
                        <Button type="button" variant="destructive"><BanIcon />Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing"><SaveIcon />Update Game</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
