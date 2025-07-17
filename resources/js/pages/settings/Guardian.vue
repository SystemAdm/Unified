<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { ShieldIcon, ShieldCheckIcon, PlusIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';

// Define props for the component
const props = defineProps<{
    guardians?: Array<{
        id: number;
        name: string;
        relation: string;
        isVerified: boolean;
        verifiedBy: number | null;
        verifiedAt: string | null;
    }>;
    guardedUsers?: Array<{
        id: number;
        name: string;
        relation: string;
        isVerified: boolean;
        verifiedBy: number | null;
        verifiedAt: string | null;
    }>;
    relationGuardedOptions?: Array<{
        value: string;
        label: string;
    }>;
    relationGuardianOptions?: Array<{
        value: string;
        label: string;
    }>;
    user?: {
        id: number;
        account_type: string;
        roles: string[];
    };
}>();

// Check if the user is a guest (either by account_type or specific user ID)
const isGuest = computed(() => {
    return props.user?.account_type === 'guest' || props.user?.id === 8;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Guardian settings',
        href: '/settings/guardian',
    },
];


// Form for adding a guardian
const guardianForm = useForm({
    name: '',
    email: '',
    relation_guarded: '',
    relation_guardian: '',
});

// Method to add a guardian
const addGuardian = () => {
    guardianForm.post(route('profile.guardians.add'), {
        onSuccess: () => {
            // Reset the form
            guardianForm.reset();
            // Show success message
            guardianForm.recentlySuccessful = true;
            // Reset the recently successful flag after a delay
            setTimeout(() => {
                guardianForm.recentlySuccessful = false;
            }, 2000);
        },
    });
};

// Form for adding a guarded user
const guardedUserForm = useForm({
    name: '',
    email: '',
    phone: '',
    birthday: '',
    relation_guarded: '',
    relation_guardian: '',
});

// Method to add a guarded user
const addGuardedUser = () => {
    guardedUserForm.post(route('profile.guarded-users.add'), {
        onSuccess: () => {
            // Reset the form
            guardedUserForm.reset();
            // Show success message
            guardedUserForm.recentlySuccessful = true;
            // Reset the recently successful flag after a delay
            setTimeout(() => {
                guardedUserForm.recentlySuccessful = false;
            }, 2000);
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Guardian settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Guardian Management" description="Manage your guardians and guarded users" />

                <!-- Current Guardians List -->
                <div class="mb-4 p-3 bg-card rounded-lg shadow-sm">
                    <div class="flex flex-col">
                        <div class="flex items-center mb-2">
                            <ShieldIcon class="h-6 w-6 text-primary mr-2" />
                            <span class="text-sm font-medium">Your Guardians:</span>
                        </div>
                        <div v-if="props.guardians && props.guardians.length > 0">
                            <ul class="ml-8 space-y-2">
                                <li v-for="guardian in props.guardians" :key="guardian.id" class="flex items-center">
                                    <ShieldCheckIcon v-if="guardian.isVerified" class="h-5 w-5 text-green-500 mr-2" />
                                    <ShieldIcon v-else class="h-5 w-5 text-gray-400 mr-2" />
                                    <span>{{ guardian.name }} ({{ guardian.relation }})</span>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="ml-8">
                            <span class="text-sm text-muted-foreground">No guardians registered</span>
                        </div>
                    </div>
                </div>

                <!-- Guarded Users List -->
                <div class="mb-4 p-3 bg-card rounded-lg shadow-sm">
                    <div class="flex flex-col">
                        <div class="flex items-center mb-2">
                            <ShieldIcon class="h-6 w-6 text-primary mr-2" />
                            <span class="text-sm font-medium">Users you are guarding:</span>
                        </div>
                        <div v-if="props.guardedUsers && props.guardedUsers.length > 0">
                            <ul class="ml-8 space-y-2">
                                <li v-for="user in props.guardedUsers" :key="user.id" class="flex items-center">
                                    <ShieldCheckIcon v-if="user.isVerified" class="h-5 w-5 text-green-500 mr-2" />
                                    <ShieldIcon v-else class="h-5 w-5 text-gray-400 mr-2" />
                                    <span>{{ user.name }} ({{ user.relation }})</span>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="ml-8">
                            <span class="text-sm text-muted-foreground">You are not guarding any users</span>
                        </div>
                    </div>
                </div>

                <!-- Add Guardian Button and Dialog -->
                <div class="flex justify-between items-center mb-4">
                    <HeadingSmall title="Add a Guardian" description="Add a new guardian to your account" />
                    <Dialog>
                        <DialogTrigger asChild>
                            <Button>
                                <PlusIcon class="h-4 w-4 mr-2" />
                                Add Guardian
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Add a Guardian</DialogTitle>
                                <DialogDescription>
                                    Add a new guardian to your account. Fill in the details below.
                                </DialogDescription>
                            </DialogHeader>

                            <form @submit.prevent="addGuardian" class="space-y-6">
                                <div class="grid gap-2">
                                    <Label for="guardian-name">Guardian Name</Label>
                                    <Input id="guardian-name" v-model="guardianForm.name" class="mt-1 block w-full" placeholder="Full name" />
                                    <InputError class="mt-2" :message="guardianForm.errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="guardian-email">Guardian Email</Label>
                                    <Input id="guardian-email" v-model="guardianForm.email" class="mt-1 block w-full" type="email" placeholder="Email address" />
                                    <InputError class="mt-2" :message="guardianForm.errors.email" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="relation-guarded">Your Relation to Guardian</Label>
                                    <Select v-model="guardianForm.relation_guarded">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select your relation to the guardian" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="option in props.relationGuardedOptions" :key="option.value" :value="option.value">
                                                {{ option.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-2" :message="guardianForm.errors.relation_guarded" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="relation-guardian">Guardian's Relation to You</Label>
                                    <Select v-model="guardianForm.relation_guardian">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select guardian's relation to you" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="option in props.relationGuardianOptions" :key="option.value" :value="option.value">
                                                {{ option.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-2" :message="guardianForm.errors.relation_guardian" />
                                </div>

                                <DialogFooter>
                                    <Button variant="outline" type="button" @click="$el.closest('dialog').close()">Cancel</Button>
                                    <Button type="submit" :disabled="guardianForm.processing">Add Guardian</Button>
                                </DialogFooter>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-show="guardianForm.recentlySuccessful" class="text-sm text-neutral-600">Guardian added.</p>
                                </Transition>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <!-- Add Guarded User Button and Dialog - Only visible to non-guest users -->
                <div v-if="!isGuest" class="flex justify-between items-center mb-4">
                    <HeadingSmall title="Add a Guarded User" description="Add a new user to guard" />
                    <Dialog>
                        <DialogTrigger asChild>
                            <Button>
                                <PlusIcon class="h-4 w-4 mr-2" />
                                Add Guarded User
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Add a Guarded User</DialogTitle>
                                <DialogDescription>
                                    Add a new user to guard. Fill in the details below.
                                </DialogDescription>
                            </DialogHeader>

                            <form @submit.prevent="addGuardedUser" class="space-y-6">
                                <div class="grid gap-2">
                                    <Label for="guarded-name">Name</Label>
                                    <Input id="guarded-name" v-model="guardedUserForm.name" class="mt-1 block w-full" placeholder="Full name" />
                                    <InputError class="mt-2" :message="guardedUserForm.errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="guarded-email">Email</Label>
                                    <Input id="guarded-email" v-model="guardedUserForm.email" class="mt-1 block w-full" type="email" placeholder="Email address" />
                                    <InputError class="mt-2" :message="guardedUserForm.errors.email" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="guarded-phone">Phone</Label>
                                    <Input id="guarded-phone" v-model="guardedUserForm.phone" class="mt-1 block w-full" placeholder="Phone number" />
                                    <InputError class="mt-2" :message="guardedUserForm.errors.phone" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="guarded-birthday">Birthday</Label>
                                    <Input id="guarded-birthday" v-model="guardedUserForm.birthday" class="mt-1 block w-full" type="date" />
                                    <InputError class="mt-2" :message="guardedUserForm.errors.birthday" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="guarded-relation-guarded">Their Relation to You</Label>
                                    <Select v-model="guardedUserForm.relation_guarded">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select their relation to you" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="option in props.relationGuardedOptions" :key="option.value" :value="option.value">
                                                {{ option.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-2" :message="guardedUserForm.errors.relation_guarded" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="guarded-relation-guardian">Your Relation to Them</Label>
                                    <Select v-model="guardedUserForm.relation_guardian">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select your relation to them" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="option in props.relationGuardianOptions" :key="option.value" :value="option.value">
                                                {{ option.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-2" :message="guardedUserForm.errors.relation_guardian" />
                                </div>

                                <DialogFooter>
                                    <Button variant="outline" type="button" @click="$el.closest('dialog').close()">Cancel</Button>
                                    <Button type="submit" :disabled="guardedUserForm.processing">Add Guarded User</Button>
                                </DialogFooter>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-show="guardedUserForm.recentlySuccessful" class="text-sm text-neutral-600">Guarded user added.</p>
                                </Transition>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
