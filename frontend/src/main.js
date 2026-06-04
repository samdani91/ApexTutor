import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router/index.js'
import './assets/main.css'
import { useAuthStore } from './stores/auth.js'
import DropSelect from './components/search/DropSelect.vue'
import { toast } from 'vue-sonner'

const pinia = createPinia()
const app = createApp(App)
app.component('DropSelect', DropSelect)
app.use(pinia)

// fetchMe MUST resolve before app.use(router).
// Vue Router fires the initial navigation synchronously inside app.use(router),
// which triggers beforeEach. If fetchMe hasn't run yet, user is null and every
// protected route redirects to /login on every page refresh.
;(async () => {
  const authStore = useAuthStore()
  await authStore.fetchMe()

  app.use(router)
  app.mount('#app')
})()

// ── API event handlers ────────────────────────────────────────────────────────
// Registered outside the async IIFE — they reference the imported router
// directly so they don't need the app to be mounted first.

window.addEventListener('auth:expired',   () => router.push({ name: 'login' }))
window.addEventListener('auth:suspended', (e) => {
  toast.error(e.detail?.message ?? 'Your account has been suspended.')
  router.push({ name: 'login' })
})
window.addEventListener('api:error', (e) => {
  toast.error(e.detail?.message ?? 'An unexpected error occurred.')
})
