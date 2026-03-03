import api from './api'
import type { User } from '@/types'

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface AuthResponse {
  user: User
  token: string
}

export const authService = {
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const { data } = await api.post<AuthResponse>('/auth/login', credentials)
    return data
  },

  async register(userData: RegisterData): Promise<AuthResponse> {
    const { data } = await api.post<AuthResponse>('/auth/register', userData)
    return data
  },

  async logout(): Promise<void> {
    await api.post('/auth/logout')
  },

  async getUser(): Promise<{ user: User }> {
    const { data } = await api.get<{ user: User }>('/auth/user')
    return data
  },

  async updateProfile(profileData: Partial<User>): Promise<{ user: User; message: string }> {
    const { data } = await api.put('/auth/profile', profileData)
    return data
  },

  async forgotPassword(email: string): Promise<{ message: string }> {
    const { data } = await api.post('/auth/forgot-password', { email })
    return data
  },
}
