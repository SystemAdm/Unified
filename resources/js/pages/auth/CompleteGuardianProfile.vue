<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

// Define props
const props = defineProps<{
    user: {
        id: number;
        given_name: string;
        family_name: string;
    };
}>();

// Initialize form
const form = useForm({
    password: '',
    password_confirmation: '',
    birthday: '',
    phone: '',
});

const submit = () => {
    form.post(route('guardian.complete-profile.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase
        title="Complete Your Guardian Profile"
        description="Please provide the required information to complete your guardian profile"
    >
        <Head title="Complete Guardian Profile" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="text-center mb-4">
                    <h2 class="text-lg font-semibold">Welcome, {{ props.user.given_name }}!</h2>
                    <p class="text-sm text-muted-foreground">
                        You've been registered as a guardian. Please complete your profile to continue.
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="phone">Phone Number</Label>
                    <Input
                        id="phone"
                        type="tel"
                        required
                        autofocus
                        :tabindex="1"
                        v-model="form.phone"
                        placeholder="Enter your phone number"
                    />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="birthday">Birthday</Label>
                    <Input
                        id="birthday"
                        type="date"
                        required
                        :tabindex="2"
                        v-model="form.birthday"
                    />
                    <InputError :message="form.errors.birthday" />
                    <p class="text-xs text-muted-foreground">You must be at least 18 years old.</p>
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        v-model="form.password"
                        placeholder="Create a password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        v-model="form.password_confirmation"
                        placeholder="Confirm your password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="w-full mt-4"
                    :tabindex="5"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Complete Profile
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                <p>
                    By completing your profile, you'll be able to monitor and control the activities of your child.
                </p>
                <div class="mt-4 flex justify-center space-x-4">
                    <TextLink :href="route('logout')" method="post" as="button" class="text-sm">
                        Log out
                    </TextLink>
                </div>
            </div>
        </form>
    </AuthBase>
</template>
