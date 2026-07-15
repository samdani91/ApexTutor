<template>
  <div class="dashboard-card reveal">
    <div class="mb-4 flex items-center justify-between gap-3">
      <div class="flex items-center gap-2.5 min-w-0">
        <span class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-md"
          :class="hasUnread ? 'bg-gold-400 text-navy-900 shadow-sm' : 'bg-navy-50 text-navy-600'">
          <svg :class="hasUnread ? 'bell-ring' : ''"
            class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
          </svg>
          <span v-if="hasUnread"
            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 font-display text-[10px] font-bold text-white ring-2 ring-white">
            {{ unreadCount > 99 ? '99+' : unreadCount }}
          </span>
        </span>
        <div class="min-w-0">
          <p class="font-display text-[10px] font-bold uppercase tracking-[0.14em] text-paper-400">Notification centre</p>
          <h2 class="font-display text-xl font-bold text-navy-900 truncate">Latest Updates</h2>
        </div>
      </div>
      <RouterLink :to="allNotificationsPath"
        class="shrink-0 rounded-pill bg-gold-400 px-3.5 py-1.5 font-display text-xs font-bold text-navy-900 shadow-sm transition-colors hover:bg-gold-300">
        View all
      </RouterLink>
    </div>

    <p v-if="hasUnread" class="mb-3 rounded-sm border-l-4 border-gold-400 bg-gold-50 px-3 py-2 font-body text-sm font-medium text-gold-800">
      {{ unreadCount }} unread notification{{ unreadCount === 1 ? '' : 's' }} need{{ unreadCount === 1 ? 's' : '' }} your attention.
    </p>

    <div v-if="!notifStore.initialized" class="text-paper-500 font-body text-sm">Loading…</div>

    <p v-else-if="!latest.length" class="text-sm text-paper-400 font-body">
      No updates yet — we'll let you know when something happens.
    </p>

    <ul v-else class="overflow-hidden rounded-lg border border-paper-200 bg-white divide-y divide-paper-100">
      <li v-for="n in latest" :key="n.id"
        class="flex items-start gap-3 px-4 py-3 transition-colors"
        :class="n.read_at ? 'hover:bg-paper-50' : 'bg-gold-50/70'">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full"
          :class="n.read_at ? 'bg-paper-200' : 'bg-gold-500'"></span>
        <div class="min-w-0 flex-1">
          <p class="font-body text-sm leading-snug text-navy-800">{{ n.data.message }}</p>
          <p class="mt-0.5 font-body text-xs text-paper-400">{{ timeAgo(n.created_at) }}</p>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useNotificationStore } from '@/stores/notification.js'
import { useAuthStore } from '@/stores/auth.js'
import { timeAgo } from '@/utils/helpers.js'

const notifStore = useNotificationStore()
const auth       = useAuthStore()

// Populated by DashboardLayout's existing on-mount fetch — no extra request here.
const latest      = computed(() => notifStore.items.slice(0, 5))
const unreadCount = computed(() => notifStore.unreadCount)
const hasUnread   = computed(() => unreadCount.value > 0)

const allNotificationsPath = computed(() =>
  auth.isTutor ? '/tutor/notifications' : '/guardian/notifications'
)
</script>

<style scoped>
.bell-ring {
  transform-origin: top center;
  animation: bell-ring 2.6s ease-in-out infinite;
}

@keyframes bell-ring {
  0%, 55%, 100% { transform: rotate(0); }
  60% { transform: rotate(12deg); }
  67% { transform: rotate(-10deg); }
  74% { transform: rotate(7deg); }
  81% { transform: rotate(-5deg); }
  88% { transform: rotate(2deg); }
}

@media (prefers-reduced-motion: reduce) {
  .bell-ring { animation: none; }
}
</style>
