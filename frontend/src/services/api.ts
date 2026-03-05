import axios, { type AxiosInstance, type AxiosError } from 'axios'
import type { ApiError } from '@/types'

const api: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
})

// Request interceptor - add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor - handle errors
api.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ApiError>) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      // Redirect to appropriate login page based on current path
      const currentPath = window.location.pathname
      if (currentPath.startsWith('/fisio')) {
        window.location.href = '/fisio/login'
      } else if (currentPath.startsWith('/pacient')) {
        window.location.href = '/pacient/login'
      } else {
        window.location.href = '/admin/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
