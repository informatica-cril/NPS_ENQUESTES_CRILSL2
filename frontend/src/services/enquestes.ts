import api from './api'
import type { Enquesta, Pregunta, Participacio, PaginatedResponse } from '@/types'

export interface EnquestaFilters {
  estat?: string
  tipus?: string
  centre_id?: number
  search?: string
  sort_by?: string
  sort_dir?: 'asc' | 'desc'
  page?: number
  per_page?: number
}

export interface CreateEnquestaData {
  titol: string
  descripcio?: string
  tipus: string
  centre_id?: number
  data_inici?: string
  data_fi?: string
  anonima?: boolean
  requereix_autenticacio?: boolean
  temps_estimat_minuts?: number
  preguntes?: Partial<Pregunta>[]
}

export const enquestaService = {
  async list(filters: EnquestaFilters = {}): Promise<PaginatedResponse<Enquesta>> {
    const { data } = await api.get<PaginatedResponse<Enquesta>>('/enquestes', { params: filters })
    return data
  },

  async get(id: number): Promise<Enquesta> {
    const { data } = await api.get<Enquesta>(`/enquestes/${id}`)
    return data
  },

  async getBySlug(slug: string): Promise<Enquesta> {
    const { data } = await api.get<Enquesta>(`/public/enquesta/${slug}`)
    return data
  },

  async create(enquestaData: CreateEnquestaData): Promise<{ enquesta: Enquesta; message: string }> {
    const { data } = await api.post('/enquestes', enquestaData)
    return data
  },

  async update(id: number, enquestaData: Partial<CreateEnquestaData>): Promise<{ enquesta: Enquesta; message: string }> {
    const { data } = await api.put(`/enquestes/${id}`, enquestaData)
    return data
  },

  async delete(id: number): Promise<{ message: string }> {
    const { data } = await api.delete(`/enquestes/${id}`)
    return data
  },

  async duplicate(id: number): Promise<{ enquesta: Enquesta; message: string }> {
    const { data } = await api.post(`/enquestes/${id}/duplicate`)
    return data
  },

  async getEstadistiques(id: number): Promise<any> {
    const { data } = await api.get(`/enquestes/${id}/estadistiques`)
    return data
  },

  // Preguntes
  async listPreguntes(enquestaId: number): Promise<Pregunta[]> {
    const { data } = await api.get<Pregunta[]>(`/enquestes/${enquestaId}/preguntes`)
    return data
  },

  async createPregunta(enquestaId: number, preguntaData: Partial<Pregunta>): Promise<{ pregunta: Pregunta; message: string }> {
    const { data } = await api.post(`/enquestes/${enquestaId}/preguntes`, preguntaData)
    return data
  },

  async updatePregunta(enquestaId: number, preguntaId: number, preguntaData: Partial<Pregunta>): Promise<{ pregunta: Pregunta; message: string }> {
    const { data } = await api.put(`/enquestes/${enquestaId}/preguntes/${preguntaId}`, preguntaData)
    return data
  },

  async deletePregunta(enquestaId: number, preguntaId: number): Promise<{ message: string }> {
    const { data } = await api.delete(`/enquestes/${enquestaId}/preguntes/${preguntaId}`)
    return data
  },

  async reorderPreguntes(enquestaId: number, ordre: number[]): Promise<{ message: string; preguntes: Pregunta[] }> {
    const { data } = await api.post(`/enquestes/${enquestaId}/preguntes/reorder`, { ordre })
    return data
  },

  // Participacions
  async listParticipacions(enquestaId: number, filters: any = {}): Promise<PaginatedResponse<Participacio>> {
    const { data } = await api.get<PaginatedResponse<Participacio>>(`/enquestes/${enquestaId}/participacions`, { params: filters })
    return data
  },

  async createParticipacio(enquestaId: number, participacioData: any): Promise<{ participacio: Participacio; url: string; message: string }> {
    const { data } = await api.post(`/enquestes/${enquestaId}/participacions`, participacioData)
    return data
  },
}
