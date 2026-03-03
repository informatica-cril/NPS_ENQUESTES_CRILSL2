<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const form = ref({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
  phone: authStore.user?.phone || '',
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const success = ref('')
const error = ref('')

async function handleSubmit() {
  loading.value = true
  success.value = ''
  error.value = ''
  try {
    await authStore.updateProfile(form.value)
    success.value = 'Perfil actualitzat correctament'
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al actualitzar el perfil'
  } finally {
    loading.value = false
  }
}

async function handlePasswordSubmit() {
  loading.value = true
  success.value = ''
  error.value = ''
  try {
    await authStore.updateProfile(passwordForm.value)
    success.value = 'Contrasenya actualitzada correctament'
    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: '',
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al canviar la contrasenya'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h1 class="page-title mb-6">El meu perfil</h1>

    <div class="grid gap-6 lg:grid-cols-2">
      <!-- Profile info -->
      <div class="card">
        <h2 class="text-lg font-semibold mb-4">Informació personal</h2>

        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-4">
          <p class="text-sm text-green-700">{{ success }}</p>
        </div>

        <div v-if="error" class="mb-4 rounded-md bg-red-50 p-4">
          <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="label">Nom</label>
            <input type="text" v-model="form.name" class="input" required />
          </div>

          <div>
            <label class="label">Correu electrònic</label>
            <input type="email" v-model="form.email" class="input" required />
          </div>

          <div>
            <label class="label">Telèfon</label>
            <input type="tel" v-model="form.phone" class="input" />
          </div>

          <div>
            <button type="submit" :disabled="loading" class="btn-primary">
              Guardar canvis
            </button>
          </div>
        </form>
      </div>

      <!-- Change password -->
      <div class="card">
        <h2 class="text-lg font-semibold mb-4">Canviar contrasenya</h2>

        <form @submit.prevent="handlePasswordSubmit" class="space-y-4">
          <div>
            <label class="label">Contrasenya actual</label>
            <input type="password" v-model="passwordForm.current_password" class="input" required />
          </div>

          <div>
            <label class="label">Nova contrasenya</label>
            <input type="password" v-model="passwordForm.password" class="input" required minlength="8" />
          </div>

          <div>
            <label class="label">Confirmar nova contrasenya</label>
            <input type="password" v-model="passwordForm.password_confirmation" class="input" required />
          </div>

          <div>
            <button type="submit" :disabled="loading" class="btn-primary">
              Canviar contrasenya
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
