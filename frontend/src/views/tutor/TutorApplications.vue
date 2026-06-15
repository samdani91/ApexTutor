<template>
  <div class="space-y-5">
    <div>
      <h1 class="font-display font-bold text-2xl text-navy-900">My Applications</h1>
      <p class="mt-1 text-sm font-body text-paper-500">Track the status of all your tuition job applications.</p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 border-b border-paper-200 overflow-x-auto">
      <button v-for="tab in tabs" :key="tab.value" @click="switchTab(tab.value)"
        class="px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors whitespace-nowrap"
        :class="activeTab === tab.value
          ? 'bg-white border border-b-white border-paper-200 -mb-px text-navy-900'
          : 'text-paper-500 hover:text-navy-700'">
        {{ tab.label }}
      </button>
    </div>

    <div v-if="loading" class="card py-16 text-center text-paper-400 font-body text-sm">Loading…</div>

    <div v-else-if="!applications.length" class="card py-16 text-center">
      <p class="font-display font-semibold text-navy-700">No applications here</p>
      <p class="text-sm text-paper-500 font-body mt-1">
        {{ activeTab === 'all' ? 'You haven\'t applied to any jobs yet.' : `No applications with status '${activeTab}'.` }}
      </p>
      <RouterLink to="/jobs" class="btn-primary text-sm py-2 px-5 mt-4 inline-block">Browse Jobs</RouterLink>
    </div>

    <div v-else class="space-y-3">
      <div v-for="app in applications" :key="app.id"
        class="card flex flex-col gap-3 sm:flex-row sm:items-start">

        <!-- Job info -->
        <div class="flex-1 min-w-0">
          <RouterLink :to="`/jobs/${app.tuition_job?.public_id}`"
            class="font-display font-bold text-navy-900 text-sm leading-snug hover:text-navy-600 transition-colors block">
            {{ app.tuition_job?.title }}
          </RouterLink>
          <p class="text-xs text-paper-400 font-body mt-1">
            Job ID: #{{ app.tuition_job?.public_id }}
            &middot; {{ app.tuition_job?.area?.name ? `${app.tuition_job.area.name}, ` : '' }}{{ app.tuition_job?.district?.name }}
          </p>
          <p class="text-xs text-paper-400 font-body mt-0.5">
            Salary: {{ app.tuition_job?.offered_salary?.toLocaleString() }} BDT
            &middot; Applied {{ formatDate(app.applied_at) }}
          </p>
        </div>

        <!-- Badges -->
        <div class="flex flex-wrap gap-2 items-center shrink-0">
          <!-- Job status -->
          <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border"
            :class="app.tuition_job?.status === 'open'
              ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
              : 'bg-paper-100 text-paper-500 border-paper-200'">
            Job {{ app.tuition_job?.status === 'open' ? 'Open' : 'Closed' }}
          </span>
          <!-- Application status -->
          <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border"
            :class="appStatusClass(app.status)">
            {{ appStatusLabel(app.status) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { tutorJobsApi } from '@/api/jobs.js'

const tabs = [
  { value: 'all',         label: 'All'         },
  { value: 'applied',     label: 'Applied'     },
  { value: 'shortlisted', label: 'Shortlisted' },
  { value: 'appointed',   label: 'Appointed'   },
  { value: 'connected',   label: 'Connected'   },
  { value: 'removed',     label: 'Removed'     },
]

const applications = ref([])
const loading      = ref(false)
const activeTab    = ref('all')

async function load() {
  loading.value = true
  try {
    const status = activeTab.value === 'all' ? undefined : activeTab.value
    const { data } = await tutorJobsApi.myApplications(status)
    applications.value = data.data || []
  } finally {
    loading.value = false
  }
}

function switchTab(tab) {
  activeTab.value = tab
  load()
}

function appStatusClass(s) {
  if (s === 'applied')     return 'bg-blue-50 text-blue-700 border-blue-200'
  if (s === 'shortlisted') return 'bg-gold-50 text-gold-700 border-gold-200'
  if (s === 'appointed')   return 'bg-purple-50 text-purple-700 border-purple-200'
  if (s === 'connected')   return 'bg-emerald-50 text-emerald-700 border-emerald-200'
  return 'bg-red-50 text-red-600 border-red-200'
}

function appStatusLabel(s) {
  return { applied:'Applied', shortlisted:'Shortlisted', appointed:'Appointed', connected:'Connected', removed:'Removed' }[s] ?? s
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' }) : ''
}

onMounted(load)
</script>
