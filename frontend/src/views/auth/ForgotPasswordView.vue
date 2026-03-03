<script setup lang="ts">
import { ref } from 'vue'
import { authService } from '@/services/auth'

const email = ref('')
const loading = ref(false)
const success = ref(false)
const error = ref('')

async function handleSubmit() {
  loading.value = true
  error.value = ''
  try {
    await authService.forgotPassword(email.value)
    success.value = true
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al enviar el correu'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Recuperar contrasenya
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Introdueix el teu correu per rebre instruccions
        </p>
      </div>

      <div v-if="success" class="rounded-md bg-green-50 p-4">
        <p class="text-sm text-green-700">
          Si el correu existeix, rebràs un enllaç per restablir la contrasenya.
        </p>
        <RouterLink to="/login" class="mt-2 inline-block text-primary-600 hover:text-primary-500">
          Tornar a l'inici de sessió
        </RouterLink>
      </div>

      <form v-else class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div v-if="error" class="rounded-md bg-red-50 p-4">
          <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <div>
          <label for="email" class="label">Correu electrònic</label>
          <input
            id="email"
            type="email"
            v-model="email"
            required
            class="input"
            placeholder="correu@exemple.com"
          />
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="btn-primary w-full py-3"
          >
            <span v-if="loading">Enviant...</span>
            <span v-else>Enviar instruccions</span>
          </button>
        </div>

        <div class="text-center">
          <RouterLink to="/login" class="text-sm text-primary-600 hover:text-primary-500">
            Tornar a l'inici de sessió
          </RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>
