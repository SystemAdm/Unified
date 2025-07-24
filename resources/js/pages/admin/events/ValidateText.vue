<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';

interface Props {
    event: {
        id: number;
        title: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.index') },
    { title: 'Events', href: route('admin.events.index') },
    { title: 'Event Details', href: route('admin.events.show', { event: props.event.id }) },
    { title: 'Validate Text', href: route('admin.events.validate-text', { event: props.event.id }) },
];

const encryptedText = ref('');
const responseMessage = ref('');
const isSuccess = ref(false);
const isLoading = ref(false);

const validateText = async () => {
    if (!encryptedText.value.trim()) {
        responseMessage.value = 'Please enter encrypted text to validate.';
        isSuccess.value = false;
        return;
    }

    isLoading.value = true;
    responseMessage.value = '';

    try {
        const response = await axios.post(route('admin.events.validate-text.submit', { event: props.event.id }), {
            encrypted_text: encryptedText.value
        });

        responseMessage.value = response.data.message;
        isSuccess.value = response.data.success;
    } catch (error: any) {
        if (error.response && error.response.data) {
            responseMessage.value = error.response.data.message || 'An error occurred during validation.';
        } else {
            responseMessage.value = 'An error occurred during validation.';
        }
        isSuccess.value = false;
    } finally {
        isLoading.value = false;
    }
};

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        validateText();
    }
};

const clearForm = () => {
    encryptedText.value = '';
    responseMessage.value = '';
    isSuccess.value = false;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Validate Encrypted Text" />

        <div class="flex flex-col items-center justify-center min-h-[60vh] space-y-8">
            <div class="w-full max-w-2xl">
                <h1 class="text-3xl font-bold text-center mb-8">Validate Encrypted Text</h1>

                <!-- Large Input Box -->
                <div class="space-y-4">
                    <label for="encrypted-text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Enter Encrypted Text:
                    </label>
                    <Input
                        id="encrypted-text"
                        v-model="encryptedText"
                        type="text"
                        placeholder="Paste your encrypted text here..."
                        class="w-full h-16 text-lg px-4 py-3"
                        @keypress="handleKeyPress"
                        :disabled="isLoading"
                    />
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Press Enter to validate or click the button below.
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-center space-x-4 mt-6">
                    <Button
                        @click="validateText"
                        :disabled="isLoading || !encryptedText.trim()"
                        class="px-8 py-2"
                    >
                        {{ isLoading ? 'Validating...' : 'Validate Text' }}
                    </Button>
                    <Button
                        variant="outline"
                        @click="clearForm"
                        :disabled="isLoading"
                        class="px-8 py-2"
                    >
                        Clear
                    </Button>
                </div>
            </div>

            <!-- Response Box -->
            <div
                v-if="responseMessage"
                class="w-full max-w-2xl p-6 rounded-lg border-2 transition-all duration-300"
                :class="{
                    'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800': isSuccess,
                    'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800': !isSuccess
                }"
            >
                <div class="flex items-center space-x-3">
                    <div
                        class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center"
                        :class="{
                            'bg-green-100 dark:bg-green-800': isSuccess,
                            'bg-red-100 dark:bg-red-800': !isSuccess
                        }"
                    >
                        <svg
                            v-if="isSuccess"
                            class="w-4 h-4 text-green-600 dark:text-green-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <svg
                            v-else
                            class="w-4 h-4 text-red-600 dark:text-red-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div>
                        <h3
                            class="font-semibold"
                            :class="{
                                'text-green-800 dark:text-green-200': isSuccess,
                                'text-red-800 dark:text-red-200': !isSuccess
                            }"
                        >
                            {{ isSuccess ? 'Validation Successful' : 'Validation Failed' }}
                        </h3>
                        <p
                            class="mt-1"
                            :class="{
                                'text-green-700 dark:text-green-300': isSuccess,
                                'text-red-700 dark:text-red-300': !isSuccess
                            }"
                        >
                            {{ responseMessage }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="mt-8">
                <a
                    :href="route('admin.events.show', { event: props.event.id })"
                    class="text-blue-600 dark:text-blue-400 hover:underline"
                >
                    ← Back to Event
                </a>
            </div>
        </div>
    </AppLayout>
</template>
