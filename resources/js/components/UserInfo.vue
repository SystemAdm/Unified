<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { computed, ref } from 'vue';
import { UserRound, QrCode, X } from 'lucide-vue-next';
import QRCode from 'qrcode';
import { encryptUserData } from '@/utils/encryption';
import { usePage } from '@inertiajs/vue3';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();
const page = usePage();

// QR Code modal state
const showQRModal = ref(false);
const qrCodeDataUrl = ref<string>('');
const isGeneratingQR = ref(false);

// Compute whether we should show the avatar image
const showAvatar = computed(() => {
    // Show avatar if user exists, avatar exists, is not empty, and is either gravatar or image type
    return props.user &&
           props.user.avatar &&
           props.user.avatar !== '' &&
           (props.user.avatar_type === 'gravatar' || props.user.avatar_type === 'image');
});

// Check if we should show the silhouette
const isSilhouette = computed(() => props.user && props.user.avatar_type === 'silhouette');

// Generate QR code with encrypted user data
const generateQRCode = async () => {
    if (!props.user || !props.user.family_name) {
        console.error('User or family_name not available');
        return;
    }

    try {
        isGeneratingQR.value = true;

        // Get APP_KEY from page props
        const appKey = page.props.appKey as string;

        // Encrypt user data
        const encryptedData = await encryptUserData(props.user.id, props.user.family_name, appKey);

        // Generate QR code
        const qrDataUrl = await QRCode.toDataURL(encryptedData, {
            width: 256,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        });

        qrCodeDataUrl.value = qrDataUrl;
        showQRModal.value = true;
    } catch (error) {
        console.error('Failed to generate QR code:', error);
    } finally {
        isGeneratingQR.value = false;
    }
};

// Handle click on user info
const handleClick = () => {
    generateQRCode();
};

// Close QR modal
const closeQRModal = () => {
    showQRModal.value = false;
    qrCodeDataUrl.value = '';
};
</script>

<template>
    <div
        class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg p-1 transition-colors"
        @click="handleClick"
        :class="{ 'opacity-50': isGeneratingQR }"
    >
        <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
            <AvatarImage v-if="showAvatar && user" :src="user.avatar!" :alt="user.name" />
            <AvatarFallback class="rounded-lg">
                <UserRound v-if="isSilhouette" class="size-5" />
                <template v-else-if="user">{{ getInitials(user.name) }}</template>
                <template v-else>?</template>
            </AvatarFallback>
        </Avatar>

        <div class="grid flex-1 text-left text-sm leading-tight">
            <span v-if="user" class="truncate font-medium">{{ user.name }}</span>
            <span v-else class="truncate font-medium">Unknown User</span>
            <span v-if="showEmail && user && user.email" class="truncate text-xs text-muted-foreground">{{ user.email }}</span>
        </div>

        <QrCode class="h-4 w-4 text-muted-foreground" />
    </div>

    <!-- QR Code Modal -->
    <div
        v-if="showQRModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click="closeQRModal"
    >
        <div
            class="bg-white dark:bg-gray-900 rounded-lg p-6 max-w-sm w-full mx-4 relative"
            @click.stop
        >
            <button
                @click="closeQRModal"
                class="absolute top-2 right-2 p-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded"
            >
                <X class="h-4 w-4" />
            </button>

            <div class="text-center">
                <h3 class="text-lg font-semibold mb-4">User QR Code</h3>

                <div v-if="qrCodeDataUrl" class="mb-4">
                    <img :src="qrCodeDataUrl" alt="User QR Code" class="mx-auto" />
                </div>

                <p class="text-sm text-muted-foreground mb-2">
                    This QR code contains encrypted user information
                </p>

                <div v-if="user" class="text-xs text-muted-foreground">
                    <p>User: {{ user.name }}</p>
                    <p>ID: {{ user.id }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
