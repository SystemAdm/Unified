<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { GiftIcon, ExternalLinkIcon } from 'lucide-vue-next';
import LaravelPaginator from '@/components/LaravelPaginator.vue';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';

interface WishlistItem {
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
}

interface Props {
    wishlists: {
        data: WishlistItem[];
        current_page: number;
        from: number;
        last_page: number;
        links: any[];
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Wishlist',
        href: '/wishlist',
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
        <Head title="Wishlist" />

        <div class="mb-10 flex items-center justify-between">
            <div class="flex items-center">
                <GiftIcon class="mr-3 h-8 w-8 text-muted-foreground" />
                <h2 class="text-3xl font-bold">Wishlist</h2>
            </div>
            <Link :href="route('admin.wishlist.index')" class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80">
                Admin
            </Link>
        </div>

        <!-- Wishlist items -->
        <div v-if="props.wishlists.data.length === 0" class="p-4 text-center text-gray-500">No wishlist items found.</div>
        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            <div v-for="item in props.wishlists.data" :key="item.id" class="overflow-hidden rounded-lg border bg-card text-card-foreground shadow">
                <div class="relative">
                    <img v-if="item.image" :src="item.image.startsWith('http') ? item.image : `/storage/${item.image}`" :alt="item.name" class="h-48 w-full object-cover" />
                    <div v-else class="flex h-48 w-full items-center justify-center bg-gray-200">
                        <GiftIcon class="h-16 w-16 text-gray-400" />
                    </div>
                    <div v-if="item.is_expired" class="absolute top-2 right-2 rounded-full bg-red-500 px-3 py-1 text-xs font-semibold text-white">
                        Expired
                    </div>
                    <div v-else-if="item.is_fully_funded" class="absolute top-2 right-2 rounded-full bg-green-500 px-3 py-1 text-xs font-semibold text-white">
                        Fully Funded
                    </div>
                    <div v-else-if="item.deadline" class="absolute top-2 right-2 rounded-full bg-blue-500 px-3 py-1 text-xs font-semibold text-white">
                        Deadline: {{ formatDate(item.deadline) }}
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="mb-2 text-xl font-bold">{{ item.name }}</h3>
                    <p v-if="item.description" class="mb-4 text-sm text-gray-600">{{ item.description }}</p>

                    <div class="mb-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Progress:</span>
                            <span>{{ calculateProgress(item.total_paid, item.total_cost) }}%</span>
                        </div>
                        <Progress :value="calculateProgress(item.total_paid, item.total_cost)" class="h-2" />
                        <div class="flex justify-between text-sm">
                            <span>{{ formatCurrency(item.total_paid) }} of {{ formatCurrency(item.total_cost) }}</span>
                            <span>{{ item.count }} units</span>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <Link :href="route('wishlist.show', item.id)">
                            <Button>View Details</Button>
                        </Link>
                        <a v-if="item.link" :href="item.link" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-blue-500 hover:underline">
                            <ExternalLinkIcon class="mr-1 h-4 w-4" />
                            Link
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <LaravelPaginator
            v-if="props.wishlists.data.length > 0"
            :pagination="props.wishlists"
            onlyKey="wishlists"
        />
    </AppLayout>
</template>
