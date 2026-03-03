<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEnquestesStore } from '@/stores/enquestes'
import { centreService } from '@/services/centres'
import type { Centre, Pregunta, PreguntaTipus } from '@/types'
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const enquestesStore = useEnquestesStore()

const isEdit = computed(() => !!route.params.id)
const enquestaId = computed(() => parseInt(route.params.id as string))

const loading = ref(false)
const centres = ref<Centre[]>([])
const error = ref('')

const form = ref({
  titol: '',
  descripcio: '',
  tipus: 'nps' as const,
  centre_id: undefined as number | undefined,
  data_inici: '',
  data_fi: '',
  anonima: false,
  requereix_autenticacio: false,
  temps_estimat_minuts: 5,
})

const preguntes = ref<Array<Partial<Pregunta>>>([])

const tipusOptions: Array<{ value: PreguntaTipus; label: string }> = [
  { value: 'nps', label: 'NPS (0-10)' },
  { value: 'escala', label: 'Escala (1-5)' },
  { value: 'text_curt', label: 'Text curt' },
  { value: 'text_llarg', label: 'Text llarg' },
  { value: 'opcio_unica', label: 'Opció única' },
  { value: 'opcio_multiple', label: 'Opció múltiple' },
  { value: 'si_no', label: 'Sí/No' },
  { value: 'valoracio_estrelles', label: 'Estrelles' },
]

onMounted(async () => {
  centres.value = await centreService.list({ active_only: true })

  if (isEdit.value) {
    await enquestesStore.fetchEnquesta(enquestaId.value)
    const enquesta = enquestesStore.currentEnquesta
    if (enquesta) {
      form.value = {
        titol: enquesta.titol,
        descripcio: enquesta.descripcio || '',
        tipus: enquesta.tipus,
        centre_id: enquesta.centre_id || undefined,
        data_inici: enquesta.data_inici || '',
        data_fi: enquesta.data_fi || '',
        anonima: enquesta.anonima,
        requereix_autenticacio: enquesta.requereix_autenticacio,
        temps_estimat_minuts: enquesta.temps_estimat_minuts || 5,
      }
      preguntes.value = enquesta.preguntes || []
    }
  }
})

function addPregunta() {
  preguntes.value.push({
    text_pregunta: '',
    tipus: 'text_curt',
    obligatoria: false,
    opcions: [],
  })
}

function removePregunta(index: number) {
  preguntes.value.splice(index, 1)
}

async function handleSubmit() {
  loading.value = true
  error.value = ''
  
  try {
    const data = {
      ...form.value,
      preguntes: preguntes.value,
    }

    if (isEdit.value) {
      await enquestesStore.updateEnquesta(enquestaId.value, data)
    } else {
      await enquestesStore.createEnquesta(data)
    }

    router.push('/enquestes')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al guardar l\'enquesta'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h1 class="page-title mb-6">{{ isEdit ? 'Editar enquesta' : 'Nova enquesta' }}</h1>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div v-if="error" class="rounded-md bg-red-50 p-4">
        <p class="text-sm text-red-700">{{ error }}</p>
      </div>

      <!-- Basic info -->
      <div class="card">
        <h2 class="text-lg font-semibold mb-4">Informació bàsica</h2>
        <div class="grid gap-4 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="label">Títol *</label>
            <input v-model="form.titol" type="text" class="input" required />
          </div>

          <div class="md:col-span-2">
            <label class="label">Descripció</label>
            <textarea v-model="form.descripcio" class="input" rows="3"></textarea>
          </div>

          <div>
            <label class="label">Tipus *</label>
            <select v-model="form.tipus" class="input">
              <option value="nps">NPS</option>
              <option value="satisfaccio">Satisfacció</option>
              <option value="qualitat">Qualitat</option>
              <option value="general">General</option>
            </select>
          </div>

          <div>
            <label class="label">Centre</label>
            <select v-model="form.centre_id" class="input">
              <option :value="undefined">Tots els centres</option>
              <option v-for="c in centres" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div>
            <label class="label">Data inici</label>
            <input v-model="form.data_inici" type="date" class="input" />
          </div>

          <div>
            <label class="label">Data fi</label>
            <input v-model="form.data_fi" type="date" class="input" />
          </div>

          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2">
              <input v-model="form.anonima" type="checkbox" class="rounded" />
              <span class="text-sm">Enquesta anònima</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Questions -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold">Preguntes</h2>
          <button type="button" @click="addPregunta" class="btn-secondary">
            <PlusIcon class="h-5 w-5 mr-2" />
            Afegir pregunta
          </button>
        </div>

        <div class="space-y-4">
          <div
            v-for="(pregunta, index) in preguntes"
            :key="index"
            class="p-4 border rounded-lg"
          >
            <div class="flex items-start justify-between mb-3">
              <span class="text-sm font-medium text-gray-500">Pregunta {{ index + 1 }}</span>
              <button type="button" @click="removePregunta(index)" class="text-red-500 hover:text-red-700">
                <TrashIcon class="h-5 w-5" />
              </button>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <div class="md:col-span-2">
                <label class="label">Text de la pregunta *</label>
                <input v-model="pregunta.text_pregunta" type="text" class="input" required />
              </div>

              <div>
                <label class="label">Tipus</label>
                <select v-model="pregunta.tipus" class="input">
                  <option v-for="opt in tipusOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
              </div>

              <div class="flex items-center">
                <label class="flex items-center gap-2">
                  <input v-model="pregunta.obligatoria" type="checkbox" class="rounded" />
                  <span class="text-sm">Obligatòria</span>
                </label>
              </div>
            </div>
          </div>

          <p v-if="!preguntes.length" class="text-center py-8 text-gray-500">
            Afegeix preguntes a l'enquesta
          </p>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-end gap-4">
        <RouterLink to="/enquestes" class="btn-secondary">Cancel·lar</RouterLink>
        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Guardant...' : (isEdit ? 'Guardar canvis' : 'Crear enquesta') }}
        </button>
      </div>
    </form>
  </div>
</template>
