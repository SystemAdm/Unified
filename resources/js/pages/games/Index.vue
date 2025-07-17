<script setup lang="ts">
import Layout from '@/layouts/app/AppSidebarLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Search } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types';

interface Game {
  id: number
  name: string
  version: string
  console: string
  image: string | null
  is_active: boolean
}

defineProps<{
  games: Game[]
  consoles: string[]
  filters: {
    console?: string
    search?: string
  }
}>()

function filterGames(e: Event) {
  e.preventDefault()
  const form = e.target as HTMLFormElement
  const formData = new FormData(form)

  const consoleValue = formData.get('console') as string
  router.get('/games', {
    console: consoleValue === 'all' ? undefined : consoleValue || undefined,
    search: formData.get('search') as string || undefined,
  }, {
    preserveState: false,
    replace: true
  })
}

function clearFilters() {
  router.get('/games', {}, {
    preserveState: false,
    replace: true
  })
}
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Games',
        href: '/games',
    },
];
</script>

<template>
  <Layout :breadcrumbs="breadcrumbs">
    <Head title="Games Library" />

    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-8">Games Library</h1>

      <!-- Filters -->
      <Card class="mb-8">
        <CardHeader>
          <CardTitle>Filter Games</CardTitle>
          <CardDescription>Find games by console or name</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit="filterGames" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <Label for="console">Console</Label>
                <Select name="console" :value="filters.console || 'all'">
                  <SelectTrigger>
                    <SelectValue placeholder="All Consoles" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Consoles</SelectItem>
                    <SelectItem v-for="console in consoles" :key="console" :value="console">
                      {{ console }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <Label for="search">Search</Label>
                <div class="relative">
                  <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="search"
                    name="search"
                    :value="filters.search || ''"
                    placeholder="Search by game name"
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

      <!-- Games Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <Card v-for="game in games" :key="game.id">
              <img
                  :src="game.image ? `/storage/${game.image}` : `https://placehold.co/200x300/0f0f0f/ffffff?text=${encodeURIComponent(game.name)}`"
                  :alt="game.name"
                  class="w-full h-64 object-cover"
              />
              <CardHeader>
                  <div class="flex justify-between items-center">
                      <CardTitle class="text-lg">{{ game.name }}</CardTitle>
                      <Badge class="font-semibold" v-if="game.version != '0'">ver.: {{ game.version }}</Badge>
                  </div>
                  <Badge>{{ game.console }}</Badge>
              </CardHeader>
              <CardFooter>
                  <Link :href="`/games/${game.id}`" class="w-full" as="button">
                    <Button class="w-full">View Details</Button>
                  </Link>
              </CardFooter>
          </Card>

        <div v-if="games.length === 0" class="col-span-full text-center py-12">
          <h3 class="text-xl font-medium mb-2">No games found</h3>
          <p class="text-muted-foreground">Try adjusting your filters or search criteria</p>
        </div>
      </div>
    </div>
  </Layout>
</template>
