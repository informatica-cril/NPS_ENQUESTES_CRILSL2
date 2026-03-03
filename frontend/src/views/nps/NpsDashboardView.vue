<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useNpsStore } from '@/stores/nps'
import NpsScoreCard from '@/components/nps/NpsScoreCard.vue'
import NpsDistributionChart from '@/components/charts/NpsDistributionChart.vue'
import NpsEvolutionChart from '@/components/charts/NpsEvolutionChart.vue'
import NpsFiltersPanel from '@/components/nps/NpsFiltersPanel.vue'

const npsStore = useNpsStore()
const loading = ref(true)

onMounted(async () => {
  try {
    await Promise.all([
      npsStore.fetchDashboard(),
      npsStore.fetchEvolucio(),
      npsStore.fetchPerFisioterapeuta(),
    ])
  } finally {
    loading.value = false
  }
})

async function handleFiltersChange() {
  loading.value = true
  try {
    await Promise.all([
      npsStore.fetchDashboard(),
      npsStore.fetchEvolucio(),
      npsStore.fetchPerFisioterapeuta(),
    ])
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="page-title">Dashboard NPS</h1>
    </div>

    <NpsFiltersPanel @change="handleFiltersChange" class="mb-6" />

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- NPS Summary Cards -->
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <NpsScoreCard
          v-if="npsStore.dashboard"
          :score="npsStore.dashboard.nps_score"
          :total="npsStore.dashboard.total_respostes"
          :mitjana="npsStore.dashboard.mitjana_puntuacio"
        />

        <div class="card">
          <h3 class="text-sm font-medium text-gray-500">Promotors (9-10)</h3>
          <p class="mt-2 text-3xl font-bold text-nps-promotor">
            {{ npsStore.dashboard?.promotors || 0 }}
          </p>
          <p class="text-sm text-gray-500">
            {{ npsStore.dashboard?.percent_promotors?.toFixed(1) || 0 }}%
          </p>
        </div>

        <div class="card">
          <h3 class="text-sm font-medium text-gray-500">Passius (7-8)</h3>
          <p class="mt-2 text-3xl font-bold text-nps-passiu">
            {{ npsStore.dashboard?.passius || 0 }}
          </p>
          <p class="text-sm text-gray-500">
            {{ npsStore.dashboard?.percent_passius?.toFixed(1) || 0 }}%
          </p>
        </div>

        <div class="card">
          <h3 class="text-sm font-medium text-gray-500">Detractors (0-6)</h3>
          <p class="mt-2 text-3xl font-bold text-nps-detractor">
            {{ npsStore.dashboard?.detractors || 0 }}
          </p>
          <p class="text-sm text-gray-500">
            {{ npsStore.dashboard?.percent_detractors?.toFixed(1) || 0 }}%
          </p>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid gap-6 lg:grid-cols-2">
        <div class="card">
          <h3 class="text-lg font-semibold mb-4">Evolució NPS</h3>
          <NpsEvolutionChart
            v-if="npsStore.evolucio.length"
            :data="npsStore.evolucio"
          />
          <p v-else class="text-gray-500 text-center py-8">No hi ha dades disponibles</p>
        </div>

        <div class="card">
          <h3 class="text-lg font-semibold mb-4">Distribució de puntuacions</h3>
          <NpsDistributionChart
            v-if="npsStore.dashboard?.distribucio"
            :data="npsStore.dashboard.distribucio"
          />
          <p v-else class="text-gray-500 text-center py-8">No hi ha dades disponibles</p>
        </div>
      </div>

      <!-- Top Fisioterapeutes -->
      <div class="card">
        <h3 class="text-lg font-semibold mb-4">Rànquing de Fisioterapeutes</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fisioterapeuta</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">NPS</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Mitjana</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Respostes</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="fisio in npsStore.perFisioterapeuta" :key="fisio.fisioterapeuta_id">
                <td class="px-4 py-3 text-sm">{{ fisio.nom }}</td>
                <td class="px-4 py-3 text-sm text-right">
                  <span
                    :class="[
                      'font-semibold',
                      fisio.nps_score >= 50 ? 'text-nps-promotor' :
                      fisio.nps_score >= 0 ? 'text-nps-passiu' : 'text-nps-detractor'
                    ]"
                  >
                    {{ fisio.nps_score }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-right">{{ fisio.mitjana.toFixed(1) }}</td>
                <td class="px-4 py-3 text-sm text-right">{{ fisio.total_respostes }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
