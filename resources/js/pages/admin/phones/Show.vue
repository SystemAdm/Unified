<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { StarIcon, ShieldCheckIcon } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    pivot: {
        is_primary: boolean;
        verified_at: string | null;
    };
}

interface Phone {
    id: number;
    country_code: string;
    number: string;
    phone_number: string;
    users: User[];
}

interface Props {
    phone: Phone;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: route('admin.index'),
    },
    {
        title: 'Phones',
        href: route('admin.phones.index'),
    },
    {
        title: props.phone.phone_number,
        href: route('admin.phones.show', { phone: props.phone.id }),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Phone: ${props.phone.phone_number}`" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall :title="`Phone: ${props.phone.phone_number}`" description="View phone number details" />

            <div class="grid gap-6">
                <!-- Phone Details -->
                <div class="space-y-4 p-6 border rounded-lg">
                    <h3 class="text-lg font-medium">Phone Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Phone Number</p>
                            <p>{{ props.phone.phone_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Country Code</p>
                            <p>{{ props.phone.country_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">National Number</p>
                            <p>{{ props.phone.number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Users with this phone number -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Users with this phone number</h3>
                    <div class="overflow-x-auto">
                        <Table class="min-w-full">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        Name
                                    </TableHead>
                                    <TableHead>
                                        Status
                                    </TableHead>
                                    <TableHead class="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="user in props.phone.users" :key="user.id">
                                    <TableCell>
                                        {{ user.name }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center space-x-2">
                                            <div v-if="user.pivot.is_primary" class="flex items-center space-x-1">
                                                <StarIcon class="h-4 w-4 text-yellow-500" />
                                                <span class="text-xs text-yellow-500">Primary</span>
                                            </div>
                                            <div v-if="user.pivot.verified_at" class="flex items-center space-x-1">
                                                <ShieldCheckIcon class="h-4 w-4 text-green-500" />
                                                <span class="text-xs text-green-500">Verified</span>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Link :href="route('admin.users.edit', { user: user.id })">
                                            <Button variant="outline" size="sm">
                                                Edit User
                                            </Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.phone.users.length === 0">
                                    <TableCell colspan="3" class="text-center text-sm text-gray-500">
                                        No users have this phone number
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <Link :href="route('admin.phones.edit', { phone: props.phone.id })">
                    <Button>Edit Phone</Button>
                </Link>
                <Link :href="route('admin.phones.index')">
                    <Button variant="outline">Back to Phones</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
