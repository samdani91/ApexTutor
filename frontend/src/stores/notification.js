import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('notification', {
  state: () => ({ toasts: [], unreadCount: 0, initialized: false }),
  actions: {
    show(message, type = 'success') {
      const id = Date.now()
      this.toasts.push({ id, message, type })
      setTimeout(() => this.dismiss(id), 4000)
    },
    dismiss(id) {
      this.toasts = this.toasts.filter(t => t.id !== id)
    },
    setUnread(n)  { this.unreadCount = n; this.initialized = true },
    decrement()   { this.unreadCount = Math.max(0, this.unreadCount - 1) },
    clearUnread() { this.unreadCount = 0 },
  },
})
