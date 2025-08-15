<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  type: string;
}

const props = defineProps<Props>();

const badgeClass = computed(() => {
  switch (props.type) {
    case 'danger':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    case 'warning':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    case 'info':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    case 'primary':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    case 'secondary':
    case 'default':
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
  }
});
</script>

<template>
  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="badgeClass">
    {{ type }}
  </span>
</template>
