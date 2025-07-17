<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { CheckIcon } from 'lucide-vue-next';

interface MembershipTier {
    id: number;
    name: string;
    price: number;
    price_display: string;
    features: string[];
}

interface Props {
    membershipTiers: MembershipTier[];
    currentMembership: {
        tier_id: number;
        status: string;
        expires_at: string | null;
    } | null;
    status?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Membership', href: route('membership.edit') }];

const purchaseForm = useForm({
    tier_id: 0,
});

const cancelForm = useForm({});

const initiatePurchase = (tierId: number) => {
    purchaseForm.tier_id = tierId;
    purchaseForm.post(route('membership.purchase'), {
        preserveScroll: true,
    });
};

const cancelMembership = () => {
    if (
        confirm(
            'Are you sure you want to cancel your membership? You will lose access to membership benefits at the end of your current billing period.',
        )
    ) {
        cancelForm.delete(route('membership.cancel'), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Membership" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Membership" description="Manage your membership subscription" />

                <!-- Status Messages -->
                <div
                    v-if="props.status === 'payment-unavailable'"
                    class="mb-4 rounded-lg bg-yellow-100 p-4 text-sm text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100"
                >
                    Payment functionality is currently unavailable. Please contact support for assistance.
                </div>
                <div
                    v-if="props.status === 'payment-successful'"
                    class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-800 dark:bg-green-900 dark:text-green-100"
                >
                    Your membership has been successfully activated. Thank you for your support!
                </div>
                <div
                    v-if="props.status === 'membership-cancelled'"
                    class="mb-4 rounded-lg bg-yellow-100 p-4 text-sm text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100"
                >
                    Your membership has been cancelled. You will have access until the end of your current billing period.
                </div>

                <!-- Current Membership Info (if any) -->
                <div v-if="props.currentMembership" class="rounded-lg border bg-muted p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium">
                                Current Membership:
                                {{ props.membershipTiers.find((t) => t.id === props.currentMembership?.tier_id)?.name }}
                            </h3>
                            <p class="text-muted-foreground">
                                Status:
                                <span :class="props.currentMembership.status === 'active' ? 'text-green-600' : 'text-yellow-600'">
                                    {{ props.currentMembership.status.charAt(0).toUpperCase() + props.currentMembership.status.slice(1) }}
                                </span>
                            </p>
                            <p v-if="props.currentMembership.expires_at" class="text-muted-foreground">
                                Expires: {{ new Date(props.currentMembership.expires_at).toLocaleDateString() }}
                            </p>
                        </div>
                        <Button variant="destructive" @click="cancelMembership" :disabled="cancelForm.processing"> Cancel Membership </Button>
                    </div>
                </div>

                <!-- Membership Option -->
                <div class="mx-auto max-w-md">
                    <Card
                        v-for="tier in props.membershipTiers"
                        :key="tier.id"
                        :class="{ 'border-primary': props.currentMembership?.tier_id === tier.id }"
                    >
                        <CardHeader>
                            <CardTitle>{{ tier.name }}</CardTitle>
                            <CardDescription>{{ tier.price_display }}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-2">
                                <li v-for="(feature, index) in tier.features" :key="index" class="flex items-start">
                                    <CheckIcon class="mt-0.5 mr-2 h-5 w-5 text-green-500" />
                                    <span>{{ feature }}</span>
                                </li>
                            </ul>
                        </CardContent>
                        <CardFooter>
                            <Button
                                class="w-full"
                                @click="initiatePurchase(tier.id)"
                                :disabled="
                                    purchaseForm.processing ||
                                    (props.currentMembership?.tier_id === tier.id && props.currentMembership?.status === 'active')
                                "
                            >
                                <span v-if="props.currentMembership?.tier_id === tier.id && props.currentMembership?.status === 'active'">
                                    Current Membership
                                </span>
                                <span v-else-if="props.currentMembership?.tier_id === tier.id && props.currentMembership?.status !== 'active'">
                                    Renew Membership
                                </span>
                                <span v-else>Purchase Membership</span>
                            </Button>
                        </CardFooter>
                    </Card>
                </div>

                <!-- Membership Information -->
                <div class="mt-6 rounded-lg border bg-muted p-4">
                    <div class="flex items-start">
                        <div>
                            <h3 class="text-lg font-medium">Membership Information</h3>
                            <p class="text-muted-foreground">
                                Your membership costs 50 NOK per year and provides access to community events, game discounts, and more.
                                You can cancel at any time from this page.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
