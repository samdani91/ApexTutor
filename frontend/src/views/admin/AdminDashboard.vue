<template>
  <div class="admin-dashboard space-y-6">
    <div class="dashboard-card reveal">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="font-display text-xs font-bold uppercase text-gold-600">Admin Dashboard</p>
          <h1 class="mt-1 font-display font-bold text-2xl text-navy-900 md:text-3xl">Admin Overview</h1>
          <p class="mt-2 max-w-2xl font-body text-sm leading-relaxed text-paper-600">
            Monitor tutor verification, user growth, profile changes, reviews and active tuition connections.
          </p>
        </div>
        <RouterLink to="/admin/users" class="btn-primary w-fit rounded-sm px-5 py-2.5 text-sm">
          Manage Users
        </RouterLink>
      </div>
    </div>

    <div v-if="loading" class="dashboard-card reveal flex items-center justify-center py-16 text-paper-500 font-body">
      <div class="mr-3 h-8 w-8 rounded-full border-4 border-navy-100 border-t-navy-700 animate-spin"></div>
      Loading…
    </div>
    <template v-else>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="metric-card reveal">
          <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-md bg-navy-50 text-navy-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
            </svg>
          </div>
          <p class="font-display font-bold text-3xl text-navy-700">{{ stats.total_tutors }}</p>
          <p class="text-sm text-paper-500 font-body mt-1">Total Tutors</p>
          <p class="text-xs text-emerald-600 font-semibold mt-0.5">{{ stats.verified_tutors }} Verified</p>
        </div>
        <div class="metric-card reveal delay-1">
          <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-md bg-gold-50 text-gold-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M12 3.75l7.5 3.75v4.875c0 4.525-3.103 8.474-7.5 9.625-4.397-1.151-7.5-5.1-7.5-9.625V7.5L12 3.75Z" />
            </svg>
          </div>
          <p class="font-display font-bold text-3xl text-gold-500">{{ stats.pending_verifications }}</p>
          <p class="text-sm text-paper-500 font-body mt-1">Pending Verifications</p>
          <RouterLink to="/admin/verifications" class="text-xs text-navy-700 font-semibold hover:underline mt-1 block">Review</RouterLink>
        </div>
        <div class="metric-card reveal delay-2">
          <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-md bg-emerald-50 text-emerald-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>
          </div>
          <p class="font-display font-bold text-3xl text-emerald-600">{{ stats.active_connections }}</p>
          <p class="text-sm text-paper-500 font-body mt-1">Active Connections</p>
          <p class="text-xs text-amber-600 font-semibold mt-0.5">{{ stats.pending_connections }} Pending</p>
        </div>
        <div class="metric-card reveal delay-3">
          <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-md bg-navy-50 text-navy-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72M12 15.75a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm0 0a6.75 6.75 0 0 0-6.75 6.75M12 15.75a6.75 6.75 0 0 1 6.75 6.75" />
            </svg>
          </div>
          <p class="font-display font-bold text-3xl text-navy-700">{{ stats.total_users }}</p>
          <p class="text-sm text-paper-500 font-body mt-1">Total Users</p>
          <p class="text-xs text-paper-400 font-body mt-0.5">{{ stats.total_guardians }} Guardians</p>
          <RouterLink to="/admin/users" class="text-xs text-navy-700 font-semibold hover:underline mt-1 block">Manage</RouterLink>
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div class="action-card reveal">
          <div>
            <p class="font-display text-sm font-bold text-navy-900">Pending Profile Changes</p>
            <p class="mt-1 font-body text-sm text-paper-600">Review tutor edits before they go live.</p>
          </div>
          <p class="font-display font-bold text-3xl text-amber-600">{{ stats.pending_profile_changes ?? 0 }}</p>
          <RouterLink to="/admin/pending-changes" class="btn-outline rounded-sm px-4 py-2 text-sm">Review</RouterLink>
        </div>
        <div class="action-card reveal delay-1">
          <div>
            <p class="font-display text-sm font-bold text-navy-900">Pending Reviews</p>
            <p class="mt-1 font-body text-sm text-paper-600">Moderate new guardian reviews before publishing.</p>
          </div>
          <p class="font-display font-bold text-3xl text-gold-500">{{ stats.pending_reviews ?? 0 }}</p>
          <RouterLink to="/admin/reviews" class="btn-outline rounded-sm px-4 py-2 text-sm">Moderate</RouterLink>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { adminApi } from '@/api/admin.js'

const stats = ref({})
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await adminApi.getDashboard()
    stats.value = data.data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.dashboard-card {
  @apply rounded-md border border-paper-200 bg-white p-5 shadow-lg md:p-6;
}

.metric-card {
  @apply rounded-md border border-paper-200 bg-white p-5 text-center shadow-sm;
}

.action-card {
  @apply flex flex-col gap-4 rounded-md border border-paper-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between;
}

.reveal {
  animation: reveal-up 0.54s ease both;
}

.delay-1 { animation-delay: 70ms; }
.delay-2 { animation-delay: 140ms; }
.delay-3 { animation-delay: 210ms; }

@keyframes reveal-up {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .reveal {
    animation: none;
  }
}
</style>
