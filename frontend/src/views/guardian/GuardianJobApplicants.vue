<template>
  <div class="space-y-5">
    <div>
      <RouterLink to="/guardian/jobs"
        class="inline-flex items-center gap-1.5 rounded-sm border border-paper-300 bg-white px-3.5 py-2 text-sm font-semibold font-display text-navy-700 shadow-sm hover:bg-paper-100 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
        My Jobs
      </RouterLink>
      <div v-if="job" class="mt-3">
        <div class="flex flex-wrap items-center gap-2 mb-1">
          <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border"
            :class="job.status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-paper-100 text-paper-500 border-paper-200'">
            {{ job.status === 'open' ? 'Open' : 'Closed' }}
          </span>
          <span class="text-xs text-paper-400 font-body">Job ID: #{{ job.public_id }}</span>
        </div>
        <h1 class="font-display font-bold text-2xl text-navy-900">{{ job.title }}</h1>
        <p class="text-sm text-paper-400 font-body mt-0.5">
          {{ job.area?.name ? `${job.area.name}, ` : '' }}{{ job.district?.name }}
          &middot; {{ job.applications_count ?? 0 }} total applicants
        </p>
      </div>
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

    <div v-else-if="!applicants.length" class="card py-16 text-center">
      <p class="font-display font-semibold text-navy-700">No applicants here</p>
      <p class="text-sm text-paper-500 font-body mt-1">
        {{ activeTab === 'all' ? 'No tutors have applied to this job yet.' : `No applicants with status '${activeTab}'.` }}
      </p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="app in applicants" :key="app.id"
        class="card flex flex-col gap-4 sm:flex-row sm:items-start">

        <!-- Avatar + tutor info -->
        <div class="flex items-center gap-3 shrink-0">
          <div class="h-12 w-12 rounded-full bg-navy-100 flex items-center justify-center overflow-hidden shrink-0">
            <img v-if="app.tutor_profile?.user?.avatar_url" :src="app.tutor_profile.user.avatar_url" class="h-full w-full object-cover" />
            <span v-else class="font-display font-bold text-base text-navy-700">
              {{ (app.tutor_profile?.user?.name || '?').charAt(0).toUpperCase() }}
            </span>
          </div>
          <div>
            <p class="font-display font-semibold text-navy-900 text-sm">{{ app.tutor_profile?.user?.name }}</p>
            <p class="text-xs text-paper-400 font-body">{{ app.tutor_profile?.tutor_id }}</p>
            <div class="flex items-center gap-1 mt-0.5">
              <span class="text-xs text-gold-500">★</span>
              <span class="text-xs text-paper-600 font-body">{{ app.tutor_profile?.rating || '—' }}</span>
              <span class="text-xs text-paper-400 font-body">({{ app.tutor_profile?.review_count ?? 0 }})</span>
            </div>
          </div>
        </div>

        <!-- Status + meta -->
        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap gap-2 mb-2">
            <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border"
              :class="statusClass(app.status)">
              {{ statusLabel(app.status) }}
            </span>
            <span v-if="app.tutor_profile?.personal_info?.gender && app.tutor_profile.personal_info.gender !== 'other'"
              class="text-xs font-body text-paper-500 px-2 py-0.5 rounded-pill border border-paper-200 bg-paper-50 capitalize">
              {{ app.tutor_profile.personal_info.gender }}
            </span>
          </div>
          <p class="text-xs text-paper-400 font-body">Applied {{ formatDate(app.applied_at) }}</p>
          <p v-if="app.tutor_profile?.tuition_preference" class="text-xs text-paper-500 font-body mt-0.5">
            Salary: {{ fmtSalary(app.tutor_profile.tuition_preference) }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap gap-2 shrink-0">
          <a :href="`/tutors/${app.tutor_profile?.public_id}`" target="_blank"
            class="rounded-sm border border-navy-200 bg-navy-50 px-3 py-1.5 text-xs font-semibold font-display text-navy-700 hover:bg-navy-100 transition-colors">
            View Profile
          </a>
          <button v-if="app.status === 'applied'" @click="act('shortlist', app)"
            :disabled="acting[app.id]"
            class="rounded-sm bg-gold-500 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-gold-600 transition-colors disabled:opacity-50">
            Shortlist
          </button>
          <button v-if="app.status === 'shortlisted'" @click="act('appoint', app)"
            :disabled="acting[app.id]"
            class="rounded-sm bg-purple-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-purple-700 transition-colors disabled:opacity-50">
            Set for Demo
          </button>
          <button v-if="app.status === 'appointed'" @click="confirmTarget = app"
            :disabled="acting[app.id]"
            class="rounded-sm bg-emerald-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-emerald-700 transition-colors disabled:opacity-50">
            Confirm Tutor
          </button>
          <button v-if="!['connected','removed'].includes(app.status)" @click="removeTarget = app"
            :disabled="acting[app.id]"
            class="rounded-sm border border-red-200 px-3 py-1.5 text-xs font-semibold font-display text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50">
            Remove
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Tutor dialog -->
    <ConfirmDialog
      :show="!!confirmTarget"
      title="Confirm This Tutor?"
      :message="confirmTarget ? `Confirm ${confirmTarget.tutor_profile?.user?.name} as tutor? This will close the job and send a connection request to admin.` : ''"
      confirm-label="Confirm Tutor"
      @confirm="doConfirm"
      @cancel="confirmTarget = null"
    />

    <!-- Remove dialog -->
    <ConfirmDialog
      :show="!!removeTarget"
      title="Remove Applicant?"
      :message="removeTarget ? `Remove ${removeTarget.tutor_profile?.user?.name} from this job's applicants?` : ''"
      confirm-label="Remove"
      :danger="true"
      @confirm="doRemove"
      @cancel="removeTarget = null"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { guardianJobsApi } from '@/api/jobs.js'
import { toast } from 'vue-sonner'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const route       = useRoute()
const publicId    = route.params.publicId

const tabs = [
  { value: 'all',         label: 'All'         },
  { value: 'applied',     label: 'Applied'     },
  { value: 'shortlisted', label: 'Shortlisted' },
  { value: 'appointed',   label: 'Appointed'   },
  { value: 'connected',   label: 'Confirmed'   },
  { value: 'removed',     label: 'Removed'     },
]

const job           = ref(null)
const applicants    = ref([])
const loading       = ref(false)
const activeTab     = ref('all')
const acting        = ref({})
const confirmTarget = ref(null)
const removeTarget  = ref(null)

async function loadJob() {
  const { data } = await guardianJobsApi.show(publicId)
  job.value = data.data
}

async function loadApplicants() {
  loading.value = true
  try {
    const status = activeTab.value === 'all' ? undefined : activeTab.value
    const { data } = await guardianJobsApi.applicants(publicId, status)
    applicants.value = data.data || []
  } finally {
    loading.value = false
  }
}

function switchTab(tab) {
  activeTab.value = tab
  loadApplicants()
}

async function act(action, app) {
  acting.value[app.id] = true
  try {
    const fnMap = {
      shortlist: () => guardianJobsApi.shortlistApplicant(publicId, app.id),
      appoint:   () => guardianJobsApi.appointApplicant(publicId, app.id),
    }
    await fnMap[action]()
    const nextStatus = { shortlist: 'shortlisted', appoint: 'appointed' }[action]
    app.status = nextStatus
    toast.success(action === 'shortlist' ? 'Tutor shortlisted.' : 'Set for demo class.')
    if (activeTab.value !== 'all') loadApplicants()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Action failed.')
  } finally {
    delete acting.value[app.id]
  }
}

async function doConfirm() {
  const app = confirmTarget.value
  confirmTarget.value = null
  if (!app) return
  acting.value[app.id] = true
  try {
    await guardianJobsApi.confirmApplicant(publicId, app.id)
    app.status = 'connected'
    if (job.value) job.value.status = 'closed'
    toast.success('Tutor confirmed! Connection request sent to admin.')
    loadApplicants()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to confirm tutor.')
  } finally {
    delete acting.value[app.id]
  }
}

async function doRemove() {
  const app = removeTarget.value
  removeTarget.value = null
  if (!app) return
  acting.value[app.id] = true
  try {
    await guardianJobsApi.removeApplicant(publicId, app.id)
    app.status = 'removed'
    toast.success('Applicant removed.')
    if (activeTab.value !== 'all' && activeTab.value !== 'removed') loadApplicants()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to remove applicant.')
  } finally {
    delete acting.value[app.id]
  }
}

function statusClass(s) {
  if (s === 'applied')     return 'bg-blue-50 text-blue-700 border-blue-200'
  if (s === 'shortlisted') return 'bg-gold-50 text-gold-700 border-gold-200'
  if (s === 'appointed')   return 'bg-purple-50 text-purple-700 border-purple-200'
  if (s === 'connected')   return 'bg-emerald-50 text-emerald-700 border-emerald-200'
  return 'bg-paper-100 text-paper-500 border-paper-200'
}

function statusLabel(s) {
  const map = { applied: 'Applied', shortlisted: 'Shortlisted', appointed: 'Appointed', connected: 'Confirmed', removed: 'Removed' }
  return map[s] ?? s
}

function fmtSalary(pref) {
  if (!pref) return '—'
  if (pref.expected_salary_min && pref.expected_salary_max)
    return `${pref.expected_salary_min.toLocaleString()}–${pref.expected_salary_max.toLocaleString()} BDT`
  return pref.expected_salary_min?.toLocaleString() + ' BDT' || '—'
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(async () => {
  await Promise.all([loadJob(), loadApplicants()])
})
</script>
