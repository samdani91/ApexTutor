<template>
  <div class="admin-dashboard space-y-5">

    <!-- Header -->
    <div class="dashboard-card reveal">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="font-display text-xs font-bold uppercase text-gold-600">Admin Dashboard</p>
          <h1 class="mt-1 font-display font-bold text-2xl text-navy-900 md:text-3xl">Admin Overview</h1>
          <p class="mt-1.5 max-w-2xl font-body text-sm leading-relaxed text-paper-500 hidden sm:block">
            Monitor tutor verification, user growth, profile changes, reviews and active tuition connections.
          </p>
        </div>
        <RouterLink to="/admin/users"
          class="btn-primary w-full rounded-sm px-5 py-2.5 text-sm text-center sm:w-fit">
          Manage Users
        </RouterLink>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="dashboard-card reveal flex items-center justify-center py-16 text-paper-500 font-body">
      <div class="mr-3 h-8 w-8 rounded-full border-4 border-navy-100 border-t-navy-700 animate-spin"></div>
      Loading…
    </div>

    <template v-else>

      <!-- Attention banner -->
      <div v-if="totalActionRequired > 0"
        class="dashboard-card reveal border-amber-200 bg-amber-50/60 flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-5">
        <div class="flex items-center gap-3 min-w-0 flex-1">
          <div class="w-10 h-10 rounded-md bg-amber-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
          </div>
          <div class="min-w-0">
            <p class="font-display font-bold text-amber-900 text-base leading-tight">
              {{ totalActionRequired }} item{{ totalActionRequired === 1 ? '' : 's' }} need{{ totalActionRequired === 1 ? 's' : '' }} your attention
            </p>
            <p class="text-xs text-amber-700 font-body mt-0.5 leading-relaxed">{{ attentionSummary }}</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:shrink-0">
          <RouterLink v-if="stats.pending_verifications" to="/admin/verifications"
            class="text-center text-xs font-semibold font-display px-3 py-2 rounded-sm bg-amber-600 text-white hover:bg-amber-700 transition-colors">
            Verifications
          </RouterLink>
          <RouterLink v-if="stats.pending_profile_changes" to="/admin/pending-changes"
            class="text-center text-xs font-semibold font-display px-3 py-2 rounded-sm bg-amber-600 text-white hover:bg-amber-700 transition-colors">
            Profile Changes
          </RouterLink>
          <RouterLink v-if="stats.pending_reviews" to="/admin/reviews"
            class="text-center text-xs font-semibold font-display px-3 py-2 rounded-sm bg-amber-600 text-white hover:bg-amber-700 transition-colors">
            Reviews
          </RouterLink>
          <RouterLink v-if="stats.open_tickets" to="/admin/tickets"
            class="text-center text-xs font-semibold font-display px-3 py-2 rounded-sm bg-amber-600 text-white hover:bg-amber-700 transition-colors">
            Support Tickets
          </RouterLink>
        </div>
      </div>

      <!-- Metric cards: 2-col on mobile, 4-col on xl -->
      <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="metric-card reveal">
          <div class="metric-icon bg-navy-50 text-navy-700">
            <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
            </svg>
          </div>
          <p class="metric-value text-navy-700">{{ formatCount(animatedStats.total_tutors) }}</p>
          <p class="metric-label">Total Tutors</p>
          <p class="metric-sub text-emerald-600">{{ formatCount(animatedStats.verified_tutors) }} verified</p>
        </div>

        <div class="metric-card reveal delay-1">
          <div class="metric-icon bg-gold-50 text-gold-700">
            <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M12 3.75l7.5 3.75v4.875c0 4.525-3.103 8.474-7.5 9.625-4.397-1.151-7.5-5.1-7.5-9.625V7.5L12 3.75Z" />
            </svg>
          </div>
          <p class="metric-value text-gold-500">{{ formatCount(animatedStats.pending_verifications) }}</p>
          <p class="metric-label">Pending Verifications</p>
          <RouterLink to="/admin/verifications" class="metric-link">Review →</RouterLink>
        </div>

        <div class="metric-card reveal delay-2">
          <div class="metric-icon bg-emerald-50 text-emerald-700">
            <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>
          </div>
          <p class="metric-value text-emerald-600">{{ formatCount(animatedStats.active_connections) }}</p>
          <p class="metric-label">Active Connections</p>
          <p class="metric-sub text-amber-600">{{ formatCount(animatedStats.pending_connections) }} pending</p>
        </div>

        <div class="metric-card reveal delay-3">
          <div class="metric-icon bg-navy-50 text-navy-700">
            <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72M12 15.75a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm0 0a6.75 6.75 0 0 0-6.75 6.75M12 15.75a6.75 6.75 0 0 1 6.75 6.75" />
            </svg>
          </div>
          <p class="metric-value text-navy-700">{{ formatCount(animatedStats.total_users) }}</p>
          <p class="metric-label">Total Users</p>
          <p class="metric-sub text-paper-400">{{ formatCount(animatedStats.total_guardians) }} guardians</p>
        </div>
      </div>

      <!-- Action cards: 2-col on mobile, 4-col on xl -->
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">

        <div class="action-card reveal">
          <div class="flex-1 min-w-0">
            <p class="font-display text-sm font-bold text-navy-900">Profile Changes</p>
            <p class="mt-0.5 font-body text-xs text-paper-500 leading-relaxed">Review tutor edits before they go live.</p>
          </div>
          <div class="flex items-center justify-between gap-3 sm:contents">
            <p class="font-display font-bold text-3xl text-amber-600 shrink-0">{{ formatCount(animatedStats.pending_profile_changes) }}</p>
            <RouterLink to="/admin/pending-changes" class="action-btn shrink-0">Review</RouterLink>
          </div>
        </div>

        <div class="action-card reveal delay-1">
          <div class="flex-1 min-w-0">
            <p class="font-display text-sm font-bold text-navy-900">Pending Reviews</p>
            <p class="mt-0.5 font-body text-xs text-paper-500 leading-relaxed">Moderate guardian reviews before publishing.</p>
          </div>
          <div class="flex items-center justify-between gap-3 sm:contents">
            <p class="font-display font-bold text-3xl text-gold-500 shrink-0">{{ formatCount(animatedStats.pending_reviews) }}</p>
            <RouterLink to="/admin/reviews" class="action-btn shrink-0">Moderate</RouterLink>
          </div>
        </div>

        <div class="action-card reveal delay-2">
          <div class="flex-1 min-w-0">
            <p class="font-display text-sm font-bold text-navy-900">Pending Videos</p>
            <p class="mt-0.5 font-body text-xs text-paper-500 leading-relaxed">Approve teaching videos before they go public.</p>
          </div>
          <div class="flex items-center justify-between gap-3 sm:contents">
            <p class="font-display font-bold text-3xl text-navy-600 shrink-0">{{ formatCount(animatedStats.pending_videos) }}</p>
            <RouterLink to="/admin/users" class="action-btn shrink-0">Review</RouterLink>
          </div>
        </div>

        <div class="action-card reveal delay-3">
          <div class="flex-1 min-w-0">
            <p class="font-display text-sm font-bold text-navy-900">Open Tickets</p>
            <p class="mt-0.5 font-body text-xs text-paper-500 leading-relaxed">Open and in-progress support tickets.</p>
          </div>
          <div class="flex items-center justify-between gap-3 sm:contents">
            <p class="font-display font-bold text-3xl text-red-600 shrink-0">{{ formatCount(animatedStats.open_tickets) }}</p>
            <RouterLink to="/admin/tickets" class="action-btn shrink-0">View</RouterLink>
          </div>
        </div>

      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { RouterLink } from 'vue-router'
