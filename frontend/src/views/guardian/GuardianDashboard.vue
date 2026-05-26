<template>
  <div>
    <div class="mb-6">
      <h1 class="font-display font-bold text-2xl text-navy-900">Dashboard</h1>
      <span v-if="guardianId"
        class="inline-block mt-1 text-xs font-semibold font-display text-navy-700 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill">
        {{ guardianId }}
      </span>
    </div>

    <!-- Stats -->
    <div class="grid sm:grid-cols-2 gap-5 mb-8">
      <RouterLink to="/guardian/shortlist" class="card text-center block hover:shadow-md transition-shadow">
        <p class="font-display font-bold text-3xl text-navy-700">{{ shortlistCount }}</p>
        <p class="text-sm text-paper-500 font-body mt-1">Shortlisted tutors</p>
      </RouterLink>
      <div class="card flex flex-col items-center justify-center gap-3">
        <p class="text-sm font-body text-paper-500 text-center">Browse verified tutors and shortlist the ones that fit your needs.</p>
        <RouterLink to="/search" class="btn-primary text-sm py-2 px-5">Find Tutors</RouterLink>
      </div>
    </div>

    <!-- How it works -->
    <div class="card">
      <h2 class="font-display font-semibold text-navy-700 text-base mb-4">How it works</h2>
      <ol class="space-y-3">
        <li class="flex items-start gap-3">
          <span class="w-6 h-6 rounded-full bg-navy-700 text-white text-xs font-bold font-display flex items-center justify-center shrink-0 mt-0.5">1</span>
          <p class="text-sm font-body text-paper-700">Browse tutors on the <RouterLink to="/search" class="font-semibold text-navy-700 hover:underline">Find Tutors</RouterLink> page and view their full profiles.</p>
        </li>
        <li class="flex items-start gap-3">
          <span class="w-6 h-6 rounded-full bg-navy-700 text-white text-xs font-bold font-display flex items-center justify-center shrink-0 mt-0.5">2</span>
          <p class="text-sm font-body text-paper-700">Click <strong>Shortlist</strong> on any tutor profile you like — this notifies our admin team.</p>
        </li>
        <li class="flex items-start gap-3">
          <span class="w-6 h-6 rounded-full bg-navy-700 text-white text-xs font-bold font-display flex items-center justify-center shrink-0 mt-0.5">3</span>
          <p class="text-sm font-body text-paper-700">Our admin will contact both you and the tutor to arrange a mutually convenient tuition session.</p>
        </li>
      </ol>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { guardianApi } from '@/api/guardian.js'

const shortlistCount = ref(0)
const guardianId     = ref('')

onMounted(async () => {
  const [shortRes, profileRes] = await Promise.all([
    guardianApi.getShortlist().catch(() => ({ data: { data: [] } })),
    guardianApi.getProfile().catch(() => ({ data: { data: null } })),
  ])
  shortlistCount.value = (shortRes.data.data || []).length
  guardianId.value     = profileRes.data.data?.guardian_id || ''
})
</script>
