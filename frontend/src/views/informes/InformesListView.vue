<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '@/services/api'
import type { Informe, PaginatedResponse } from '@/types'
import { PlusIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline'

const informes = ref<Informe[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await api.get<PaginatedResponse<Informe>>('/informes')
    informes.value = data.data
  } finally {
    loading.value = false
  }
})

function getEstatClass(estat: string): string {
  const classes: Record<string, string> = {
    pendent: 'bg-gray-100 text-gray-800',
    processant: 'bg-blue-100 text-blue-800',
    completat: 'bg-green-100 text-green-800',
    error: 'bg-red-100 text-red-800',
  }
  return classes[estat] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="page-title">Informes</h1>
      <RouterLink to="/informes/create" class="btn-primary">
        <PlusIcon class="h-5 w-5 mr-2" />
        Nou informe
      </RouterLink>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Títol</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipus</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Període</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estat</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="informe in informes" :key="informe.id">
              <td class="px-4 py-3">
                <RouterLink :to="`/informes/${informe.id}`" class="font-medium text-primary-600 hover:text-primary-800">
                  {{ informe.titol }}
                </RouterLink>
              </td>
              <td class="px-4 py-3 text-sm capitalize">{{ informe.tipus }}</td>
              <td class="px-4 py-3 text-sm">
                {{ new Date(informe.data_inici).toLocaleDateString('ca') }} -
                {{ new Date(informe.data_fi).toLocaleDateString('ca') }}
              </td>
              <td class="px-4 py-3">
                <span :class="['px-2 py-1 text-xs rounded-full', getEstatClass(informe.estat)]">
                  {{ informe.estat }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <a
                  v-if="informe.estat === 'completat' && informe.fitxer_path"
                  :href="`/api/informes/${informe.id}/download`"
                  class="text-primary-600 hover:text-primary-800"
                >
                  <DocumentArrowDownIcon class="h-5 w-5" />
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
