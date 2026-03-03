import api from './api'
import type { Enquesta, Participacio, Resposta } from '@/types'

export interface SubmitRespostesData {
  respostes: Array<{
    pregunta_id: number
    valor: any
  }>
  comentari_nps?: string
}

export const publicService = {
  async getEnquestaBySlug(slug: string): Promise<Enquesta> {
    const { data } = await api.get<Enquesta>(`/public/enquesta/${slug}`)
    return data
  },

  async getParticipacioByToken(token: string): Promise<Participacio | { message: string; completada?: boolean }> {
    const { data } = await api.get(`/public/participacio/${token}`)
    return data
  },

  async submitRespostes(token: string, respostesData: SubmitRespostesData): Promise<{ message: string; participacio: Participacio }> {
    const { data } = await api.post(`/public/participacio/${token}/submit`, respostesData)
    return data
  },
}
