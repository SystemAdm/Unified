<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';


// Get props for the component
const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    user: {
        id: number;
        given_name: string;
        family_name: string;
    };
    email: string;
    remember?: boolean;
}>();

// Create form with password and remember fields
const form = useForm({
    password: '',
    remember: props.remember || false,
    user_id: props.user.id,
});

// Submit handler
const submit = () => {
    form.post(route('login.authenticate-with-password'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase
        :title="trans('Enter your password')"
        :description="trans('Please enter your password to log in as') + ' ' + user.given_name + ' ' + user.family_name"
    >
        <Head :title="trans('Log in')" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="mb-4 text-center">
            <p class="text-muted-foreground">
                <strong>{{ email }}</strong>
            </p>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">{{ trans('Password') }}</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="3">
                            {{ trans('Forgot password?') }}
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model="form.remember" :tabindex="2" />
                        <span>{{ trans('Remember me') }}</span>
                    </Label>
                </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="3" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    {{ trans('Log in') }}
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                {{ trans('Not you?') }}
                <TextLink :href="route('login')" :tabindex="4">{{ trans('Go back') }}</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
