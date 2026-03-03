<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import type { Fisioterapeuta } from '@/types'

const route = useRoute()
const fisio = ref<Fisioterapeuta | null>(null)
const stats = ref<any>(null)
const loading = ref(true)

const fisioId = parseInt(route.params.id as string)

onMounted(async () => {
  try {
    const [fisioRes, statsRes] = await Promise.all([
      api.get<Fisioterapeuta>(`/fisioterapeutes/${fisioId}`),
      api.get(`/fisioterapeutes/${fisioId}/estadistiques`),
    ])
    fisio.value = fisioRes.data
    stats.value = statsRes.data
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

    <div v-else-if="fisio">
      <h1 class="page-title mb-6">{{ fisio.nom }} {{ fisio.cognoms }}</h1>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="card">
          <h2 class="text-lg font-semibold mb-4">Informació</h2>
          <dl class="space-y-3">
            <div v-if="fisio.especialitat" class="flex justify-between">
              <dt class="text-gray-500">Especialitat</dt>
              <dd>{{ fisio.especialitat }}</dd>
            </div>
            <div v-if="fisio.num_colegiat" class="flex justify-between">
              <dt class="text-gray-500">Núm. Col·legiat</dt>
              <dd class="font-mono">{{ fisio.num_colegiat }}</dd>
            </div>
            <div v-if="fisio.centre" class="flex justify-between">
              <dt class="text-gray-500">Centre</dt>
              <dd>{{ fisio.centre.name }}</dd>
            </div>
            <div v-if="fisio.data_alta" class="flex justify-between">
              <dt class="text-gray-500">Data d'alta</dt>
              <dd>{{ new Date(fisio.data_alta).toLocaleDateString('ca') }}</dd>
            </div>
          </dl>
        </div>

        <div class="card">
          <h2 class="text-lg font-semibold mb-4">Estadístiques NPS</h2>
          <div v-if="stats">
            <div class="text-center mb-4">
              <span :class="[
                'text-4xl font-bold',
                stats.nps_score >= 50 ? 'text-nps-promotor' :
                stats.nps_score >= 0 ? 'text-nps-passiu' : 'text-nps-detractor'
              ]">
                {{ stats.nps_score ?? '-' }}
              </span>
              <p class="text-sm text-gray-500">NPS Score</p>
            </div>
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between">
                <dt class="text-gray-500">Total respostes</dt>
                <dd>{{ stats.total_respostes }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500">Promotors</dt>
                <dd class="text-nps-promotor">{{ stats.promotors }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500">Passius</dt>
                <dd class="text-nps-passiu">{{ stats.passius }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500">Detractors</dt>
                <dd class="text-nps-detractor">{{ stats.detractors }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
