<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useNpsStore } from '@/stores/nps'
import NpsEvolutionChart from '@/components/charts/NpsEvolutionChart.vue'
import NpsFiltersPanel from '@/components/nps/NpsFiltersPanel.vue'

const npsStore = useNpsStore()
const loading = ref(true)

onMounted(async () => {
  try {
    await npsStore.fetchEvolucio()
  } finally {
    loading.value = false
  }
})

async function handleFiltersChange() {
  loading.value = true
  try {
    await npsStore.fetchEvolucio()
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h1 class="page-title mb-6">Evolució NPS</h1>

    <NpsFiltersPanel @change="handleFiltersChange" class="mb-6" />

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="card">
      <NpsEvolutionChart
        v-if="npsStore.evolucio.length"
        :data="npsStore.evolucio"
        :height="400"
      />
      <p v-else class="text-gray-500 text-center py-8">No hi ha dades disponibles</p>
    </div>
  </div>
</template>
