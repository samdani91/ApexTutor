import { defineStore } from 'pinia'
import { authApi } from '@/api/auth.js'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
  }),
  getters: {
    // Token lives in an httpOnly cookie — not readable from JS. Auth state = user object.
    isAuthenticated: (state) => !!state.user,
    isTutor:         (state) => state.user?.role === 'tutor',
    isGuardian:      (state) => ['guardian', 'student'].includes(state.user?.role),
    isAdmin:         (state) => state.user?.role === 'super_admin',
  },
  actions: {
    setAuth(user, _token = null) {
      // _token ignored: backend sets httpOnly cookie, browser sends it automatically
      this.user = user
    },
    async login(credentials) {
      this.loading = true
      try {
        const { data } = await authApi.login(credentials)
        this.setAuth(data.data.user)
        return data
      } finally {
        this.loading = false
      }
    },
    async register(payload) {
      this.loading = true
      try {
        const { data } = await authApi.register(payload)
        return data
      } finally {
        this.loading = false
      }
    },
    async fetchMe() {
      try {
        const { data } = await authApi.me()
        this.user = data.data
      } catch (err) {
        // Only clear user on auth failure; leave state intact on transient server errors
        if (!err.response || err.response.status === 401) {
          this.user = null
        }
      }
    },
    async uploadAvatar(formData) {
      const { data } = await authApi.uploadAvatar(formData)
      if (this.user) {
        if (data.pending) {
          // Avatar is pending approval — keep old avatar, just update pending_avatar_url
          this.user.pending_avatar_url = data.pending_avatar_url
        } else {
          this.user.avatar     = data.avatar_url
          this.user.avatar_url = data.avatar_url
          this.user.pending_avatar_url = null
        }
      }
      return data
    },
    async logout() {
      try { await authApi.logout() } catch {}
      this.user = null
    },
  },
})
