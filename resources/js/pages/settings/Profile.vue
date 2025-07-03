<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import axios from 'axios';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { useInitials } from '@/composables/useInitials';
import { UserRound } from 'lucide-vue-next';

// No props needed for this component

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User;
const { getInitials } = useInitials();

// Get the current avatar type from the user or default to 'initials'
const currentAvatarType = ref(user.avatar_type || 'initials');
const avatarImage = ref<File | null>(null);
const previewUrl = ref<string | null>(null);

const form = useForm({
    name: user.name,
    avatar_type: currentAvatarType.value,
    avatar_image: null as File | null,
});

// Watch for changes to the avatar type
watch(currentAvatarType, (newValue, oldValue) => {
    form.avatar_type = newValue;

    // Preload Gravatar image when switching to Gravatar
    if (newValue === 'gravatar' && gravatarAvatarUrl.value) {
        const img = new Image();
        img.src = gravatarAvatarUrl.value;
    }

    // Clear preview URL when changing from image to another type
    if (oldValue === 'image' && newValue !== 'image') {
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = null;
        }
        // Also clear the file input
        form.avatar_image = null;
        avatarImage.value = null;
    }
});

// Computed properties for different avatar types
// Check if we should show the silhouette
const isSilhouette = computed(() => currentAvatarType.value === 'silhouette');

const gravatarAvatarUrl = computed(() => {
    if (!user.email) return null;
    // Use the server-side computed hash for consistency
    const hash = md5(user.email.toLowerCase().trim());
    // Include the Gravatar key from the environment if available
    const gravatarKey = import.meta.env.VITE_GRAVATAR_KEY || "";
    return `https://secure.gravatar.com/avatar/${hash}?s=200&d=mp&r=g${gravatarKey ? `&key=${gravatarKey}` : ''}`;
});

// Helper function to get MD5 hash for Gravatar
function md5(input: string): string {
    // For client-side, we'll use a simplified approach
    // In a real app, you'd use a proper crypto library

    // Convert the input to lowercase and trim whitespace
    const cleanInput = input.toLowerCase().trim();

    // For common email domains, we can use pre-computed hashes
    // This helps ensure consistency with server-side hashing
    if (cleanInput.endsWith('@gmail.com')) {
        // Gmail ignores dots and everything after + in the local part
        const localPart = cleanInput.split('@')[0].replace(/\./g, '').split('+')[0];
        const domain = 'gmail.com';
        const normalizedEmail = `${localPart}@${domain}`;

        // Now use a simple hash function on the normalized email
        return simpleHash(normalizedEmail);
    }

    // For other domains, just use a simple hash
    return simpleHash(cleanInput);
}

// A more consistent simple hash function
function simpleHash(input: string): string {
    let hash = 0;
    if (input.length === 0) return '00000000000000000000000000000000';

    for (let i = 0; i < input.length; i++) {
        const char = input.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Convert to 32bit integer
    }

    // Convert to hex string and ensure it's 32 characters
    let hexHash = Math.abs(hash).toString(16);
    while (hexHash.length < 32) {
        hexHash = '0' + hexHash;
    }

    return hexHash;
}

// Computed property to determine which avatar URL to show
const currentAvatarUrl = computed(() => {
    switch (currentAvatarType.value) {
        case 'gravatar':
            // Always prefer the server-side computed URL when available
            if (user.avatar_type === 'gravatar' && user.avatar) {
                return user.avatar;
            }
            // Otherwise use our computed URL
            return gravatarAvatarUrl.value;
        case 'image':
            // For image type, use the preview URL if available (for newly uploaded images)
            // Otherwise use the existing avatar URL if the user already has an image avatar
            return previewUrl.value || (user.avatar_type === 'image' ? user.avatar : null);
        case 'silhouette':
        case 'initials':
        default:
            // For silhouette and initials, we don't need an image URL
            // The AvatarFallback component will handle these types
            return null;
    }
});

// Preload the Gravatar image when the component is mounted
onMounted(() => {
    if (gravatarAvatarUrl.value) {
        const img = new Image();
        img.src = gravatarAvatarUrl.value;
    }
});

