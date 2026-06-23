<template>
  <div class="space-y-5">
    <div class="rounded-md border border-paper-200 bg-white p-4 shadow-sm sm:p-5">
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
            :class="job.status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200'">
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

    <!-- Mobile filter -->
    <div class="sm:hidden">
      <p class="mb-1 text-xs font-semibold font-display text-paper-500 uppercase tracking-wide">Applicant status</p>
      <DropSelect
        :model-value="activeTab"
        :options="tabs"
        @update:modelValue="switchTab"
      />
    </div>

    <!-- Desktop tabs -->
    <div class="hidden gap-1 overflow-x-auto border-b border-paper-200 sm:flex">
      <button v-for="tab in tabs" :key="tab.value" @click="switchTab(tab.value)"
        class="shrink-0 px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors"
        :class="activeTab === tab.value
          ? 'bg-white border border-b-white border-paper-200 -mb-px text-navy-900'
          : 'text-paper-500 hover:text-navy-700'">
        {{ tab.label }}
      </button>
    </div>

    <!-- Flow guide -->
    <div class="rounded-md border border-navy-100 bg-navy-50 px-4 py-3">
      <p class="text-xs font-semibold font-display text-navy-700 mb-1.5">How it works</p>
      <div class="flex flex-wrap items-center gap-1.5 text-xs font-body text-navy-600">
        <span class="rounded-pill bg-white border border-navy-200 px-2.5 py-1 font-semibold">1. Shortlist</span>
        <svg class="h-3 w-3 text-navy-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="rounded-pill bg-white border border-navy-200 px-2.5 py-1 font-semibold">2. Request Demo</span>
        <svg class="h-3 w-3 text-navy-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="rounded-pill bg-white border border-navy-200 px-2.5 py-1 font-semibold">3. Admin appoints</span>
        <svg class="h-3 w-3 text-navy-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="rounded-pill bg-white border border-navy-200 px-2.5 py-1 font-semibold">4. Request Confirm</span>
        <svg class="h-3 w-3 text-navy-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="rounded-pill bg-white border border-navy-200 px-2.5 py-1 font-semibold">5. Admin confirms</span>
      </div>
    </div>

    <div v-if="loading" class="card py-16 text-center text-paper-400 font-body text-sm">Loading…</div>

    <div v-else-if="!applicants.length" class="card py-16 text-center">
      <p class="font-display font-semibold text-navy-700">No applicants here</p>
      <p class="text-sm text-paper-500 font-body mt-1">
        {{ activeTab === 'all' ? 'No tutors have applied to this job yet.' : `No applicants with status '${activeTab}'.` }}
      </p>
    </div>

    <div v-else class="grid gap-4">
      <div v-for="app in applicants" :key="app.id" class="applicant-card">

        <!-- Header: avatar + identity + status -->
        <div class="flex items-start gap-3 sm:gap-4">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border border-paper-200 bg-navy-100 shadow-xs sm:h-14 sm:w-14">
            <img v-if="app.tutor_profile?.user?.avatar_url" :src="app.tutor_profile.user.avatar_url" class="h-full w-full object-cover" />
            <span v-else class="font-display font-bold text-lg text-navy-700">
              {{ (app.tutor_profile?.user?.name || '?').charAt(0).toUpperCase() }}
            </span>
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <p class="truncate font-display font-bold text-base leading-tight text-navy-900 sm:text-lg">
                  {{ app.tutor_profile?.user?.name || 'Unnamed tutor' }}
                </p>
                <p class="mt-0.5 text-xs text-paper-500 font-body">
                  ID: <span class="font-semibold text-navy-600">{{ app.tutor_profile?.tutor_id || '—' }}</span>
                </p>
              </div>
              <div class="flex shrink-0 flex-col items-end gap-2">
                <span class="text-xs font-semibold font-display px-2.5 py-1 rounded-pill border"
                  :class="statusClass(app.status)">
                  {{ statusLabel(app.status) }}
                </span>
                <a :href="`/tutors/${app.tutor_profile?.public_id}`" target="_blank"
                  class="inline-flex items-center justify-center whitespace-nowrap rounded-md border border-navy-200 bg-navy-50 px-3 py-1.5 text-xs font-semibold font-display text-navy-700 transition-colors hover:bg-navy-100">
                  View Profile
                </a>
              </div>
            </div>

            <!-- Meta chips -->
            <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
              <span class="inline-flex items-center gap-1 rounded-pill border border-gold-200 bg-gold-50 px-2.5 py-1 text-xs font-semibold font-display text-gold-700">
                <span class="text-gold-500">★</span>
                {{ app.tutor_profile?.rating || '—' }} ({{ app.tutor_profile?.review_count ?? 0 }})
              </span>
              <span v-if="app.tutor_profile?.personal_info?.gender && app.tutor_profile.personal_info.gender !== 'other'"
                class="inline-flex items-center rounded-pill border border-paper-200 bg-paper-50 px-2.5 py-1 text-xs font-semibold font-display text-paper-600 capitalize">
                {{ app.tutor_profile.personal_info.gender }}
              </span>
              <span class="inline-flex items-center gap-1 rounded-pill border border-paper-200 bg-paper-50 px-2.5 py-1 text-xs font-semibold font-display text-paper-600">
                <svg class="h-3 w-3 text-paper-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0V11.25A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                {{ formatDate(app.applied_at) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div v-if="actionableButtons(app)" class="mt-4 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
          <button v-if="app.status === 'applied'" @click="act('shortlist', app)"
            :disabled="acting[app.id]"
            class="app-action border-navy-200 bg-navy-700 text-white hover:bg-navy-800 disabled:opacity-50">
            Shortlist
          </button>
          <button v-if="app.status === 'shortlisted'" @click="demoTarget = app"
            :disabled="acting[app.id]"
            class="app-action border-gold-500 bg-gold-500 text-navy-900 hover:bg-gold-600 disabled:opacity-50">
            Request Demo
          </button>
          <button v-if="app.status === 'appointed'" @click="confirmTarget = app"
            :disabled="acting[app.id]"
            class="app-action border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50">
            Request Confirm
          </button>
          <button v-if="!['connected','not_selected','demo_requested','confirm_requested'].includes(app.status)" @click="removeTarget = app"
            :disabled="acting[app.id]"
            class="app-action border-red-200 text-red-600 hover:bg-red-50 disabled:opacity-50">
            Remove
          </button>
        </div>

        <!-- Pending request notice -->
        <div v-if="app.status === 'demo_requested'" class="mt-3 rounded-md border border-gold-200 bg-gold-50 px-3 py-2 text-xs font-body text-gold-800">
          Demo request sent to admin. Waiting for admin to appoint this tutor.
        </div>
        <div v-if="app.status === 'confirm_requested'" class="mt-3 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-body text-emerald-800">
          Confirmation request sent to admin. Waiting for admin to confirm this tutor.
        </div>
      </div>
    </div>

    <!-- Request Demo dialog -->
    <ConfirmDialog
      :show="!!demoTarget"
      title="Request Demo Appointment?"
      :message="demoTarget ? `Send a request to admin to appoint ${demoTarget.tutor_profile?.user?.name} for a demo class? Admin will coordinate the schedule.` : ''"
      confirm-label="Send Request"
      @confirm="doRequestDemo"
      @cancel="demoTarget = null"
    />

    <!-- Request Confirm dialog -->
    <ConfirmDialog
      :show="!!confirmTarget"
      title="Request Confirmation?"
      :message="confirmTarget ? `Send a request to admin to confirm ${confirmTarget.tutor_profile?.user?.name} as the tutor for this job?` : ''"
      confirm-label="Send Request"
      @confirm="doRequestConfirm"
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
import DropSelect from '@/components/search/DropSelect.vue'

