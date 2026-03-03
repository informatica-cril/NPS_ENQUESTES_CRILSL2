import api from './api'
import type { Centre } from '@/types'

export interface CentreFilters {
  active_only?: boolean
  search?: string
}

export const centreService = {
  async list(filters: CentreFilters = {}): Promise<Centre[]> {
    const { data } = await api.get<Centre[]>('/centres', { params: filters })
    return data
  },

  async get(id: number): Promise<Centre> {
    const { data } = await api.get<Centre>(`/centres/${id}`)
    return data
  },

  async create(centreData: Partial<Centre>): Promise<{ centre: Centre; message: string }> {
    const { data } = await api.post('/centres', centreData)
    return data
  },

  async update(id: number, centreData: Partial<Centre>): Promise<{ centre: Centre; message: string }> {
    const { data } = await api.put(`/centres/${id}`, centreData)
    return data
  },

  async delete(id: number): Promise<{ message: string }> {
    const { data } = await api.delete(`/centres/${id}`)
    return data
  },

  async getMapData(): Promise<any[]> {
    const { data } = await api.get('/centres-map')
    return data
  },
}
