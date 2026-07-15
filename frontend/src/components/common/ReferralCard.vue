<template>
  <div class="dashboard-card reveal">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
      <h2 class="font-display font-bold text-navy-900 text-xl">Refer &amp; Earn</h2>
      <span v-if="data" class="text-xs font-semibold px-3 py-1 rounded-pill bg-emerald-50 text-emerald-700 self-start sm:self-auto">
        {{ data.referral_points }} points
      </span>
    </div>

    <div v-if="loading" class="text-paper-500 font-body text-sm">Loading…</div>

    <template v-else-if="data">
      <p class="text-sm text-paper-500 font-body mb-3">
        Share your referral code with friends. You earn points once a tutor you referred is approved by our team.
      </p>

      <div class="flex items-center gap-2 mb-4">
        <span class="font-display font-bold text-navy-900 text-lg bg-navy-50 border border-navy-200 rounded-lg px-4 py-2 tracking-wide">
          {{ data.referral_code || '—' }}
        </span>
        <button type="button" @click="copyCode" :disabled="!data.referral_code"
          class="text-xs font-semibold font-display text-navy-700 border border-paper-200 rounded-lg px-3 py-2 hover:bg-paper-50 transition-colors disabled:opacity-50">
          {{ copied ? 'Copied!' : 'Copy' }}
        </button>
      </div>

      <div v-if="data.referrals?.length">
        <p class="font-display font-semibold text-navy-800 text-sm mb-2">People you've referred</p>
        <ul class="divide-y divide-paper-100 rounded-lg border border-paper-200 overflow-hidden">
          <li v-for="(r, i) in data.referrals" :key="i" class="flex items-center justify-between px-4 py-2.5 text-sm">
            <span class="font-body text-navy-700">{{ r.name }}</span>
            <span :class="r.earned ? 'text-emerald-600' : 'text-paper-400'" class="text-xs font-semibold font-display">
              {{ r.earned ? 'Earned' : 'Pending approval' }}
            </span>
          </li>
        </ul>
      </div>
      <p v-else class="text-sm text-paper-400 font-body">No referrals yet — share your code to start earning.</p>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { authApi } from '@/api/auth.js'

const data    = ref(null)
const loading = ref(true)
const copied  = ref(false)

async function load() {
  loading.value = true
  try {
    const res = await authApi.getReferral()
    data.value = res.data.data
  } finally {
    loading.value = false
  }
}

async function copyCode() {
  if (!data.value?.referral_code) return
  await navigator.clipboard.writeText(data.value.referral_code)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}

onMounted(load)
</script>
