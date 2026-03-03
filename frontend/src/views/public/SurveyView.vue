<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { publicService } from '@/services/public'
import type { Enquesta, Participacio, Pregunta } from '@/types'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const enquesta = ref<Enquesta | null>(null)
const participacio = ref<Participacio | null>(null)
const isCompleted = ref(false)

const responses = ref<Record<number, any>>({})
const comentariNps = ref('')

const slug = route.params.slug as string
const token = route.query.token as string | undefined

onMounted(async () => {
  try {
    if (token) {
      const result = await publicService.getParticipacioByToken(token)
      if ('completada' in result && result.completada) {
        isCompleted.value = true
        return
      }
      participacio.value = result as Participacio
      enquesta.value = participacio.value.enquesta || null
    } else {
      enquesta.value = await publicService.getEnquestaBySlug(slug)
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al carregar l\'enquesta'
  } finally {
    loading.value = false
  }
})

const progress = computed(() => {
  if (!enquesta.value?.preguntes?.length) return 0
  const answered = Object.keys(responses.value).length
  return Math.round((answered / enquesta.value.preguntes.length) * 100)
})

async function handleSubmit() {
  if (!token) {
    error.value = 'Token no vàlid'
    return
  }

  submitting.value = true
  error.value = ''

  try {
    const respostesArray = Object.entries(responses.value).map(([preguntaId, valor]) => ({
      pregunta_id: parseInt(preguntaId),
      valor,
    }))

    await publicService.submitRespostes(token, {
      respostes: respostesArray,
      comentari_nps: comentariNps.value || undefined,
    })

    router.push(`/enquesta/${slug}/completada`)
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al enviar les respostes'
  } finally {
    submitting.value = false
  }
}

function getNpsLabel(value: number): string {
  if (value >= 9) return 'Molt probable'
  if (value >= 7) return 'Probable'
  if (value >= 5) return 'Neutral'
  if (value >= 3) return 'Poc probable'
  return 'Gens probable'
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4">
      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <!-- Already completed -->
      <div v-else-if="isCompleted" class="card text-center py-12">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Enquesta completada</h1>
        <p class="text-gray-600">Ja has completat aquesta enquesta. Gràcies!</p>
      </div>

      <!-- Error -->
      <div v-else-if="error && !enquesta" class="card text-center py-12">
        <h1 class="text-2xl font-bold text-red-600 mb-4">Error</h1>
        <p class="text-gray-600">{{ error }}</p>
      </div>

      <!-- Survey -->
      <template v-else-if="enquesta">
        <!-- Header -->
        <div class="card mb-6">
          <h1 class="text-2xl font-bold text-gray-900">{{ enquesta.titol }}</h1>
          <p v-if="enquesta.descripcio" class="mt-2 text-gray-600">{{ enquesta.descripcio }}</p>
          <p v-if="enquesta.temps_estimat_minuts" class="mt-2 text-sm text-gray-500">
            Temps estimat: {{ enquesta.temps_estimat_minuts }} minuts
          </p>
        </div>

        <!-- Progress -->
        <div class="mb-6">
          <div class="flex justify-between text-sm text-gray-600 mb-1">
            <span>Progrés</span>
            <span>{{ progress }}%</span>
          </div>
          <div class="h-2 bg-gray-200 rounded-full">
            <div
              class="h-2 bg-primary-600 rounded-full transition-all"
              :style="{ width: progress + '%' }"
            ></div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div v-if="error" class="rounded-md bg-red-50 p-4">
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>

          <!-- Questions -->
          <div
            v-for="pregunta in enquesta.preguntes"
            :key="pregunta.id"
            class="card"
          >
            <label class="block text-lg font-medium text-gray-900 mb-4">
              {{ pregunta.text_pregunta }}
              <span v-if="pregunta.obligatoria" class="text-red-500">*</span>
            </label>

            <!-- NPS -->
            <div v-if="pregunta.tipus === 'nps'" class="space-y-4">
              <div class="flex justify-between gap-2">
                <button
                  v-for="n in 11"
                  :key="n - 1"
                  type="button"
                  @click="responses[pregunta.id] = n - 1"
                  :class="[
                    'w-10 h-10 rounded-lg font-semibold text-sm transition-all',
                    responses[pregunta.id] === n - 1
                      ? (n - 1 >= 9 ? 'bg-nps-promotor text-white' : n - 1 >= 7 ? 'bg-nps-passiu text-white' : 'bg-nps-detractor text-white')
                      : 'bg-gray-100 hover:bg-gray-200'
                  ]"
                >
                  {{ n - 1 }}
                </button>
              </div>
              <div class="flex justify-between text-sm text-gray-500">
                <span>Gens probable</span>
                <span>Molt probable</span>
              </div>
              <div v-if="responses[pregunta.id] !== undefined">
                <label class="label">Comentari (opcional)</label>
                <textarea
                  v-model="comentariNps"
                  class="input"
                  rows="3"
                  placeholder="Explica'ns més sobre la teva valoració..."
                ></textarea>
              </div>
            </div>

            <!-- Scale 1-5 -->
            <div v-else-if="pregunta.tipus === 'escala' || pregunta.tipus === 'valoracio_estrelles'" class="flex gap-2">
              <button
                v-for="n in 5"
                :key="n"
                type="button"
                @click="responses[pregunta.id] = n"
                :class="[
                  'w-12 h-12 rounded-lg font-semibold transition-all',
                  responses[pregunta.id] === n
                    ? 'bg-primary-600 text-white'
                    : 'bg-gray-100 hover:bg-gray-200'
                ]"
              >
                {{ pregunta.tipus === 'valoracio_estrelles' ? '★' : n }}
              </button>
            </div>

            <!-- Yes/No -->
            <div v-else-if="pregunta.tipus === 'si_no'" class="flex gap-4">
              <button
                type="button"
                @click="responses[pregunta.id] = 'si'"
                :class="[
                  'px-6 py-3 rounded-lg font-semibold transition-all',
                  responses[pregunta.id] === 'si'
                    ? 'bg-green-600 text-white'
                    : 'bg-gray-100 hover:bg-gray-200'
                ]"
              >
                Sí
              </button>
              <button
                type="button"
                @click="responses[pregunta.id] = 'no'"
                :class="[
                  'px-6 py-3 rounded-lg font-semibold transition-all',
                  responses[pregunta.id] === 'no'
                    ? 'bg-red-600 text-white'
                    : 'bg-gray-100 hover:bg-gray-200'
                ]"
              >
                No
              </button>
            </div>

            <!-- Single choice -->
            <div v-else-if="pregunta.tipus === 'opcio_unica'" class="space-y-2">
              <label
                v-for="opcio in pregunta.opcions"
                :key="opcio"
                class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                :class="{ 'border-primary-500 bg-primary-50': responses[pregunta.id] === opcio }"
              >
                <input
                  type="radio"
                  :name="`pregunta-${pregunta.id}`"
                  :value="opcio"
                  v-model="responses[pregunta.id]"
                  class="mr-3"
                />
                {{ opcio }}
              </label>
            </div>

            <!-- Multiple choice -->
            <div v-else-if="pregunta.tipus === 'opcio_multiple'" class="space-y-2">
              <label
                v-for="opcio in pregunta.opcions"
                :key="opcio"
                class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
              >
                <input
                  type="checkbox"
                  :value="opcio"
                  v-model="responses[pregunta.id]"
                  class="mr-3 rounded"
                />
                {{ opcio }}
              </label>
            </div>

            <!-- Short text -->
            <input
              v-else-if="pregunta.tipus === 'text_curt'"
              type="text"
              v-model="responses[pregunta.id]"
              class="input"
              :required="pregunta.obligatoria"
            />

            <!-- Long text -->
            <textarea
              v-else-if="pregunta.tipus === 'text_llarg'"
              v-model="responses[pregunta.id]"
              class="input"
              rows="4"
              :required="pregunta.obligatoria"
            ></textarea>
          </div>

          <!-- Submit -->
          <div class="card">
            <button
              type="submit"
              :disabled="submitting"
              class="btn-primary w-full py-3 text-lg"
            >
              {{ submitting ? 'Enviant...' : 'Enviar respostes' }}
            </button>
          </div>
        </form>
      </template>
    </div>
  </div>
</template>