// Handle file selection
const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        avatarImage.value = file;
        form.avatar_image = file;

        // Create a preview URL
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
        }
        previewUrl.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    // Reset any previous errors
    form.clearErrors();

    // Create a FormData object for the submission
    const formData = new FormData();

    // Always include these fields
    formData.append('_method', 'PATCH'); // For Laravel method spoofing
    formData.append('avatar_type', form.avatar_type);

    // Always include name field
    formData.append('name', form.name || '');

    // Add avatar_image if it exists
    if (form.avatar_image) {
        formData.append('avatar_image', form.avatar_image);
    }

    // Get the CSRF token
    const csrfToken = page.props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Final check: log the actual FormData contents
    console.log('FormData entries:');
    for (const pair of formData.entries()) {
        console.log(pair[0] + ': ' + (pair[0] === 'avatar_image' ? '[File]' : pair[1]));
    }

    // Set processing state
    form.processing = true;

    // Use axios directly for the submission
    axios.post(route('profile.update'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        console.log('Form submission successful', response);

        // Check if the response contains updated user data
        if (response.data && response.data.user) {
            console.log('User data updated:', response.data.user);
            // Update the local user data with the new values
            user.name = response.data.user.name;
            user.avatar_type = response.data.user.avatar_type;
            user.avatar = response.data.user.avatar;

            // Update currentAvatarType to match the new avatar_type
            currentAvatarType.value = response.data.user.avatar_type;

            // If the new avatar type is gravatar, preload the image
            if (response.data.user.avatar_type === 'gravatar' && response.data.user.avatar) {
                const img = new Image();
                img.src = response.data.user.avatar;
            }
        } else {
            console.log('Response does not contain updated user data:', response.data);
            // Reload the page to get the latest data
            window.location.reload();
        }

        form.processing = false;
        form.recentlySuccessful = true;

        // Reset the recently successful flag after a delay
        setTimeout(() => {
            form.recentlySuccessful = false;
        }, 2000);
    })
    .catch(error => {
        console.error('Form submission failed with errors:', error);
        form.processing = false;

        // Handle validation errors
        if (error.response && error.response.data && error.response.data.errors) {
            form.errors = error.response.data.errors;
        } else {
            // If there's an unexpected error, show an alert and reload the page
            alert('An error occurred while saving your profile. The page will reload to refresh your data.');
            window.location.reload();
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and avatar" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" autocomplete="name" placeholder="Full name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-4">
                        <Label>Avatar</Label>

                        <div class="flex items-center gap-4">
                            <!-- Avatar Preview -->
                            <div class="flex-shrink-0">
                                <Avatar class="h-16 w-16 overflow-hidden rounded-full">
                                    <AvatarImage
                                        v-if="currentAvatarUrl"
                                        :src="currentAvatarUrl"
                                        :alt="user.name"
                                        @error="() => { console.error('Avatar image failed to load:', currentAvatarUrl); }"
                                    />
                                    <AvatarFallback class="rounded-full font-semibold">
                                        <UserRound v-if="isSilhouette" class="size-8" />
                                        <template v-else>{{ getInitials(user.name) }}</template>
                                    </AvatarFallback>
                                </Avatar>
                            </div>

                            <!-- Avatar Type Selection -->
                            <RadioGroup v-model="currentAvatarType" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="initials" value="initials" />
                                    <Label for="initials">Initials</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="silhouette" value="silhouette" />
                                    <Label for="silhouette">Silhouette</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="gravatar" value="gravatar" />
                                    <Label for="gravatar">Gravatar</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="image" value="image" />
                                    <Label for="image">Upload Image</Label>
                                </div>
                            </RadioGroup>
                        </div>

                        <!-- File Upload (only shown when 'image' is selected) -->
                        <div v-if="currentAvatarType === 'image'" class="mt-2">
                            <Input type="file" accept="image/*" @change="handleFileChange" />
                            <InputError class="mt-2" :message="form.errors.avatar_image" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
