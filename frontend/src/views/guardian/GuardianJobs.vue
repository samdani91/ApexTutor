<template>
  <div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="font-display font-bold text-2xl text-navy-900">My Tuition Jobs</h1>
        <p class="mt-1 text-sm font-body text-paper-500">Manage your posted jobs and review applicants.</p>
      </div>
      <RouterLink to="/guardian/jobs/post" class="btn-primary text-sm py-2.5 px-5 shrink-0">
        + Post New Job
      </RouterLink>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 border-b border-paper-200">
      <button v-for="tab in tabs" :key="tab.value" @click="switchTab(tab.value)"
        class="px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors"
        :class="activeTab === tab.value
          ? 'bg-white border border-b-white border-paper-200 -mb-px text-navy-900'
          : 'text-paper-500 hover:text-navy-700'">
        {{ tab.label }}
      </button>
    </div>

    <div v-if="loading" class="card py-16 text-center text-paper-400 font-body text-sm">Loading…</div>

    <div v-else-if="!jobs.length" class="card py-16 text-center">
      <p class="font-display font-semibold text-navy-700 text-lg">No jobs here yet</p>
      <p class="mt-1 text-paper-500 text-sm font-body">
        {{ activeTab === 'open' ? 'You have no open jobs.' : activeTab === 'closed' ? 'No closed jobs.' : 'Post your first job to start receiving applications.' }}
      </p>
      <RouterLink v-if="activeTab === 'all'" to="/guardian/jobs/post" class="btn-primary text-sm py-2 px-5 mt-4 inline-block">Post a Job</RouterLink>
    </div>

    <div v-else class="space-y-3">
      <div v-for="job in jobs" :key="job.id"
        class="card grid gap-5 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start">

        <!-- Job info -->
        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2 mb-1">
            <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border"
              :class="job.status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200'">
              {{ job.status === 'open' ? 'Open' : 'Closed' }}
            </span>
            <span class="text-xs font-body text-paper-400">Job ID: #{{ job.public_id }}</span>
          </div>
          <p class="font-display font-bold text-navy-900 text-base leading-snug">{{ job.title }}</p>
          <p class="text-xs text-paper-400 font-body mt-1">
            {{ job.area?.name ? `${job.area.name}, ` : '' }}{{ job.district?.name }} &middot; Posted {{ formatDate(job.created_at) }}
          </p>
          <div class="mt-2.5 flex flex-wrap gap-1.5">
            <span v-for="s in job.subjects" :key="s.id"
              class="text-xs bg-navy-50 text-navy-700 border border-navy-100 px-2 py-0.5 rounded-pill font-body">
              {{ s.name }}
            </span>
          </div>
        </div>

        <div class="rounded-md border border-paper-200 bg-paper-50/70 p-3">
          <!-- Applicant count -->
          <div class="job-count-chip border-navy-200 bg-white text-navy-700">
            <p class="font-display font-bold text-3xl leading-none">{{ job.applications_count ?? 0 }}</p>
            <p class="mt-1 text-[11px] font-semibold font-display uppercase tracking-wide">Total Applicants</p>
          </div>

          <!-- Actions -->
          <div class="mt-3 flex flex-col gap-2">
            <RouterLink :to="`/guardian/jobs/${job.public_id}/applicants`"
              class="inline-flex min-h-[40px] items-center justify-center rounded-md bg-navy-700 px-3 py-2 text-xs font-semibold font-display text-white hover:bg-navy-800 transition-colors">
              View Applicants
            </RouterLink>
            <button v-if="job.status === 'open'" @click="closeTarget = job"
              class="inline-flex min-h-[40px] items-center justify-center rounded-md bg-red-600 px-3 py-2 text-xs font-semibold font-display text-white hover:bg-red-700 transition-colors">
              Close Job
            </button>
            <button v-else @click="doReopen(job)"
              class="inline-flex min-h-[40px] items-center justify-center rounded-md border border-emerald-300 bg-white px-3 py-2 text-xs font-semibold font-display text-emerald-700 hover:bg-emerald-50 transition-colors">
              Reopen
            </button>
          </div>
        </div>
      </div>
    </div>

    <ConfirmDialog
      :show="!!closeTarget"
      title="Close This Job?"
      :message="closeTarget ? `Close '${closeTarget.title}'? It will stop accepting new applications.` : ''"
      confirm-label="Close Job"
      :danger="true"
      @confirm="doClose"
      @cancel="closeTarget = null"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { guardianJobsApi } from '@/api/jobs.js'
import { toast } from 'vue-sonner'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const tabs = [
  { value: 'all',    label: 'All'    },
  { value: 'open',   label: 'Open'   },
  { value: 'closed', label: 'Closed' },
]

const jobs        = ref([])
const loading     = ref(false)
const activeTab   = ref('all')
const closeTarget = ref(null)

async function load() {
  loading.value = true
  try {
    const { data } = await guardianJobsApi.list(activeTab.value)
    jobs.value = data.data || []
  } finally {
    loading.value = false
  }
}

function switchTab(tab) {
  activeTab.value = tab
  load()
}

async function doClose() {
  const job = closeTarget.value
  closeTarget.value = null
  if (!job) return
  try {
    await guardianJobsApi.close(job.public_id)
    job.status = 'closed'
    toast.success('Job closed.')
    if (activeTab.value === 'open') jobs.value = jobs.value.filter(j => j.id !== job.id)
  } catch { toast.error('Failed to close job.') }
}

async function doReopen(job) {
  try {
    await guardianJobsApi.reopen(job.public_id)
    job.status = 'open'
    toast.success('Job reopened.')
    if (activeTab.value === 'closed') jobs.value = jobs.value.filter(j => j.id !== job.id)
  } catch { toast.error('Failed to reopen job.') }
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(load)
</script>

<style scoped>
.job-count-chip {
  @apply rounded-md border px-3 py-2.5 text-center shadow-xs;
}
</style>
