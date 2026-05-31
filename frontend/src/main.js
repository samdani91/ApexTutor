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
