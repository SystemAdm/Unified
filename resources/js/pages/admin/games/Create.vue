<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Select, SelectItem,SelectTrigger,SelectValue,SelectGroup,SelectContent,SelectLabel } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { BanIcon,SaveIcon } from 'lucide-vue-next';

interface Equipment {
    name: string;
    value: string;
}

interface Props {
    types: Equipment[];
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Games', href: route('admin.games.index') },
    { title: 'Add', href: route('admin.games.create') },
];
const form = useForm({
    name: '',
    type: 0,
    version: '1.0',
    image: null,
})

const submit = () => {
    form.post(route('admin.games.store'), {
        onSuccess: () => {
            form.reset();
        }
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Add Game" />
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Add Game" description="Add a new game to the system" />
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
                    <Select id="equipment" :model-value="form.type" @update:model-value="form.type = (Number)($event??0)">
                        <SelectTrigger>
                            <SelectValue placeholder="Select equipment" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Equipment</SelectLabel>
                                <SelectItem v-for="(type,index) in props.types" :value="index" :key="index">{{type}}</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.type" />
                </div>
                <div class="space-y-2">
                    <Label for="image">Game Image</Label>
                    <Input id="image" type="file" @input="form.image = $event.target.files[0]" accept="image/*" />
                    <p class="text-sm text-gray-500">Upload an image for the game (optional). Max size: 2MB.</p>
                    <InputError :message="form.errors.image" />
                </div>
                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.games.index')">
                        <Button type="button" variant="destructive"><BanIcon />Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing"><SaveIcon />Add Game</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
