<script setup lang="ts">
import Layout from '@/layouts/app/AppSidebarLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Search, Settings, Cpu, Microchip, HardDrive } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue'
import LaravelPaginator from '@/components/LaravelPaginator.vue'

interface Console {
  id: number
  name: string
  cpu: string | null
  gpu: string | null
  psu: string | null
  ram: string | null
  hdd: string | null
  ssd: string | null
  description: string | null
  is_active: boolean
}

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const userRoles = computed(() => user.value?.roles || [])

// Check if user has admin privileges (ADMIN, MODERATOR, or OWNER role)
const isAdmin = computed(() => {
  return userRoles.value.some((role) => ['admin', 'moderator', 'owner'].includes(role))
})

defineProps<{
  consoles: {
    data: Console[]
    links: any
    current_page: number
    from: number
    last_page: number
    path: string
    per_page: number
    to: number
    total: number
  }
  filters?: {
    search?: string
  }
}>()

function filterConsoles(e: Event) {
  e.preventDefault()
  const form = e.target as HTMLFormElement
  const formData = new FormData(form)

  router.get('/consoles', {
    search: formData.get('search') as string || undefined,
  }, {
    preserveState: false,
    replace: true
  })
}

function clearFilters() {
  router.get('/consoles', {}, {
    preserveState: false,
    replace: true
  })
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consoles',
        href: '/consoles',
    },
];
</script>

<template>
  <Layout :breadcrumbs="breadcrumbs">
    <Head title="Gaming Consoles" />

    <div class="container mx-auto px-4 py-8">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Gaming Consoles</h1>
        <div v-if="isAdmin">
          <Link :href="route('admin.consoles.index')" class="inline-flex items-center">
            <Button variant="outline" class="gap-2">
              <Settings class="h-4 w-4" />
              Manage Consoles
            </Button>
          </Link>
        </div>
      </div>

      <!-- Filters -->
      <Card class="mb-8">
        <CardHeader>
          <CardTitle>Find Consoles</CardTitle>
          <CardDescription>Search for gaming consoles by name or specifications</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit="filterConsoles" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label for="search">Search</Label>
                <div class="relative">
                  <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="search"
                    name="search"
                    :value="filters?.search || ''"
                    placeholder="Search by name or specifications"
                    class="pl-8"
                  />
                </div>
              </div>

              <div class="flex items-end space-x-2">
                <Button type="submit" class="flex-1">Apply Filters</Button>
                <Button type="button" variant="outline" @click="clearFilters">Clear</Button>
              </div>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Consoles Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card v-for="console in consoles.data" :key="console.id" class="flex flex-col">
          <CardHeader>
            <CardTitle class="text-xl">{{ console.name }}</CardTitle>
          </CardHeader>
          <CardContent class="flex-grow">
            <div class="space-y-3">
              <div v-if="console.cpu" class="flex items-center">
                <Cpu class="h-4 w-4 mr-2 text-muted-foreground" />
                <span class="text-sm font-medium">CPU:</span>
                <span class="ml-2 text-sm">{{ console.cpu }}</span>
              </div>
              <div v-if="console.gpu" class="flex items-center">
                <Microchip class="h-4 w-4 mr-2 text-muted-foreground" />
                <span class="text-sm font-medium">GPU:</span>
                <span class="ml-2 text-sm">{{ console.gpu }}</span>
              </div>
              <div v-if="console.ram" class="flex items-center">
                <Badge variant="outline" class="mr-2">RAM</Badge>
                <span class="text-sm">{{ console.ram }}</span>
              </div>
              <div v-if="console.ssd || console.hdd" class="flex items-center">
                <HardDrive class="h-4 w-4 mr-2 text-muted-foreground" />
                <span class="text-sm font-medium">Storage:</span>
                <span class="ml-2 text-sm">
                  {{ [console.ssd ? `SSD: ${console.ssd}` : null, console.hdd ? `HDD: ${console.hdd}` : null].filter(Boolean).join(', ') }}
                </span>
              </div>
              <div v-if="console.description" class="mt-4">
                <p class="text-sm text-muted-foreground line-clamp-3">{{ console.description }}</p>
              </div>
            </div>
          </CardContent>
          <CardFooter>
            <Link :href="`/consoles/${console.id}`" class="w-full" as="button">
              <Button class="w-full">View Details</Button>
            </Link>
          </CardFooter>
        </Card>

        <div v-if="consoles.data.length === 0" class="col-span-full text-center py-12">
          <h3 class="text-xl font-medium mb-2">No consoles found</h3>
          <p class="text-muted-foreground">Try adjusting your search criteria</p>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-8">
        <LaravelPaginator
          v-if="consoles.data.length > 0"
          :pagination="consoles"
          onlyKey="consoles"
        />
      </div>
    </div>
  </Layout>
</template>
