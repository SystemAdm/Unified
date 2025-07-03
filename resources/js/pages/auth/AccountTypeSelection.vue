<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

// Define props and emits
const props = defineProps<{
    selectedType?: string;
}>();

const emit = defineEmits<{
    (e: 'select', type: string): void;
}>();

// Account type definitions with descriptions
const accountTypes = [
    {
        id: 'crew',
        name: 'Crew',
        description: 'For staff and volunteers',
        requirements: '13+ years old'
    },
    {
        id: 'membership',
        name: 'Membership',
        description: 'Regular membership',
        requirements: '13+ years old'
    },
    {
        id: 'guardian',
        name: 'Guardian',
        description: 'For parents and guardians',
        requirements: '18+ years old'
    },
    {
        id: 'guest',
        name: 'Guest',
        description: 'Limited access account',
        requirements: 'Any age'
    }
];

// Computed property to determine if a type is selected
const isSelected = computed(() => (type: string) => type === props.selectedType);

// Handle selection
const selectType = (type: string) => {
    emit('select', type);
};
</script>

<template>
    <AuthBase title="Choose Account Type" description="Select the type of account you want to create">
        <Head title="Choose Account Type" />

        <div class="grid gap-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div v-for="type in accountTypes" :key="type.id" class="col-span-1">
                    <Button
                        @click="selectType(type.id)"
                        :class="[
                            'w-full h-32 flex flex-col items-center justify-center p-4 text-left',
                            isSelected(type.id) ? 'ring-2 ring-primary' : ''
                        ]"
                        variant="outline"
                    >
                        <div class="font-bold text-lg">{{ type.name }}</div>
                        <div class="text-sm text-muted-foreground">{{ type.description }}</div>
                        <div class="text-xs mt-1">{{ type.requirements }}</div>
                    </Button>
                </div>
            </div>
        </div>
    </AuthBase>
</template>
