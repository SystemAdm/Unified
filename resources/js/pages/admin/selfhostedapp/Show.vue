<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { PencilIcon, ArrowLeftIcon, ExternalLinkIcon, UserIcon } from 'lucide-vue-next';

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
    app: SelfHostedApp;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Self-Hosted Apps', href: route('admin.selfhostedapps.index') },
    { title: props.app.name, href: route('admin.selfhostedapps.show', { selfhostedapp: props.app.id }) },
];

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadgeVariant = () => {
    return props.app.status === 'published' ? 'success' : 'secondary';
};

const getVisibilityBadgeVariant = () => {
    return props.app.visibility === 'user' ? 'default' : 'outline';
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="'Self-Hosted App: ' + app.name" />
        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall :title="app.name" description="Self-Hosted App Details" />
                <div class="flex space-x-2">
                    <Link :href="route('admin.selfhostedapps.edit', { selfhostedapp: app.id })">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="h-4 w-4" /> Edit app
                        </Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Self-Hosted App Information</CardTitle>
                    <CardDescription>Detailed information about this self-hosted application</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="app.image" class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Image</h3>
                        <img :src="app.image.startsWith('http') ? app.image : `/storage/${app.image}`" :alt="app.name" class="max-w-md rounded-md shadow-md" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">Name</h3>
                            <p class="mt-1 text-lg font-semibold">{{ app.name }}</p>
                        </div>

                        <div class="md:col-span-2" v-if="app.description">
                            <h3 class="text-sm font-medium text-gray-500">Description</h3>
                            <p class="mt-1 text-gray-700 whitespace-pre-wrap">{{ app.description }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <div class="mt-1">
                                <Badge :variant="getStatusBadgeVariant()">
                                    {{ app.status === 'published' ? 'Published' : 'Draft' }}
                                </Badge>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Visibility</h3>
                            <div class="mt-1">
                                <Badge :variant="getVisibilityBadgeVariant()">
                                    {{ app.visibility === 'user' ? 'All Users' : 'Admin Only' }}
                                </Badge>
                            </div>
                        </div>

                        <div v-if="app.public_link">
                            <h3 class="text-sm font-medium text-gray-500">Public Link</h3>
                            <a :href="app.public_link" target="_blank" class="mt-1 text-blue-600 hover:underline flex items-center">
                                <span class="truncate">{{ app.public_link }}</span>
                                <ExternalLinkIcon class="h-4 w-4 ml-1" />
                            </a>
                        </div>

                        <div v-if="app.admin_link">
                            <h3 class="text-sm font-medium text-gray-500">Admin Link</h3>
                            <a :href="app.admin_link" target="_blank" class="mt-1 text-blue-600 hover:underline flex items-center">
                                <span class="truncate">{{ app.admin_link }}</span>
                                <ExternalLinkIcon class="h-4 w-4 ml-1" />
                            </a>
                        </div>

                        <div v-if="app.demo_username">
                            <h3 class="text-sm font-medium text-gray-500">Demo Username</h3>
                            <p class="mt-1">{{ app.demo_username }}</p>
                        </div>

                        <div v-if="app.demo_password">
                            <h3 class="text-sm font-medium text-gray-500">Demo Password</h3>
                            <p class="mt-1">{{ app.demo_password }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                            <p class="mt-1">{{ formatDate(app.created_at) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                            <p class="mt-1">{{ formatDate(app.updated_at) }}</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">App Managers</h3>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="manager in app.managers" :key="manager.id" variant="outline" class="flex items-center">
                                <UserIcon class="h-4 w-4 mr-1" />
                                {{ manager.name }} ({{ manager.email }})
                            </Badge>
                        </div>
                        <p v-if="app.managers.length === 0" class="text-sm text-gray-500 italic mt-2">
                            No managers assigned to this app.
                        </p>
                    </div>
                </CardContent>
                <CardFooter>
                    <div class="flex justify-end space-x-2">
                        <Link :href="route('admin.selfhostedapps.index')">
                            <Button variant="outline"><ArrowLeftIcon /> Back to Self-Hosted Apps</Button>
                        </Link>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