import { adminApi } from '@/api/admin.js'

const stats = ref({})
const animatedStats = ref({
  total_tutors: 0,
  verified_tutors: 0,
  pending_verifications: 0,
  active_connections: 0,
  pending_connections: 0,
  total_users: 0,
  total_guardians: 0,
  pending_profile_changes: 0,
  pending_reviews: 0,
  pending_videos: 0,
  open_tickets: 0,
})

const attentionSummary = computed(() => {
  const parts = []
  const s = stats.value
  if (s.pending_verifications)   parts.push(`${s.pending_verifications} verification${s.pending_verifications === 1 ? '' : 's'}`)
  if (s.pending_profile_changes) parts.push(`${s.pending_profile_changes} profile change${s.pending_profile_changes === 1 ? '' : 's'}`)
  if (s.pending_reviews)         parts.push(`${s.pending_reviews} review${s.pending_reviews === 1 ? '' : 's'}`)
  if (s.pending_videos)          parts.push(`${s.pending_videos} video${s.pending_videos === 1 ? '' : 's'}`)
  if (s.open_tickets)            parts.push(`${s.open_tickets} open ticket${s.open_tickets === 1 ? '' : 's'}`)
  return parts.join(' · ')
})

const totalActionRequired = computed(() =>
  (stats.value.pending_verifications   ?? 0) +
  (stats.value.pending_profile_changes ?? 0) +
  (stats.value.pending_reviews         ?? 0) +
  (stats.value.pending_videos          ?? 0) +
  (stats.value.open_tickets            ?? 0)
)

