<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useNpsStore } from '@/stores/nps'
import NpsMap from '@/components/nps/NpsMap.vue'
import NpsFiltersPanel from '@/components/nps/NpsFiltersPanel.vue'

const npsStore = useNpsStore()
const loading = ref(true)

onMounted(async () => {
  try {
    await npsStore.fetchPerCentre()
  } finally {
    loading.value = false
  }
})

async function handleFiltersChange() {
  loading.value = true
  try {
    await npsStore.fetchPerCentre()
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h1 class="page-title mb-6">Mapa NPS per Centre</h1>

    <NpsFiltersPanel @change="handleFiltersChange" class="mb-6" />

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 card p-0 overflow-hidden" style="min-height: 500px;">
        <NpsMap :centres="npsStore.perCentre" />
      </div>

      <div class="space-y-4">
        <div
          v-for="centre in npsStore.perCentre"
          :key="centre.centre_id"
          class="card"
        >
          <h3 class="font-semibold">{{ centre.nom }}</h3>
          <div class="mt-2 flex items-baseline gap-2">
            <span
              v-if="centre.nps_score !== null"
              :class="[
                'text-2xl font-bold',
                centre.nps_score >= 50 ? 'text-nps-promotor' :
                centre.nps_score >= 0 ? 'text-nps-passiu' : 'text-nps-detractor'
              ]"
            >
              {{ centre.nps_score }}
            </span>
            <span v-else class="text-gray-400">-</span>
            <span class="text-sm text-gray-500">
              {{ centre.total_respostes }} respostes
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
