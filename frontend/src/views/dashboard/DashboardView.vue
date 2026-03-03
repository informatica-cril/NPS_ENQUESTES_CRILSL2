<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useNpsStore } from '@/stores/nps'
import { useEnquestesStore } from '@/stores/enquestes'
import NpsScoreCard from '@/components/nps/NpsScoreCard.vue'
import NpsDistributionChart from '@/components/charts/NpsDistributionChart.vue'

const npsStore = useNpsStore()
const enquestesStore = useEnquestesStore()
const loading = ref(true)

onMounted(async () => {
  try {
    await Promise.all([
      npsStore.fetchDashboard(),
      npsStore.fetchEvolucio(),
      enquestesStore.fetchEnquestes({ per_page: 5, estat: 'activa' }),
    ])
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <h1 class="page-title mb-6">Dashboard</h1>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- NPS Summary -->
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <NpsScoreCard
          v-if="npsStore.dashboard"
          :score="npsStore.dashboard.nps_score"
          :total="npsStore.dashboard.total_respostes"
        />

        <div class="card">
          <h3 class="text-sm font-medium text-gray-500">Promotors</h3>
          <p class="mt-2 text-3xl font-bold text-nps-promotor">
            {{ npsStore.dashboard?.promotors || 0 }}
          </p>
          <p class="text-sm text-gray-500">
            {{ npsStore.dashboard?.percent_promotors || 0 }}%
          </p>
        </div>

        <div class="card">
          <h3 class="text-sm font-medium text-gray-500">Passius</h3>
          <p class="mt-2 text-3xl font-bold text-nps-passiu">
            {{ npsStore.dashboard?.passius || 0 }}
          </p>
          <p class="text-sm text-gray-500">
            {{ npsStore.dashboard?.percent_passius || 0 }}%
          </p>
        </div>

        <div class="card">
          <h3 class="text-sm font-medium text-gray-500">Detractors</h3>
          <p class="mt-2 text-3xl font-bold text-nps-detractor">
            {{ npsStore.dashboard?.detractors || 0 }}
          </p>
          <p class="text-sm text-gray-500">
            {{ npsStore.dashboard?.percent_detractors || 0 }}%
          </p>
        </div>
      </div>

      <!-- Charts row -->
      <div class="grid gap-6 lg:grid-cols-2">
        <div class="card">
          <h3 class="text-lg font-semibold mb-4">Distribució NPS</h3>
          <NpsDistributionChart
            v-if="npsStore.dashboard?.distribucio"
            :data="npsStore.dashboard.distribucio"
          />
          <p v-else class="text-gray-500 text-center py-8">No hi ha dades disponibles</p>
        </div>

        <div class="card">
          <h3 class="text-lg font-semibold mb-4">Enquestes actives</h3>
          <div v-if="enquestesStore.activeEnquestes.length" class="space-y-3">
            <RouterLink
              v-for="enquesta in enquestesStore.activeEnquestes"
              :key="enquesta.id"
              :to="`/enquestes/${enquesta.id}`"
              class="block p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
            >
              <h4 class="font-medium">{{ enquesta.titol }}</h4>
              <p class="text-sm text-gray-500">
                {{ enquesta.total_participacions || 0 }} participacions
              </p>
            </RouterLink>
          </div>
          <p v-else class="text-gray-500 text-center py-8">
            No hi ha enquestes actives
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
