<template>
  <div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center gap-3">
      <RouterLink to="/admin/tuition-jobs"
        class="flex items-center gap-1.5 text-sm font-semibold font-display text-paper-500 hover:text-navy-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
        Tuition Jobs
      </RouterLink>
    </div>

    <div v-if="loadingJob" class="card py-16 text-center text-paper-400 font-body text-sm">Loading…</div>

    <template v-else-if="job">
      <!-- Job Info Card -->
      <div class="card space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <span class="text-xs font-semibold font-display px-2.5 py-0.5 rounded-pill border"
                :class="job.status === 'open'
                  ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                  : 'bg-red-50 text-red-600 border-red-200'">
                {{ job.status === 'open' ? 'Open' : 'Closed' }}
              </span>
              <span class="text-xs text-paper-400 font-body">#{{ job.public_id }}</span>
              <span class="text-xs text-paper-400 font-body">·</span>
              <span class="text-xs text-paper-400 font-body">{{ job.applications_count ?? 0 }} applicants</span>
              <span class="text-xs text-paper-400 font-body">·</span>
              <span class="text-xs text-paper-400 font-body">{{ formatDate(job.created_at) }}</span>
            </div>
            <h1 class="font-display font-bold text-xl text-navy-900 leading-snug">{{ job.title }}</h1>
          </div>

          <!-- Close / Reopen -->
          <div class="shrink-0">
            <button v-if="job.status === 'open'" @click="closeJobConfirm = true" :disabled="acting"
              class="w-full sm:w-auto rounded-sm bg-red-600 px-4 py-2 text-sm font-semibold font-display text-white hover:bg-red-700 transition-colors disabled:opacity-50">
              Close Job
            </button>
            <button v-else @click="reopenJobConfirm = true" :disabled="acting"
              class="w-full sm:w-auto rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold font-display text-white hover:bg-emerald-700 transition-colors disabled:opacity-50">
              Reopen Job
            </button>
          </div>
        </div>

        <!-- Details grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 pt-1">
          <div v-for="d in details" :key="d.label" class="bg-paper-50 rounded-sm px-3 py-2.5">
            <p class="text-[10px] font-bold font-display uppercase text-paper-400 tracking-wide">{{ d.label }}</p>
            <p class="mt-0.5 text-sm font-semibold font-display text-navy-800 leading-snug">{{ d.value || '—' }}</p>
          </div>
        </div>

        <!-- Subjects -->
        <div v-if="job.subjects?.length" class="flex flex-wrap gap-1.5 pt-1">
          <span class="text-xs font-bold font-display uppercase text-paper-400 tracking-wide self-center mr-1">Subjects:</span>
          <span v-for="s in job.subjects" :key="s.id"
            class="text-xs bg-navy-50 text-navy-700 border border-navy-100 px-2.5 py-0.5 rounded-pill font-body">
            {{ s.name }}
          </span>
        </div>

        <!-- Guardian -->
        <div class="pt-1 border-t border-paper-100">
          <p class="text-xs font-bold font-display uppercase text-paper-400 tracking-wide mb-1.5">Posted by</p>
          <div class="flex items-center gap-2">
            <p class="text-sm font-semibold font-display text-navy-800">{{ job.guardian_profile?.user?.name ?? '—' }}</p>
          </div>
          <p class="text-xs text-paper-500 font-body mt-0.5">{{ job.guardian_profile?.user?.email }}</p>
          <p v-if="job.guardian_profile?.guardian_id" class="text-xs text-paper-400 font-body mt-0.5">
            ID: <span class="font-semibold text-navy-600">{{ job.guardian_profile.guardian_id }}</span>
          </p>
        </div>
      </div>

      <!-- Applicants -->
      <div class="overflow-hidden rounded-md border border-paper-200 bg-white shadow-sm">
        <div class="border-b border-paper-100 p-5">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
              <h2 class="font-display font-bold text-xl text-navy-900">Applicants</h2>
              <p class="mt-1 text-sm font-body text-paper-500">
                {{ applicants.length }} shown for {{ activeTab === 'all' ? 'all statuses' : statusLabel(activeTab).toLowerCase() }}.
              </p>
            </div>

            <!-- Mobile: dropdown -->
            <div class="sm:hidden">
              <DropSelect
                :model-value="activeTab"
                :options="appTabs"
                @update:modelValue="switchTab"
              />
            </div>

            <!-- Desktop: tabs -->
            <div class="hidden max-w-full gap-1 overflow-x-auto rounded-md bg-paper-50 p-1 sm:flex">
              <button v-for="tab in appTabs" :key="tab.value" @click="switchTab(tab.value)"
                class="min-h-[36px] rounded-md px-3 py-1.5 text-xs font-semibold font-display transition-colors whitespace-nowrap"
                :class="activeTab === tab.value
                  ? 'bg-navy-700 text-white shadow-sm'
                  : 'text-paper-500 hover:text-navy-700 hover:bg-white'">
                {{ tab.label }}
              </button>
            </div>
          </div>
        </div>

        <div v-if="loadingApps" class="py-14 text-center text-paper-400 font-body text-sm">Loading applicants…</div>

        <div v-else-if="!applicants.length" class="py-14 text-center">
          <p class="font-display font-semibold text-navy-700 text-sm">No applicants</p>
          <p class="text-xs text-paper-500 font-body mt-1">
            {{ activeTab === 'all' ? 'No one has applied to this job yet.' : `No ${activeTab} applicants.` }}
          </p>
        </div>

        <div v-else class="space-y-3 bg-paper-50/60 p-4 sm:p-5">
          <div v-for="app in applicants" :key="app.id"
            class="rounded-md border border-paper-200 bg-white p-4 shadow-xs transition-colors hover:border-navy-200 sm:p-5">

            <div class="flex min-w-0 items-start gap-3">
              <!-- Avatar -->
              <span class="flex h-12 w-12 shrink-0 overflow-hidden rounded-md border border-paper-200 bg-navy-50">
              <img v-if="app.tutor_profile?.user?.avatar_url"
                :src="app.tutor_profile.user.avatar_url" class="h-full w-full object-cover" />
              <span v-else class="flex h-full w-full items-center justify-center text-sm font-bold font-display text-navy-600">
                {{ nameInitials(app.tutor_profile?.user?.name) }}
              </span>
              </span>

              <!-- Name + email + ID -->
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <p class="font-display font-bold text-navy-900 text-base leading-tight">
                    {{ app.tutor_profile?.user?.name ?? '—' }}
                  </p>
                  <VerifiedBadge v-if="app.tutor_profile?.is_verified" size="sm" />
                </div>
                <p class="mt-1 truncate text-xs text-paper-500 font-body">{{ app.tutor_profile?.user?.email || 'No email' }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                  <span v-if="app.tutor_profile?.tutor_id" class="app-meta-chip">
                    ID: {{ app.tutor_profile.tutor_id }}
                  </span>
                  <span class="app-meta-chip">Applied {{ formatDate(app.applied_at) }}</span>
                  <span class="app-status-chip" :class="statusClass(app.status)">
                    {{ statusLabel(app.status) }}
                  </span>
                  <span class="app-rating-chip">{{ ratingLabel(app) }}</span>
                </div>
              </div>

              <!-- Top-right stack: Profile + status dropdown -->
              <div class="flex w-28 shrink-0 flex-col gap-2 sm:w-44">
                <RouterLink v-if="app.tutor_profile?.tutor_id"
                  :to="`/admin/tutors/${app.tutor_profile.tutor_id}`"
                  class="inline-flex min-h-[38px] items-center justify-center rounded-md border border-paper-300 px-3 py-1.5 text-xs font-semibold font-display text-paper-700 transition-colors hover:bg-paper-100">
                  Profile
                </RouterLink>
                <DropSelect v-if="jobOpen && app.status === 'not_selected'"
                  :model-value="''"
                  :options="restoreOptions"
                  placeholder="Change status…"
                  @update:modelValue="(val) => onPickStatus(app, val)"
                />
              </div>
            </div>

            <!-- Guardian request notice -->
            <div v-if="app.status === 'demo_requested'" class="mt-3 rounded-md border border-gold-200 bg-gold-50 px-3 py-2 text-xs font-body text-gold-800">
              Guardian has requested a demo class appointment for this tutor.
            </div>
            <div v-if="app.status === 'confirm_requested'" class="mt-3 rounded-md border border-teal-200 bg-teal-50 px-3 py-2 text-xs font-body text-teal-800">
              Guardian has requested confirmation of this tutor after the demo class.
            </div>

            <div v-if="hasWorkflowActions(app)" class="mt-4 flex flex-col gap-2 border-t border-paper-100 pt-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <button v-if="app.status === 'applied'" @click="shortlistTarget = app"
                  :disabled="!!acting"
                class="app-action-primary bg-navy-700 hover:bg-navy-800">
                  Shortlist
                </button>
                <button v-if="['shortlisted', 'demo_requested'].includes(app.status)" @click="appointTarget = app"
                  :disabled="!!acting"
                class="app-action-primary bg-purple-600 hover:bg-purple-700">
                  {{ app.status === 'demo_requested' ? 'Appoint (Requested)' : 'Appoint' }}
                </button>
                <button v-if="['appointed', 'confirm_requested'].includes(app.status)" @click="confirmTarget = app"
                  :disabled="!!acting"
                class="app-action-primary bg-emerald-600 hover:bg-emerald-700">
                  {{ app.status === 'confirm_requested' ? 'Confirm (Requested)' : 'Confirm' }}
                </button>
                <button @click="removeTarget = app"
                  :disabled="!!acting"
                class="app-action-secondary border-red-200 text-red-600 hover:bg-red-50">
                  Not Selected
                </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Close job dialog -->
    <ConfirmDialog
      :show="closeJobConfirm"
      title="Close Job"
      message="Are you sure you want to close this job? It will no longer be visible to new applicants."
      confirm-label="Close Job"
      :danger="true"
      @confirm="doCloseJob"
      @cancel="closeJobConfirm = false"
    />

    <!-- Reopen job dialog -->
    <ConfirmDialog
      :show="reopenJobConfirm"
      title="Reopen Job"
      message="Reopen this job? It will become visible to tutors again."
      confirm-label="Reopen Job"
      @confirm="doReopenJob"
      @cancel="reopenJobConfirm = false"
    />

    <!-- Shortlist dialog -->
    <ConfirmDialog
      :show="!!shortlistTarget"
      title="Shortlist Tutor"
      :message="shortlistTarget ? `Shortlist ${shortlistTarget.tutor_profile?.user?.name} for this job?` : ''"
      confirm-label="Shortlist"
      @confirm="doShortlist"
      @cancel="shortlistTarget = null"
    />

    <!-- Appoint dialog -->
    <ConfirmDialog
      :show="!!appointTarget"
      title="Appoint for Demo"
      :message="appointTarget
        ? `Appoint ${appointTarget.tutor_profile?.user?.name} for a demo class?${appointTarget.status === 'demo_requested' ? ' (Guardian has requested this.)' : ''}`
        : ''"
      confirm-label="Appoint"
      @confirm="doAppoint"
      @cancel="appointTarget = null"
    />

    <!-- Confirm tutor dialog -->
    <ConfirmDialog
      :show="!!confirmTarget"
      title="Confirm Tutor"
      :message="confirmTarget
        ? `Confirm ${confirmTarget.tutor_profile?.user?.name} as the tutor? The job will be closed.${confirmTarget.status === 'confirm_requested' ? ' (Guardian has requested this confirmation.)' : ''}`
        : ''"
      confirm-label="Confirm Tutor"
      @confirm="doConfirm"
      @cancel="confirmTarget = null"
    />

    <!-- Not selected dialog -->
    <ConfirmDialog
      :show="!!removeTarget"
      title="Mark as Not Selected"
      :message="removeTarget ? `Mark ${removeTarget.tutor_profile?.user?.name} as not selected for this job?` : ''"
      confirm-label="Not Selected"
      :danger="true"
      @confirm="doRemove"
      @cancel="removeTarget = null"
    />

    <!-- Change status dialog -->
    <ConfirmDialog
      :show="!!statusChangeTarget"
      title="Change Applicant Status"
      :message="statusChangeTarget ? `Change ${statusChangeTarget.app.tutor_profile?.user?.name}'s status to ${statusLabel(statusChangeTarget.status)}?` : ''"
      confirm-label="Change Status"
      @confirm="doChangeStatus"
      @cancel="statusChangeTarget = null"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import VerifiedBadge from '@/components/common/VerifiedBadge.vue'
import DropSelect from '@/components/search/DropSelect.vue'

const route = useRoute()
const publicId = route.params.publicId

const job         = ref(null)
const applicants  = ref([])
const loadingJob  = ref(true)
const loadingApps = ref(true)
const acting          = ref(false)
const activeTab       = ref('all')
const closeJobConfirm  = ref(false)
const reopenJobConfirm = ref(false)
const shortlistTarget  = ref(null)
const appointTarget    = ref(null)
const confirmTarget    = ref(null)
const removeTarget     = ref(null)
const statusChangeTarget = ref(null)

const jobOpen = computed(() => job.value?.status === 'open')

function hasWorkflowActions(app) {
  return jobOpen.value && ['applied', 'shortlisted', 'demo_requested', 'appointed', 'confirm_requested'].includes(app.status)
}

const restoreOptions = [
  { value: 'applied',          label: 'Applied'           },
  { value: 'shortlisted',      label: 'Shortlisted'       },
  { value: 'demo_requested',   label: 'Demo Requested'    },
  { value: 'appointed',        label: 'Appointed'         },
  { value: 'confirm_requested',label: 'Confirm Requested' },
]

const appTabs = [
  { value: 'all',              label: 'All'               },
  { value: 'applied',          label: 'Applied'           },
  { value: 'shortlisted',      label: 'Shortlisted'       },
  { value: 'demo_requested',   label: 'Demo Requested'    },
  { value: 'appointed',        label: 'Appointed'         },
  { value: 'confirm_requested',label: 'Confirm Requested' },
  { value: 'connected',        label: 'Confirmed'         },
  { value: 'not_selected',     label: 'Not Selected'      },
]

const MEDIUM_MAP = {
  bangla_medium:   'Bangla Medium',
  english_medium:  'English Medium',
  english_version: 'English Version',
}
const PLACE_MAP = {
  student_home: "Student's Home",
  tutor_home:   "Tutor's Home",
  online:       'Online',
}
const STYLE_MAP = {
  one_to_one: 'One-to-one',
  group:      'Group',
  online:     'Online',
}

const details = computed(() => {
  if (!job.value) return []
  const j = job.value
  return [
    { label: 'Location',     value: j.area ? `${j.area.name}, ${j.district?.name}` : j.district?.name },
    { label: 'Salary',       value: j.offered_salary ? `৳${j.offered_salary.toLocaleString()}/mo` : null },
    { label: 'Class',        value: j.class_level },
    { label: 'Medium',       value: MEDIUM_MAP[j.medium] ?? j.medium },
    { label: 'Place',        value: PLACE_MAP[j.tuition_type] ?? j.tuition_type },
    { label: 'Style',        value: STYLE_MAP[j.tutoring_style] ?? j.tutoring_style },
    { label: 'Days/Week',    value: j.tutoring_days_per_week ? `${j.tutoring_days_per_week} days` : null },
    { label: 'Hire Date',    value: j.hire_date ? formatDate(j.hire_date) : null },
    { label: 'Time',         value: j.tutoring_time ? formatTime(j.tutoring_time) : null },
    { label: 'Students',     value: j.num_students ? `${j.num_students}` : null },
    { label: 'Student Gender', value: j.student_gender ? capitalize(j.student_gender) : null },
    { label: 'Tutor Pref',   value: j.tutor_gender_pref ? capitalize(j.tutor_gender_pref) : null },
    { label: 'Institute',    value: j.student_institute },
  ].filter(d => d.value)
})

async function loadJob() {
  loadingJob.value = true
  try {
    const { data } = await adminApi.getTuitionJob(publicId)
    job.value = data.data
  } catch {
    toast.error('Failed to load job.')
  } finally {
    loadingJob.value = false
  }
}

async function loadApplicants() {
  loadingApps.value = true
  try {
    const params = activeTab.value !== 'all' ? { status: activeTab.value } : {}
    const { data } = await adminApi.getTuitionJobApplications(publicId, params)
    applicants.value = data.data || []
  } catch {
    toast.error('Failed to load applicants.')
  } finally {
    loadingApps.value = false
  }
}

function switchTab(tab) {
  activeTab.value = tab
  loadApplicants()
}

async function doShortlist() {
  const app = shortlistTarget.value
  shortlistTarget.value = null
  acting.value = true
  try {
    await adminApi.shortlistApplicant(publicId, app.id)
    app.status = 'shortlisted'
    toast.success('Tutor shortlisted.')
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Action failed.')
  } finally {
    acting.value = false
  }
}

async function doAppoint() {
  const app = appointTarget.value
  appointTarget.value = null
  acting.value = true
  try {
    await adminApi.appointApplicant(publicId, app.id)
    app.status = 'appointed'
    toast.success('Tutor appointed for demo class.')
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Action failed.')
  } finally {
    acting.value = false
  }
}

async function doConfirm() {
  const app = confirmTarget.value
  confirmTarget.value = null
  acting.value = true
  try {
    await adminApi.confirmApplicant(publicId, app.id)
    app.status = 'connected'
    if (job.value) job.value.status = 'closed'
    toast.success('Tutor confirmed. Connection request created.')
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Failed to confirm tutor.')
  } finally {
    acting.value = false
  }
}

async function doRemove() {
  const app = removeTarget.value
  removeTarget.value = null
  acting.value = true
  try {
    await adminApi.removeApplicant(publicId, app.id)
    app.status = 'not_selected'
    toast.success('Applicant marked as not selected.')
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Failed to remove applicant.')
  } finally {
    acting.value = false
  }
}

function onPickStatus(app, status) {
  if (!status) return
  statusChangeTarget.value = { app, status }
}

async function doChangeStatus() {
  const { app, status } = statusChangeTarget.value
  statusChangeTarget.value = null
  acting.value = true
  try {
    await adminApi.changeApplicantStatus(publicId, app.id, status)
    app.status = status
    toast.success('Applicant status updated.')
    if (activeTab.value !== 'all') loadApplicants()
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Failed to change status.')
  } finally {
    acting.value = false
  }
}

async function doCloseJob() {
  closeJobConfirm.value = false
  acting.value = true
  try {
    await adminApi.closeTuitionJob(publicId)
    if (job.value) job.value.status = 'closed'
    toast.success('Job closed.')
  } catch {
    toast.error('Failed to close job.')
  } finally {
    acting.value = false
  }
}

async function doReopenJob() {
  reopenJobConfirm.value = false
  acting.value = true
  try {
    await adminApi.reopenTuitionJob(publicId)
    if (job.value) job.value.status = 'open'
    toast.success('Job reopened.')
  } catch {
    toast.error('Failed to reopen job.')
  } finally {
    acting.value = false
  }
}

function statusClass(s) {
  if (s === 'applied')           return 'bg-blue-50 text-blue-700 border-blue-200'
  if (s === 'shortlisted')       return 'bg-navy-50 text-navy-700 border-navy-200'
  if (s === 'demo_requested')    return 'bg-gold-50 text-gold-700 border-gold-200'
  if (s === 'appointed')         return 'bg-purple-50 text-purple-700 border-purple-200'
  if (s === 'confirm_requested') return 'bg-teal-50 text-teal-700 border-teal-200'
  if (s === 'connected')         return 'bg-emerald-50 text-emerald-700 border-emerald-200'
  return 'bg-red-50 text-red-600 border-red-200'
}

function statusLabel(s) {
  return {
    applied:          'Applied',
    shortlisted:      'Shortlisted',
    demo_requested:   'Demo Requested',
    appointed:        'Appointed',
    confirm_requested:'Confirm Requested',
    connected:        'Confirmed',
    not_selected:     'Not Selected',
  }[s] ?? s
}

function nameInitials(name = '') {
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
}

function ratingLabel(app) {
  const rating = app.tutor_profile?.rating
  if (!rating) return '—'
  const count = app.tutor_profile?.review_count ?? 0
  return `★ ${Number(rating).toFixed(1)} (${count})`
}

function capitalize(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
}

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatTime(t) {
  if (!t) return ''
  const [h, m] = t.split(':').map(Number)
  const period = h >= 12 ? 'PM' : 'AM'
  const hour   = h % 12 || 12
  return `${hour}:${String(m).padStart(2, '0')} ${period}`
}

onMounted(() => {
  loadJob()
  loadApplicants()
})
</script>

<style scoped>
.app-meta-chip {
  @apply inline-flex items-center rounded-pill border border-paper-200 bg-paper-50 px-2.5 py-1 text-[11px] font-semibold font-display text-paper-600;
}

.app-status-chip {
  @apply inline-flex items-center rounded-pill border px-2.5 py-1 text-[11px] font-semibold font-display;
}

.app-rating-chip {
  @apply inline-flex items-center rounded-pill border border-gold-200 bg-gold-50 px-2.5 py-1 text-[11px] font-semibold font-display text-gold-700;
}

.app-action-primary {
  @apply inline-flex min-h-[40px] flex-1 items-center justify-center rounded-md px-3 py-2 text-xs font-semibold font-display text-white transition-colors disabled:opacity-50 sm:flex-none;
}

.app-action-secondary {
  @apply inline-flex min-h-[40px] flex-1 items-center justify-center rounded-md border px-3 py-2 text-xs font-semibold font-display transition-colors disabled:opacity-50 sm:flex-none;
}
</style>
