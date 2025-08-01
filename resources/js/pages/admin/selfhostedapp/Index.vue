<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import LaravelPaginator from '@/components/LaravelPaginator.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon, PlusIcon, TrashIcon, BanIcon, LinkIcon, UserIcon } from 'lucide-vue-next';
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
import { Badge } from '@/components/ui/badge';

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
    apps: {
        data: SelfHostedApp[];
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
    { title: 'Self-Hosted Apps', href: route('admin.selfhostedapps.index') },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Self-Hosted Apps" />

        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall title="Self-Hosted Apps" description="Manage self-hosted applications in the system" />
                <div class="flex space-x-2">
                    <Link :href="route('admin.selfhostedapps.create')">
                        <Button>
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add Self-Hosted App
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <Table class="min-w-full">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Image</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Visibility</TableHead>
                            <TableHead>Managers</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="app in apps.data" :key="app.id">
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <img v-if="app.image" :src="app.image.startsWith('http') ? app.image : `/storage/${app.image}`" alt="App image" class="w-16 h-16 object-cover rounded-md" />
                                <div v-else class="w-16 h-16 bg-gray-200 rounded-md flex items-center justify-center">
                                    <span class="text-gray-500 text-xs">No image</span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div>
                                    <div class="font-medium">{{ app.name }}</div>
                                    <div class="text-sm text-gray-500 truncate max-w-xs">{{ app.description || 'No description' }}</div>
                                    <div v-if="app.public_link" class="text-sm text-blue-500 flex items-center mt-1">
                                        <LinkIcon class="h-3 w-3 mr-1" />
                                        <a :href="app.public_link" target="_blank" class="truncate max-w-xs">{{ app.public_link }}</a>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <Badge :variant="app.status === 'published' ? 'success' : 'secondary'">
                                    {{ app.status === 'published' ? 'Published' : 'Draft' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <Badge :variant="app.visibility === 'user' ? 'default' : 'outline'">
                                    {{ app.visibility === 'user' ? 'All Users' : 'Admin Only' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <Badge v-for="manager in app.managers" :key="manager.id" variant="outline" class="flex items-center">
                                        <UserIcon class="h-3 w-3 mr-1" />
                                        {{ manager.name }}
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.selfhostedapps.show', app.id)">
                                        <Button variant="secondary" size="sm"><EyeIcon class="h-4 w-4" /></Button>
                                    </Link>
                                    <Link :href="route('admin.selfhostedapps.edit', app.id)">
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
                                                <AlertDialogTitle>Are you sure you want to delete this app?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the self-hosted app from the system.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel><BanIcon />Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="router.delete(route('admin.selfhostedapps.destroy', app.id))">
                                                    <TrashIcon />Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="apps.data.length === 0">
                            <TableCell colspan="6" class="py-4 text-center">No self-hosted apps found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Pagination -->
        <LaravelPaginator
            v-if="apps.data.length > 0"
            :pagination="apps"
            onlyKey="apps"
        />
    </AppLayout>
</template>
