import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { enquestaService, type EnquestaFilters, type CreateEnquestaData } from '@/services/enquestes'
import type { Enquesta, Pregunta, PaginatedResponse } from '@/types'

export const useEnquestesStore = defineStore('enquestes', () => {
  // State
  const enquestes = ref<Enquesta[]>([])
  const currentEnquesta = ref<Enquesta | null>(null)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  })
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const activeEnquestes = computed(() => 
    enquestes.value.filter(e => e.estat === 'activa')
  )

  // Actions
  async function fetchEnquestes(filters: EnquestaFilters = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await enquestaService.list(filters)
      enquestes.value = response.data
      pagination.value = {
        currentPage: response.current_page,
        lastPage: response.last_page,
        perPage: response.per_page,
        total: response.total,
      }
      return response
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al carregar les enquestes'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchEnquesta(id: number) {
    loading.value = true
    error.value = null
    try {
      const enquesta = await enquestaService.get(id)
      currentEnquesta.value = enquesta
      return enquesta
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al carregar l\'enquesta'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function createEnquesta(data: CreateEnquestaData) {
    loading.value = true
    error.value = null
    try {
      const response = await enquestaService.create(data)
      enquestes.value.unshift(response.enquesta)
      return response
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al crear l\'enquesta'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function updateEnquesta(id: number, data: Partial<CreateEnquestaData>) {
    loading.value = true
    error.value = null
    try {
      const response = await enquestaService.update(id, data)
      const index = enquestes.value.findIndex(e => e.id === id)
      if (index !== -1) {
        enquestes.value[index] = response.enquesta
      }
      if (currentEnquesta.value?.id === id) {
        currentEnquesta.value = response.enquesta
      }
      return response
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al actualitzar l\'enquesta'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function deleteEnquesta(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await enquestaService.delete(id)
      enquestes.value = enquestes.value.filter(e => e.id !== id)
      if (currentEnquesta.value?.id === id) {
        currentEnquesta.value = null
      }
      return response
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al eliminar l\'enquesta'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function duplicateEnquesta(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await enquestaService.duplicate(id)
      enquestes.value.unshift(response.enquesta)
      return response
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al duplicar l\'enquesta'
      throw e
    } finally {
      loading.value = false
    }
  }

  function clearCurrent() {
    currentEnquesta.value = null
  }

  function clearError() {
    error.value = null
  }

  return {
    // State
    enquestes,
    currentEnquesta,
    pagination,
    loading,
    error,
    // Getters
    activeEnquestes,
    // Actions
    fetchEnquestes,
    fetchEnquesta,
    createEnquesta,
    updateEnquesta,
    deleteEnquesta,
    duplicateEnquesta,
    clearCurrent,
    clearError,
  }
})
