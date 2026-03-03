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
    <div v-else class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Títol</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipus</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estat</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Participacions</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="enquesta in enquestesStore.enquestes" :key="enquesta.id">
              <td class="px-4 py-3">
                <RouterLink :to="`/enquestes/${enquesta.id}`" class="font-medium text-primary-600 hover:text-primary-800">
                  {{ enquesta.titol }}
                </RouterLink>
                <p v-if="enquesta.descripcio" class="text-sm text-gray-500 truncate max-w-xs">
                  {{ enquesta.descripcio }}
                </p>
              </td>
              <td class="px-4 py-3 text-sm capitalize">{{ enquesta.tipus }}</td>
              <td class="px-4 py-3">
                <span :class="['px-2 py-1 text-xs rounded-full', getEstatBadgeClass(enquesta.estat)]">
                  {{ enquesta.estat }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-right">{{ enquesta.total_participacions || 0 }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex justify-end gap-2">
                  <RouterLink :to="`/enquestes/${enquesta.id}/edit`" class="text-gray-400 hover:text-primary-600">
                    <PencilIcon class="h-5 w-5" />
                  </RouterLink>
                  <button @click="handleDuplicate(enquesta.id)" class="text-gray-400 hover:text-primary-600">
                    <DocumentDuplicateIcon class="h-5 w-5" />
                  </button>
                  <button @click="handleDelete(enquesta.id)" class="text-gray-400 hover:text-red-600">
                    <TrashIcon class="h-5 w-5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!enquestesStore.enquestes.length" class="text-center py-12 text-gray-500">
        No s'han trobat enquestes
      </div>
    </div>
  </div>
</template>
