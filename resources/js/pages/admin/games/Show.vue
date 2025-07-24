<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { PencilIcon, ArrowLeftIcon, ExternalLinkIcon } from 'lucide-vue-next';

interface Game {
    id: number;
    name: string;
    version: string;
    console: string;
    image: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    game: Game;
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Games', href: route('admin.games.index') },
    { title: props.game.name, href: route('admin.games.show', { game: props.game.id }) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="'Game: ' + game.name" />
        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall :title="game.name" description="Game Details" />
                <div class="flex space-x-2">
                    <Link v-if="game.is_active" :href="`/games/${game.id}`">
                        <Button variant="outline" size="sm" class="gap-2">
                            <ExternalLinkIcon class="h-4 w-4" /> View Public Page
                        </Button>
                    </Link>
                    <Link :href="route('admin.games.edit', { game: game.id })">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="h-4 w-4" /> Edit game
                        </Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Game Information</CardTitle>
                    <CardDescription>Detailed information about this game</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="game.image" class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Game Image</h3>
                        <img :src="`/storage/${game.image}`" alt="Game image" class="max-w-md rounded-md shadow-md" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Name</h3>
                            <p class="mt-1">{{ game.name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Version</h3>
                            <p class="mt-1">{{ game.version }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Console</h3>
                            <p class="mt-1">{{ game.console }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1" :class="game.is_active ? 'text-green-600' : 'text-red-600'">
                                {{ game.is_active ? 'Active' : 'Inactive' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                            <p class="mt-1">{{ new Date(game.created_at).toLocaleString() }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                            <p class="mt-1">{{ new Date(game.updated_at).toLocaleString() }}</p>
                        </div>
                    </div>
                </CardContent>
                <CardFooter>
                    <div class="flex justify-end space-x-2">
                        <Link :href="route('admin.games.index')">
                            <Button variant="outline"><ArrowLeftIcon /> Back to Games</Button>
                        </Link>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
