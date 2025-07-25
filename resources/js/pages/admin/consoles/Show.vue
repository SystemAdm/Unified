<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { PencilIcon, ArrowLeftIcon, ExternalLinkIcon } from 'lucide-vue-next';

interface Console {
    id: number;
    name: string;
    cpu: string | null;
    gpu: string | null;
    psu: string | null;
    ram: string | null;
    hdd: string | null;
    ssd: string | null;
    description: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    console: Console;
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Consoles', href: route('admin.consoles.index') },
    { title: props.console.name, href: route('admin.consoles.show', { console: props.console.id }) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="'Console: ' + console.name" />
        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall :title="console.name" description="Console Details" />
                <div class="flex space-x-2">
                    <Link v-if="console.is_active" :href="`/consoles/${console.id}`">
                        <Button variant="outline" size="sm" class="gap-2">
                            <ExternalLinkIcon class="h-4 w-4" /> View Public Page
                        </Button>
                    </Link>
                    <Link :href="route('admin.consoles.edit', { console: console.id })">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="h-4 w-4" /> Edit Console
                        </Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Console Information</CardTitle>
                    <CardDescription>Detailed information about this console</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Name</h3>
                            <p class="mt-1">{{ console.name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1" :class="console.is_active ? 'text-green-600' : 'text-red-600'">
                                {{ console.is_active ? 'Active' : 'Inactive' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">CPU</h3>
                            <p class="mt-1">{{ console.cpu || 'Not specified' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">GPU</h3>
                            <p class="mt-1">{{ console.gpu || 'Not specified' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">PSU</h3>
                            <p class="mt-1">{{ console.psu || 'Not specified' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">RAM</h3>
                            <p class="mt-1">{{ console.ram || 'Not specified' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">HDD</h3>
                            <p class="mt-1">{{ console.hdd || 'Not specified' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">SSD</h3>
                            <p class="mt-1">{{ console.ssd || 'Not specified' }}</p>
                        </div>
                        <div class="col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">Description</h3>
                            <p class="mt-1">{{ console.description || 'No description provided' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                            <p class="mt-1">{{ new Date(console.created_at).toLocaleString() }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                            <p class="mt-1">{{ new Date(console.updated_at).toLocaleString() }}</p>
                        </div>
                    </div>
                </CardContent>
                <CardFooter>
                    <div class="flex justify-end space-x-2">
                        <Link :href="route('admin.consoles.index')">
                            <Button variant="outline"><ArrowLeftIcon /> Back to Consoles</Button>
                        </Link>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
