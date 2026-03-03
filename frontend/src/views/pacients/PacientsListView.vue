<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '@/services/api'
import type { Pacient, PaginatedResponse } from '@/types'

const pacients = ref<Pacient[]>([])
const loading = ref(true)
const search = ref('')

onMounted(async () => {
  await loadPacients()
})

async function loadPacients() {
  loading.value = true
  try {
    const { data } = await api.get<PaginatedResponse<Pacient>>('/pacients', {
      params: { search: search.value || undefined }
    })
    pacients.value = data.data
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h1 class="page-title mb-6">Pacients</h1>

    <div class="card mb-6">
      <div class="flex gap-4">
        <input
          v-model="search"
          type="search"
          placeholder="Cercar per nom, DNI o CIP..."
          class="input w-64"
          @keyup.enter="loadPacients"
        />
        <button @click="loadPacients" class="btn-secondary">Cercar</button>
      </div>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Centre</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telèfon</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">RGPD</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="pacient in pacients" :key="pacient.id">
              <td class="px-4 py-3">
                <RouterLink :to="`/pacients/${pacient.id}`" class="font-medium text-primary-600 hover:text-primary-800">
                  {{ pacient.nom }} {{ pacient.cognoms }}
                </RouterLink>
              </td>
              <td class="px-4 py-3 text-sm font-mono">{{ pacient.dni || '-' }}</td>
              <td class="px-4 py-3 text-sm">{{ pacient.centre?.name || '-' }}</td>
              <td class="px-4 py-3 text-sm">{{ pacient.telefon || '-' }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  pacient.consentiment_rgpd ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]">
                  {{ pacient.consentiment_rgpd ? 'Sí' : 'No' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
