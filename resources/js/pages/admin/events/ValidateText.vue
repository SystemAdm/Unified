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

interface ValidationResult {
    check: string;
    status: 'passed' | 'failed' | 'warning';
    message: string;
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
const validationResults = ref<ValidationResult[]>([]);

const validateText = async () => {
    if (!encryptedText.value.trim()) {
        responseMessage.value = 'Please enter encrypted text to validate.';
        isSuccess.value = false;
        validationResults.value = [];
        return;
    }

    isLoading.value = true;
    responseMessage.value = '';
    validationResults.value = [];

    try {
        const response = await axios.post(route('admin.events.validate-text.submit', { event: props.event.id }), {
            encrypted_text: encryptedText.value
        });

        responseMessage.value = response.data.message;
        isSuccess.value = response.data.success;

        // Store validation results if they exist in the response
        if (response.data.validation_results) {
            validationResults.value = response.data.validation_results;
        } else {
            // If decryption failed, add a manual validation result
            if (!response.data.success && response.data.message.includes('Invalid encrypted text')) {
                validationResults.value = [{
                    check: 'Decrypting',
                    status: 'failed',
                    message: 'Failed to decrypt the provided text'
                }];
            }
        }
    } catch (error: any) {
        if (error.response && error.response.data) {
            responseMessage.value = error.response.data.message || 'An error occurred during validation.';
        } else {
            responseMessage.value = 'An error occurred during validation.';
        }
        isSuccess.value = false;
        validationResults.value = [];
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
    validationResults.value = [];
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

                <!-- Validation Results -->
                <div v-if="validationResults.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold mb-2">Validation Details:</h4>
                    <ul class="space-y-2">
                        <li
                            v-for="(result, index) in validationResults"
                            :key="index"
                            class="flex items-start space-x-2 p-2 rounded"
                            :class="{
                                'bg-green-50 dark:bg-green-900/10': result.status === 'passed',
                                'bg-red-50 dark:bg-red-900/10': result.status === 'failed',
                                'bg-yellow-50 dark:bg-yellow-900/10': result.status === 'warning'
                            }"
                        >
                            <!-- Status Icon -->
                            <div
                                class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center mt-0.5"
                                :class="{
                                    'bg-green-100 dark:bg-green-800': result.status === 'passed',
                                    'bg-red-100 dark:bg-red-800': result.status === 'failed',
                                    'bg-yellow-100 dark:bg-yellow-800': result.status === 'warning'
                                }"
                            >
                                <!-- Check Icon -->
                                <svg
                                    v-if="result.status === 'passed'"
                                    class="w-3 h-3 text-green-600 dark:text-green-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>

                                <!-- X Icon -->
                                <svg
                                    v-if="result.status === 'failed'"
                                    class="w-3 h-3 text-red-600 dark:text-red-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>

                                <!-- Warning Icon -->
                                <svg
                                    v-if="result.status === 'warning'"
                                    class="w-3 h-3 text-yellow-600 dark:text-yellow-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>

                            <!-- Check Info -->
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <span
                                        class="font-medium"
                                        :class="{
                                            'text-green-700 dark:text-green-300': result.status === 'passed',
                                            'text-red-700 dark:text-red-300': result.status === 'failed',
                                            'text-yellow-700 dark:text-yellow-300': result.status === 'warning'
                                        }"
                                    >
                                        {{ result.check }}
                                    </span>
                                    <span
                                        class="text-xs uppercase font-semibold px-2 py-0.5 rounded"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': result.status === 'passed',
                                            'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100': result.status === 'failed',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100': result.status === 'warning'
                                        }"
                                    >
                                        {{ result.status }}
                                    </span>
                                </div>
                                <p
                                    class="text-sm mt-1"
                                    :class="{
                                        'text-green-600 dark:text-green-400': result.status === 'passed',
                                        'text-red-600 dark:text-red-400': result.status === 'failed',
                                        'text-yellow-600 dark:text-yellow-400': result.status === 'warning'
                                    }"
                                >
                                    {{ result.message }}
                                </p>
                            </div>
                        </li>
                    </ul>
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