const route       = useRoute()
const publicId    = route.params.publicId

const tabs = [
  { value: 'all',              label: 'All'              },
  { value: 'applied',          label: 'Applied'          },
  { value: 'shortlisted',      label: 'Shortlisted'      },
  { value: 'demo_requested',   label: 'Demo Requested'   },
  { value: 'appointed',        label: 'Appointed'        },
  { value: 'confirm_requested',label: 'Confirm Requested'},
  { value: 'connected',        label: 'Confirmed'        },
  { value: 'not_selected',     label: 'Not Selected'     },
]

const job           = ref(null)
const applicants    = ref([])
const loading       = ref(false)
const activeTab     = ref('all')
const acting        = ref({})
const demoTarget    = ref(null)
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
    await guardianJobsApi.shortlistApplicant(publicId, app.id)
    app.status = 'shortlisted'
    toast.success('Tutor shortlisted.')
    if (activeTab.value !== 'all') loadApplicants()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Action failed.')
  } finally {
    delete acting.value[app.id]
  }
}

async function doRequestDemo() {
  const app = demoTarget.value
  demoTarget.value = null
  if (!app) return
  acting.value[app.id] = true
  try {
    await guardianJobsApi.requestDemoApplicant(publicId, app.id)
    app.status = 'demo_requested'
    toast.success('Demo request sent to admin.')
    if (activeTab.value !== 'all' && activeTab.value !== 'demo_requested') loadApplicants()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to send demo request.')
  } finally {
    delete acting.value[app.id]
  }
}

