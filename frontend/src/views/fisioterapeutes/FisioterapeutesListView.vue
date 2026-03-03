<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '@/services/api'
import type { Fisioterapeuta, PaginatedResponse } from '@/types'

const fisioterapeutes = ref<Fisioterapeuta[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await api.get<PaginatedResponse<Fisioterapeuta>>('/fisioterapeutes')
    fisioterapeutes.value = data.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <h1 class="page-title mb-6">Fisioterapeutes</h1>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Especialitat</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Centre</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Núm. Col·legiat</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actiu</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="fisio in fisioterapeutes" :key="fisio.id">
              <td class="px-4 py-3">
                <RouterLink :to="`/fisioterapeutes/${fisio.id}`" class="font-medium text-primary-600 hover:text-primary-800">
                  {{ fisio.nom }} {{ fisio.cognoms }}
                </RouterLink>
              </td>
              <td class="px-4 py-3 text-sm">{{ fisio.especialitat || '-' }}</td>
              <td class="px-4 py-3 text-sm">{{ fisio.centre?.name || '-' }}</td>
              <td class="px-4 py-3 text-sm font-mono">{{ fisio.num_colegiat || '-' }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  fisio.actiu ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]">
                  {{ fisio.actiu ? 'Sí' : 'No' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
