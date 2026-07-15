import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('notification', {
  state: () => ({ toasts: [], unreadCount: 0, items: [], initialized: false }),
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
    setItems(items) { this.items = items ?? [] },
    // Keep the cached list's read state in step with the badge, so the dashboard
    // news card doesn't keep showing rows as unread after they've been read.
    markItemRead(id) {
      const item = this.items.find(i => i.id === id)
      if (item && !item.read_at) item.read_at = new Date().toISOString()
    },
    markAllItemsRead() {
      this.items.forEach(i => { if (!i.read_at) i.read_at = new Date().toISOString() })
    },
    decrement()   { this.unreadCount = Math.max(0, this.unreadCount - 1) },
    clearUnread() { this.unreadCount = 0 },
  },
})
