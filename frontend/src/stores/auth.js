import { defineStore } from 'pinia'
import { authApi } from '@/api/auth.js'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    isTutor:         (state) => state.user?.role === 'tutor',
    isGuardian:      (state) => ['guardian', 'student'].includes(state.user?.role),
    isAdmin:         (state) => ['admin', 'super_admin'].includes(state.user?.role),
  },
  actions: {
    setAuth(user, token) {
      this.user  = user
      this.token = token
      localStorage.setItem('token', token)
    },
    async login(credentials) {
      this.loading = true
      try {
        const { data } = await authApi.login(credentials)
        this.setAuth(data.data.user, data.data.token)
        return data
      } finally {
        this.loading = false
      }
    },
    async register(payload) {
      this.loading = true
      try {
        const { data } = await authApi.register(payload)
        // token not issued yet — email verification required
        return data
      } finally {
        this.loading = false
      }
    },
    async fetchMe() {
      try {
        const { data } = await authApi.me()
        this.user = data.data
      } catch {
        this.logout()
      }
    },
    async uploadAvatar(formData) {
      const { data } = await authApi.uploadAvatar(formData)
      if (this.user) {
        this.user.avatar     = data.avatar_url
        this.user.avatar_url = data.avatar_url
      }
      return data
    },
    async logout() {
      try { await authApi.logout() } catch {}
      this.token = null
      this.user  = null
      localStorage.removeItem('token')
    },
  },
})
