<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { GiftIcon, ExternalLinkIcon, CalendarIcon, DollarSignIcon, UsersIcon, ArrowLeftIcon } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface User {
    id: number;
    name: string;
}

interface WishlistPayment {
    id: number;
    user_id: number;
    wishlist_id: number;
    count: number;
    payment_timestamp: string;
    created_at: string;
    updated_at: string;
    user: User;
}

interface Wishlist {
    id: number;
    name: string;
    image: string | null;
    description: string | null;
    link: string | null;
    cost_per_unit: number;
    count: number;
    deadline: string | null;
    created_at: string;
    updated_at: string;
    total_cost: number;
    total_paid: number;
    remaining_amount: number;
    is_expired: boolean;
    is_fully_funded: boolean;
    payments: WishlistPayment[];
}

interface Props {
    wishlist: Wishlist;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Wishlist',
        href: route('wishlist.index'),
    },
    {
        title: props.wishlist.name,
        href: route('wishlist.show', props.wishlist.id),
    },
];

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'No deadline';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const calculateProgress = (paid: number, total: number) => {
    if (total <= 0) return 100;
    return Math.min(100, Math.round((paid / total) * 100));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="wishlist.name" />

        <div class="mb-6 flex items-center">
            <Link :href="route('wishlist.index')" class="mr-4 inline-flex items-center text-blue-500 hover:underline">
                <ArrowLeftIcon class="mr-1 h-4 w-4" />
                Back to Wishlist
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Left column: Image and details -->
            <div class="lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-2xl">{{ wishlist.name }}</CardTitle>
                        <CardDescription v-if="wishlist.description">{{ wishlist.description }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="mb-6">
                            <div v-if="wishlist.image" class="mb-6 overflow-hidden rounded-lg">
                                <img :src="wishlist.image.startsWith('http') ? wishlist.image : `/storage/${wishlist.image}`" :alt="wishlist.name" class="w-full" />
                            </div>
                            <div v-else class="mb-6 flex h-64 items-center justify-center rounded-lg bg-gray-200">
                                <GiftIcon class="h-24 w-24 text-gray-400" />
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="flex items-center">
                                    <DollarSignIcon class="mr-2 h-5 w-5 text-gray-500" />
                                    <div>
                                        <div class="text-sm text-gray-500">Cost per unit</div>
                                        <div class="font-medium">{{ formatCurrency(wishlist.cost_per_unit) }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <UsersIcon class="mr-2 h-5 w-5 text-gray-500" />
                                    <div>
                                        <div class="text-sm text-gray-500">Units needed</div>
                                        <div class="font-medium">{{ wishlist.count }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <CalendarIcon class="mr-2 h-5 w-5 text-gray-500" />
                                    <div>
                                        <div class="text-sm text-gray-500">Deadline</div>
                                        <div class="font-medium">{{ formatDate(wishlist.deadline) }}</div>
                                    </div>
                                </div>
                                <div v-if="wishlist.link" class="flex items-center">
                                    <ExternalLinkIcon class="mr-2 h-5 w-5 text-gray-500" />
                                    <div>
                                        <div class="text-sm text-gray-500">External Link</div>
                                        <a :href="wishlist.link" target="_blank" rel="noopener noreferrer" class="font-medium text-blue-500 hover:underline">
                                            Visit Link
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Right column: Funding status -->
            <div>
                <Card>
                    <CardHeader>
                        <CardTitle>Funding Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="mb-6 space-y-4">
                            <div v-if="wishlist.is_expired" class="rounded-md bg-red-100 p-3 text-red-800">
                                <div class="font-semibold">Deadline Expired</div>
                                <div class="text-sm">This item's deadline has passed.</div>
                            </div>
                            <div v-else-if="wishlist.is_fully_funded" class="rounded-md bg-green-100 p-3 text-green-800">
                                <div class="font-semibold">Fully Funded!</div>
                                <div class="text-sm">This item has been fully funded.</div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Progress:</span>
                                    <span>{{ calculateProgress(wishlist.total_paid, wishlist.total_cost) }}%</span>
                                </div>
                                <Progress :value="calculateProgress(wishlist.total_paid, wishlist.total_cost)" class="h-3" />
                                <div class="flex justify-between text-sm">
                                    <span>{{ formatCurrency(wishlist.total_paid) }} raised</span>
                                    <span>of {{ formatCurrency(wishlist.total_cost) }}</span>
                                </div>
                            </div>

                            <div class="rounded-md bg-gray-100 p-3">
                                <div class="font-semibold">Remaining</div>
                                <div class="text-xl font-bold">{{ formatCurrency(wishlist.remaining_amount) }}</div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button class="w-full" :disabled="wishlist.is_fully_funded || wishlist.is_expired">
                            Contribute to this item
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>

        <!-- Contributions section -->
        <Card class="mt-8">
            <CardHeader>
                <CardTitle>Contributions</CardTitle>
                <CardDescription>People who have contributed to this item</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="wishlist.payments.length === 0" class="p-4 text-center text-gray-500">
                    No contributions yet. Be the first to contribute!
                </div>
                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Contributor</TableHead>
                            <TableHead>Units</TableHead>
                            <TableHead>Amount</TableHead>
                            <TableHead>Date</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="payment in wishlist.payments" :key="payment.id">
                            <TableCell>{{ payment.user.name }}</TableCell>
                            <TableCell>{{ payment.count }}</TableCell>
                            <TableCell>{{ formatCurrency(payment.count * wishlist.cost_per_unit) }}</TableCell>
                            <TableCell>{{ formatDateTime(payment.payment_timestamp) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </AppLayout>
</template>
