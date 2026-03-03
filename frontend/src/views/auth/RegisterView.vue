<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'

const router = useRouter()
const authStore = useAuthStore()

const schema = toTypedSchema(
  z.object({
    name: z.string().min(2, 'El nom ha de tenir almenys 2 caràcters'),
    email: z.string().email('Correu electrònic invàlid'),
    password: z.string().min(8, 'La contrasenya ha de tenir almenys 8 caràcters'),
    password_confirmation: z.string(),
  }).refine(data => data.password === data.password_confirmation, {
    message: 'Les contrasenyes no coincideixen',
    path: ['password_confirmation'],
  })
)

const { defineField, handleSubmit, errors } = useForm({
  validationSchema: schema,
})

const [name, nameAttrs] = defineField('name')
const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')
const [passwordConfirmation, passwordConfirmationAttrs] = defineField('password_confirmation')
const loading = ref(false)
const error = ref('')

const onSubmit = handleSubmit(async (values) => {
  loading.value = true
  error.value = ''
  try {
    await authStore.register(values)
    router.push('/dashboard')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al registrar-se'
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
          Crear compte
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Registra't a NPS Enquestes
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit="onSubmit">
        <div v-if="error" class="rounded-md bg-red-50 p-4">
          <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <div class="space-y-4">
          <div>
            <label for="name" class="label">Nom</label>
            <input
              id="name"
              type="text"
              v-model="name"
              v-bind="nameAttrs"
              class="input"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <div>
            <label for="email" class="label">Correu electrònic</label>
            <input
              id="email"
              type="email"
              v-model="email"
              v-bind="emailAttrs"
              class="input"
              :class="{ 'border-red-500': errors.email }"
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
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="label">Confirmar contrasenya</label>
            <input
              id="password_confirmation"
              type="password"
              v-model="passwordConfirmation"
              v-bind="passwordConfirmationAttrs"
              class="input"
              :class="{ 'border-red-500': errors.password_confirmation }"
            />
            <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation }}</p>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="btn-primary w-full py-3"
          >
            <span v-if="loading">Registrant...</span>
            <span v-else>Registrar-se</span>
          </button>
        </div>

        <div class="text-center text-sm text-gray-600">
          Ja tens compte?
          <RouterLink to="/login" class="font-medium text-primary-600 hover:text-primary-500">
            Inicia sessió
          </RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>
