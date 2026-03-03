<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useNpsStore } from '@/stores/nps'
import NpsFiltersPanel from '@/components/nps/NpsFiltersPanel.vue'
import { format } from 'date-fns'
import { ca } from 'date-fns/locale'

const npsStore = useNpsStore()
const loading = ref(true)
const categoriaFilter = ref<string>('')

onMounted(async () => {
  try {
    await npsStore.fetchComentaris()
  } finally {
    loading.value = false
  }
})

async function handleFiltersChange() {
  loading.value = true
  try {
    await npsStore.fetchComentaris({ categoria: categoriaFilter.value || undefined })
  } finally {
    loading.value = false
  }
}

function formatDate(date: string) {
  return format(new Date(date), 'dd MMM yyyy', { locale: ca })
}

function getNpsClass(puntuacio: number): string {
  if (puntuacio >= 9) return 'bg-nps-promotor'
  if (puntuacio >= 7) return 'bg-nps-passiu'
  return 'bg-nps-detractor'
}
</script>

<template>
  <div>
    <h1 class="page-title mb-6">Comentaris NPS</h1>

    <div class="flex flex-wrap gap-4 mb-6">
      <NpsFiltersPanel @change="handleFiltersChange" />
      
      <select
        v-model="categoriaFilter"
        @change="handleFiltersChange"
        class="input w-auto"
      >
        <option value="">Totes les categories</option>
        <option value="promotor">Promotors</option>
        <option value="passiu">Passius</option>
        <option value="detractor">Detractors</option>
      </select>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="comentari in npsStore.comentaris"
        :key="comentari.id"
        class="card"
      >
        <div class="flex items-start gap-4">
          <div
            :class="[
              'flex items-center justify-center w-12 h-12 rounded-full text-white font-bold',
              getNpsClass(comentari.puntuacio)
            ]"
          >
            {{ comentari.puntuacio }}
          </div>
          <div class="flex-1">
            <p class="text-gray-900">{{ comentari.comentari }}</p>
            <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-500">
              <span>{{ formatDate(comentari.data) }}</span>
              <span v-if="comentari.centre">{{ comentari.centre.name }}</span>
              <span v-if="comentari.fisioterapeuta">{{ comentari.fisioterapeuta.nom }} {{ comentari.fisioterapeuta.cognoms }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!npsStore.comentaris.length" class="text-center py-12 text-gray-500">
        No hi ha comentaris per mostrar
      </div>

      <!-- Pagination -->
      <div v-if="npsStore.comentarisPagination.lastPage > 1" class="flex justify-center gap-2">
        <button
          v-for="page in npsStore.comentarisPagination.lastPage"
          :key="page"
          @click="npsStore.fetchComentaris({ page })"
          :class="[
            'px-3 py-1 rounded',
            page === npsStore.comentarisPagination.currentPage
              ? 'bg-primary-600 text-white'
              : 'bg-gray-100 hover:bg-gray-200'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>
