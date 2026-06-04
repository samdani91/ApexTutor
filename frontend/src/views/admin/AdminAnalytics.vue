<template>
  <div class="space-y-6">
    <div class="analytics-hero reveal">
      <div>
        <p class="font-display text-xs font-bold uppercase text-gold-600">Analytics</p>
        <h1 class="mt-1 font-display text-2xl font-bold text-navy-900 md:text-3xl">Platform Insights</h1>
        <p class="mt-2 max-w-2xl font-body text-sm leading-relaxed text-paper-600">
          Track connection flow, conversion quality and tutor performance across the platform.
        </p>
      </div>
    </div>

    <div v-if="loading" class="analytics-card flex items-center justify-center py-16 text-paper-500 font-body">
      <div class="mr-3 h-8 w-8 rounded-full border-4 border-navy-100 border-t-navy-700 animate-spin"></div>
      Loading...
    </div>

    <template v-else>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="summary-card reveal">
          <span class="summary-icon bg-navy-50 text-navy-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
            </svg>
          </span>
          <p class="summary-value text-navy-800">{{ totalConnections.toLocaleString() }}</p>
          <p class="summary-label">Total Requests</p>
        </div>
        <div class="summary-card reveal delay-1">
          <span class="summary-icon bg-emerald-50 text-emerald-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
          </span>
          <p class="summary-value text-emerald-600">{{ connectedTotal.toLocaleString() }}</p>
          <p class="summary-label">Connected</p>
        </div>
        <div class="summary-card reveal delay-2">
          <span class="summary-icon bg-gold-50 text-gold-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941"/>
            </svg>
          </span>
          <p class="summary-value text-gold-600">{{ conversionRate }}%</p>
          <p class="summary-label">Conversion</p>
        </div>
        <div class="summary-card reveal delay-3">
          <span class="summary-icon bg-blue-50 text-blue-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557L1.938 10.385a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345l2.125-5.111Z"/>
            </svg>
          </span>
          <p class="summary-value text-blue-700">{{ analytics.top_tutors?.length || 0 }}</p>
          <p class="summary-label">Ranked Tutors</p>
        </div>
      </div>

      <div class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(360px,0.65fr)]">
        <section class="analytics-card reveal">
          <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <p class="section-kicker">Month Detail</p>
              <h2 class="section-title">Monthly Connections</h2>
            </div>
            <div class="w-full sm:w-56">
              <DropSelect
                v-model="selectedMonth"
                :options="monthOptions"
                placeholder="Select month"
              />
            </div>
          </div>

          <div v-if="!analytics.monthly_connections?.length" class="empty-state">No monthly data yet.</div>
          <div v-else-if="selectedMonthData" class="month-detail">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <p class="font-display text-lg font-bold text-navy-900">{{ selectedMonthData.month }}</p>
                <p class="mt-1 max-w-md font-body text-sm text-paper-500">
                  {{ Number(selectedMonthData.connected || 0).toLocaleString() }} connected from
                  {{ Number(selectedMonthData.connections || 0).toLocaleString() }} total requests.
                </p>
              </div>
              <span class="w-fit rounded-full bg-emerald-50 px-3 py-1 text-sm font-bold font-display text-emerald-700">
                {{ selectedMonthRate }}% conversion
              </span>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
              <div class="month-stat-card">
                <p class="stat-label">Requests</p>
                <p class="stat-value text-navy-800">{{ Number(selectedMonthData.connections || 0).toLocaleString() }}</p>
              </div>
              <div class="month-stat-card">
                <p class="stat-label">Connected</p>
                <p class="stat-value text-emerald-600">{{ Number(selectedMonthData.connected || 0).toLocaleString() }}</p>
              </div>
              <div class="month-stat-card">
                <p class="stat-label">Not Connected</p>
                <p class="stat-value text-amber-600">{{ unconnectedSelectedMonth.toLocaleString() }}</p>
              </div>
            </div>

            <div class="mt-5 rounded-md border border-paper-100 bg-white p-4">
              <div class="mb-2 flex items-center justify-between">
                <span class="font-display text-xs font-bold uppercase tracking-wide text-paper-400">Conversion Progress</span>
                <span class="font-display text-sm font-bold text-navy-800">{{ selectedMonthRate }}%</span>
              </div>
              <div class="h-3 overflow-hidden rounded-full bg-paper-100">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-700"
                  :style="{ width: `${selectedMonthRate}%` }"></div>
              </div>
              <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-body text-paper-500">
                <div class="rounded-sm bg-emerald-50 px-2.5 py-2 text-emerald-700">
                  Connected: <span class="font-bold">{{ Number(selectedMonthData.connected || 0).toLocaleString() }}</span>
                </div>
                <div class="rounded-sm bg-amber-50 px-2.5 py-2 text-amber-700">
                  Remaining: <span class="font-bold">{{ unconnectedSelectedMonth.toLocaleString() }}</span>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="analytics-card reveal delay-1">
          <div class="mb-5">
            <p class="section-kicker">Pipeline</p>
            <h2 class="section-title">Connections By Status</h2>
          </div>

          <div v-if="!statusRows.length" class="empty-state">No status data yet.</div>
          <div v-else class="space-y-3">
            <div v-for="row in statusRows" :key="row.status" class="status-row">
              <div class="flex items-center justify-between gap-3">
                <span class="rounded-full border px-2.5 py-1 text-xs font-bold font-display capitalize"
                  :class="statusClass(row.status)">
                  {{ row.label }}
                </span>
                <span class="font-display text-sm font-bold text-navy-900">{{ row.count.toLocaleString() }}</span>
              </div>
              <div class="mt-2 h-2 overflow-hidden rounded-full bg-paper-100">
                <div class="h-full rounded-full transition-all duration-700"
                  :class="statusBarClass(row.status)"
                  :style="{ width: `${row.percent}%` }"></div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <section class="analytics-card reveal delay-2">
        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="section-kicker">Quality Signal</p>
            <h2 class="section-title">Top Tutors By Reviews</h2>
          </div>
          <span class="rounded-full bg-gold-50 px-3 py-1 text-xs font-semibold font-display text-gold-700">
            Review leaders
          </span>
        </div>

        <div v-if="!analytics.top_tutors?.length" class="empty-state">No tutor review data yet.</div>
        <div v-else class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
          <div v-for="(tutor, i) in analytics.top_tutors" :key="tutor.id" class="tutor-rank-card">
            <span class="rank-badge" :class="rankClass(i)">{{ i + 1 }}</span>
            <div class="min-w-0 flex-1">
              <p class="truncate font-display text-sm font-bold text-navy-900">{{ tutor.user?.name || 'Unnamed Tutor' }}</p>
              <div class="mt-1 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-gold-50 px-2 py-0.5 text-xs font-semibold text-gold-700">
                  <span>★</span>
                  {{ tutor.rating ?? '-' }}
                </span>
                <span class="text-xs font-body text-paper-500">{{ Number(tutor.review_count || 0).toLocaleString() }} reviews</span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'

