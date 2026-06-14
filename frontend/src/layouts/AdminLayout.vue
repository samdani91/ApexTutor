<template>
  <div class="flex h-screen overflow-hidden bg-paper-50">
    <!-- Dark scrim (mobile) -->
    <Transition name="fade">
      <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/40 lg:hidden"
        @click="sidebarOpen = false" />
    </Transition>

    <!-- ─── Sidebar ─────────────────────────────────────────── -->
    <aside
      class="fixed lg:static inset-y-0 left-0 w-64 bg-navy-900 text-navy-100 z-50
             flex flex-col transition-transform duration-200 ease-in-out"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Brand -->
      <div class="flex items-center justify-between px-5 h-16 border-b border-white/10 shrink-0">
        <div class="flex items-center gap-3">
          <span class="flex h-10 w-10 items-center justify-center rounded-md bg-gold-400 font-display text-sm font-bold text-navy-900 shadow-sm">
            TK
          </span>
          <div>
            <p class="font-display font-bold text-white text-base leading-tight">TutorKhujo</p>
            <p class="text-[10px] font-body text-navy-300 uppercase tracking-widest leading-none mt-0.5">Admin Panel</p>
          </div>
        </div>
        <!-- Close button (mobile only) -->
        <button @click="sidebarOpen = false"
          class="lg:hidden p-1.5 rounded-md text-navy-200 hover:text-white hover:bg-white/10 transition-colors" aria-label="Close">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Admin profile (top) -->
      <div class="px-4 py-4 border-b border-white/10 shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center overflow-hidden shrink-0 ring-1 ring-white/15">
            <img v-if="auth.user?.avatar_url" :src="auth.user.avatar_url" class="w-full h-full object-cover" alt="" />
            <span v-else class="font-display font-bold text-sm text-gold-200">{{ initials }}</span>
          </div>
          <div class="min-w-0">
            <p class="font-display font-semibold text-sm text-white truncate leading-tight">{{ auth.user?.name }}</p>
            <p class="text-xs text-navy-300 font-body mt-0.5">Admin</p>
          </div>
        </div>
      </div>

      <!-- Nav items (scrollable middle) -->
      <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
        <RouterLink
          v-for="item in navItems" :key="item.to"
          :to="item.to"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold font-display transition-colors"
          :class="isActive(item.to) ? 'bg-gold-400 text-navy-900 shadow-sm' : 'text-navy-200 hover:bg-white/10 hover:text-white'"
        >
          <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path v-if="item.icon === 'chart'" stroke-linecap="round" stroke-linejoin="round"
              d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
            <path v-else-if="item.icon === 'users'" stroke-linecap="round" stroke-linejoin="round"
              d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
            <path v-else-if="item.icon === 'check'" stroke-linecap="round" stroke-linejoin="round"
              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            <path v-else-if="item.icon === 'link'" stroke-linecap="round" stroke-linejoin="round"
              d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
            <path v-else-if="item.icon === 'unlock'" stroke-linecap="round" stroke-linejoin="round"
              d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
            <path v-else-if="item.icon === 'heart'" stroke-linecap="round" stroke-linejoin="round"
              d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
            <path v-else-if="item.icon === 'star'" stroke-linecap="round" stroke-linejoin="round"
              d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
            <path v-else-if="item.icon === 'analytics'" stroke-linecap="round" stroke-linejoin="round"
              d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
            <path v-else-if="item.icon === 'list'" stroke-linecap="round" stroke-linejoin="round"
              d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
            <path v-else-if="item.icon === 'database'" stroke-linecap="round" stroke-linejoin="round"
              d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>
            <path v-else-if="item.icon === 'shield'" stroke-linecap="round" stroke-linejoin="round"
              d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            <path v-else-if="item.icon === 'photo'" stroke-linecap="round" stroke-linejoin="round"
              d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316zM16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
            <path v-else-if="item.icon === 'ticket'" stroke-linecap="round" stroke-linejoin="round"
              d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z"/>
            <path v-else-if="item.icon === 'chat'" stroke-linecap="round" stroke-linejoin="round"
              d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
          </svg>
          {{ item.label }}
          <span v-if="getNavCount(item.to) > 0"
            class="ml-auto text-[10px] font-bold font-display bg-red-500 text-white rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 leading-none">
            {{ getNavCount(item.to) > 99 ? '99+' : getNavCount(item.to) }}
          </span>
        </RouterLink>
      </nav>

      <!-- Bottom: Homepage + Settings + Logout -->
      <div class="p-3 border-t border-white/10 space-y-1 shrink-0">
        <RouterLink to="/"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold font-display text-navy-200 hover:bg-white/10 hover:text-white transition-colors w-full"
        >
          <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253M3 12a8.959 8.959 0 01.284-2.253"/>
          </svg>
          Visit Homepage
        </RouterLink>

        <RouterLink to="/admin/settings"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold font-display transition-colors w-full"
          :class="$route.path.startsWith('/admin/settings') ? 'bg-gold-400 text-navy-900 shadow-sm' : 'text-navy-200 hover:bg-white/10 hover:text-white'"
        >
          <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Settings
        </RouterLink>

        <button @click="sidebarOpen = false; showLogoutDialog = true"
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold font-display bg-red-600 text-white hover:bg-red-700 transition-colors">
          <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
          </svg>
          Log Out
        </button>
      </div>
    </aside>

    <!-- ─── Main area ─────────────────────────────────────────── -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Mobile top bar -->
      <div class="lg:hidden bg-navy-900 text-navy-100 h-14 flex items-center px-4 gap-3 shrink-0">
        <button @click="sidebarOpen = true"
          class="p-1.5 rounded-md text-navy-100 hover:bg-white/10 transition-colors" aria-label="Open menu">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h18"/>
          </svg>
        </button>
        <span class="font-display font-bold text-white">TutorKhujo Admin</span>
      </div>

      <!-- Scrollable content -->
      <main class="admin-content flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
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
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { getInitials } from '@/utils/helpers.js'
import LogoutConfirmDialog from '@/components/common/LogoutConfirmDialog.vue'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'

