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
import { BanIcon, SaveIcon } from 'lucide-vue-next';
import { Textarea } from '@/components/ui/textarea';

interface Game {
    id: number;
    name: string;
}

interface Props {
    games: Game[];
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Game Servers', href: route('admin.gameservers.index') },
    { title: 'Add', href: route('admin.gameservers.create') },
];

const form = useForm({
    name: '',
    ip_address: '',
    port: 25565, // Default port for Minecraft as an example
    game_id: '',
    description: '',
    max_players: 0,
});

const submit = () => {
    form.post(route('admin.gameservers.store'), {
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Add Game Server" />
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Add Game Server" description="Add a new game server to the system" />
            <form @submit.prevent="submit" class="max-w-xl space-y-6">
                <div class="space-y-2">
                    <Label for="name">Server Name</Label>
                    <Input id="name" v-model="form.name" type="text" required autofocus placeholder="Enter server name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="ip_address">IP Address</Label>
                    <Input id="ip_address" v-model="form.ip_address" type="text" required placeholder="Enter IP address" />
                    <InputError :message="form.errors.ip_address" />
                </div>

                <div class="space-y-2">
                    <Label for="port">Port</Label>
                    <Input id="port" v-model="form.port" type="number" required min="1" max="65535" placeholder="Enter port number" />
                    <InputError :message="form.errors.port" />
                </div>

                <div class="space-y-2">
                    <Label for="game">Game</Label>
                    <Select id="game" v-model="form.game_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Select game" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Games</SelectLabel>
                                <SelectItem v-for="game in props.games" :value="game.id" :key="game.id">{{ game.name }}</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.game_id" />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" placeholder="Enter server description" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="space-y-2">
                    <Label for="max_players">Max Players</Label>
                    <Input id="max_players" v-model="form.max_players" type="number" min="0" placeholder="Enter maximum number of players" />
                    <InputError :message="form.errors.max_players" />
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.gameservers.index')">
                        <Button type="button" variant="destructive"><BanIcon />Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing"><SaveIcon />Add Game Server</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
