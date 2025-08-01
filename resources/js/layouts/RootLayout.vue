<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
  <div>
    <component :is="$slots.default" />
    <Toaster />
  </div>
</template>