const loading = ref(true)
let animationFrame = null

onMounted(async () => {
  try {
    const { data } = await adminApi.getDashboard()
    stats.value = data.data || {}
    animateCounts(stats.value)
  } finally {
    loading.value = false
  }
})

onBeforeUnmount(() => {
  if (animationFrame) cancelAnimationFrame(animationFrame)
})

function animateCounts(targets) {
  const keys = Object.keys(animatedStats.value)
  const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches

  if (reducedMotion) {
    keys.forEach(key => { animatedStats.value[key] = Number(targets[key] ?? 0) })
    return
  }

  const duration = 900
  const startedAt = performance.now()

  function tick(now) {
    const progress = Math.min((now - startedAt) / duration, 1)
    const eased = 1 - Math.pow(1 - progress, 3)
    keys.forEach(key => {
      animatedStats.value[key] = Math.round(Number(targets[key] ?? 0) * eased)
    })
    if (progress < 1) animationFrame = requestAnimationFrame(tick)
  }

  animationFrame = requestAnimationFrame(tick)
}

function formatCount(value) {
  return Number(value || 0).toLocaleString()
}
</script>

<style scoped>
.dashboard-card {
  @apply rounded-md border border-paper-200 bg-white p-4 shadow-lg md:p-6;
}

/* Metric cards */
.metric-card {
  @apply rounded-md border border-paper-200 bg-white p-4 text-center shadow-sm md:p-5;
}

.metric-icon {
  @apply mx-auto mb-2 flex h-9 w-9 items-center justify-center rounded-md md:mb-3 md:h-10 md:w-10;
}

.metric-value {
  @apply font-display font-bold text-2xl md:text-3xl;
}

.metric-label {
  @apply mt-0.5 font-body text-xs text-paper-500 md:text-sm;
}

.metric-sub {
  @apply mt-0.5 text-xs font-semibold;
}

.metric-link {
  @apply mt-1 block text-xs font-semibold text-navy-700 hover:underline;
}

/* Action cards */
.action-card {
  @apply flex flex-col gap-3 rounded-md border border-paper-200 bg-white p-4 shadow-sm
         sm:flex-row sm:items-center sm:justify-between sm:gap-4 md:p-5;
}

.action-btn {
  @apply rounded-sm border border-navy-200 px-3 py-1.5 text-xs font-semibold font-display
         text-navy-700 transition-colors hover:bg-navy-50 whitespace-nowrap;
}

/* Animations */
.reveal {
  animation: reveal-up 0.54s ease both;
}

.delay-1 { animation-delay: 70ms; }
.delay-2 { animation-delay: 140ms; }
.delay-3 { animation-delay: 210ms; }

@keyframes reveal-up {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
  .reveal { animation: none; }
}
</style>
