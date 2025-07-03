<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { computed } from 'vue';
import { UserRound } from 'lucide-vue-next';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

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
</script>

<template>
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
</template>
