<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { BanIcon, SaveIcon } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Consoles', href: route('admin.consoles.index') },
    { title: 'Add', href: route('admin.consoles.create') },
];

const form = useForm({
    name: '',
    cpu: '',
    gpu: '',
    psu: '',
    ram: '',
    hdd: '',
    ssd: '',
    description: '',
})

const submit = () => {
    form.post(route('admin.consoles.store'), {
        onSuccess: () => {
            form.reset();
        }
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Add Console" />
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Add Console" description="Add a new console to the system" />
            <form @submit.prevent="submit" class="max-w-xl space-y-6">
                <div class="space-y-2">
                    <Label for="name">Console Name</Label>
                    <Input id="name" v-model="form.name" type="text" required autofocus placeholder="Enter console name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="space-y-2">
                    <Label for="cpu">CPU</Label>
                    <Input id="cpu" v-model="form.cpu" type="text" placeholder="Enter CPU specifications" />
                    <InputError :message="form.errors.cpu" />
                </div>
                <div class="space-y-2">
                    <Label for="gpu">GPU</Label>
                    <Input id="gpu" v-model="form.gpu" type="text" placeholder="Enter GPU specifications" />
                    <InputError :message="form.errors.gpu" />
                </div>
                <div class="space-y-2">
                    <Label for="psu">PSU</Label>
                    <Input id="psu" v-model="form.psu" type="text" placeholder="Enter PSU specifications" />
                    <InputError :message="form.errors.psu" />
                </div>
                <div class="space-y-2">
                    <Label for="ram">RAM</Label>
                    <Input id="ram" v-model="form.ram" type="text" placeholder="Enter RAM specifications" />
                    <InputError :message="form.errors.ram" />
                </div>
                <div class="space-y-2">
                    <Label for="hdd">HDD</Label>
                    <Input id="hdd" v-model="form.hdd" type="text" placeholder="Enter HDD specifications" />
                    <InputError :message="form.errors.hdd" />
                </div>
                <div class="space-y-2">
                    <Label for="ssd">SSD</Label>
                    <Input id="ssd" v-model="form.ssd" type="text" placeholder="Enter SSD specifications" />
                    <InputError :message="form.errors.ssd" />
                </div>
                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" rows="5" placeholder="Enter console description" />
                    <InputError :message="form.errors.description" />
                </div>
                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.consoles.index')">
                        <Button type="button" variant="destructive"><BanIcon />Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing"><SaveIcon />Add Console</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
