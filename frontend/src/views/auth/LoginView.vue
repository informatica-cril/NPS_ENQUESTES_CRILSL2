<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const schema = toTypedSchema(
  z.object({
    email: z.string().email('Correu electrònic invàlid'),
    password: z.string().min(1, 'La contrasenya és obligatòria'),
  })
)

const { defineField, handleSubmit, errors } = useForm({
  validationSchema: schema,
})

const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')
const loading = ref(false)
const error = ref('')

const onSubmit = handleSubmit(async (values) => {
  loading.value = true
  error.value = ''
  try {
    await authStore.login(values)
    const redirect = (route.query.redirect as string) || '/dashboard'
    router.push(redirect)
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al iniciar sessió'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          NPS Enquestes
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Inicia sessió al teu compte
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit="onSubmit">
        <div v-if="error" class="rounded-md bg-red-50 p-4">
          <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <div class="space-y-4">
          <div>
            <label for="email" class="label">Correu electrònic</label>
            <input
              id="email"
              type="email"
              v-model="email"
              v-bind="emailAttrs"
              class="input"
              :class="{ 'border-red-500': errors.email }"
              placeholder="correu@exemple.com"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <div>
            <label for="password" class="label">Contrasenya</label>
            <input
              id="password"
              type="password"
              v-model="password"
              v-bind="passwordAttrs"
              class="input"
              :class="{ 'border-red-500': errors.password }"
              placeholder="********"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="text-sm">
            <RouterLink to="/forgot-password" class="font-medium text-primary-600 hover:text-primary-500">
              Has oblidat la contrasenya?
            </RouterLink>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="btn-primary w-full py-3"
          >
            <span v-if="loading">Entrant...</span>
            <span v-else>Entra</span>
          </button>
        </div>

        <div class="text-center text-sm text-gray-600">
          No tens compte?
          <RouterLink to="/register" class="font-medium text-primary-600 hover:text-primary-500">
            Registra't
          </RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>
