<template>
  <div class="flex h-screen overflow-hidden bg-paper-100">
    <!-- Dark scrim (mobile) -->
    <Transition name="fade">
      <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/40 lg:hidden"
        @click="sidebarOpen = false" />
    </Transition>

    <!-- ─── Sidebar ─────────────────────────────────────────── -->
    <aside
      class="fixed lg:static inset-y-0 left-0 w-64 bg-white border-r border-paper-200 z-50
             flex flex-col transition-transform duration-200 ease-in-out"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Brand -->
      <div class="flex items-center justify-between px-5 h-14 border-b border-paper-100 shrink-0">
        <RouterLink to="/" class="group">
          <p class="font-display font-bold text-navy-700 group-hover:text-navy-900 transition-colors text-base leading-tight">
            Tutor<span class="text-blue-600">Khujo</span>
          </p>
          <p class="text-[10px] font-body text-paper-400 uppercase tracking-widest leading-none mt-0.5 capitalize">
            {{ auth.user?.role }}
          </p>
        </RouterLink>
        <!-- Close button (mobile only) -->
        <button @click="sidebarOpen = false"
          class="lg:hidden p-1.5 rounded-md text-paper-400 hover:text-navy-700 hover:bg-navy-50 transition-colors" aria-label="Close">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- User profile -->
      <div class="px-4 py-4 border-b border-paper-100 shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-navy-100 flex items-center justify-center overflow-hidden shrink-0 ring-2 ring-paper-100">
            <img v-if="auth.user?.avatar_url" :src="auth.user.avatar_url" class="w-full h-full object-cover" alt="" />
            <span v-else class="font-display font-bold text-sm text-navy-700">{{ initials }}</span>
          </div>
          <div class="min-w-0">
            <p class="font-display font-semibold text-sm text-navy-900 truncate leading-tight">{{ auth.user?.name }}</p>
            <p class="text-xs text-paper-400 font-body capitalize mt-0.5">{{ auth.user?.role }}</p>
          </div>
        </div>
      </div>

      <!-- Nav items -->
      <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
        <RouterLink
          v-for="item in navItems" :key="item.to"
          :to="item.to"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold font-display transition-colors"
          :class="$route.path.startsWith(item.to) ? 'bg-navy-700 text-white' : 'text-navy-700 hover:bg-navy-50'"
        >
          <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path v-if="item.icon === 'home'" stroke-linecap="round" stroke-linejoin="round"
              d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
            <path v-else-if="item.icon === 'user'" stroke-linecap="round" stroke-linejoin="round"
              d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path v-else-if="item.icon === 'plus'" stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            <path v-else-if="item.icon === 'search'" stroke-linecap="round" stroke-linejoin="round"
              d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
            <path v-else-if="item.icon === 'heart'" stroke-linecap="round" stroke-linejoin="round"
              d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
            <path v-else-if="item.icon === 'bell'" stroke-linecap="round" stroke-linejoin="round"
              d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
          </svg>
          {{ item.label }}
          <span v-if="item.badge && unreadCount > 0"
            class="ml-auto text-[10px] font-bold font-display bg-red-500 text-white rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shrink-0">
            {{ unreadCount > 99 ? '99+' : unreadCount }}
          </span>
        </RouterLink>
      </nav>

      <!-- Bottom: Settings + Home link + Logout -->
      <div class="p-3 border-t border-paper-100 space-y-1 shrink-0">
        <RouterLink :to="settingsPath"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold font-display transition-colors"
          :class="$route.path.endsWith('/settings') ? 'bg-navy-700 text-white' : 'text-navy-700 hover:bg-navy-50'"
        >
          <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Settings
        </RouterLink>
        <RouterLink to="/"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold font-display text-navy-700 hover:bg-navy-50 transition-colors w-full"
        >
          <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253M3 12a8.959 8.959 0 01.284-2.253"/>
          </svg>
          Visit Homepage
        </RouterLink>

        <button @click="sidebarOpen = false; showLogoutDialog = true"
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold font-display text-red-600 border border-red-200 hover:bg-red-50 transition-colors">
          <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
          </svg>
          Log out
        </button>
      </div>
    </aside>

    <!-- ─── Main area ─────────────────────────────────────────── -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Mobile top bar -->
      <div class="lg:hidden bg-white border-b border-paper-200 h-14 flex items-center px-4 gap-3 shrink-0">
        <button @click="sidebarOpen = true"
          class="p-1.5 rounded-md text-navy-700 hover:bg-navy-50 transition-colors" aria-label="Open menu">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h18"/>
          </svg>
        </button>
        <RouterLink to="/" class="font-display font-bold text-navy-700 hover:text-navy-900 transition-colors">
          Tutor<span class="text-blue-600">Khujo</span>
        </RouterLink>
        <span class="text-paper-300 text-sm">·</span>
        <span class="font-display font-semibold text-sm text-navy-800 truncate">{{ currentLabel }}</span>
      </div>

      <!-- Scrollable content -->
      <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
        <RouterView />
      </main>
    </div>

    <LogoutConfirmDialog
      :show="showLogoutDialog"
      @confirm="confirmLogout"
      @cancel="showLogoutDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import LogoutConfirmDialog from '@/components/common/LogoutConfirmDialog.vue'
import { useAuthStore } from '@/stores/auth.js'
import { useNotificationStore } from '@/stores/notification.js'
import { getInitials } from '@/utils/helpers.js'
import { notificationApi } from '@/api/notifications.js'
import { toast } from 'vue-sonner'

const auth     = useAuthStore()
const notifStore = useNotificationStore()
const $route   = useRoute()
const router   = useRouter()

const sidebarOpen      = ref(false)
const showLogoutDialog = ref(false)
const initials         = computed(() => getInitials(auth.user?.name))
const unreadCount      = computed(() => notifStore.unreadCount)
const logoutToast      = { id: 'auth-logout', position: 'top-right' }

watch(() => $route.path, () => { sidebarOpen.value = false })

onMounted(async () => {
  try {
    const { data } = await notificationApi.getAll()
    notifStore.setUnread(data.unread)
  } catch {}
})

const navItems = computed(() => {
  if (auth.isTutor) return [
    { to: '/tutor/dashboard',      label: 'Dashboard',      icon: 'home'  },
    { to: '/tutor/profile',        label: 'My Profile',     icon: 'user'  },
    { to: '/tutor/notifications',  label: 'Notifications',  icon: 'bell', badge: true },
  ]
  if (auth.isGuardian) return [
    { to: '/guardian/dashboard',     label: 'Dashboard',     icon: 'home'   },
    { to: '/guardian/profile',       label: 'My Profile',    icon: 'user'   },
    { to: '/search',                 label: 'Find Tutors',   icon: 'search' },
    { to: '/guardian/shortlist',     label: 'Shortlist',     icon: 'heart'  },
    { to: '/guardian/notifications', label: 'Notifications', icon: 'bell', badge: true },
  ]
  return []
})

const settingsPath = computed(() => auth.isTutor ? '/tutor/settings' : '/guardian/settings')

const currentLabel = computed(() => {
  const match = navItems.value.find(i => $route.path.startsWith(i.to))
  return match?.label ?? 'Menu'
})

async function confirmLogout() {
  showLogoutDialog.value = false
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
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
