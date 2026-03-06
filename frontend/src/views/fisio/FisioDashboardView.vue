<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNpsStore } from '@/stores/nps'
import api from '@/services/api'
const authStore = useAuthStore()
const npsStore = useNpsStore()
const loading = ref(true)
const loadingPacients = ref(false)
const pacients = ref<any[]>([])
const fisioId = computed(() => authStore.user?.fisioterapeuta?.id)
onMounted(async () => {
  try {
    await npsStore.fetchDashboard()
    await carregarPacients()
  } catch(e) {}
  finally { loading.value = false }
})
async function carregarPacients() {
  if (!fisioId.value) return
  loadingPacients.value = true
  try {
    const r = await api.get('/missatges/fisio/pacients')
    pacients.value = (r.data || []).map((p: any) => ({
      ...p,
      completades: p.enquestes?.filter((e: any) => e.respostes?.length > 0).length || 0,
      pendents: p.enquestes?.filter((e: any) => !e.respostes || e.respostes.length === 0).length || 0,
      total_enquestes: p.enquestes?.length || 0
    }))
  } catch(e) {} finally { loadingPacients.value = false }
}
</script>
<template>
  <div class="p-6 space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-gray-900">El meu Dashboard</h1>
      <p class="text-gray-600 mt-1">Evolució dels teus pacients i seguiment de les encuestas</p>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Estadísticas -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 shadow-sm p-5">
          <p class="text-sm text-blue-700 font-medium">NPS Global</p>
          <p class="text-3xl font-bold mt-2" :class="(npsStore.dashboard?.nps_score??0)>=0?'text-blue-600':'text-red-600'">
            {{ npsStore.dashboard?.nps_score??0 }}
          </p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 shadow-sm p-5">
          <p class="text-sm text-purple-700 font-medium">Pacients</p>
          <p class="text-3xl font-bold mt-2 text-purple-600">{{ pacients.length }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 shadow-sm p-5">
          <p class="text-sm text-green-700 font-medium">Respostes</p>
          <p class="text-3xl font-bold mt-2 text-green-600">{{ npsStore.dashboard?.total_respostes??0 }}</p>
        </div>
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl border border-amber-200 shadow-sm p-5">
          <p class="text-sm text-amber-700 font-medium">Promotors</p>
          <p class="text-3xl font-bold mt-2 text-amber-600">{{ npsStore.dashboard?.promotors??0 }}</p>
        </div>
      </div>

      <!-- Indicadors detallats -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
          <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          Indicadors de Qualitat
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div v-for="ind in [{label:'Qualitat',value:npsStore.dashboard?.mitja_qualitat,icon:'✨'},{label:'Puntualitat',value:npsStore.dashboard?.mitja_puntualitat,icon:'⏱️'},{label:'Tracte',value:npsStore.dashboard?.mitja_tracte,icon:'🤝'},{label:'Millora Percebuda',value:npsStore.dashboard?.mitja_millora,icon:'📈'},{label:'Comunicació',value:npsStore.dashboard?.mitja_comunicacio,icon:'💬'},{label:'Experiència Global',value:npsStore.dashboard?.mitja_experiencia,icon:'⭐'}]" :key="ind.label" class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:border-primary-300 transition-all">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-600 font-medium">{{ ind.label }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ ind.value!=null?Number(ind.value).toFixed(1):'—' }}<span class="text-sm text-gray-500">/5</span></p>
              </div>
              <span class="text-2xl">{{ ind.icon }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pacients i Encuestas -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12v-2a9 9 0 00-12-8.022A9 9 0 006 20z" />
            </svg>
            Els meus Pacients
          </h2>
          <span class="bg-primary-100 text-primary-700 px-3 py-1 rounded-full text-sm font-semibold">{{ pacients.length }} pacients</span>
        </div>

        <div v-if="loadingPacients" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        </div>

        <div v-else-if="pacients.length===0" class="p-12 text-center">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
          <p class="text-gray-500 text-lg font-medium">Sense pacients assignats</p>
          <p class="text-gray-400 mt-1">Aviat tindràs pacients assignats</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Pacient</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wide">Completades</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wide">Pendents</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wide">Total</th>
                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wide">Estat</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="p in pacients" :key="p.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                      {{ p.nom_complet?.split(' ').map((n:string)=>n[0]).slice(0,2).join('') }}
                    </div>
                    <div>
                      <p class="font-semibold text-gray-900">{{ p.nom_complet }}</p>
                      <p v-if="p.email" class="text-xs text-gray-500">{{ p.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm" :class="p.completades>0?'bg-green-100 text-green-700':'bg-gray-100 text-gray-400'">
                    ✓ {{ p.completades }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm" :class="p.pendents>0?'bg-amber-100 text-amber-700':'bg-gray-100 text-gray-400'">
                    ⏳ {{ p.pendents }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center text-sm font-medium text-gray-700">{{ p.total_enquestes }}</td>
                <td class="px-6 py-4 text-right">
                  <span v-if="p.total_enquestes===0" class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Sense enquestes</span>
                  <span v-else-if="p.pendents>0" class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">⏳ Pendent</span>
                  <span v-else class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">✓ Completa</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
