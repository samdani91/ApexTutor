import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: { Accept: 'application/json' },
  withCredentials: true,
})

// No Authorization header injection — auth_token httpOnly cookie is sent automatically
// by the browser on every request because withCredentials: true is set above.

http.interceptors.response.use(
  res => res,
  err => {
    const status  = err.response?.status
    const data    = err.response?.data

    if (status === 401) {
      emit('auth:expired')
      return Promise.reject(err)
    }

    if (status === 403 && data?.suspended) {
      emit('auth:suspended', { message: 'Your account has been suspended. Please contact support.' })
      err._handled = true
      return Promise.reject(err)
    }

    // Fire a generic error event so the UI layer (not the API client) shows toasts.
    if (!err.response) {
      emit('api:error', { message: 'Cannot connect to the server. Please check your connection.' })
      err._handled = true
    } else if (status === 503) {
      emit('api:error', { message: 'Service temporarily unavailable. Please try again.' })
      err._handled = true
    } else if (status >= 500) {
      emit('api:error', { message: 'Something went wrong on our end. Please try again.' })
      err._handled = true
    }

    if (import.meta.env.DEV) {
      console.error('[API Error]', status, err.config?.url, data)
    }

    return Promise.reject(err)
  }
)

function emit(name, detail = {}) {
  window.dispatchEvent(new CustomEvent(name, { detail }))
}

export default http
