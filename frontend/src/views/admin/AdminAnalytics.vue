<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-6">Analytics</h1>

    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>

    <template v-else>
      <!-- Monthly connections chart (table-based) -->
      <div class="card mb-6">
        <p class="font-display font-semibold text-navy-900 mb-4">Monthly connections (last 6 months)</p>
        <div class="overflow-x-auto">
          <table class="w-full text-sm font-body">
            <thead>
              <tr class="border-b border-paper-200">
                <th class="text-left px-3 py-2 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Month</th>
                <th class="text-right px-3 py-2 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Requests</th>
                <th class="text-right px-3 py-2 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Connected</th>
                <th class="px-3 py-2 w-1/2">
                  <span class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Conversion</span>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in analytics.monthly_connections" :key="m.month"
                class="border-b border-paper-100 last:border-0 hover:bg-paper-50">
                <td class="px-3 py-2.5 font-semibold font-display text-navy-900">{{ m.month }}</td>
                <td class="px-3 py-2.5 text-right text-navy-700">{{ m.connections }}</td>
                <td class="px-3 py-2.5 text-right text-emerald-600 font-semibold">{{ m.connected }}</td>
                <td class="px-3 py-2.5">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 bg-paper-100 rounded-full h-2 overflow-hidden">
                      <div class="h-full bg-emerald-500 rounded-full transition-all"
                        :style="{ width: (m.connections ? (m.connected/m.connections*100) : 0) + '%' }" />
                    </div>
                    <span class="text-xs text-paper-500 w-8 text-right">
                      {{ m.connections ? Math.round(m.connected/m.connections*100) : 0 }}%
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Connection statuses + Top tutors side by side -->
      <div class="grid sm:grid-cols-2 gap-5 mb-6">
        <!-- Status breakdown -->
        <div class="card">
          <p class="font-display font-semibold text-navy-900 mb-4">Connections by status</p>
          <div class="space-y-2.5">
            <div v-for="(count, status) in analytics.connection_statuses" :key="status"
              class="flex items-center gap-3">
              <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border w-28 text-center shrink-0 capitalize"
                :class="statusClass(status)">
                {{ status.replace(/_/g,' ') }}
              </span>
              <div class="flex-1 bg-paper-100 rounded-full h-2 overflow-hidden">
                <div class="h-full rounded-full transition-all"
                  :class="statusBarClass(status)"
                  :style="{ width: (totalConnections ? count/totalConnections*100 : 0) + '%' }" />
              </div>
              <span class="text-sm font-semibold font-display text-navy-900 w-8 text-right shrink-0">{{ count }}</span>
            </div>
          </div>
        </div>

        <!-- Top tutors by reviews -->
        <div class="card">
          <p class="font-display font-semibold text-navy-900 mb-4">Top tutors by reviews</p>
          <div class="space-y-3">
            <div v-for="(tutor, i) in analytics.top_tutors" :key="tutor.id" class="flex items-center gap-3">
              <span class="w-6 h-6 rounded-full text-xs font-bold font-display flex items-center justify-center shrink-0"
                :class="i === 0 ? 'bg-gold-400 text-white' : i === 1 ? 'bg-paper-300 text-navy-800' : 'bg-paper-100 text-paper-500'">
                {{ i + 1 }}
              </span>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold font-display text-navy-900 truncate">{{ tutor.user?.name }}</p>
                <div class="flex items-center gap-1 mt-0.5">
                  <span class="text-gold-400 text-xs">★</span>
                  <span class="text-xs text-paper-500 font-body">{{ tutor.rating ?? '—' }} · {{ tutor.review_count }} reviews</span>
                </div>
              </div>
            </div>
            <p v-if="!analytics.top_tutors?.length" class="text-sm text-paper-400 font-body">No data yet.</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'

const analytics = ref({})
const loading   = ref(true)

const totalConnections = computed(() =>
  Object.values(analytics.value.connection_statuses ?? {}).reduce((a, b) => a + b, 0)
)

function statusClass(s) {
  const m = { pending:'bg-paper-100 text-paper-500 border-paper-200', admin_reviewing:'bg-blue-50 text-blue-700 border-blue-200', tutor_contacted:'bg-purple-50 text-purple-700 border-purple-200', connected:'bg-emerald-50 text-emerald-700 border-emerald-200', declined:'bg-red-50 text-red-700 border-red-200', closed:'bg-paper-100 text-paper-500 border-paper-200' }
  return m[s] ?? 'bg-paper-100 text-paper-500 border-paper-200'
}

function statusBarClass(s) {
  const m = { pending:'bg-paper-400', admin_reviewing:'bg-blue-400', tutor_contacted:'bg-purple-400', connected:'bg-emerald-500', declined:'bg-red-400', closed:'bg-paper-300' }
  return m[s] ?? 'bg-paper-300'
}

onMounted(async () => {
  try {
    const { data } = await adminApi.getAnalytics()
    analytics.value = data.data
  } finally {
    loading.value = false
  }
})
</script>
