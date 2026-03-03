import api from './api'
import type { NpsEstadistiques, NpsEvolucio, NpsResultat, PaginatedResponse } from '@/types'

export interface NpsFilters {
  data_inici?: string
  data_fi?: string
  centre_id?: number
  periode?: 'diari' | 'setmanal' | 'mensual'
}

export interface CentreNps {
  centre_id: number
  nom: string
  coordinates: { lat: number; lng: number } | null
  total_respostes: number
  nps_score: number | null
  mitjana: number | null
}

export interface FisioNps {
  fisioterapeuta_id: number
  nom: string
  total_respostes: number
  nps_score: number
  mitjana: number
  promotors: number
  passius: number
  detractors: number
}

export const npsService = {
  async getDashboard(filters: NpsFilters = {}): Promise<NpsEstadistiques> {
    const { data } = await api.get<NpsEstadistiques>('/nps/dashboard', { params: filters })
    return data
  },

  async getEvolucio(filters: NpsFilters = {}): Promise<NpsEvolucio[]> {
    const { data } = await api.get<NpsEvolucio[]>('/nps/evolucio', { params: filters })
    return data
  },

  async getPerCentre(filters: NpsFilters = {}): Promise<CentreNps[]> {
    const { data } = await api.get<CentreNps[]>('/nps/per-centre', { params: filters })
    return data
  },

  async getPerFisioterapeuta(filters: NpsFilters = {}): Promise<FisioNps[]> {
    const { data } = await api.get<FisioNps[]>('/nps/per-fisioterapeuta', { params: filters })
    return data
  },

  async getComentaris(filters: any = {}): Promise<PaginatedResponse<NpsResultat>> {
    const { data } = await api.get<PaginatedResponse<NpsResultat>>('/nps/comentaris', { params: filters })
    return data
  },

  async exportData(filters: NpsFilters & { format?: string }): Promise<any> {
    const { data } = await api.get('/nps/export', { params: filters })
    return data
  },
}
