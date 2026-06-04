import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router/index.js'
import './assets/main.css'
import { useAuthStore } from './stores/auth.js'
import DropSelect from './components/search/DropSelect.vue'

const pinia = createPinia()
const app = createApp(App)
app.component('DropSelect', DropSelect)
app.use(pinia)
app.use(router)

// Rehydrate user from token before mounting so role-based logic works immediately
;(async () => {
  const authStore = useAuthStore()
  if (authStore.token) {
    await authStore.fetchMe()
  }
  app.mount('#app')
})()

// Let the router — not the API layer — handle auth-triggered redirects.
// http.js fires these events; the listener here keeps navigation concerns out of axios.
window.addEventListener('auth:expired',   () => router.push({ name: 'login' }))
window.addEventListener('auth:suspended', () => router.push({ name: 'login' }))
