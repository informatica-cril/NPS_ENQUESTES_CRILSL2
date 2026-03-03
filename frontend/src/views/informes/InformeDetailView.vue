<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import type { Informe } from '@/types'

const route = useRoute()
const informe = ref<Informe | null>(null)
const loading = ref(true)

const informeId = parseInt(route.params.id as string)

onMounted(async () => {
  try {
    const { data } = await api.get<Informe>(`/informes/${informeId}`)
    informe.value = data
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

    <div v-else-if="informe">
      <h1 class="page-title mb-6">{{ informe.titol }}</h1>

      <div class="card mb-6">
        <dl class="grid gap-4 md:grid-cols-3">
          <div>
            <dt class="text-sm text-gray-500">Tipus</dt>
            <dd class="mt-1 font-medium capitalize">{{ informe.tipus }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Període</dt>
            <dd class="mt-1">
              {{ new Date(informe.data_inici).toLocaleDateString('ca') }} -
              {{ new Date(informe.data_fi).toLocaleDateString('ca') }}
            </dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Estat</dt>
            <dd class="mt-1">
              <span :class="[
                'px-2 py-1 text-xs rounded-full',
                informe.estat === 'completat' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
              ]">
                {{ informe.estat }}
              </span>
            </dd>
          </div>
        </dl>
      </div>

      <div v-if="informe.dades" class="card">
        <h2 class="text-lg font-semibold mb-4">Dades de l'informe</h2>
        <pre class="text-sm bg-gray-50 p-4 rounded-lg overflow-auto">{{ JSON.stringify(informe.dades, null, 2) }}</pre>
      </div>
    </div>
  </div>
</template>
