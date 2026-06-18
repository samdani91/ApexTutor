<template>
  <header class="sticky top-0 z-50 shrink-0 bg-navy-900 text-navy-100 shadow-sm">
    <div class="mx-auto flex h-16 max-w-[84rem] items-center justify-between gap-4 px-4 md:px-6">

      <!-- Logo -->
      <RouterLink to="/" class="flex items-center gap-2 shrink-0 group">
        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-gold-400 font-display text-sm font-bold text-navy-900 shadow-sm">
          AT
        </span>
        <span class="font-display font-bold text-xl text-white transition-colors group-hover:text-gold-200">
          Apex Tutor
        </span>
      </RouterLink>

      <!-- Desktop nav -->
      <nav class="hidden md:flex items-center gap-1">
        <!-- Always: Find Tutors + Tuition Jobs -->
        <RouterLink to="/search"
          class="px-3 py-2 rounded-lg text-sm font-semibold font-display text-navy-200 hover:text-white hover:bg-white/10 transition-colors"
          :class="$route.path === '/search' ? 'text-white bg-white/10' : ''">
          Find Tutors
        </RouterLink>
        <RouterLink to="/jobs"
          class="px-3 py-2 rounded-lg text-sm font-semibold font-display text-navy-200 hover:text-white hover:bg-white/10 transition-colors"
          :class="$route.path.startsWith('/jobs') ? 'text-white bg-white/10' : ''">
          Tuition Jobs
        </RouterLink>

        <div class="w-px h-5 bg-white/15 mx-1" />

        <!-- Not authenticated -->
        <template v-if="!auth.isAuthenticated">
          <RouterLink to="/login"
            class="px-3 py-2 rounded-lg text-sm font-semibold font-display text-navy-200 hover:text-white hover:bg-white/10 transition-colors">
            Log In
          </RouterLink>
          <RouterLink to="/register"
            class="ml-1 inline-flex items-center gap-1.5 bg-gold-400 text-navy-900 px-4 py-2 rounded-lg text-sm font-semibold font-display hover:bg-gold-300 transition-colors">
            Sign Up
          </RouterLink>
        </template>

        <!-- Authenticated — avatar dropdown (non-dashboard) -->
        <template v-else-if="!inDashboard">
          <div class="relative" ref="avatarDropdownRef">
            <button @click="avatarOpen = !avatarOpen"
              class="relative flex items-center gap-2 rounded-lg p-1 pr-2 hover:bg-white/10 transition-colors focus:outline-none">
              <!-- Avatar -->
              <span class="flex h-8 w-8 shrink-0 rounded-full overflow-hidden border-2 border-white/20">
                <img v-if="auth.user?.avatar_url" :src="auth.user.avatar_url" :alt="auth.user?.name"
                  class="h-full w-full object-cover" />
                <span v-else class="flex h-full w-full items-center justify-center bg-navy-600 text-white text-xs font-bold font-display">
                  {{ initials }}
                </span>
              </span>
              <!-- Caret -->
              <svg class="w-3.5 h-3.5 text-navy-300 transition-transform duration-150"
                :class="avatarOpen ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <!-- Dropdown -->
            <Transition name="dropdown">
              <div v-if="avatarOpen"
                class="absolute right-0 top-full mt-2 w-64 rounded-xl bg-white shadow-xl border border-paper-200 overflow-hidden z-50">
                <!-- User info -->
                <div class="px-4 py-4 border-b border-paper-100 flex items-center gap-3">
                  <span class="flex h-11 w-11 shrink-0 rounded-full overflow-hidden border border-paper-200">
                    <img v-if="auth.user?.avatar_url" :src="auth.user.avatar_url" :alt="auth.user?.name"
                      class="h-full w-full object-cover" />
                    <span v-else class="flex h-full w-full items-center justify-center bg-navy-100 text-navy-700 text-sm font-bold font-display">
                      {{ initials }}
                    </span>
                  </span>
                  <div class="min-w-0 flex-1">
                    <p class="font-display font-semibold text-navy-900 text-sm leading-snug break-words">{{ auth.user?.name }}</p>
                    <p class="text-xs text-paper-500 font-body capitalize mt-0.5">{{ auth.user?.role }}</p>
                  </div>
                </div>

                <!-- Dashboard link -->
                <RouterLink :to="dashboardPath" @click="avatarOpen = false"
                  class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold font-display text-navy-700 hover:bg-navy-50 transition-colors">
                  <svg class="w-4 h-4 text-navy-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                  </svg>
                  My Dashboard
                  <span v-if="notifStore.unreadCount > 0"
                    class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold font-display bg-red-500 text-white rounded-full leading-none">
                    {{ notifStore.unreadCount > 99 ? '99+' : notifStore.unreadCount }}
                  </span>
                </RouterLink>

                <!-- Logout -->
                <button @click="avatarOpen = false; showLogoutDialog = true"
                  class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold font-display text-red-600 hover:bg-red-50 transition-colors border-t border-paper-100">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                  </svg>
                  Log Out
                </button>
              </div>
            </Transition>
          </div>
        </template>
        <!-- In dashboard: sidebar handles navigation -->
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
        <RouterLink @click="mobileOpen = false" to="/jobs"
          class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold font-display text-navy-100 hover:bg-white/10 transition-colors">
          Tuition Jobs
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
          <!-- Mobile user info -->
          <div class="flex items-center gap-3 px-3 py-2.5 border-t border-white/10 mt-1">
            <span class="flex h-8 w-8 shrink-0 rounded-full overflow-hidden border border-white/20">
              <img v-if="auth.user?.avatar_url" :src="auth.user.avatar_url" :alt="auth.user?.name"
                class="h-full w-full object-cover" />
              <span v-else class="flex h-full w-full items-center justify-center bg-navy-600 text-white text-xs font-bold font-display">
                {{ initials }}
              </span>
            </span>
            <div>
              <p class="text-sm font-semibold font-display text-white leading-tight">{{ auth.user?.name }}</p>
              <p class="text-xs text-navy-300 font-body capitalize">{{ auth.user?.role }}</p>
            </div>
          </div>
          <RouterLink @click="mobileOpen = false" :to="dashboardPath"
            class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold font-display text-navy-100 hover:bg-white/10 transition-colors">
            My Dashboard
            <span v-if="notifStore.unreadCount > 0"
              class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold font-display bg-red-500 text-white rounded-full leading-none">
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { useNotificationStore } from '@/stores/notification.js'
import { notificationApi } from '@/api/notifications.js'
import { adminApi } from '@/api/admin.js'
import LogoutConfirmDialog from './LogoutConfirmDialog.vue'
import { toast } from 'vue-sonner'

