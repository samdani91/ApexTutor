import axios from 'axios'
import { toast } from 'vue-sonner'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: { Accept: 'application/json' },
  withCredentials: true,
})

http.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

http.interceptors.response.use(
  res => res,
  err => {
    if (err.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
      return Promise.reject(err)
    }

    if (err.response?.status === 403 && err.response?.data?.suspended) {
      localStorage.removeItem('token')
      toast.error('Your account has been suspended. Please contact support.')
      window.location.href = '/login'
      return Promise.reject(err)
    }

    if (!err.response) {
      // Network error or server unreachable
      toast.error('Cannot connect to the server. Please check your connection.')
      err._toasted = true
    } else if (err.response.status === 503) {
      toast.error('Service temporarily unavailable. Please try again.')
      err._toasted = true
    } else if (err.response.status >= 500) {
      toast.error('Something went wrong on our end. Please try again.')
      err._toasted = true
    }

    return Promise.reject(err)
  }
)

export default http
