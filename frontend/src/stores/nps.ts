import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { npsService, type NpsFilters, type CentreNps, type FisioNps } from '@/services/nps'
import type { NpsEstadistiques, NpsEvolucio, NpsResultat, PaginatedResponse } from '@/types'
import { subMonths, format } from 'date-fns'

export const useNpsStore = defineStore('nps', () => {
  // State
  const dashboard = ref<NpsEstadistiques | null>(null)
  const evolucio = ref<NpsEvolucio[]>([])
  const perCentre = ref<CentreNps[]>([])
  const perFisioterapeuta = ref<FisioNps[]>([])
  const comentaris = ref<NpsResultat[]>([])
  const comentarisPagination = ref({
    currentPage: 1,
    lastPage: 1,
    total: 0,
  })
  const filters = ref<NpsFilters>({
    data_inici: format(subMonths(new Date(), 3), 'yyyy-MM-dd'),
    data_fi: format(new Date(), 'yyyy-MM-dd'),
    periode: 'mensual',
  })
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const npsScore = computed(() => dashboard.value?.nps_score ?? null)
  const npsClass = computed(() => {
    const score = npsScore.value
    if (score === null) return ''
    if (score >= 50) return 'nps-promotor'
    if (score >= 0) return 'nps-passiu'
    return 'nps-detractor'
  })

  // Actions
  async function fetchDashboard(customFilters?: NpsFilters) {
    loading.value = true
    error.value = null
    try {
      const data = await npsService.getDashboard(customFilters || filters.value)
      dashboard.value = data
      return data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al carregar les dades NPS'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchEvolucio(customFilters?: NpsFilters) {
    loading.value = true
    error.value = null
    try {
      const data = await npsService.getEvolucio(customFilters || filters.value)
      evolucio.value = data
      return data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al carregar l\'evolució NPS'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchPerCentre(customFilters?: NpsFilters) {
    loading.value = true
    error.value = null
    try {
      const data = await npsService.getPerCentre(customFilters || filters.value)
      perCentre.value = data
      return data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al carregar NPS per centre'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchPerFisioterapeuta(customFilters?: NpsFilters) {
    loading.value = true
    error.value = null
    try {
      const data = await npsService.getPerFisioterapeuta(customFilters || filters.value)
      perFisioterapeuta.value = data
      return data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al carregar NPS per fisioterapeuta'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchComentaris(customFilters?: any) {
    loading.value = true
    error.value = null
    try {
      const data = await npsService.getComentaris({ ...filters.value, ...customFilters })
      comentaris.value = data.data
      comentarisPagination.value = {
        currentPage: data.current_page,
        lastPage: data.last_page,
        total: data.total,
      }
      return data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al carregar els comentaris'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchAll() {
    await Promise.all([
      fetchDashboard(),
      fetchEvolucio(),
      fetchPerCentre(),
      fetchPerFisioterapeuta(),
    ])
  }

  function updateFilters(newFilters: Partial<NpsFilters>) {
    filters.value = { ...filters.value, ...newFilters }
  }

  function clearError() {
    error.value = null
  }

  return {
    // State
    dashboard,
    evolucio,
    perCentre,
    perFisioterapeuta,
    comentaris,
    comentarisPagination,
    filters,
    loading,
    error,
    // Getters
    npsScore,
    npsClass,
    // Actions
    fetchDashboard,
    fetchEvolucio,
    fetchPerCentre,
    fetchPerFisioterapeuta,
    fetchComentaris,
    fetchAll,
    updateFilters,
    clearError,
  }
})
