<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { centreService } from '@/services/centres'
import type { Centre } from '@/types'
import { BuildingOffice2Icon, MapPinIcon, PhoneIcon } from '@heroicons/vue/24/outline'

const centres = ref<Centre[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    centres.value = await centreService.list()
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <h1 class="page-title mb-6">Centres</h1>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      <RouterLink
        v-for="centre in centres"
        :key="centre.id"
        :to="`/centres/${centre.id}`"
        class="card hover:shadow-lg transition-shadow"
      >
        <div class="flex items-start gap-4">
          <div class="p-3 bg-primary-100 rounded-lg">
            <BuildingOffice2Icon class="h-6 w-6 text-primary-600" />
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">{{ centre.name }}</h3>
            <p class="text-sm text-gray-500">{{ centre.code }}</p>
            <div v-if="centre.address" class="mt-2 flex items-center text-sm text-gray-500">
              <MapPinIcon class="h-4 w-4 mr-1" />
              {{ centre.city }}
            </div>
            <div v-if="centre.phone" class="mt-1 flex items-center text-sm text-gray-500">
              <PhoneIcon class="h-4 w-4 mr-1" />
              {{ centre.phone }}
            </div>
          </div>
        </div>
      </RouterLink>
    </div>
  </div>
</template>
