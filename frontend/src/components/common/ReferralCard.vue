<template>
  <div class="dashboard-card reveal">
    <h2 class="font-display font-bold text-navy-900 text-xl mb-4">Refer &amp; Earn</h2>

    <div v-if="loading" class="text-paper-500 font-body text-sm">Loading…</div>

    <template v-else-if="data">
      <!-- Points balance — the headline number of this card -->
      <div class="mb-4 flex items-center gap-4 rounded-lg border border-emerald-100 bg-emerald-50 px-5 py-4">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100">
          <svg class="h-6 w-6 text-emerald-700" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
          </svg>
        </div>
        <div class="min-w-0">
          <p class="font-display text-4xl font-bold leading-none text-emerald-700 sm:text-5xl">
            {{ data.referral_points }}
          </p>
          <p class="mt-1.5 font-display text-xs font-semibold uppercase tracking-wide text-emerald-700/70">
            {{ data.referral_points === 1 ? 'Point earned' : 'Points earned' }}
          </p>
        </div>
      </div>

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
