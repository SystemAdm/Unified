<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useToast } from '@/components/ui/sonner';

const { toast } = useToast();

const showToast = () => {
  toast({
    title: 'Toast Test',
    description: 'This is a test of the Sonner toast system',
  });
};

const showSuccessToast = () => {
  toast.success('Success toast test');
};

const showErrorToast = () => {
  toast.error('Error toast test');
};
</script>

<template>
  <div class="flex flex-col gap-4 p-4">
    <h2 class="text-xl font-bold">Toast Test Component</h2>
    <div class="flex gap-2">
      <Button @click="showToast">Show Default Toast</Button>
      <Button @click="showSuccessToast" variant="success">Show Success Toast</Button>
      <Button @click="showErrorToast" variant="destructive">Show Error Toast</Button>
    </div>
  </div>
</template>