async function doRequestConfirm() {
  const app = confirmTarget.value
  confirmTarget.value = null
  if (!app) return
  acting.value[app.id] = true
  try {
    await guardianJobsApi.requestConfirmApplicant(publicId, app.id)
    app.status = 'confirm_requested'
    toast.success('Confirmation request sent to admin.')
    if (activeTab.value !== 'all' && activeTab.value !== 'confirm_requested') loadApplicants()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to send confirmation request.')
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
    app.status = 'not_selected'
    toast.success('Applicant marked as not selected.')
    if (activeTab.value !== 'all' && activeTab.value !== 'not_selected') loadApplicants()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to remove applicant.')
  } finally {
    delete acting.value[app.id]
  }
}

function actionableButtons(app) {
  return ['applied', 'shortlisted', 'appointed', 'demo_requested', 'confirm_requested'].includes(app.status)
}

function statusClass(s) {
  if (s === 'applied')           return 'bg-blue-50 text-blue-700 border-blue-200'
  if (s === 'shortlisted')       return 'bg-navy-50 text-navy-700 border-navy-200'
  if (s === 'demo_requested')    return 'bg-gold-50 text-gold-700 border-gold-200'
  if (s === 'appointed')         return 'bg-purple-50 text-purple-700 border-purple-200'
  if (s === 'confirm_requested') return 'bg-teal-50 text-teal-700 border-teal-200'
  if (s === 'connected')         return 'bg-emerald-50 text-emerald-700 border-emerald-200'
  if (s === 'not_selected')      return 'bg-red-50 text-red-600 border-red-200'
  return 'bg-paper-100 text-paper-500 border-paper-200'
}

function statusLabel(s) {
  const map = {
    applied:          'Applied',
    shortlisted:      'Shortlisted',
    demo_requested:   'Demo Requested',
    appointed:        'Appointed',
    confirm_requested:'Confirm Requested',
    connected:        'Confirmed',
    not_selected:     'Not Selected',
  }
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

<style scoped>
.applicant-card {
  @apply rounded-md border border-paper-200 bg-white p-4 shadow-sm transition-all hover:border-navy-200 hover:shadow-md sm:p-5;
}

.app-action {
  @apply inline-flex min-h-[40px] items-center justify-center rounded-md border px-4 py-2 text-xs font-semibold font-display transition-colors;
}
</style>
