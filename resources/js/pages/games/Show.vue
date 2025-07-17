<script setup lang="ts">
import Layout from '@/layouts/app/AppSidebarLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import type { BreadcrumbItem } from '@/types';

interface Game {
  id: number
  name: string
  version: string
  console: string
  image: string | null
  is_active: boolean
  created_at: string
  updated_at: string
}

const props = defineProps<{
  game: Game
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Games',
        href: '/games',
    },{
        title: props.game.name,
        href: '/games/' + props.game.id,
    },
];
</script>

<template>
  <Layout :breadcrumbs="breadcrumbs">
    <Head :title="'Game: ' + game.name" />

    <div class="container mx-auto px-4 py-8">
      <div class="mb-6">
        <Link :href="`/games`" class="text-primary hover:underline mb-4 inline-block" as="button">
          &larr; Back to Games Library
        </Link>
        <h1 class="text-3xl font-bold mt-2">{{ game.name }}</h1>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Game Image -->
        <div>
          <Card>
            <img
              :src="game.image ? `/storage/${game.image}` : `https://placehold.co/400x600/0f0f0f/ffffff?text=${encodeURIComponent(game.name)}`"
              :alt="game.name"
              class="w-full object-cover rounded-t-lg"
            />
            <CardContent class="pt-4">
              <div class="flex flex-wrap gap-2">
                <Badge>{{ game.console }}</Badge>
                <Badge variant="outline" v-if="game.version">Version: {{ game.version }}</Badge>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Game Details -->
        <div class="lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>Game Information</CardTitle>
              <CardDescription>Detailed information about this game</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h3 class="text-sm font-medium text-muted-foreground">Name</h3>
                  <p class="mt-1 text-lg">{{ game.name }}</p>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-muted-foreground">Version</h3>
                  <p class="mt-1 text-lg">{{ game.version || 'N/A' }}</p>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-muted-foreground">Console</h3>
                  <p class="mt-1 text-lg">{{ game.console }}</p>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-muted-foreground">Last Updated</h3>
                  <p class="mt-1">{{ new Date(game.updated_at).toLocaleDateString() }}</p>
                </div>
              </div>
            </CardContent>
            <CardFooter>
              <Button variant="outline" class="w-full" @click="router.get('/games', {}, { preserveState: false })">
                Back to Games Library
              </Button>
            </CardFooter>
          </Card>
        </div>
      </div>
    </div>
  </Layout>
</template>
