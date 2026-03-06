import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService, type LoginCredentials, type RegisterData } from '@/services/auth'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isFisioterapeuta = computed(() => user.value?.role === 'fisioterapeuta')
  const isPacient = computed(() => user.value?.role === 'pacient')
  const userRole = computed(() => user.value?.role)

  // Actions
  async function login(credentials: LoginCredentials) {
    loading.value = true
    error.value = null
    try {
      const response = await authService.login(credentials)
      token.value = response.token
      user.value = response.user
      localStorage.setItem('auth_token', response.token)
      return response
    } catch (e: any) {
      const errors = e.response?.data?.errors
      if (errors) {
        error.value = Object.values(errors).flat()[0] as string
      } else {
        error.value = e.response?.data?.message || 'Error al iniciar sessió'
      }
      throw e
    } finally {
      loading.value = false
    }
  }

  async function register(data: RegisterData) {
    loading.value = true
    error.value = null
    try {
      const response = await authService.register(data)
      token.value = response.token
      user.value = response.user
      localStorage.setItem('auth_token', response.token)
      return response
    } catch (e: any) {
      const errors = e.response?.data?.errors
      if (errors) {
        error.value = Object.values(errors).flat()[0] as string
      } else {
        error.value = e.response?.data?.message || 'Error al registrar-se'
      }
      throw e
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true
    try {
      await authService.logout()
    } catch (e) {
      // Ignore errors on logout
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      loading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) return null
    
    loading.value = true
    try {
      const response = await authService.getUser()
      user.value = response.user
      return response.user
    } catch (e) {
      // Token might be invalid
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateProfile(data: Partial<User>) {
    loading.value = true
    error.value = null
    try {
      const response = await authService.updateProfile(data)
      user.value = response.user
      return response
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al actualitzar el perfil'
      throw e
    } finally {
      loading.value = false
    }
  }

  function clearError() {
    error.value = null
  }

  return {
    // State
    user,
    token,
    loading,
    error,
    // Getters
    isAuthenticated,
    isAdmin,
    isFisioterapeuta,
    isPacient,
    userRole,
    // Actions
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
    clearError,
  }
})
