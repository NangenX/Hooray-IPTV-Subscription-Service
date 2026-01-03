import api from './axios'
import type { Admin, Channel, ImportLog, PaginatedResponse } from '@/types'

// Auth API
export const authAPI = {
  login: (credentials: { username: string; password: string }) =>
    api.post('/admin/login', credentials),
  
  me: () => api.get('/admin/me'),
  
  logout: () => api.post('/admin/logout'),
  
  changePassword: (data: {
    current_password: string
    new_password: string
    new_password_confirmation: string
  }) => api.post('/admin/change-password', data),
}

// Channel API
export const channelAPI = {
  getAll: (params?: any) => api.get<PaginatedResponse<Channel>>('/admin/channels', { params }),
  
  getOne: (id: number) => api.get(`/admin/channels/${id}`),
  
  create: (data: Partial<Channel>) => api.post('/admin/channels', data),
  
  update: (id: number, data: Partial<Channel>) => api.put(`/admin/channels/${id}`, data),
  
  delete: (id: number) => api.delete(`/admin/channels/${id}`),
  
  bulkDelete: (ids: number[]) => api.post('/admin/channels/bulk-delete', { ids }),
  
  bulkUpdateStatus: (ids: number[], is_active: boolean) =>
    api.post('/admin/channels/bulk-update-status', { ids, is_active }),
  
  getGroups: () => api.get<{ groups: string[] }>('/admin/channels/groups'),
}

// M3U Import API
export const m3uAPI = {
  import: (file: File) => {
    const formData = new FormData()
    formData.append('m3u_file', file)
    return api.post('/admin/m3u/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },
  
  getHistory: (params?: any) => api.get<PaginatedResponse<ImportLog>>('/admin/m3u/history', { params }),
  
  downloadLog: (logId: number) =>
    api.get(`/admin/m3u/download-log/${logId}`, {
      responseType: 'blob',
    }),
}

// Admin API
export const adminAPI = {
  getAll: (params?: any) => api.get<PaginatedResponse<Admin>>('/admin/admins', { params }),
  
  create: (data: Partial<Admin> & { password: string; password_confirmation: string }) =>
    api.post('/admin/admins', data),
  
  update: (id: number, data: Partial<Admin>) => api.put(`/admin/admins/${id}`, data),
  
  delete: (id: number) => api.delete(`/admin/admins/${id}`),
}

// Log API
export const logAPI = {
  getAll: (params?: any) => api.get('/admin/logs', { params }),
  
  export: (params?: { date?: string; module?: string }) =>
    api.get('/admin/logs/export', {
      params,
      responseType: 'blob',
    }),
  
  getImportLogs: (params?: any) => api.get('/admin/logs/imports', { params }),
}
