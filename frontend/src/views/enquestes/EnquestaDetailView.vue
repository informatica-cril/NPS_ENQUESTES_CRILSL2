<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEnquestesStore } from '@/stores/enquestes'
import { enquestaService } from '@/services/enquestes'
import { PencilIcon, LinkIcon, ChartBarIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const enquestesStore = useEnquestesStore()
const loading = ref(true)
const stats = ref<any>(null)
const participacions = ref<any[]>([])

const enquestaId = parseInt(route.params.id as string)

onMounted(async () => {
  try {
    await enquestesStore.fetchEnquesta(enquestaId)
    stats.value = await enquestaService.getEstadistiques(enquestaId)
    const response = await enquestaService.listParticipacions(enquestaId, { per_page: 10 })
    participacions.value = response.data
  } finally {
    loading.value = false
  }
})

async function createParticipacio() {
  try {
    const response = await enquestaService.createParticipacio(enquestaId, {})
    alert(`Enllaç creat:\n${response.url}`)
    navigator.clipboard.writeText(response.url)
  } catch (e: any) {
    alert('Error al crear la participació')
  }
}

function getPublicUrl(): string {
  const baseUrl = window.location.origin
  return `${baseUrl}/enquesta/${enquestesStore.currentEnquesta?.slug}`
}
</script>

<template>
  <div>
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else-if="enquestesStore.currentEnquesta">
      <!-- Header -->
      <div class="flex items-start justify-between mb-6">
        <div>
          <h1 class="page-title">{{ enquestesStore.currentEnquesta.titol }}</h1>
          <p v-if="enquestesStore.currentEnquesta.descripcio" class="mt-1 text-gray-600">
            {{ enquestesStore.currentEnquesta.descripcio }}
          </p>
          <div class="mt-2 flex gap-2">
            <span :class="[
              'px-2 py-1 text-xs rounded-full',
              enquestesStore.currentEnquesta.estat === 'activa' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
            ]">
              {{ enquestesStore.currentEnquesta.estat }}
            </span>
            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
              {{ enquestesStore.currentEnquesta.tipus }}
            </span>
          </div>
        </div>
        <div class="flex gap-2">
          <RouterLink :to="`/enquestes/${enquestaId}/edit`" class="btn-secondary">
            <PencilIcon class="h-5 w-5 mr-2" />
            Editar
          </RouterLink>
          <button @click="createParticipacio" class="btn-primary">
            <LinkIcon class="h-5 w-5 mr-2" />
            Generar enllaç
          </button>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Stats -->
        <div class="card">
          <h3 class="font-semibold mb-4">Estadístiques</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-gray-500">Total participacions</dt>
              <dd class="font-semibold">{{ stats?.total_participacions || 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">En curs</dt>
              <dd>{{ stats?.en_curs || 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Pendents</dt>
              <dd>{{ stats?.pendents || 0 }}</dd>
            </div>
            <template v-if="stats?.nps">
              <div class="pt-3 border-t">
                <div class="flex justify-between">
                  <dt class="text-gray-500">NPS Score</dt>
                  <dd :class="[
                    'font-bold',
                    stats.nps.score >= 50 ? 'text-nps-promotor' :
                    stats.nps.score >= 0 ? 'text-nps-passiu' : 'text-nps-detractor'
                  ]">
                    {{ stats.nps.score ?? '-' }}
                  </dd>
                </div>
              </div>
            </template>
          </dl>
        </div>

        <!-- Public URL -->
        <div class="card">
          <h3 class="font-semibold mb-4">Enllaç públic</h3>
          <div class="p-3 bg-gray-50 rounded-lg">
            <code class="text-sm break-all">{{ getPublicUrl() }}</code>
          </div>
          <button
            @click="navigator.clipboard.writeText(getPublicUrl())"
            class="mt-3 btn-secondary w-full"
          >
            Copiar enllaç
          </button>
        </div>

        <!-- Questions summary -->
        <div class="card">
          <h3 class="font-semibold mb-4">Preguntes ({{ enquestesStore.currentEnquesta.preguntes?.length || 0 }})</h3>
          <ul class="space-y-2">
            <li
              v-for="pregunta in enquestesStore.currentEnquesta.preguntes?.slice(0, 5)"
              :key="pregunta.id"
              class="text-sm text-gray-600 truncate"
            >
              {{ pregunta.text_pregunta }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Recent participations -->
      <div class="card mt-6">
        <h3 class="font-semibold mb-4">Últimes participacions</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Token</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estat</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pacient</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="p in participacions" :key="p.id">
                <td class="px-4 py-3 text-sm font-mono">{{ p.token.substring(0, 8) }}...</td>
                <td class="px-4 py-3 text-sm">{{ p.estat }}</td>
                <td class="px-4 py-3 text-sm">{{ p.pacient?.nom_complet || 'Anònim' }}</td>
                <td class="px-4 py-3 text-sm">{{ new Date(p.created_at).toLocaleDateString('ca') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
