<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { trans } from 'laravel-vue-i18n';

// Define props
defineProps<{
    users: { id: number; given_name: string; family_name: string; created_at: string }[];
    email: string;
    email_id: number | string;
}>();

// Reactive state for selected user
const selectedUserId = ref<number | null>(null);

// Use Inertia form
const form = useForm({
    user_id: null as number | null,
});

// Submit handler
const submit = () => {
    if (selectedUserId.value) {
        // Redirect to password page for the selected user
        window.location.href = route('login.password', { user_id: selectedUserId.value });
    }
};

// Format timestamp (helper)
const formatDate = (date: string): string => new Date(date).toLocaleDateString();
</script>

<template>
    <AuthLayout :title="trans('Choose an Account')" :description="trans('Multiple accounts are associated with this email')">
        <Head :title="trans('Choose an Account')" />

        <div class="space-y-6">
            <div class="text-center">
                <p class="text-muted-foreground">
                    <strong>{{ email }}</strong>
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Loop through users -->
                <div v-for="user in users" :key="user.id" class="rounded-md bg-muted p-4">
                    <Label class="flex cursor-pointer items-center space-x-3">
                        <input
                            type="radio"
                            :value="user.id"
                            :id="`user-${user.id}`"
                            v-model="selectedUserId"
                            class="h-5 w-5 rounded-full border-muted-foreground"
                        />
                        <span>
                            {{ user.given_name }} {{ user.family_name }}
                            <span class="text-sm text-muted-foreground"> ({{ trans('Created') }}: {{ formatDate(user.created_at) }}) </span>
                        </span>
                    </Label>
                </div>

                <InputError :message="form.errors.user_id" />

                <div class="mt-6">
                    <Button type="submit" class="w-full" :disabled="!selectedUserId">
                        {{ trans('Continue') }}
                    </Button>
                </div>
            </form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>{{ trans('Or, return to') }}</span>
                <TextLink :href="route('login')">{{ trans('log in page') }}</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
/* Custom styling for radio buttons */
input[type="radio"] {
    accent-color: currentColor;
}
</style>
