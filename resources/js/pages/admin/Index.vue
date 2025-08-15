<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import AdminModelCard from '@/components/AdminModelCard.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ArrowDownAZ, ArrowDownZA, ArrowDownUp } from 'lucide-vue-next';

interface ModelData {
    title: string;
    modelType: string;
    totalCount: number;
    newCount: number;
    indexRoute: string;
    createRoute: string;
    activeCount?: number;
    inactiveCount?: number;
}

interface Props {
    modelData?: Record<string, ModelData>;
}

const props = withDefaults(defineProps<Props>(), {
    modelData: () => ({}),
});
// Sort mode: 'default', 'asc' (A->Z), or 'desc' (Z->A)
const sortMode = ref('asc');

// Computed property to sort models based on current sort mode
const sortedModelData = computed(() => {
    // Return empty object if modelData is null, undefined, or empty
    if (!props.modelData || Object.keys(props.modelData).length === 0) {
        console.log('modelData is empty or undefined:', props.modelData);
        return {};
    }

    if (sortMode.value === 'default') {
        return props.modelData;
    }

    // Convert object to array, sort by title, and convert back to object
    const entries = Object.entries(props.modelData);
    console.log('modelData entries:', entries);

    if (sortMode.value === 'asc') {
        // Sort A->Z
        entries.sort((a, b) => a[1].title.localeCompare(b[1].title));
    } else {
        // Sort Z->A
        entries.sort((a, b) => b[1].title.localeCompare(a[1].title));
    }

    return Object.fromEntries(entries);
});

// Cycle through sort modes: asc -> desc -> default -> asc
const toggleSort = () => {
    if (sortMode.value === 'asc') {
        sortMode.value = 'desc';
    } else if (sortMode.value === 'desc') {
        sortMode.value = 'default';
    } else {
        sortMode.value = 'asc';
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin Dashboard" />

        <div class="flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <HeadingSmall title="Admin Dashboard" description="Manage your application" class="m-3" />
                <div class="mr-3 flex items-center">
                    <Button @click="toggleSort" variant="default" class="flex items-center">
                        <ArrowDownAZ v-if="sortMode === 'asc'" />
                        <!-- ArrowDownZA icon for Z->A sorting -->
                        <ArrowDownZA v-else-if="sortMode === 'desc'" />
                        <!-- Default sorting icon -->
                        <ArrowDownUp v-else />
                    </Button>
                </div>
            </div>

            <div v-if="Object.keys(sortedModelData).length > 0" class="grid grid-cols-1 gap-6 xl:grid-cols-2 2xl:grid-cols-3">
                <AdminModelCard
                    v-for="(model, key) in sortedModelData"
                    :key="key"
                    :title="model.title"
                    :model-type="model.modelType"
                    :total-count="model.totalCount"
                    :new-count="model.newCount"
                    :active-count="model.activeCount"
                    :inactive-count="model.inactiveCount"
                    :index-route="model.indexRoute"
                    :create-route="model.createRoute"
                />
            </div>
            <div v-else class="flex flex-col items-center justify-center rounded-lg bg-white p-8 shadow-md dark:bg-gray-800">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="64"
                    height="64"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="mb-4 text-gray-400"
                >
                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                    <line x1="3" x2="21" y1="9" y2="9"></line>
                    <path d="M9 16h6"></path>
                </svg>
                <h3 class="mb-2 text-xl font-semibold text-gray-700 dark:text-gray-300">No models available</h3>
                <p class="text-gray-600 dark:text-gray-400">There are no models to display at the moment.</p>
            </div>
        </div>
    </AppLayout>
</template>
