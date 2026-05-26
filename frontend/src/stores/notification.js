import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('notification', {
  state: () => ({ toasts: [] }),
  actions: {
    show(message, type = 'success') {
      const id = Date.now()
      this.toasts.push({ id, message, type })
      setTimeout(() => this.dismiss(id), 4000)
    },
    dismiss(id) {
      this.toasts = this.toasts.filter(t => t.id !== id)
    },
  },
})
