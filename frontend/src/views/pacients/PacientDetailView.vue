<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import type { Pacient, Participacio } from '@/types'

const route = useRoute()
const pacient = ref<Pacient | null>(null)
const historial = ref<Participacio[]>([])
const loading = ref(true)

const pacientId = parseInt(route.params.id as string)

onMounted(async () => {
  try {
    const [pacientRes, historialRes] = await Promise.all([
      api.get<Pacient>(`/pacients/${pacientId}`),
      api.get<Participacio[]>(`/pacients/${pacientId}/historial`),
    ])
    pacient.value = pacientRes.data
    historial.value = historialRes.data
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

    <div v-else-if="pacient">
      <h1 class="page-title mb-6">{{ pacient.nom }} {{ pacient.cognoms }}</h1>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="card">
          <h2 class="text-lg font-semibold mb-4">Dades personals</h2>
          <dl class="space-y-3">
            <div v-if="pacient.dni" class="flex justify-between">
              <dt class="text-gray-500">DNI</dt>
              <dd class="font-mono">{{ pacient.dni }}</dd>
            </div>
            <div v-if="pacient.cip" class="flex justify-between">
              <dt class="text-gray-500">CIP</dt>
              <dd class="font-mono">{{ pacient.cip }}</dd>
            </div>
            <div v-if="pacient.data_naixement" class="flex justify-between">
              <dt class="text-gray-500">Data naixement</dt>
              <dd>{{ new Date(pacient.data_naixement).toLocaleDateString('ca') }}</dd>
            </div>
            <div v-if="pacient.sexe" class="flex justify-between">
              <dt class="text-gray-500">Sexe</dt>
              <dd class="capitalize">{{ pacient.sexe }}</dd>
            </div>
            <div v-if="pacient.telefon" class="flex justify-between">
              <dt class="text-gray-500">Telèfon</dt>
              <dd>{{ pacient.telefon }}</dd>
            </div>
            <div v-if="pacient.email" class="flex justify-between">
              <dt class="text-gray-500">Email</dt>
              <dd>{{ pacient.email }}</dd>
            </div>
            <div v-if="pacient.centre" class="flex justify-between">
              <dt class="text-gray-500">Centre</dt>
              <dd>{{ pacient.centre.name }}</dd>
            </div>
            <div class="flex justify-between pt-3 border-t">
              <dt class="text-gray-500">Consentiment RGPD</dt>
              <dd>
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  pacient.consentiment_rgpd ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]">
                  {{ pacient.consentiment_rgpd ? 'Sí' : 'No' }}
                </span>
              </dd>
            </div>
          </dl>
        </div>

        <div class="card">
          <h2 class="text-lg font-semibold mb-4">Historial d'enquestes</h2>
          <div v-if="historial.length" class="space-y-3">
            <div
              v-for="p in historial"
              :key="p.id"
              class="p-3 border rounded-lg"
            >
              <h4 class="font-medium">{{ p.enquesta?.titol || 'Enquesta' }}</h4>
              <div class="mt-1 flex gap-4 text-sm text-gray-500">
                <span :class="[
                  'px-2 py-0.5 rounded-full text-xs',
                  p.estat === 'completada' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                ]">
                  {{ p.estat }}
                </span>
                <span>{{ new Date(p.created_at).toLocaleDateString('ca') }}</span>
              </div>
              <div v-if="p.nps_resultat" class="mt-2">
                <span :class="[
                  'font-semibold',
                  p.nps_resultat.categoria === 'promotor' ? 'text-nps-promotor' :
                  p.nps_resultat.categoria === 'passiu' ? 'text-nps-passiu' : 'text-nps-detractor'
                ]">
                  NPS: {{ p.nps_resultat.puntuacio }}
                </span>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-500 text-center py-4">No hi ha enquestes</p>
        </div>
      </div>
    </div>
  </div>
</template>
