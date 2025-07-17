<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

// Define props
const props = defineProps<{
    relationGuardedOptions: Array<{ value: string, label: string }>;
    relationGuardianOptions: Array<{ value: string, label: string }>;
}>();

// Initialize form
const form = useForm({
    name: '',
    email: '',
    relation_guarded: '',
    relation_guardian: '',
});

const submit = () => {
    form.post(route('register.guardian'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthBase
        title="Register Guardian"
        description="Please provide your guardian's information"
    >
        <Head title="Register Guardian" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Guardian's Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        v-model="form.name"
                        placeholder="Full name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Guardian's Email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="relation_guarded">Your Relationship to Guardian</Label>
                    <Select v-model="form.relation_guarded" required>
                        <SelectTrigger :tabindex="3">
                            <SelectValue placeholder="Select relationship" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in props.relationGuardedOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.relation_guarded" />
                </div>

                <div class="grid gap-2">
                    <Label for="relation_guardian">Guardian's Relationship to You</Label>
                    <Select v-model="form.relation_guardian" required>
                        <SelectTrigger :tabindex="4">
                            <SelectValue placeholder="Select relationship" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in props.relationGuardianOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.relation_guardian" />
                </div>

                <Button
                    type="submit"
                    class="w-full mt-4"
                    :tabindex="5"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Register Guardian
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                <p>
                    We'll send an email to your guardian with instructions on how to complete their registration.
                </p>
                <div class="mt-4 flex justify-center space-x-4">
                    <TextLink :href="route('dashboard')" class="text-sm">
                        Back to Dashboard
                    </TextLink>
                    <TextLink :href="route('logout')" method="post" as="button" class="text-sm">
                        Log out
                    </TextLink>
                </div>
            </div>
        </form>
    </AuthBase>
</template>
