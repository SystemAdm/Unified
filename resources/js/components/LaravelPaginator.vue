<!--
/**
 * LaravelPaginator Component
 *
 * A reusable pagination component that works with Laravel's pagination data structure.
 * This component provides a consistent pagination UI across the application and handles
 * navigation between pages using Inertia.js.
 *
 * Features:
 * - Works with Laravel's standard pagination data structure
 * - Provides fallbacks for missing data
 * - Fully accessible with keyboard navigation and ARIA attributes
 * - Type-safe with generic type parameter for data items
 * - Consistent UI using the application's design system
 *
 * @component
 */
-->
<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';

// Constants for pagination labels to avoid hardcoded strings
const PAGINATION_LABELS = {
  PREVIOUS: '&laquo; Previous',
  NEXT: 'Next &raquo;',
  ELLIPSIS: '...'
};

// Helper function to safely compare pagination labels
const isLabelType = (label: string, type: keyof typeof PAGINATION_LABELS): boolean => {
  return label === PAGINATION_LABELS[type];
};

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

/**
 * Generic interface for Laravel pagination data
 * @template T - The type of items in the data array
 */
interface PaginationData<T = any> {
  data: T[];
  // Array of all page links including numbers and ellipsis
  links: PaginationLink[];
  // Navigation URLs
  first_page_url: string;
  last_page_url: string;
  prev_page_url?: string | null;
  next_page_url?: string | null;
  current_page: number;
  from: number;
  last_page: number;
  path: string;
  per_page: number;
  to: number;
  total: number;
}

/**
 * Props interface for LaravelPaginator component
 * @template T - The type of items in the pagination data array
 */
interface Props<T = any> {
  pagination: PaginationData<T>;
  onlyKey?: string;
  preserveScroll?: boolean;
}

/**
 * Component props with default values
 */
const props = withDefaults(defineProps<Props>(), {
  onlyKey: '',
  preserveScroll: true,
});

/**
 * Function to handle page navigation
 * Uses Inertia.js router to navigate to the specified URL
 */
const navigateToPage = (url: string | null) => {
  if (!url) return;

  const options: { preserveState: boolean; preserveScroll: boolean; only?: string[] } = {
    preserveState: true,
    preserveScroll: props.preserveScroll,
  };

  if (props.onlyKey) {
    options.only = [props.onlyKey];
  }

  router.visit(url, options);
};

/**
 * Computed properties with fallbacks for missing data
 * These provide safe access to pagination properties with default values
 * to prevent errors when the pagination data is incomplete or missing
 */

/**
 * Safe access to navigation URLs with fallback
 */
const safeNavigation = computed(() => {
  return {
    prev: props.pagination?.prev_page_url || null,
    next: props.pagination?.next_page_url || null,
    first: props.pagination?.first_page_url || '',
    last: props.pagination?.last_page_url || ''
  };
});

/**
 * Safe access to pagination links array with fallback
 */
const safeLinks = computed(() => {
  return props.pagination?.links || [];
});

/**
 * Safe access to current_page with fallback to page 1
 */
const safeCurrentPage = computed(() => {
  return props.pagination?.current_page || 1;
});

/**
 * Safe access to per_page with fallback to 10 items per page
 */
const safePerPage = computed(() => {
  return props.pagination?.per_page || 10;
});

/**
 * Safe access to total with fallback to 0 items
 */
const safeTotal = computed(() => {
  return props.pagination?.total || 0;
});
</script>

<template>
  <div class="mt-4">
    <Pagination
      :items-per-page="safePerPage"
      :total="safeTotal"
      :default-page="safeCurrentPage"
    >
      <PaginationContent>
        <!-- Previous Page Button -->
        <a
          v-if="safeNavigation.prev"
          href="#"
          @click.prevent="navigateToPage(safeNavigation.prev)"
          aria-label="Go to previous page"
          tabindex="0"
          role="button"
          @keydown.enter="navigateToPage(safeNavigation.prev)"
          @keydown.space="navigateToPage(safeNavigation.prev)"
        >
          <PaginationPrevious />
        </a>
        <PaginationPrevious
          v-else
          class="opacity-50 pointer-events-none"
          aria-disabled="true"
          tabindex="-1"
        />

        <!-- Page Numbers and Ellipsis -->
        <template v-for="(link, index) in safeLinks" :key="index">
          <!-- Skip previous and next links as they're handled separately -->
          <template v-if="!isLabelType(link.label, 'PREVIOUS') && !isLabelType(link.label, 'NEXT')">
            <!-- Page Number -->
            <a
              v-if="!isNaN(parseInt(link.label, 10)) && link.url"
              href="#"
              @click.prevent="navigateToPage(link.url)"
              :aria-label="`Go to page ${link.label}`"
              tabindex="0"
              role="button"
              @keydown.enter="navigateToPage(link.url)"
              @keydown.space="navigateToPage(link.url)"
            >
              <PaginationItem
                :value="parseInt(link.label, 10)"
                :is-active="link.active"
              >
                {{ link.label }}
              </PaginationItem>
            </a>

            <!-- Current Page (no link) -->
            <PaginationItem
              v-else-if="!isNaN(parseInt(link.label, 10))"
              :value="parseInt(link.label, 10)"
              :is-active="link.active"
              aria-current="page"
            >
              {{ link.label }}
            </PaginationItem>

            <!-- Ellipsis -->
            <PaginationEllipsis v-else-if="isLabelType(link.label, 'ELLIPSIS')" />
          </template>
        </template>

        <!-- Next Page Button -->
        <a
          v-if="safeNavigation.next"
          href="#"
          @click.prevent="navigateToPage(safeNavigation.next)"
          aria-label="Go to next page"
          tabindex="0"
          role="button"
          @keydown.enter="navigateToPage(safeNavigation.next)"
          @keydown.space="navigateToPage(safeNavigation.next)"
        >
          <PaginationNext />
        </a>
        <PaginationNext
          v-else
          class="opacity-50 pointer-events-none"
          aria-disabled="true"
          tabindex="-1"
        />
      </PaginationContent>
    </Pagination>
  </div>
</template>