const auth        = useAuthStore()
const notifStore  = useNotificationStore()
const router      = useRouter()
const $route      = useRoute()

onMounted(async () => {
  if (!auth.isAuthenticated || notifStore.initialized) return
  try {
    if (auth.isAdmin) {
      const { data } = await adminApi.getNotifications({ per_page: 1 })
      notifStore.setUnread(data.unread ?? 0)
    } else if (auth.isTutor || auth.isGuardian) {
      const { data } = await notificationApi.getAll()
      notifStore.setUnread(data.unread)
    }
  } catch {}
})

const mobileOpen       = ref(false)
const avatarOpen       = ref(false)
const avatarDropdownRef = ref(null)
const showLogoutDialog = ref(false)
const logoutToast = { id: 'auth-logout', position: 'top-right' }

const inDashboard = computed(() =>
  $route.path.startsWith('/tutor/') || $route.path.startsWith('/guardian/') || $route.path.startsWith('/admin/')
)

const dashboardPath = computed(() => {
  if (auth.isTutor) return '/tutor/dashboard'
  if (auth.isAdmin) return '/admin/dashboard'
  return '/guardian/dashboard'
})

const initials = computed(() => {
  const name = auth.user?.name || ''
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
})

function onClickOutside(e) {
  if (avatarDropdownRef.value && !avatarDropdownRef.value.contains(e.target)) {
    avatarOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', onClickOutside, true))
onUnmounted(() => document.removeEventListener('click', onClickOutside, true))

async function confirmLogout() {
  showLogoutDialog.value = false
  mobileOpen.value = false
  avatarOpen.value = false
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

.dropdown-enter-active, .dropdown-leave-active { transition: all 0.15s ease; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-6px) scale(0.97); transform-origin: top right; }
</style>
