<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '@/services/api'
import type { Pacient, PaginatedResponse } from '@/types'
const pacients = ref<Pacient[]>([])
const loading = ref(true)
const search = ref('')
const filtre = ref<'tots'|'sense_enquestes'>('tots')
const senseEnquestes = ref<any[]>([])
const loadingSE = ref(false)
onMounted(async () => { await loadPacients() })
async function loadPacients() {
  loading.value = true
  try {
    const { data } = await api.get<PaginatedResponse<Pacient>>('/pacients', { params: { search: search.value || undefined } })
    pacients.value = data.data
  } finally { loading.value = false }
}
async function loadSenseEnquestes() {
  if (senseEnquestes.value.length > 0) return
  loadingSE.value = true
  try {
    const { data } = await api.get('/pacients/sense-enquestes')
    senseEnquestes.value = data
  } finally { loadingSE.value = false }
}
async function canviarFiltre(f: 'tots'|'sense_enquestes') {
  filtre.value = f
  if (f === 'sense_enquestes') await loadSenseEnquestes()
}
</script>
<template>
  <div>
    <h1 class="page-title mb-6">Pacients</h1>
    <div class="card mb-6">
      <div class="flex flex-wrap gap-4 items-center">
        <input v-model="search" type="search" placeholder="Cercar per nom, DNI o CIP..." class="input w-64" @keyup.enter="loadPacients" />
        <button @click="loadPacients" class="btn-secondary">Cercar</button>
        <div class="ml-auto flex gap-2">
          <button @click="canviarFiltre('tots')" :class="filtre==='tots'?'bg-primary-600 text-white':'bg-gray-100 text-gray-700'" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">Tots</button>
          <button @click="canviarFiltre('sense_enquestes')" :class="filtre==='sense_enquestes'?'bg-amber-500 text-white':'bg-gray-100 text-gray-700'" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
            <span>Sense enquestes</span>
            <span v-if="senseEnquestes.length>0" class="bg-white bg-opacity-30 text-xs font-bold px-1.5 py-0.5 rounded-full">{{ senseEnquestes.length }}</span>
          </button>
        </div>
      </div>
    </div>
    <div v-if="filtre==='tots'">
      <div v-if="loading" class="flex items-center justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div></div>
      <div v-else class="card">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead><tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Centre</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telèfon</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">RGPD</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="pacient in pacients" :key="pacient.id">
                <td class="px-4 py-3"><RouterLink :to="`/pacients/${pacient.id}`" class="font-medium text-primary-600 hover:text-primary-800">{{ pacient.nom }} {{ pacient.cognoms }}</RouterLink></td>
                <td class="px-4 py-3 text-sm font-mono">{{ pacient.dni || '-' }}</td>
                <td class="px-4 py-3 text-sm">{{ pacient.centre?.name || '-' }}</td>
                <td class="px-4 py-3 text-sm">{{ pacient.telefon || '-' }}</td>
                <td class="px-4 py-3 text-center"><span :class="['px-2 py-1 text-xs rounded-full', pacient.consentiment_rgpd?'bg-green-100 text-green-800':'bg-red-100 text-red-800']">{{ pacient.consentiment_rgpd ? 'Sí' : 'No' }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div v-else-if="filtre==='sense_enquestes'">
      <div v-if="loadingSE" class="flex items-center justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div></div>
      <div v-else-if="senseEnquestes.length===0" class="card text-center py-12">
        <p class="text-4xl mb-3">🎉</p>
        <p class="text-gray-700 font-semibold">Tots els pacients han participat en alguna enquesta</p>
      </div>
      <div v-else class="card">
        <div class="flex items-center gap-2 mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
          <span class="text-amber-600">⚠️</span>
          <p class="text-sm text-amber-700 font-medium">{{ senseEnquestes.length }} pacient{{ senseEnquestes.length>1?'s':'' }} no ha{{ senseEnquestes.length>1?'n':'' }} participat en cap enquesta</p>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead><tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Centre</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alta</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actiu</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="p in senseEnquestes" :key="p.id" class="hover:bg-amber-50 transition-colors">
                <td class="px-4 py-3"><RouterLink :to="`/pacients/${p.id}`" class="font-medium text-primary-600 hover:text-primary-800">{{ p.nom_complet }}</RouterLink></td>
                <td class="px-4 py-3 text-sm">{{ p.email || '-' }}</td>
                <td class="px-4 py-3 text-sm">{{ p.centre?.name || '-' }}</td>
                <td class="px-4 py-3 text-sm">{{ p.data_alta ? new Date(p.data_alta).toLocaleDateString('ca-ES') : '-' }}</td>
                <td class="px-4 py-3 text-center"><span :class="['px-2 py-1 text-xs rounded-full', p.actiu?'bg-green-100 text-green-800':'bg-gray-100 text-gray-500']">{{ p.actiu?'Sí':'No' }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
