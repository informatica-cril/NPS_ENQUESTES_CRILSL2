<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { centreService } from '@/services/centres'
import type { Centre, Enquesta, Fisioterapeuta } from '@/types'

const router = useRouter()
const loading = ref(false)
const error = ref('')

const centres = ref<Centre[]>([])
const enquestes = ref<Enquesta[]>([])
const fisioterapeutes = ref<Fisioterapeuta[]>([])

const form = ref({
  titol: '',
  descripcio: '',
  tipus: 'nps' as const,
  enquesta_id: undefined as number | undefined,
  centre_id: undefined as number | undefined,
  fisioterapeuta_id: undefined as number | undefined,
  data_inici: '',
  data_fi: '',
})

onMounted(async () => {
  const [centresRes, enquestesRes, fisioRes] = await Promise.all([
    centreService.list(),
    api.get('/enquestes'),
    api.get('/fisioterapeutes'),
  ])
  centres.value = centresRes
  enquestes.value = enquestesRes.data.data
  fisioterapeutes.value = fisioRes.data.data
})

async function handleSubmit() {
  loading.value = true
  error.value = ''

  try {
    await api.post('/informes', form.value)
    router.push('/informes')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al crear l\'informe'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h1 class="page-title mb-6">Nou informe</h1>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div v-if="error" class="rounded-md bg-red-50 p-4">
        <p class="text-sm text-red-700">{{ error }}</p>
      </div>

      <div class="card">
        <div class="grid gap-4 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="label">Títol *</label>
            <input v-model="form.titol" type="text" class="input" required />
          </div>

          <div class="md:col-span-2">
            <label class="label">Descripció</label>
            <textarea v-model="form.descripcio" class="input" rows="2"></textarea>
          </div>

          <div>
            <label class="label">Tipus d'informe *</label>
            <select v-model="form.tipus" class="input">
              <option value="nps">NPS General</option>
              <option value="centre">Per Centre</option>
              <option value="fisioterapeuta">Per Fisioterapeuta</option>
              <option value="enquesta">Per Enquesta</option>
              <option value="general">General</option>
            </select>
          </div>

          <div v-if="form.tipus === 'centre'">
            <label class="label">Centre</label>
            <select v-model="form.centre_id" class="input">
              <option :value="undefined">Selecciona...</option>
              <option v-for="c in centres" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div v-if="form.tipus === 'fisioterapeuta'">
            <label class="label">Fisioterapeuta</label>
            <select v-model="form.fisioterapeuta_id" class="input">
              <option :value="undefined">Selecciona...</option>
              <option v-for="f in fisioterapeutes" :key="f.id" :value="f.id">
                {{ f.nom }} {{ f.cognoms }}
              </option>
            </select>
          </div>

          <div v-if="form.tipus === 'enquesta'">
            <label class="label">Enquesta</label>
            <select v-model="form.enquesta_id" class="input">
              <option :value="undefined">Selecciona...</option>
              <option v-for="e in enquestes" :key="e.id" :value="e.id">{{ e.titol }}</option>
            </select>
          </div>

          <div>
            <label class="label">Data inici *</label>
            <input v-model="form.data_inici" type="date" class="input" required />
          </div>

          <div>
            <label class="label">Data fi *</label>
            <input v-model="form.data_fi" type="date" class="input" required />
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-4">
        <RouterLink to="/informes" class="btn-secondary">Cancel·lar</RouterLink>
        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Creant...' : 'Crear informe' }}
        </button>
      </div>
    </form>
  </div>
</template>