const auth   = useAuthStore()
const $route = useRoute()
const router = useRouter()

const sidebarOpen      = ref(false)
const showLogoutDialog = ref(false)
const unreadCount      = ref(0)
const initials         = computed(() => getInitials(auth.user?.name))
const logoutToast      = { id: 'auth-logout', position: 'top-right' }

const pendingCounts = reactive({
  verifications:   0,
  pendingChanges:  0,
  reviews:         0,
  feedbacks:       0,
})

function getNavCount(to) {
  if (to === '/admin/verifications')   return pendingCounts.verifications
  if (to === '/admin/pending-changes') return pendingCounts.pendingChanges + (pendingCounts.pendingAvatars ?? 0)
  if (to === '/admin/reviews')         return pendingCounts.reviews
  if (to === '/admin/notifications')   return unreadCount.value
  if (to === '/admin/tickets')         return pendingCounts.openTickets ?? 0
  if (to === '/admin/feedback')        return pendingCounts.feedbacks
  return 0
}

async function loadCounts() {
  try {
    const [dashRes, notifRes] = await Promise.all([
      adminApi.getDashboard(),
      adminApi.getNotifications(),
    ])
    const d = dashRes.data.data || {}
    pendingCounts.verifications  = d.pending_verifications   ?? 0
    pendingCounts.pendingChanges = d.pending_profile_changes ?? 0
    pendingCounts.reviews        = d.pending_reviews         ?? 0
    pendingCounts.pendingAvatars = d.pending_avatars          ?? 0
    pendingCounts.openTickets    = d.open_tickets             ?? 0
    pendingCounts.feedbacks      = d.pending_feedbacks        ?? 0
    unreadCount.value            = notifRes.data.unread       ?? 0
  } catch { /* counts are non-critical */ }
}

watch(() => $route.path, () => {
  sidebarOpen.value = false
  // Re-fetch real counts on every navigation so badges always reflect actual DB state.
  // This avoids badges zeroing out before items are actually processed.
  loadCounts()
})

onMounted(loadCounts)

const navItems = [
  { to: '/admin/dashboard',       label: 'Dashboard',         icon: 'chart'    },
  { to: '/admin/analytics',       label: 'Analytics',         icon: 'analytics'},
  { to: '/admin/users',           label: 'Users',             icon: 'users'    },
  { to: '/admin/verifications',   label: 'Verifications',     icon: 'check'    },
  { to: '/admin/connections',     label: 'Connections',       icon: 'link'     },
  { to: '/admin/pending-changes', label: 'Pending Changes',   icon: 'unlock'   },
  { to: '/admin/reviews',         label: 'Reviews',           icon: 'star'     },
  { to: '/admin/feedback',        label: 'Feedback',          icon: 'chat'     },
  { to: '/admin/reference-data',  label: 'Reference Data',    icon: 'database' },
  { to: '/admin/audit-log',       label: 'Audit Log',         icon: 'shield'   },
  { to: '/admin/tickets',         label: 'Support Tickets',   icon: 'ticket'   },
  { to: '/admin/notifications',   label: 'Notifications',     icon: 'heart'    },
]

function isActive(to) {
  return $route.path.startsWith(to)
}

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
  router.push('/login')
}
</script>

<style scoped>
.admin-content {
  background-color: #FBF9F3;
  background-image:
    linear-gradient(rgba(15, 46, 92, 0.032) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 46, 92, 0.032) 1px, transparent 1px);
  background-size: 34px 34px;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
