<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { PlusIcon, TrashIcon, StarIcon, ShieldCheckIcon } from 'lucide-vue-next';
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

interface User {
    id: number;
    name: string;
    pivot?: {
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
    users: User[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Phones',
        href: '/admin/phones',
    },
    {
        title: 'Edit Phone',
        href: `/admin/phones/${props.phone?.id || 0}/edit`,
    },
];

const form = useForm({
    phone_number: props.phone?.phone_number || '',
});

const attachUserForm = useForm({
    user_id: '',
    is_primary: false,
    is_verified: false,
});

const submit = () => {
    form.put(route('admin.phones.update', { phone: props.phone.id }), {
        preserveScroll: true,
    });
};

const attachUser = () => {
    attachUserForm.post(route('admin.phones.attach-user', { phone: props.phone.id }), {
        preserveScroll: true,
        onSuccess: () => {
            attachUserForm.reset();
        },
    });
};

const detachUser = (userId: number) => {
    useForm({
        user_id: userId,
    }).delete(route('admin.phones.detach-user', { phone: props.phone.id }), {
        preserveScroll: true,
    });
};

const setPrimary = (userId: number) => {
    useForm({
        user_id: userId,
    }).patch(route('admin.phones.set-primary', { phone: props.phone.id }), {
        preserveScroll: true,
    });
};

const setVerified = (userId: number) => {
    useForm({
        user_id: userId,
    }).patch(route('admin.phones.set-verified', { phone: props.phone.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit Phone" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Edit Phone" :description="`Update phone number: ${props.phone?.phone_number || 'Phone'}`" />

            <!-- Edit Phone Form -->
            <form @submit.prevent="submit" class="space-y-6 p-6 border rounded-lg">
                <div class="grid gap-4">
                    <!-- Phone Number -->
                    <div class="grid gap-2">
                        <Label for="phone_number">Phone Number</Label>
                        <Input
                            id="phone_number"
                            v-model="form.phone_number"
                            type="tel"
                            required
                            autofocus
                            autocomplete="tel"
                            placeholder="+1 123 456 7890"
                        />
                        <InputError :message="form.errors.phone_number" />
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.phones.index')">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">Update Phone</Button>
                </div>
            </form>

            <!-- Associated Users -->
            <div class="space-y-4 p-6 border rounded-lg">
                <h3 class="text-lg font-medium">Associated Users</h3>

                <div v-if="props.phone.users.length === 0" class="text-sm text-gray-500">
                    No users associated with this phone number.
                </div>

                <div v-else class="space-y-2">
                    <div v-for="user in props.phone.users" :key="user.id" class="flex items-center justify-between p-3 border rounded-md">
                        <div class="flex items-center space-x-2">
                            <span>{{ user.name || `User #${user.id}` }}</span>
                            <StarIcon v-if="user.pivot?.is_primary" class="h-4 w-4 text-yellow-500" title="Primary" />
                            <ShieldCheckIcon v-if="user.pivot?.verified_at" class="h-4 w-4 text-green-500" title="Verified" />
                        </div>
                        <div class="flex items-center space-x-2">
                            <Button
                                v-if="!user.pivot?.is_primary"
                                variant="outline"
                                size="sm"
                                @click="setPrimary(user.id)"
                            >
                                Set as primary
                            </Button>
                            <Button
                                v-if="!user.pivot?.verified_at"
                                variant="outline"
                                size="sm"
                                @click="setVerified(user.id)"
                            >
                                Mark as verified
                            </Button>
                            <AlertDialog>
                                <AlertDialogTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                    >
                                        <TrashIcon class="h-4 w-4 text-red-500" />
                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            This action cannot be undone. This will detach the user from this phone number.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                        <AlertDialogAction @click="detachUser(user.id)">
                                            Detach
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>
                </div>

                <!-- Attach User Form -->
                <form @submit.prevent="attachUser" class="space-y-4 mt-6">
                    <h4 class="text-md font-medium">Attach User</h4>

                    <div class="grid gap-4">
                        <!-- User -->
                        <div class="grid gap-2">
                            <Label for="user_id">User</Label>
                            <Select v-model="attachUserForm.user_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a user" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="user in props.users" :key="user.id" :value="user.id.toString()">
                                        {{ user.name || `User #${user.id}` }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="attachUserForm.errors.user_id" />
                        </div>

                        <!-- Is Primary -->
                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_primary" v-model="attachUserForm.is_primary" />
                            <Label for="is_primary">Set as primary phone number for this user</Label>
                            <InputError :message="attachUserForm.errors.is_primary" />
                        </div>

                        <!-- Is Verified -->
                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_verified" v-model="attachUserForm.is_verified" />
                            <Label for="is_verified">Mark as verified</Label>
                            <InputError :message="attachUserForm.errors.is_verified" />
                        </div>
                    </div>

                    <Button type="submit" :disabled="attachUserForm.processing">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Attach User
                    </Button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
