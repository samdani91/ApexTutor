<template>
  <div class="dashboard-page space-y-6">
    <div class="dashboard-card reveal">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="font-display text-xs font-bold uppercase text-gold-600">Guardian Dashboard</p>
          <h1 class="mt-1 font-display font-bold text-2xl text-navy-900 md:text-3xl">Dashboard</h1>
          <p class="mt-2 max-w-2xl font-body text-sm leading-relaxed text-paper-600">
            Shortlist suitable tutors, request connections and track confirmed tuition updates.
          </p>
        </div>
      <span v-if="guardianId"
        class="inline-block self-start text-xs font-semibold font-display text-navy-700 bg-navy-50 border border-navy-200 px-3 py-1 rounded-pill sm:self-center">
        {{ guardianId }}
      </span>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid gap-4 sm:grid-cols-3">
      <RouterLink to="/guardian/shortlist" class="metric-card reveal block transition-all hover:-translate-y-1 hover:shadow-lg">
        <p class="font-display font-bold text-3xl text-navy-700">{{ shortlistCount }}</p>
        <p class="text-sm text-paper-500 font-body mt-1">Shortlisted Tutors</p>
      </RouterLink>
      <RouterLink to="/guardian/confirmed-tuitions" class="metric-card reveal delay-1 block transition-all hover:-translate-y-1 hover:shadow-lg">
        <p class="font-display font-bold text-3xl text-emerald-600">{{ confirmedCount }}</p>
        <p class="text-sm text-paper-500 font-body mt-1">Confirmed Tuitions</p>
      </RouterLink>
      <div class="metric-card reveal delay-2 flex flex-col items-center justify-center gap-3">
        <p class="text-sm font-body text-paper-500 text-center">Browse verified tutors and shortlist the ones that fit your needs.</p>
        <RouterLink to="/search" class="btn-primary text-sm py-2 px-5">Find Tutors</RouterLink>
      </div>
    </div>

    <!-- Tuition Jobs widget -->
    <div class="dashboard-card reveal">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
        <h2 class="font-display font-bold text-navy-900 text-xl">My Tuition Jobs</h2>
        <RouterLink to="/guardian/jobs"
          class="shrink-0 inline-block text-xs font-semibold font-display bg-navy-700 text-white px-3 py-1.5 rounded-sm hover:bg-navy-800 transition-colors">
          Manage All Jobs
        </RouterLink>
      </div>
      <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="metric-card">
          <p class="font-display font-bold text-2xl text-emerald-600">{{ jobSummary.open }}</p>
          <p class="text-xs text-paper-500 font-body mt-1">Open</p>
        </div>
        <div class="metric-card">
          <p class="font-display font-bold text-2xl text-paper-500">{{ jobSummary.closed }}</p>
          <p class="text-xs text-paper-500 font-body mt-1">Closed</p>
        </div>
        <div class="metric-card">
          <p class="font-display font-bold text-2xl text-navy-700">{{ jobSummary.totalApplicants }}</p>
          <p class="text-xs text-paper-500 font-body mt-1">Applicants</p>
        </div>
      </div>
      <RouterLink to="/guardian/jobs/post" class="btn-primary text-sm py-2 px-5 inline-block">+ Post New Job</RouterLink>
    </div>

    <!-- How it works -->
    <div class="dashboard-card reveal">
      <h2 class="font-display font-bold text-navy-900 text-xl mb-4">How It Works</h2>
      <ol class="space-y-3">
        <li class="flex items-start gap-3">
          <span class="w-6 h-6 rounded-full bg-navy-700 text-white text-xs font-bold font-display flex items-center justify-center shrink-0 mt-0.5">1</span>
          <p class="text-sm font-body text-paper-700">Browse tutors on the <RouterLink to="/search" class="font-semibold text-navy-700 hover:underline">Find Tutors</RouterLink> page and shortlist the ones you like.</p>
        </li>
        <li class="flex items-start gap-3">
          <span class="w-6 h-6 rounded-full bg-navy-700 text-white text-xs font-bold font-display flex items-center justify-center shrink-0 mt-0.5">2</span>
          <p class="text-sm font-body text-paper-700">From your <RouterLink to="/guardian/shortlist" class="font-semibold text-navy-700 hover:underline">Shortlist</RouterLink>, request a connection with any tutor.</p>
        </li>
        <li class="flex items-start gap-3">
          <span class="w-6 h-6 rounded-full bg-navy-700 text-white text-xs font-bold font-display flex items-center justify-center shrink-0 mt-0.5">3</span>
          <p class="text-sm font-body text-paper-700">Our admin team reviews the request and contacts both parties to arrange the tuition.</p>
        </li>
        <li class="flex items-start gap-3">
          <span class="w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-bold font-display flex items-center justify-center shrink-0 mt-0.5">&#x2713;</span>
          <p class="text-sm font-body text-paper-700">Once confirmed, the tutor appears in your <RouterLink to="/guardian/confirmed-tuitions" class="font-semibold text-emerald-700 hover:underline">Confirmed Tuitions</RouterLink> section.</p>
        </li>
      </ol>
    </div>

    <PlatformFeedbackWidget class="reveal" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { guardianApi } from '@/api/guardian.js'
import { guardianJobsApi } from '@/api/jobs.js'
import PlatformFeedbackWidget from '@/components/common/PlatformFeedbackWidget.vue'

const shortlistCount  = ref(0)
const confirmedCount  = ref(0)
const guardianId      = ref('')
const jobSummary      = ref({ open: 0, closed: 0, total: 0, totalApplicants: 0 })

onMounted(async () => {
  const [shortRes, confirmedRes, profileRes, jobRes] = await Promise.all([
    guardianApi.getShortlist().catch(() => ({ data: { data: [] } })),
    guardianApi.getConfirmedTuitions().catch(() => ({ data: { data: [] } })),
    guardianApi.getProfile().catch(() => ({ data: { data: null } })),
    guardianJobsApi.dashboardSummary().catch(() => ({ data: { data: {} } })),
  ])

  const confirmed      = confirmedRes.data.data || []
  const confirmedIds   = new Set(confirmed.map(c => c.tutor_profile_id))
  const shortlist      = shortRes.data.data || []

  shortlistCount.value = shortlist.filter(i => !confirmedIds.has(i.tutor_profile_id)).length
  confirmedCount.value = confirmed.length
  guardianId.value     = profileRes.data.data?.guardian_id || ''
  jobSummary.value     = jobRes.data.data || jobSummary.value
})
</script>

<style scoped>
.dashboard-card {
  @apply rounded-md border border-paper-200 bg-white p-5 shadow-lg md:p-6;
}

.metric-card {
  @apply rounded-md border border-paper-200 bg-white p-5 text-center shadow-sm;
}

.reveal {
  animation: reveal-up 0.54s ease both;
}

.delay-1 { animation-delay: 70ms; }
.delay-2 { animation-delay: 140ms; }

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
