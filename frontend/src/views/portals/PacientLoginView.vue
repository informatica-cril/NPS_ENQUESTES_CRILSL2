<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const cip = ref('')
const dni = ref('')
const error = ref('')

async function onSubmit() {
  try {
    // CIP com a username, DNI com a password
    await authStore.login({ username: cip.value, password: dni.value })
    if (authStore.user?.role !== 'pacient') {
      authStore.logout()
      error.value = 'Accés no autoritzat per a aquest portal'
      return
    }
    router.push('/pacient/dashboard')
  } catch (e) {
    error.value = 'CIP o DNI incorrectes'
  }
}
</script>
<template>
  <div class="min-h-screen flex items-center justify-center bg-green-600">
    <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8">
      <h1 class="text-2xl font-bold text-gray-900 text-center mb-2">Portal Pacients</h1>
      <p class="text-center text-gray-500 mb-6">NPS Enquestes - CRIL</p>
      <div v-if="error" class="bg-red-50 text-red-700 p-3 rounded mb-4 text-sm">{{ error }}</div>
      <div class="space-y-4">
        <div>
          <label class="label">CIP (4 lletres + 10 dígits)</label>
          <input v-model="cip" type="text" class="input" placeholder="ABCD1234567890" />
        </div>
        <div>
          <label class="label">DNI</label>
          <input v-model="dni" type="text" class="input" placeholder="12345678A" />
        </div>
        <button @click="onSubmit" class="btn-primary w-full py-3">Accedir</button>
      </div>
    </div>
  </div>
</template>
