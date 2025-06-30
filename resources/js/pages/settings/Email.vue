<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { StarIcon, ShieldCheckIcon, PencilIcon, TrashIcon, PlusIcon } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

interface Email {
    id: number;
    address: string;
    is_primary: boolean;
    is_verified: boolean;
    verified_at: string | null;
}

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    emails: Email[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Email settings',
        href: '/settings/email',
    },
];


const addEmailForm = useForm({
    address: '',
});

const editEmailForm = useForm({
    id: 0,
    address: '',
});

const addEmail = () => {
    addEmailForm.post(route('emails.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addEmailForm.reset();
        },
    });
};

const setPrimaryEmail = (emailId: number) => {
    useForm().patch(route('emails.primary', { email: emailId }), {
        preserveScroll: true,
    });
};

const verifyEmail = (emailId: number) => {
    useForm().post(route('emails.send-verification', { email: emailId }), {
        preserveScroll: true,
    });
};

const deleteEmail = (emailId: number) => {
    if (confirm('Are you sure you want to delete this email address?')) {
        useForm().delete(route('emails.destroy', { email: emailId }), {
            preserveScroll: true,
        });
    }
};

const startEditEmail = (email: Email) => {
    editEmailForm.id = email.id;
    editEmailForm.address = email.address;
};

const updateEmail = () => {
    editEmailForm.patch(route('emails.update', { email: editEmailForm.id }), {
        preserveScroll: true,
        onSuccess: () => {
            editEmailForm.reset();
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Email settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Email addresses" description="Manage your email addresses" />

                <!-- Email list -->
                <div class="space-y-4">
                    <div v-for="email in props.emails" :key="email.id" class="flex items-center justify-between p-3 border rounded-md">
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center space-x-1">
                                <span>{{ email.address }}</span>
                                <StarIcon v-if="email.is_primary" class="h-4 w-4 text-yellow-500" />
                                <ShieldCheckIcon v-if="email.is_verified" class="h-4 w-4 text-green-500" />
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Button
                                v-if="!email.is_primary"
                                variant="outline"
                                size="sm"
                                @click="setPrimaryEmail(email.id)"
                            >
                                Set as primary
                            </Button>
                            <Button
                                v-if="!email.is_verified"
                                variant="outline"
                                size="sm"
                                @click="verifyEmail(email.id)"
                            >
                                Verify
                            </Button>
                            <Dialog v-if="!email.is_primary">
                                <DialogTrigger as-child>
                                    <Button variant="ghost" size="icon" @click="startEditEmail(email)">
                                        <PencilIcon class="h-4 w-4" />
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Edit Email Address</DialogTitle>
                                        <DialogDescription>
                                            Update your email address. You will need to verify it again.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form @submit.prevent="updateEmail" class="space-y-4">
                                        <div class="grid gap-2">
                                            <Label for="edit-email">Email address</Label>
                                            <Input
                                                id="edit-email"
                                                type="email"
                                                v-model="editEmailForm.address"
                                                required
                                                placeholder="Email address"
                                            />
                                            <InputError :message="editEmailForm.errors.address" />
                                        </div>
                                        <DialogFooter>
                                            <Button type="submit" :disabled="editEmailForm.processing">Save</Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                            <Button
                                v-if="!email.is_primary"
                                variant="ghost"
                                size="icon"
                                @click="deleteEmail(email.id)"
                            >
                                <TrashIcon class="h-4 w-4 text-red-500" />
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Add email form -->
                <form @submit.prevent="addEmail" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-email">Add new email address</Label>
                        <div class="flex space-x-2">
                            <Input
                                id="new-email"
                                type="email"
                                v-model="addEmailForm.address"
                                required
                                placeholder="New email address"
                                class="flex-1"
                            />
                            <Button type="submit" :disabled="addEmailForm.processing">
                                <PlusIcon class="h-4 w-4 mr-2" />
                                Add
                            </Button>
                        </div>
                        <InputError :message="addEmailForm.errors.address" />
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
