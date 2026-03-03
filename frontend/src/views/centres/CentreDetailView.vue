<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { centreService } from '@/services/centres'
import type { Centre } from '@/types'

const route = useRoute()
const centre = ref<Centre | null>(null)
const loading = ref(true)

const centreId = parseInt(route.params.id as string)

onMounted(async () => {
  try {
    centre.value = await centreService.get(centreId)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else-if="centre">
      <h1 class="page-title mb-6">{{ centre.name }}</h1>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="card">
          <h2 class="text-lg font-semibold mb-4">Informació del centre</h2>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-gray-500">Codi</dt>
              <dd class="font-mono">{{ centre.code }}</dd>
            </div>
            <div v-if="centre.address" class="flex justify-between">
              <dt class="text-gray-500">Adreça</dt>
              <dd>{{ centre.address }}</dd>
            </div>
            <div v-if="centre.city" class="flex justify-between">
              <dt class="text-gray-500">Ciutat</dt>
              <dd>{{ centre.city }}, {{ centre.postal_code }}</dd>
            </div>
            <div v-if="centre.phone" class="flex justify-between">
              <dt class="text-gray-500">Telèfon</dt>
              <dd>{{ centre.phone }}</dd>
            </div>
            <div v-if="centre.email" class="flex justify-between">
              <dt class="text-gray-500">Email</dt>
              <dd>{{ centre.email }}</dd>
            </div>
          </dl>
        </div>

        <div class="card">
          <h2 class="text-lg font-semibold mb-4">Estadístiques</h2>
          <p class="text-gray-500">Properament...</p>
        </div>
      </div>
    </div>
  </div>
</template>
