<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon, PlusIcon, TrashIcon, BanIcon, ServerIcon, WifiIcon, CheckIcon, XIcon, RefreshCwIcon } from 'lucide-vue-next';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

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
    game: Game;
}

interface Props {
    gameServers: {
        data: GameServer[];
        links: {
            label: string;
            url: string | null;
            active: boolean;
            first: string;
            last: string;
            prev: string | null;
            next: string | null;
        };
        current_page: number;
        from: number;
        last_page: number;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Game Servers', href: route('admin.gameservers.index') },
];

import { ref } from 'vue';
import axios from 'axios';
import { useToast } from '@/components/ui/sonner';

const { toast } = useToast();
const checkingStatus = ref<number | null>(null);

const checkServerStatus = async (serverId: number) => {
    checkingStatus.value = serverId;

    try {
        const response = await axios.post(route('admin.gameservers.check-status', serverId));

        if (response.data.success) {
            // Update the server status in the table
            const serverIndex = props.gameServers.data.findIndex(server => server.id === serverId);
            if (serverIndex !== -1) {
                props.gameServers.data[serverIndex].is_online = response.data.data.is_online;
                props.gameServers.data[serverIndex].port_open = response.data.data.port_open;
                props.gameServers.data[serverIndex].last_checked_at = response.data.data.last_checked_at;
            }

            toast({
                title: 'Server Status',
                description: `Status check completed for ${props.gameServers.data[serverIndex].name}`,
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
        checkingStatus.value = null;
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Game Servers" />

        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall title="Game Servers" description="Manage game servers in the system" />
                <div class="flex space-x-2">
                    <Link :href="route('admin.gameservers.create')">
                        <Button>
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add Game Server
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Game</TableHead>
                            <TableHead>IP Address</TableHead>
                            <TableHead>Port</TableHead>
                            <TableHead>Max Players</TableHead>
                            <TableHead>Active</TableHead>
                            <TableHead>Online Status</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="server in gameServers.data" :key="server.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <ServerIcon class="h-5 w-5 mr-2 text-primary" />
                                    {{ server.name }}
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ server.game.name }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ server.ip_address }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ server.port }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">{{ server.max_players }}</TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <span :class="server.is_active ? 'text-green-600' : 'text-red-600'">
                                    {{ server.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <div v-if="server.is_online === null" class="text-gray-500">
                                        Not checked
                                    </div>
                                    <div v-else-if="server.is_online" class="flex items-center text-green-600">
                                        <WifiIcon class="h-4 w-4 mr-1" />
                                        <span>Online</span>
                                        <span v-if="server.port_open" class="ml-2 flex items-center text-green-600">
                                            <CheckIcon class="h-4 w-4 mr-1" />
                                            Port Open
                                        </span>
                                        <span v-else-if="server.port_open === false" class="ml-2 flex items-center text-red-600">
                                            <XIcon class="h-4 w-4 mr-1" />
                                            Port Closed
                                        </span>
                                    </div>
                                    <div v-else class="flex items-center text-red-600">
                                        <XIcon class="h-4 w-4 mr-1" />
                                        <span>Offline</span>
                                    </div>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="checkServerStatus(server.id)"
                                        :disabled="checkingStatus === server.id"
                                    >
                                        <RefreshCwIcon class="h-4 w-4" :class="{ 'animate-spin': checkingStatus === server.id }" />
                                    </Button>
                                    <div v-if="server.last_checked_at" class="text-xs text-gray-500">
                                        Last checked: {{ new Date(server.last_checked_at).toLocaleString() }}
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.gameservers.show', server.id)">
                                        <Button variant="secondary" size="sm"><EyeIcon class="h-4 w-4" /></Button>
                                    </Link>
                                    <Link :href="route('admin.gameservers.edit', server.id)">
                                        <Button variant="outline" size="sm"><PencilIcon class="h-4 w-4" /></Button>
                                    </Link>
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                            >
                                                <TrashIcon class="h-4 w-4" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Are you sure you want to delete this game server?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the game server from the system.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel><BanIcon />Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="router.delete(route('admin.gameservers.destroy', server.id))">
                                                    <TrashIcon />Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="gameServers.data.length === 0">
                            <TableCell colspan="7" class="py-4 text-center">No game servers found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination :items-per-page="gameServers.per_page" :total="gameServers.total" :default-page="gameServers.from">
                <PaginationContent>
                    <a
                        v-if="gameServers.links.prev"
                        href="#"
                        @click.prevent="router.visit(gameServers.links.prev, { preserveState: true, preserveScroll: true, only: ['gameServers'] })"
                    >
                        <PaginationPrevious />
                    </a>

                    <template v-for="(link, index) in gameServers.links" :key="index">
                        <!-- Skip previous and next links as they're handled separately -->
                        <template v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'">
                            <a
                                v-if="!isNaN(parseInt(link.label)) && link.url"
                                href="#"
                                @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true, only: ['gameServers'] })"
                            >
                                <PaginationItem :value="parseInt(link.label)" :is-active="link.active">
                                    {{ link.label }}
                                </PaginationItem>
                            </a>
                            <PaginationItem v-else-if="!isNaN(parseInt(link.label))" :value="parseInt(link.label)" :is-active="link.active">
                                {{ link.label }}
                            </PaginationItem>
                            <PaginationEllipsis v-else-if="link.label === '...'" />
                        </template>
                    </template>

                    <a
                        v-if="gameServers.links.next"
                        href="#"
                        @click.prevent="router.visit(gameServers.links.next, { preserveState: true, preserveScroll: true, only: ['gameServers'] })"
                    >
                        <PaginationNext />
                    </a>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
