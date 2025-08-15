<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { PlusIcon, UsersIcon, CalendarIcon, BuildingIcon, NewspaperIcon, ImageIcon, BellIcon, GamepadIcon, MonitorIcon, MapPinIcon, ShieldIcon, KeyIcon, PhoneIcon, MailIcon, SearchIcon} from 'lucide-vue-next';

interface Props {
  title: string;
  modelType: string;
  totalCount: number;
  newCount?: number;
  indexRoute: string;
  createRoute: string;
  showNewCount?: boolean;
  activeCount?: number;
  inactiveCount?: number;
}

const props = withDefaults(defineProps<Props>(), {
  showNewCount: true
});

const icon = computed(() => {
  switch (props.modelType.toLowerCase()) {
    case 'users':
      return UsersIcon;
    case 'events':
      return CalendarIcon;
    case 'organizations':
      return BuildingIcon;
    case 'news':
      return NewspaperIcon;
    case 'banners':
      return ImageIcon;
    case 'announcements':
      return BellIcon;
    case 'games':
      return GamepadIcon;
    case 'consoles':
      return MonitorIcon;
    case 'locations':
      return MapPinIcon;
    case 'roles':
      return ShieldIcon;
    case 'permissions':
      return KeyIcon;
    case 'phones':
      return PhoneIcon;
    case 'emails':
      return MailIcon;
    default:
      return UsersIcon;
  }
});
</script>

<template>
  <div class="rounded-lg dark:bg-gray-800 shadow-md overflow-hidden">
    <div class="p-4">
      <div class="flex items-center mb-4">
        <component :is="icon" class="h-6 w-6 mr-2 text-primary" />
        <h3 class="text-xl font-semibold">{{ title }}</h3>
      </div>

      <div class="mb-4">
        <div class="text-3xl font-bold text-primary">{{ totalCount }}</div>
        <div class="text-sm">Total {{ title }}</div>

        <div v-if="showNewCount && newCount !== undefined" class="mt-2">
          <div class="text-lg font-semibold text-green-600">+{{ newCount }}</div>
          <div class="text-sm">New this week</div>
        </div>

        <div v-if="activeCount !== undefined || inactiveCount !== undefined" class="mt-3 grid grid-cols-2 gap-2">
          <div v-if="activeCount !== undefined" class="text-sm">
            <span class="font-semibold text-emerald-600">{{ activeCount }}</span>
            <span class="text-muted-foreground"> active/published</span>
          </div>
          <div v-if="inactiveCount !== undefined" class="text-sm">
            <span class="font-semibold text-amber-600">{{ inactiveCount }}</span>
            <span class="text-muted-foreground"> unpublished/inactive</span>
          </div>
        </div>
      </div>

      <div class="flex space-x-2 justify-between items-center">
        <Link :href="indexRoute">
          <Button variant="secondary">
            <SearchIcon class="h-4 mr-2" /> Index {{ title }}
          </Button>
        </Link>
        <Link :href="createRoute">
          <Button>
            <PlusIcon class="h-4 mr-2" />
            Create {{ title.slice(0, -1) }}
          </Button>
        </Link>
      </div>
    </div>
  </div>
</template>
