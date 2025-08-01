<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { PencilIcon, ArrowLeftIcon, ServerIcon, WifiIcon, CheckIcon, XIcon, RefreshCwIcon } from 'lucide-vue-next';

interface Game {
    id: number;
    name: string;
}

interface GameServer {
    id: number;
    name: string;
    ip_address: string;
    port: number;
    game_id: number;
    description: string | null;
    max_players: number;
    is_active: boolean;
    is_online: boolean | null;
    port_open: boolean | null;
    last_checked_at: string | null;
    created_at: string;
    updated_at: string;
    game: Game;
}

interface Props {
    gameServer: GameServer;
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Game Servers', href: route('admin.gameservers.index') },
    { title: props.gameServer.name, href: route('admin.gameservers.show', { gameserver: props.gameServer.id }) },
];

import { ref, reactive } from 'vue';
import axios from 'axios';
import { useToast } from '@/components/ui/sonner';

const { toast } = useToast();
const checkingStatus = ref(false);
const serverStatus = reactive({
    is_online: props.gameServer.is_online,
    port_open: props.gameServer.port_open,
    last_checked_at: props.gameServer.last_checked_at
});

const checkServerStatus = async () => {
    checkingStatus.value = true;

    try {
        const response = await axios.post(route('admin.gameservers.check-status', props.gameServer.id));

        if (response.data.success) {
            // Update the server status
            serverStatus.is_online = response.data.data.is_online;
            serverStatus.port_open = response.data.data.port_open;
            serverStatus.last_checked_at = response.data.data.last_checked_at;

            toast({
                title: 'Server Status',
                description: `Status check completed for ${props.gameServer.name}`,
            });
        }
    } catch (error) {
        console.error('Error checking server status:', error);
        toast({
            title: 'Error',
            description: 'Failed to check server status. Please try again.',
            variant: 'destructive',
        });
    } finally {
        checkingStatus.value = false;
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="'Game Server: ' + gameServer.name" />
        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall :title="gameServer.name" description="Game Server Details" />
                <div class="flex space-x-2">
                    <Link :href="route('admin.gameservers.edit', { gameserver: gameServer.id })">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="h-4 w-4" /> Edit Server
                        </Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Game Server Information</CardTitle>
                    <CardDescription>Detailed information about this game server</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center mb-6">
                        <ServerIcon class="h-12 w-12 text-primary mr-4" />
                        <div>
                            <h2 class="text-xl font-semibold">{{ gameServer.name }}</h2>
                            <p class="text-muted-foreground">{{ gameServer.ip_address }}:{{ gameServer.port }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Game</h3>
                            <p class="mt-1">{{ gameServer.game.name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">IP Address</h3>
                            <p class="mt-1">{{ gameServer.ip_address }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Port</h3>
                            <p class="mt-1">{{ gameServer.port }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Max Players</h3>
                            <p class="mt-1">{{ gameServer.max_players }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1" :class="gameServer.is_active ? 'text-green-600' : 'text-red-600'">
                                {{ gameServer.is_active ? 'Active' : 'Inactive' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                            <p class="mt-1">{{ new Date(gameServer.created_at).toLocaleString() }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                            <p class="mt-1">{{ new Date(gameServer.updated_at).toLocaleString() }}</p>
                        </div>
                    </div>

                    <!-- Server Online Status Section -->
                    <div class="mt-6 p-4 border rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium">Server Online Status</h3>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="checkServerStatus"
                                :disabled="checkingStatus"
                            >
                                <RefreshCwIcon class="h-4 w-4 mr-2" :class="{ 'animate-spin': checkingStatus }" />
                                Check Status
                            </Button>
                        </div>

                        <div class="flex flex-col space-y-4">
                            <div v-if="serverStatus.is_online === null" class="flex items-center text-gray-500">
                                <p>Server status has not been checked yet.</p>
                            </div>
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center">
                                    <div v-if="serverStatus.is_online" class="flex items-center text-green-600">
                                        <WifiIcon class="h-5 w-5 mr-2" />
                                        <span class="font-medium">Server is online</span>
                                    </div>
                                    <div v-else class="flex items-center text-red-600">
                                        <XIcon class="h-5 w-5 mr-2" />
                                        <span class="font-medium">Server is offline</span>
                                    </div>
                                </div>

                                <div v-if="serverStatus.is_online" class="flex items-center">
                                    <div v-if="serverStatus.port_open" class="flex items-center text-green-600">
                                        <CheckIcon class="h-5 w-5 mr-2" />
                                        <span class="font-medium">Port {{ gameServer.port }} is open</span>
                                    </div>
                                    <div v-else class="flex items-center text-red-600">
                                        <XIcon class="h-5 w-5 mr-2" />
                                        <span class="font-medium">Port {{ gameServer.port }} is closed</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="serverStatus.last_checked_at" class="text-sm text-gray-500">
                                Last checked: {{ new Date(serverStatus.last_checked_at).toLocaleString() }}
                            </div>
                        </div>
                    </div>

                    <div v-if="gameServer.description" class="mt-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                        <p class="mt-1 whitespace-pre-line">{{ gameServer.description }}</p>
                    </div>
                </CardContent>
                <CardFooter>
                    <div class="flex justify-end space-x-2">
                        <Link :href="route('admin.gameservers.index')">
                            <Button variant="outline"><ArrowLeftIcon /> Back to Game Servers</Button>
                        </Link>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
