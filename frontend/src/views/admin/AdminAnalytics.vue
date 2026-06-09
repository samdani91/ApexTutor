<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="analytics-hero reveal">
      <div>
        <p class="font-display text-xs font-bold uppercase tracking-wide text-gold-600">Analytics</p>
        <h1 class="mt-1 font-display text-2xl font-bold text-navy-900 md:text-3xl">Platform Insights</h1>
        <p class="mt-2 max-w-2xl font-body text-sm leading-relaxed text-paper-600">
          Track connection flow, conversion rates and tutor performance across the platform.
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="analytics-card flex items-center justify-center py-16 text-paper-500 font-body">
      <div class="mr-3 h-8 w-8 rounded-full border-4 border-navy-100 border-t-navy-700 animate-spin"></div>
      Loading analytics…
    </div>

    <template v-else>
      <!-- Summary Cards -->
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="summary-card reveal">
          <span class="summary-icon bg-navy-50 text-navy-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
            </svg>
          </span>
          <p class="summary-value text-navy-800">{{ totalConnections.toLocaleString() }}</p>
          <p class="summary-label">Total Connection Requests</p>
        </div>
        <div class="summary-card reveal delay-1">
          <span class="summary-icon bg-emerald-50 text-emerald-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
          </span>
          <p class="summary-value text-emerald-600">{{ confirmedTotal.toLocaleString() }}</p>
          <p class="summary-label">Confirmed Connections</p>
        </div>
        <div class="summary-card reveal delay-2">
          <span class="summary-icon bg-gold-50 text-gold-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941"/>
            </svg>
          </span>
          <p class="summary-value text-gold-600">{{ conversionRate }}%</p>
          <p class="summary-label">Requests Confirmed (%)</p>
        </div>
        <div class="summary-card reveal delay-3">
          <span class="summary-icon bg-blue-50 text-blue-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557L1.938 10.385a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345l2.125-5.111Z"/>
            </svg>
          </span>
          <p class="summary-value text-blue-700">{{ analytics.top_tutors?.length || 0 }}</p>
          <p class="summary-label">Reviewed Tutors</p>
        </div>
      </div>

      <!-- Connection Activity Over Time -->
      <section class="analytics-card reveal">
        <!-- Section header + date range controls -->
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="section-kicker">Monthly Trend</p>
            <h2 class="section-title">Connection Activity Over Time</h2>
          </div>
          <div class="flex flex-wrap items-end gap-2">
            <div>
              <span class="mb-1 block text-xs font-semibold font-display text-navy-700">From</span>
              <input type="month" v-model="rangeFrom" :max="rangeTo || currentMonth"
                class="input text-sm" />
            </div>
            <div>
              <span class="mb-1 block text-xs font-semibold font-display text-navy-700">To</span>
              <input type="month" v-model="rangeTo" :min="rangeFrom" :max="currentMonth"
                class="input text-sm" />
            </div>
            <button @click="applyRange" :disabled="rangeLoading"
              class="h-10 rounded-sm bg-navy-800 px-4 text-sm font-semibold font-display text-white
                     transition-colors hover:bg-navy-900 disabled:opacity-50">
              <span v-if="rangeLoading" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
              <span v-else>Apply</span>
            </button>
          </div>
        </div>

        <!-- Bar chart -->
        <div v-if="!monthlyData.length" class="empty-state">No data for the selected range.</div>
        <div v-else>
          <!-- Legend -->
          <div class="mb-3 flex items-center gap-5 text-xs font-display text-paper-500">
            <span class="flex items-center gap-1.5">
              <span class="inline-block h-2.5 w-2.5 rounded-sm bg-navy-200"></span>
              Total Requests
            </span>
            <span class="flex items-center gap-1.5">
              <span class="inline-block h-2.5 w-2.5 rounded-sm bg-emerald-400"></span>
              Confirmed
            </span>
            <span class="ml-auto text-paper-400">
              Click a bar for monthly details
            </span>
          </div>

          <!-- Bars -->
          <div class="overflow-x-auto pb-2">
            <div class="flex items-end gap-1.5" :style="{ minWidth: monthlyData.length > 12 ? monthlyData.length * 52 + 'px' : '100%' }">
              <div v-for="m in monthlyData" :key="m.month_key"
                class="group flex flex-1 min-w-[44px] cursor-pointer flex-col items-center gap-1.5"
                @click="selectedMonthKey = m.month_key">
                <!-- Bar group -->
                <div class="relative w-full rounded-t-sm overflow-hidden bg-paper-100"
                  style="height: 80px;">
                  <!-- Requests bar (background) -->
                  <div class="absolute bottom-0 w-full rounded-t-sm bg-navy-200 transition-all duration-500"
                    :style="{ height: barPct(m.connections) + '%' }"></div>
                  <!-- Confirmed bar (foreground) -->
                  <div class="absolute bottom-0 w-full bg-emerald-400 transition-all duration-500"
                    :style="{ height: barPct(m.confirmed) + '%' }"></div>
                  <!-- Selected highlight -->
                  <div v-if="m.month_key === selectedMonthKey"
                    class="absolute inset-0 rounded-t-sm ring-2 ring-navy-600 ring-inset pointer-events-none"></div>
                </div>
                <!-- Month label -->
                <span class="text-center font-display font-semibold leading-tight transition-colors"
                  :class="m.month_key === selectedMonthKey ? 'text-navy-900 text-[11px]' : 'text-paper-400 text-[10px] group-hover:text-paper-600'">
                  {{ shortLabel(m.month) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Selected month detail -->
          <div v-if="selectedMonth" class="mt-5 rounded-md border border-paper-200 bg-paper-50/60 p-4">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p class="font-display text-base font-bold text-navy-900">{{ selectedMonth.month }}</p>
                <p class="mt-0.5 font-body text-sm text-paper-500">
                  {{ Number(selectedMonth.confirmed || 0).toLocaleString() }} confirmed from
                  {{ Number(selectedMonth.connections || 0).toLocaleString() }} total requests
                </p>
              </div>
              <span class="w-fit rounded-full px-3 py-1 text-sm font-bold font-display"
                :class="selectedMonthRate >= 50 ? 'bg-emerald-50 text-emerald-700' : selectedMonthRate >= 25 ? 'bg-gold-50 text-gold-700' : 'bg-red-50 text-red-600'">
                {{ selectedMonthRate }}% converted
              </span>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
              <div class="month-stat-card">
                <p class="stat-label">Total Requests</p>
                <p class="stat-value text-navy-800">{{ Number(selectedMonth.connections || 0).toLocaleString() }}</p>
              </div>
              <div class="month-stat-card">
                <p class="stat-label">Confirmed</p>
                <p class="stat-value text-emerald-600">{{ Number(selectedMonth.confirmed || 0).toLocaleString() }}</p>
              </div>
              <div class="month-stat-card">
                <p class="stat-label">Not Confirmed</p>
                <p class="stat-value text-amber-600">{{ unconfirmedCount.toLocaleString() }}</p>
              </div>
            </div>

            <div class="mt-4 rounded-md border border-paper-100 bg-white p-3">
              <div class="mb-2 flex items-center justify-between">
                <span class="font-display text-xs font-bold uppercase tracking-wide text-paper-400">Conversion Progress</span>
                <span class="font-display text-sm font-bold text-navy-800">{{ selectedMonthRate }}%</span>
              </div>
              <div class="h-3 overflow-hidden rounded-full bg-paper-100">
                <div class="h-full rounded-full transition-all duration-700"
                  :class="selectedMonthRate >= 50 ? 'bg-emerald-500' : selectedMonthRate >= 25 ? 'bg-gold-400' : 'bg-red-400'"
                  :style="{ width: `${selectedMonthRate}%` }"></div>
              </div>
              <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-body">
                <div class="rounded-sm bg-emerald-50 px-2.5 py-2 text-emerald-700">
                  Confirmed: <span class="font-bold">{{ Number(selectedMonth.confirmed || 0).toLocaleString() }}</span>
                </div>
                <div class="rounded-sm bg-amber-50 px-2.5 py-2 text-amber-700">
                  Remaining: <span class="font-bold">{{ unconfirmedCount.toLocaleString() }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Status Breakdown + Top Tutors -->
      <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(320px,0.7fr)]">

        <!-- Status Breakdown -->
        <section class="analytics-card reveal delay-1">
          <div class="mb-5">
            <p class="section-kicker">Status Breakdown</p>
            <h2 class="section-title">All Connections by Status</h2>
            <p class="mt-1 font-body text-sm text-paper-500">Current distribution across all {{ totalConnections.toLocaleString() }} requests.</p>
          </div>

          <div v-if="!statusRows.length" class="empty-state">No status data yet.</div>
          <div v-else class="space-y-3">
            <div v-for="row in statusRows" :key="row.status"
              class="rounded-md border border-paper-100 bg-paper-50/70 p-3">
              <div class="flex items-center justify-between gap-2 mb-2.5">
                <span class="rounded-full border px-2.5 py-0.5 text-xs font-bold font-display"
                  :class="statusBadgeClass(row.status)">
                  {{ row.label }}
                </span>
                <div class="flex items-center gap-3">
                  <span class="font-display text-xs font-semibold text-paper-500">{{ row.percent }}%</span>
                  <span class="font-display text-sm font-bold text-navy-900 tabular-nums w-12 text-right">
                    {{ row.count.toLocaleString() }}
                  </span>
                </div>
              </div>
              <div class="h-3 overflow-hidden rounded-full bg-paper-100">
                <div class="h-full rounded-full transition-all duration-700"
                  :class="statusBarClass(row.status)"
                  :style="{ width: `${row.percent}%` }"></div>
              </div>
            </div>
          </div>
        </section>

        <!-- Top Tutors -->
        <section class="analytics-card reveal delay-2">
          <div class="mb-5 flex items-start justify-between gap-3">
            <div>
              <p class="section-kicker">Tutor Rankings</p>
              <h2 class="section-title">Top Tutors by Reviews</h2>
            </div>
            <span class="mt-1 rounded-full bg-gold-50 px-3 py-1 text-xs font-semibold font-display text-gold-700 shrink-0">
              Top {{ analytics.top_tutors?.length || 0 }}
            </span>
          </div>

          <div v-if="!analytics.top_tutors?.length" class="empty-state">No tutor review data yet.</div>
          <div v-else class="space-y-2">
            <div v-for="(tutor, i) in analytics.top_tutors" :key="tutor.id"
              class="flex items-center gap-3 rounded-md border border-paper-100 bg-white p-3">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-display text-sm font-bold"
                :class="rankClass(i)">{{ i + 1 }}</span>
              <div class="min-w-0 flex-1">
                <p class="truncate font-display text-sm font-bold text-navy-900">{{ tutor.user?.name || 'Unnamed Tutor' }}</p>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                  <span class="inline-flex items-center gap-1 rounded-full bg-gold-50 px-2 py-0.5 text-xs font-semibold text-gold-700">
                    <span>★</span>{{ tutor.rating ?? '—' }}
                  </span>
                  <span class="text-xs font-body text-paper-500">
                    {{ Number(tutor.review_count || 0).toLocaleString() }} reviews
                  </span>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'

const analytics    = ref({})
const loading      = ref(true)
const rangeLoading = ref(false)

// Default: current year (Jan → current month)
const now = new Date()
const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
const defaultFrom  = `${now.getFullYear()}-01`

const rangeFrom = ref(defaultFrom)
const rangeTo   = ref(currentMonth)

const selectedMonthKey = ref('')

// ─── derived data ────────────────────────────────────────────────────────────

const monthlyData = computed(() => analytics.value.monthly_connections ?? [])

const maxConnections = computed(() =>
  Math.max(1, ...monthlyData.value.map(m => Number(m.connections || 0)))
)

function barPct(val) {
  return Math.round((Number(val || 0) / maxConnections.value) * 100)
}

function shortLabel(monthStr) {
  // "Jan 2026" → "Jan\n26"
  const parts = monthStr.split(' ')
  return parts.length === 2 ? `${parts[0]} '${parts[1].slice(2)}` : monthStr
}

const selectedMonth = computed(() =>
  monthlyData.value.find(m => m.month_key === selectedMonthKey.value) ?? null
)

const selectedMonthRate = computed(() => {
  const m = selectedMonth.value
  if (!m || !m.connections) return 0
  return Math.round((Number(m.confirmed || 0) / Number(m.connections)) * 100)
})

const unconfirmedCount = computed(() =>
  Math.max(0, Number(selectedMonth.value?.connections || 0) - Number(selectedMonth.value?.confirmed || 0))
)

// ─── overall totals ───────────────────────────────────────────────────────────

const totalConnections = computed(() =>
  Object.values(analytics.value.connection_statuses ?? {}).reduce((a, b) => a + Number(b || 0), 0)
)

const confirmedTotal = computed(() => {
  const s = analytics.value.connection_statuses ?? {}
  return Number(s.confirmed ?? s.connected ?? 0)
})

const conversionRate = computed(() =>
  totalConnections.value ? Math.round((confirmedTotal.value / totalConnections.value) * 100) : 0
)

// ─── status rows ──────────────────────────────────────────────────────────────

const statusLabelMap = {
  pending:         'Pending',
  admin_reviewing: 'Admin Reviewing',
  tutor_contacted: 'Tutor Contacted',
  connected:       'Connected',
  confirmed:       'Confirmed',
  declined:        'Declined',
  closed:          'Closed',
}

const statusRows = computed(() =>
  Object.entries(analytics.value.connection_statuses ?? {})
    .map(([status, count]) => {
      const numeric = Number(count || 0)
      return {
        status,
        count: numeric,
        label: statusLabelMap[status] ?? status.replace(/_/g, ' '),
        percent: totalConnections.value ? Math.round((numeric / totalConnections.value) * 100) : 0,
      }
    })
    .sort((a, b) => b.count - a.count)
)

function statusBadgeClass(s) {
  const m = {
    pending:         'bg-paper-100 text-paper-600 border-paper-200',
    admin_reviewing: 'bg-blue-50 text-blue-700 border-blue-200',
    tutor_contacted: 'bg-purple-50 text-purple-700 border-purple-200',
    connected:       'bg-emerald-50 text-emerald-700 border-emerald-200',
    confirmed:       'bg-emerald-50 text-emerald-700 border-emerald-200',
    declined:        'bg-red-50 text-red-600 border-red-200',
    closed:          'bg-paper-100 text-paper-600 border-paper-200',
  }
  return m[s] ?? 'bg-paper-100 text-paper-600 border-paper-200'
}

function statusBarClass(s) {
  const m = {
    pending:         'bg-paper-400',
    admin_reviewing: 'bg-blue-400',
    tutor_contacted: 'bg-purple-400',
    connected:       'bg-emerald-500',
    confirmed:       'bg-emerald-500',
    declined:        'bg-red-400',
    closed:          'bg-paper-300',
  }
  return m[s] ?? 'bg-paper-300'
}

function rankClass(i) {
  if (i === 0) return 'bg-gold-400 text-navy-900'
  if (i === 1) return 'bg-paper-300 text-navy-800'
  if (i === 2) return 'bg-navy-100 text-navy-700'
  return 'bg-paper-100 text-paper-600'
}

// ─── data fetching ────────────────────────────────────────────────────────────

async function fetchAnalytics(params = {}) {
  const { data } = await adminApi.getAnalytics(params)
  analytics.value = data.data || {}
  // Auto-select the latest month
  const months = analytics.value.monthly_connections ?? []
  if (months.length) {
    selectedMonthKey.value = months[months.length - 1].month_key
  }
}

async function applyRange() {
  rangeLoading.value = true
  try {
    await fetchAnalytics({ from: rangeFrom.value, to: rangeTo.value })
  } finally {
    rangeLoading.value = false
  }
}

onMounted(async () => {
  try {
    await fetchAnalytics({ from: rangeFrom.value, to: rangeTo.value })
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

.month-stat-card {
  @apply rounded-md border border-paper-100 bg-white p-4;
}

.stat-label {
  @apply font-display text-xs font-bold uppercase tracking-wide text-paper-400;
}

.stat-value {
  @apply mt-1 font-display text-2xl font-bold;
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
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
  .reveal { animation: none; }
}
</style>
