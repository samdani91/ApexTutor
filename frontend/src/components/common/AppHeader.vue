<template>
  <header class="sticky top-0 z-50 shrink-0 bg-navy-900 text-navy-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 md:px-6 h-16 flex items-center justify-between gap-4">

      <!-- Logo -->
      <RouterLink to="/" class="flex items-center gap-2 shrink-0 group">
        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-gold-400 font-display text-sm font-bold text-navy-900 shadow-sm">
          TK
        </span>
        <span class="font-display font-bold text-xl text-white transition-colors group-hover:text-gold-200">
          TutorKhujo
        </span>
      </RouterLink>

      <!-- Desktop nav -->
      <nav class="hidden md:flex items-center gap-1">
        <!-- Always: Find Tutors -->
        <RouterLink to="/search"
          class="px-3 py-2 rounded-lg text-sm font-semibold font-display text-navy-200 hover:text-white hover:bg-white/10 transition-colors"
          :class="$route.path === '/search' ? 'text-white bg-white/10' : ''">
          Find Tutors
        </RouterLink>

        <!-- Not authenticated -->
        <template v-if="!auth.isAuthenticated">
          <div class="w-px h-5 bg-white/15 mx-1" />
          <RouterLink to="/login"
            class="px-3 py-2 rounded-lg text-sm font-semibold font-display text-navy-200 hover:text-white hover:bg-white/10 transition-colors">
            Log In
          </RouterLink>
          <RouterLink to="/register"
            class="ml-1 inline-flex items-center gap-1.5 bg-gold-400 text-navy-900 px-4 py-2 rounded-lg text-sm font-semibold font-display hover:bg-gold-300 transition-colors">
            Sign Up
          </RouterLink>
        </template>

        <!-- Authenticated but NOT in dashboard — show Go to Dashboard + Logout -->
        <template v-else-if="!inDashboard">
          <div class="w-px h-5 bg-white/15 mx-1" />
          <RouterLink :to="dashboardPath"
            class="relative px-3 py-2 rounded-lg text-sm font-semibold font-display text-navy-200 hover:text-white hover:bg-white/10 transition-colors inline-flex items-center gap-1.5">
            My Dashboard
            <span v-if="notifStore.unreadCount > 0"
              class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold font-display bg-red-500 text-white rounded-full leading-none">
              {{ notifStore.unreadCount > 99 ? '99+' : notifStore.unreadCount }}
            </span>
          </RouterLink>
          <button @click="showLogoutDialog = true"
            class="ml-1 px-3 py-2 rounded-lg text-sm font-semibold font-display bg-red-600 text-white hover:bg-red-700 transition-colors">
            Log Out
          </button>
        </template>
        <!-- In dashboard: sidebar handles navigation — show nothing extra -->
      </nav>

      <!-- Mobile: hamburger ONLY shown outside dashboard context -->
      <button v-if="!inDashboard" @click="mobileOpen = !mobileOpen"
        class="md:hidden p-2 rounded-lg text-navy-100 hover:bg-white/10 transition-colors" aria-label="Menu">
        <svg v-if="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h18"/>
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Mobile dropdown (non-dashboard only) -->
    <Transition name="slide-down">
      <div v-if="mobileOpen && !inDashboard"
        class="md:hidden border-t border-white/10 bg-navy-900 px-4 pb-4 pt-2 space-y-1 shadow-lg">
        <RouterLink @click="mobileOpen = false" to="/search"
          class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold font-display text-navy-100 hover:bg-white/10 transition-colors">
          Find Tutors
        </RouterLink>
        <template v-if="!auth.isAuthenticated">
          <RouterLink @click="mobileOpen = false" to="/login"
            class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold font-display text-navy-100 hover:bg-white/10 transition-colors">
            Log In
          </RouterLink>
          <RouterLink @click="mobileOpen = false" to="/register"
            class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold font-display bg-gold-400 text-navy-900 hover:bg-gold-300 transition-colors">
            Sign Up
          </RouterLink>
        </template>
        <template v-else>
          <RouterLink @click="mobileOpen = false" :to="dashboardPath"
            class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold font-display text-navy-100 hover:bg-white/10 transition-colors">
            My Dashboard
            <span v-if="notifStore.unreadCount > 0"
              class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold font-display bg-red-500 text-white rounded-full leading-none">
              {{ notifStore.unreadCount > 99 ? '99+' : notifStore.unreadCount }}
            </span>
          </RouterLink>
          <button @click="mobileOpen = false; showLogoutDialog = true"
            class="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold font-display bg-red-600 text-white hover:bg-red-700 transition-colors">
            Log Out
          </button>
        </template>
      </div>
    </Transition>

    <LogoutConfirmDialog
      :show="showLogoutDialog"
      @confirm="confirmLogout"
      @cancel="showLogoutDialog = false"
    />
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { useNotificationStore } from '@/stores/notification.js'
import { notificationApi } from '@/api/notifications.js'
import LogoutConfirmDialog from './LogoutConfirmDialog.vue'
import { toast } from 'vue-sonner'

const auth        = useAuthStore()
const notifStore  = useNotificationStore()
const router      = useRouter()
const $route      = useRoute()

onMounted(async () => {
  if (auth.isAuthenticated && (auth.isTutor || auth.isGuardian) && !notifStore.initialized) {
    try {
      const { data } = await notificationApi.getAll()
      notifStore.setUnread(data.unread)
    } catch {}
  }
})

const mobileOpen      = ref(false)
const showLogoutDialog = ref(false)
const logoutToast = { id: 'auth-logout', position: 'top-right' }

const inDashboard = computed(() =>
  $route.path.startsWith('/tutor/') || $route.path.startsWith('/guardian/')
)

const dashboardPath = computed(() => {
  if (auth.isTutor) return '/tutor/dashboard'
  if (auth.isAdmin) return '/admin/dashboard'
  return '/guardian/dashboard'
})

async function confirmLogout() {
  showLogoutDialog.value = false
  mobileOpen.value = false
  toast.loading('Signing you out...', {
    ...logoutToast,
    description: 'Ending your current session.',
  })

  await auth.logout()
  toast.success('Signed out', {
    ...logoutToast,
    description: 'You have been logged out successfully.',
  })
  router.push('/')
}
</script>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.18s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