const analytics = ref({})
const loading   = ref(true)
const selectedMonth = ref('')

const totalConnections = computed(() =>
  Object.values(analytics.value.connection_statuses ?? {}).reduce((a, b) => a + Number(b || 0), 0)
)

const connectedTotal = computed(() =>
  Number(analytics.value.connection_statuses?.connected ?? analytics.value.connection_statuses?.confirmed ?? 0)
)

const conversionRate = computed(() =>
  totalConnections.value ? Math.round((connectedTotal.value / totalConnections.value) * 100) : 0
)

const monthOptions = computed(() =>
  (analytics.value.monthly_connections ?? []).map(row => ({
    value: row.month,
    label: row.month,
  }))
)

const selectedMonthData = computed(() =>
  (analytics.value.monthly_connections ?? []).find(row => row.month === selectedMonth.value) ?? null
)

const selectedMonthRate = computed(() =>
  selectedMonthData.value ? monthRate(selectedMonthData.value) : 0
)

const unconnectedSelectedMonth = computed(() =>
  Math.max(
    0,
    Number(selectedMonthData.value?.connections || 0) - Number(selectedMonthData.value?.connected || 0)
  )
)

const statusRows = computed(() =>
  Object.entries(analytics.value.connection_statuses ?? {}).map(([status, count]) => {
    const numeric = Number(count || 0)
    return {
      status,
      count: numeric,
      label: status.replace(/_/g, ' '),
      percent: totalConnections.value ? Math.round((numeric / totalConnections.value) * 100) : 0,
    }
  })
)

