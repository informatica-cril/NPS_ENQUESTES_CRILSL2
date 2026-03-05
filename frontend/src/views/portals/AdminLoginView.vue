<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const username = ref('')
const password = ref('')
const error = ref('')

async function onSubmit() {
  try {
    await authStore.login({ username: username.value, password: password.value })
    if (authStore.user?.role !== 'admin') {
      authStore.logout()
      error.value = 'Accés no autoritzat per a aquest portal'
      return
    }
    router.push('/dashboard')
  } catch (e) {
    error.value = 'Credencials incorrectes'
  }
}
</script>
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-900">
    <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8">
      <h1 class="text-2xl font-bold text-gray-900 text-center mb-2">Portal Administrador</h1>
      <p class="text-center text-gray-500 mb-6">NPS Enquestes - CRIL</p>
      <div v-if="error" class="bg-red-50 text-red-700 p-3 rounded mb-4 text-sm">{{ error }}</div>
      <div class="space-y-4">
        <div>
          <label class="label">Nom d'usuari</label>
          <input v-model="username" type="text" class="input" placeholder="admin" />
        </div>
        <div>
          <label class="label">Contrasenya</label>
          <input v-model="password" type="password" class="input" />
        </div>
        <button @click="onSubmit" class="btn-primary w-full py-3">Iniciar sessió</button>
      </div>
    </div>
  </div>
</template>
