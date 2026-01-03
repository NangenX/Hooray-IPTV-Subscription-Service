export interface Admin {
  id: number
  username: string
  email: string
  role: 'super_admin' | 'admin' | 'moderator'
  status: 'active' | 'inactive'
  last_login_at?: string
  created_at: string
}

export interface Channel {
  id: number
  name: string
  stream_url: string
  description?: string
  logo_url?: string
  category?: string
  language?: string
  country?: string
  is_active: boolean
  quality?: string
  tvg_id?: string
  tvg_name?: string
  tvg_logo?: string
  group_title?: string
  sort_order: number
  created_at: string
  updated_at: string
}

export interface ImportLog {
  id: number
  file_name: string
  file_size: number
  total_processed: number
  imported: number
  skipped: number
  errors: number
  log_file_path?: string
  error_details?: any
  created_by: number
  created_at: string
}

export interface ApiResponse<T = any> {
  message?: string
  data?: T
  errors?: Record<string, string[]>
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
