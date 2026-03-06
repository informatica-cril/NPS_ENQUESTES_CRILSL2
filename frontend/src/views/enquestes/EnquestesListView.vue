<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useEnquestesStore } from '@/stores/enquestes'
import { PlusIcon, PencilIcon, TrashIcon, DocumentDuplicateIcon } from '@heroicons/vue/24/outline'
import type { EnquestaEstat, EnquestaTipus } from '@/types'

const enquestesStore = useEnquestesStore()
const loading = ref(true)
const filters = ref({
  search: '',
  estat: '' as EnquestaEstat | '',
  tipus: '' as EnquestaTipus | '',
})

onMounted(async () => {
  try {
    await enquestesStore.fetchEnquestes()
  } finally {
    loading.value = false
  }
})

async function handleSearch() {
  loading.value = true
  try {
    await enquestesStore.fetchEnquestes({
      search: filters.value.search || undefined,
      estat: filters.value.estat || undefined,
      tipus: filters.value.tipus || undefined,
    })
  } finally {
    loading.value = false
  }
}

async function handleDuplicate(id: number) {
  if (confirm('Vols duplicar aquesta enquesta?')) {
    await enquestesStore.duplicateEnquesta(id)
  }
}

async function handleDelete(id: number) {
  if (confirm('Estàs segur que vols eliminar aquesta enquesta?')) {
    await enquestesStore.deleteEnquesta(id)
  }
}

function getEstatBadgeClass(estat: EnquestaEstat): string {
  const classes: Record<EnquestaEstat, string> = {
    esborrany: 'bg-gray-100 text-gray-800',
    activa: 'bg-green-100 text-green-800',
    tancada: 'bg-red-100 text-red-800',
    arxivada: 'bg-yellow-100 text-yellow-800',
  }
  return classes[estat] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="page-title">Enquestes</h1>
      <RouterLink to="/enquestes/create" class="btn-primary">
        <PlusIcon class="h-5 w-5 mr-2" />
        Nova enquesta
      </RouterLink>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="flex flex-wrap gap-4">
        <input
          v-model="filters.search"
          type="search"
          placeholder="Cercar..."
          class="input w-64"
          @keyup.enter="handleSearch"
        />
        <select v-model="filters.estat" @change="handleSearch" class="input w-auto">
          <option value="">Tots els estats</option>
          <option value="esborrany">Esborrany</option>
          <option value="activa">Activa</option>
          <option value="tancada">Tancada</option>
          <option value="arxivada">Arxivada</option>
        </select>
        <select v-model="filters.tipus" @change="handleSearch" class="input w-auto">
          <option value="">Tots els tipus</option>
          <option value="nps">NPS</option>
          <option value="satisfaccio">Satisfacció</option>
          <option value="qualitat">Qualitat</option>
          <option value="general">General</option>
        </select>
        <button @click="handleSearch" class="btn-secondary">Cercar</button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <!-- List -->
    <div v-else>
      <div v-if="!enquestesStore.enquestes.length" class="card text-center py-12">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-gray-500 text-lg">No s'han trobat enquestes</p>
        <p class="text-gray-400 text-sm mt-2">Crea la primera enquesta per comenzar</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="enquesta in enquestesStore.enquestes" :key="enquesta.id" class="card border-l-4 border-primary-500 hover:shadow-lg transition-all" style="border-left-color: var(--primary-color)">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <RouterLink :to="`/enquestes/${enquesta.id}`" class="font-semibold text-gray-900 text-lg hover:text-primary-600 line-clamp-2">
                {{ enquesta.titol }}
              </RouterLink>
              <p v-if="enquesta.descripcio" class="text-sm text-gray-500 mt-1 line-clamp-2">
                {{ enquesta.descripcio }}
              </p>
            </div>
            <span :class="['px-3 py-1 text-xs font-semibold rounded-full whitespace-nowrap ml-2', getEstatBadgeClass(enquesta.estat)]">
              {{ enquesta.estat }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-4 my-4 pt-4 border-t border-gray-100">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide">Tipus</p>
              <p class="font-medium text-gray-900 capitalize">{{ enquesta.tipus }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide">Participacions</p>
              <p class="font-medium text-gray-900">{{ enquesta.total_participacions || 0 }}</p>
            </div>
          </div>

          <div class="flex gap-2 pt-4 border-t border-gray-100">
            <RouterLink :to="`/enquestes/${enquesta.id}/edit`" class="flex-1 btn-secondary text-xs flex items-center justify-center gap-1">
              <PencilIcon class="h-4 w-4" />
              Editar
            </RouterLink>
            <RouterLink :to="`/enquestes/${enquesta.id}`" class="flex-1 btn-primary text-xs flex items-center justify-center gap-1">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              Ver
            </RouterLink>
            <button @click="handleDuplicate(enquesta.id)" class="px-3 btn-secondary text-xs hover:bg-blue-50">
              <DocumentDuplicateIcon class="h-4 w-4" />
            </button>
            <button @click="handleDelete(enquesta.id)" class="px-3 btn-secondary text-xs hover:bg-red-50 hover:text-red-600">
              <TrashIcon class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
