<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { PencilIcon, ArrowLeftIcon, ExternalLinkIcon } from 'lucide-vue-next';

interface WishlistPayment {
    id: number;
    user_id: number;
    count: number;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        name: string;
    };
}

interface Wishlist {
    id: number;
    name: string;
    description: string | null;
    image: string | null;
    link: string | null;
    cost_per_unit: number;
    count: number;
    deadline: string | null;
    created_at: string;
    updated_at: string;
    payments?: WishlistPayment[];
}

interface Props {
    wishlist: Wishlist;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Wishlist', href: route('admin.wishlist.index') },
    { title: props.wishlist.name, href: route('admin.wishlist.show', { wishlist: props.wishlist.id }) },
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

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const isExpired = () => {
    if (!props.wishlist.deadline) return false;
    return new Date(props.wishlist.deadline) < new Date();
};

const totalCost = () => {
    return props.wishlist.cost_per_unit * props.wishlist.count;
};

const totalPaid = () => {
    if (!props.wishlist.payments || props.wishlist.payments.length === 0) return 0;
    return props.wishlist.payments.reduce((sum, payment) => sum + (payment.count * props.wishlist.cost_per_unit), 0);
};

const remainingAmount = () => {
    return Math.max(0, totalCost() - totalPaid());
};

const isFullyFunded = () => {
    return remainingAmount() <= 0;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="'Wishlist: ' + wishlist.name" />
        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall :title="wishlist.name" description="Wishlist Item Details" />
                <div class="flex space-x-2">
                    <Link :href="route('wishlist.show', { wishlist: wishlist.id })" v-if="route().has('wishlist.show')">
                        <Button variant="secondary" size="sm">
                            Public View
                        </Button>
                    </Link>
                    <Link :href="route('admin.wishlist.edit', { wishlist: wishlist.id })">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="h-4 w-4" /> Edit item
                        </Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Wishlist Item Information</CardTitle>
                    <CardDescription>Detailed information about this wishlist item</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="wishlist.image" class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Image</h3>
                        <img :src="wishlist.image.startsWith('http') ? wishlist.image : `/storage/${wishlist.image}`" :alt="wishlist.name" class="max-w-md rounded-md shadow-md" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">Name</h3>
                            <p class="mt-1 text-lg font-semibold">{{ wishlist.name }}</p>
                        </div>

                        <div class="md:col-span-2" v-if="wishlist.description">
                            <h3 class="text-sm font-medium text-gray-500">Description</h3>
                            <p class="mt-1 text-gray-700 whitespace-pre-wrap">{{ wishlist.description }}</p>
                        </div>

                        <div v-if="wishlist.link">
                            <h3 class="text-sm font-medium text-gray-500">Link</h3>
                            <a :href="wishlist.link" target="_blank" class="mt-1 text-blue-600 hover:underline flex items-center">
                                <span class="truncate">{{ wishlist.link }}</span>
                                <ExternalLinkIcon class="h-4 w-4 ml-1" />
                            </a>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Cost Per Unit</h3>
                            <p class="mt-1">{{ formatCurrency(wishlist.cost_per_unit) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Count</h3>
                            <p class="mt-1">{{ wishlist.count }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Cost</h3>
                            <p class="mt-1">{{ formatCurrency(totalCost()) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Deadline</h3>
                            <p class="mt-1" :class="isExpired() ? 'text-red-600' : 'text-green-600'">
                                {{ formatDate(wishlist.deadline) }}
                                <span v-if="isExpired()" class="text-red-600 ml-1">(Expired)</span>
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Funding Status</h3>
                            <p class="mt-1" :class="isFullyFunded() ? 'text-green-600' : 'text-amber-600'">
                                {{ isFullyFunded() ? 'Fully Funded' : 'Partially Funded' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Paid</h3>
                            <p class="mt-1">{{ formatCurrency(totalPaid()) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Remaining Amount</h3>
                            <p class="mt-1">{{ formatCurrency(remainingAmount()) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                            <p class="mt-1">{{ formatDate(wishlist.created_at) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                            <p class="mt-1">{{ formatDate(wishlist.updated_at) }}</p>
                        </div>
                    </div>

                    <div class="mt-8" v-if="wishlist.payments && wishlist.payments.length > 0">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Payments</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="payment in wishlist.payments" :key="payment.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ payment.user ? payment.user.name : 'Unknown User' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ payment.count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatCurrency(payment.count * wishlist.cost_per_unit) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(payment.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </CardContent>
                <CardFooter>
                    <div class="flex justify-end space-x-2">
                        <Link :href="route('wishlist.index')" v-if="route().has('wishlist.index')">
                            <Button variant="secondary">Public Index</Button>
                        </Link>
                        <Link :href="route('admin.wishlist.index')">
                            <Button variant="outline"><ArrowLeftIcon /> Back to Wishlist</Button>
                        </Link>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
