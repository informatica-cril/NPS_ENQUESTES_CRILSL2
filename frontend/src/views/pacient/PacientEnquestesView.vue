<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const authStore = useAuthStore()
const loading = ref(true)
const participacions = ref<any[]>([])
const error = ref('')

const pacientId = computed(() => authStore.user?.pacient?.id)
const pendents = computed(() => participacions.value.filter(p => p.estat !== 'completada'))
const completades = computed(() => participacions.value.filter(p => p.estat === 'completada'))

onMounted(async () => {
  try {
    if (pacientId.value) {
      const r = await api.get(`/pacients/${pacientId.value}/historial`)
      participacions.value = r.data.data || r.data || []
    }
  } catch (e) {
    error.value = "No s'han pogut carregar les encuestas."
  } finally {
    loading.value = false
  }
})

function formatDate(d: string) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ca-ES', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Les meves Enquestes</h1>
        <p class="text-gray-600 mt-2">Benvingut/da, {{ authStore.user?.name }}</p>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
        {{ error }}
      </div>

      <div v-else class="space-y-8">
        <!-- Pendents Section -->
        <div v-if="pendents.length > 0">
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
              <span class="w-8 h-8 flex items-center justify-center bg-amber-100 text-amber-700 rounded-full font-bold text-sm">⏳</span>
              Enquestes Pendents ({{ pendents.length }})
            </h2>
            <p class="text-gray-600 mt-1">Completa les encuestas que tens pendent</p>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div
              v-for="p in pendents"
              :key="p.id"
              class="bg-white rounded-xl border-2 border-amber-200 shadow-sm p-6 hover:shadow-md transition-all"
            >
              <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900">{{ p.enquesta?.titol || 'Enquesta' }}</h3>
                <p v-if="p.enquesta?.descripcio" class="text-sm text-gray-600 mt-1 line-clamp-2">
                  {{ p.enquesta.descripcio }}
                </p>
              </div>

              <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="text-sm text-gray-500">
                  <p class="font-medium">Rebuda el {{ formatDate(p.created_at) }}</p>
                </div>
                <a
                  :href="`/enquesta/${p.enquesta?.slug}?token=${p.token}`"
                  class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-bold px-6 py-3 rounded-lg transition-all transform hover:scale-105 shadow-sm hover:shadow-md"
                >
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  Respondre Ara
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Completades Section -->
        <div v-if="completades.length > 0">
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
              <span class="w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded-full font-bold text-sm">✓</span>
              Enquestes Completades ({{ completades.length }})
            </h2>
            <p class="text-gray-600 mt-1">Gràcies per completar aquestes encuestas</p>
          </div>

          <div class="space-y-3">
            <div
              v-for="p in completades"
              :key="p.id"
              class="bg-white rounded-xl border border-green-200 shadow-sm p-5 hover:shadow-md transition-all"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="font-semibold text-gray-900">{{ p.enquesta?.titol || 'Enquesta' }}</h3>
                  <p class="text-sm text-gray-500 mt-1">Completada el {{ formatDate(p.completed_at || p.updated_at) }}</p>
                </div>
                <span class="inline-flex items-center gap-2 bg-green-100 text-green-700 px-4 py-2 rounded-lg font-medium text-sm">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  Completada
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="participacions.length === 0" class="text-center py-16">
          <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="text-gray-500 text-lg font-medium">No has rebut cap enquesta</p>
          <p class="text-gray-400 mt-1">Aviat rebràs nova documentació per completar</p>
        </div>
      </div>
    </div>
  </div>
</template>