function monthRate(month) {
  return month.connections ? Math.round((Number(month.connected || 0) / Number(month.connections || 0)) * 100) : 0
}

function statusClass(s) {
  const m = {
    pending: 'bg-paper-100 text-paper-600 border-paper-200',
    admin_reviewing: 'bg-blue-50 text-blue-700 border-blue-200',
    tutor_contacted: 'bg-purple-50 text-purple-700 border-purple-200',
    connected: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    confirmed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    declined: 'bg-red-50 text-red-700 border-red-200',
    closed: 'bg-paper-100 text-paper-600 border-paper-200',
  }
  return m[s] ?? 'bg-paper-100 text-paper-600 border-paper-200'
}

function statusBarClass(s) {
  const m = {
    pending: 'bg-paper-400',
    admin_reviewing: 'bg-blue-400',
    tutor_contacted: 'bg-purple-400',
    connected: 'bg-emerald-500',
    confirmed: 'bg-emerald-500',
    declined: 'bg-red-400',
    closed: 'bg-paper-300',
  }
  return m[s] ?? 'bg-paper-300'
}

function rankClass(index) {
  if (index === 0) return 'bg-gold-400 text-navy-900'
  if (index === 1) return 'bg-paper-300 text-navy-800'
  if (index === 2) return 'bg-navy-100 text-navy-700'
  return 'bg-paper-100 text-paper-600'
}

onMounted(async () => {
  try {
    const { data } = await adminApi.getAnalytics()
    analytics.value = data.data || {}
    selectedMonth.value = analytics.value.monthly_connections?.[0]?.month ?? ''
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.analytics-hero,
.analytics-card,
.summary-card {
  @apply rounded-md border border-paper-200 bg-white shadow-sm;
}

.analytics-hero {
  @apply p-5 shadow-lg md:p-6;
}

.analytics-card {
  @apply p-5 md:p-6;
}

.summary-card {
  @apply p-5;
}

.summary-icon {
  @apply mb-4 flex h-11 w-11 items-center justify-center rounded-md;
}

.summary-value {
  @apply font-display text-3xl font-bold;
}

.summary-label {
  @apply mt-1 font-body text-sm text-paper-500;
}

.section-kicker {
  @apply font-display text-xs font-bold uppercase tracking-wide text-gold-600;
}

.section-title {
  @apply mt-1 font-display text-xl font-bold text-navy-900;
}

.month-detail,
.status-row,
.tutor-rank-card {
  @apply rounded-md border border-paper-100 bg-paper-50/70 p-4;
}

.month-stat-card {
  @apply rounded-md border border-paper-100 bg-white p-4;
}

.stat-label {
  @apply font-display text-xs font-bold uppercase tracking-wide text-paper-400;
}

.stat-value {
  @apply mt-1 font-display text-2xl font-bold;
}

.tutor-rank-card {
  @apply flex items-center gap-3 bg-white;
}

.rank-badge {
  @apply flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-display text-sm font-bold;
}

.empty-state {
  @apply rounded-md border border-dashed border-paper-200 bg-paper-50 px-4 py-8 text-center font-body text-sm text-paper-400;
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
