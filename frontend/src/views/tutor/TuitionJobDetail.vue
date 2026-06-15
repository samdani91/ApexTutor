<template>
  <div class="max-w-2xl space-y-5">
    <RouterLink to="/jobs" class="text-xs font-semibold font-display text-navy-600 hover:underline">← Back to Jobs</RouterLink>

    <div v-if="loading" class="card py-16 text-center text-paper-400 font-body text-sm">Loading…</div>

    <template v-else-if="job">
      <!-- Header -->
      <div class="card">
        <div class="flex flex-wrap items-center gap-2 mb-2">
          <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border"
            :class="job.status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-paper-100 text-paper-500 border-paper-200'">
            {{ job.status === 'open' ? 'Open' : 'Closed' }}
          </span>
          <span class="text-xs text-paper-400 font-body">Job ID: #{{ job.public_id }}</span>
        </div>
        <h1 class="font-display font-bold text-xl text-navy-900 leading-snug">{{ job.title }}</h1>
        <p class="text-xs text-paper-400 font-body mt-1">Posted {{ formatDate(job.created_at) }}</p>
      </div>

      <!-- Details grid -->
      <div class="card">
        <h2 class="font-display font-semibold text-navy-800 text-base mb-4">Job Details</h2>
        <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
          <div v-for="row in detailRows" :key="row.label">
            <dt class="text-[10px] font-bold font-display text-paper-400 uppercase tracking-wide">{{ row.label }}</dt>
            <dd class="mt-0.5 text-sm font-semibold font-display text-navy-800">{{ row.value }}</dd>
          </div>
        </dl>

        <div v-if="job.subjects?.length" class="mt-5">
          <dt class="text-[10px] font-bold font-display text-paper-400 uppercase tracking-wide mb-2">Subjects</dt>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="s in job.subjects" :key="s.id"
              class="text-sm bg-navy-50 text-navy-700 border border-navy-100 px-3 py-1 rounded-pill font-body">
              {{ s.name }}
            </span>
          </div>
        </div>

        <div v-if="job.extra_requirements" class="mt-5 rounded-sm bg-paper-50 border border-paper-200 p-4">
          <p class="text-xs font-bold font-display text-paper-400 uppercase tracking-wide mb-1.5">Other Requirements</p>
          <p class="text-sm font-body text-paper-700 leading-relaxed">{{ job.extra_requirements }}</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-wrap gap-3">
        <button @click="showMap = true"
          class="flex items-center gap-2 rounded-sm border border-paper-300 px-4 py-2.5 text-sm font-semibold font-display text-navy-700 hover:bg-paper-100 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
          </svg>
          Location
        </button>
        <a :href="directionsUrl" target="_blank" rel="noopener"
          class="flex items-center gap-2 rounded-sm border border-paper-300 px-4 py-2.5 text-sm font-semibold font-display text-navy-700 hover:bg-paper-100 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
          </svg>
          Directions
        </a>

        <div v-if="job.my_application" class="flex items-center gap-2 rounded-sm bg-emerald-50 border border-emerald-200 px-4 py-2.5">
          <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="text-sm font-semibold font-display text-emerald-700">
            Application {{ statusLabel(job.my_application.status) }}
          </span>
        </div>
        <button v-else-if="job.status === 'open'" @click="showApplyDialog = true"
          class="btn-primary py-2.5 px-6 text-sm">
          Apply
        </button>
        <span v-else class="text-sm text-paper-500 font-body py-2.5">This job is closed.</span>
      </div>
    </template>

    <!-- Map Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showMap" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4" @click.self="showMap = false">
          <div class="bg-white rounded-sm shadow-xl w-full max-w-lg p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-display font-bold text-navy-900">Job Location</h3>
              <button @click="showMap = false" class="text-paper-400 hover:text-navy-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <div class="rounded-sm overflow-hidden border border-paper-200">
              <iframe :src="mapEmbedUrl" width="100%" height="300" style="border:0" allowfullscreen loading="lazy"></iframe>
            </div>
            <p class="text-xs text-paper-400 font-body mt-3">
              Note: The exact location of this tuition job is most probably inside this area.
            </p>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Apply Dialog -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showApplyDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4" @click.self="showApplyDialog = false">
          <div class="bg-white rounded-sm shadow-xl w-full max-w-sm p-6">
            <h3 class="font-display font-bold text-navy-900 text-base mb-1">Apply for this Tuition?</h3>
            <div class="mb-4 space-y-1">
              <p class="text-sm font-semibold font-display text-navy-800">{{ job.title }}</p>
              <p class="text-sm text-paper-500 font-body">{{ job.offered_salary?.toLocaleString() }} BDT &middot; {{ locationStr }}</p>
            </div>

            <!-- Gender mismatch warning -->
            <div v-if="genderMismatch"
              class="mb-4 flex items-start gap-2 rounded-sm bg-red-50 border border-red-200 px-3 py-2.5">
              <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
              </svg>
              <p class="text-xs text-red-700 font-body">
                Note: This job requires a <strong>"{{ job.tutor_gender_pref === 'female' ? 'Female' : 'Male' }}"</strong> tutor. You may still apply.
              </p>
            </div>

            <div class="flex gap-3">
              <button @click="showApplyDialog = false"
                class="flex-1 text-sm font-semibold font-display py-2 rounded-sm border border-paper-300 bg-paper-100 text-paper-700 hover:bg-paper-200 transition-colors">
                Cancel
              </button>
              <button @click="doApply" :disabled="applying"
                class="flex-1 text-sm font-semibold font-display py-2 rounded-sm bg-navy-700 text-white hover:bg-navy-800 transition-colors disabled:opacity-50">
                {{ applying ? 'Applying…' : 'Apply' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { tutorJobsApi } from '@/api/jobs.js'
import { tutorApi } from '@/api/tutor.js'
import { toast } from 'vue-sonner'

const route   = useRoute()
const publicId = route.params.publicId

const job             = ref(null)
const loading         = ref(true)
const showMap         = ref(false)
const showApplyDialog = ref(false)
const applying        = ref(false)
const tutorGender     = ref(null)

const locationStr = computed(() => {
  if (!job.value) return ''
  const parts = [job.value.area?.name, job.value.district?.name, 'Bangladesh'].filter(Boolean)
  return parts.join(', ')
})

const directionsUrl = computed(() => {
  if (!job.value) return '#'
  const q = encodeURIComponent(locationStr.value)
  return `https://www.google.com/maps/dir/?api=1&destination=${q}`
})

const mapEmbedUrl = computed(() => {
  if (!job.value) return ''
  const q = encodeURIComponent(locationStr.value)
  return `https://maps.google.com/maps?q=${q}&output=embed`
})

const genderMismatch = computed(() => {
  if (!job.value || !tutorGender.value) return false
  const pref = job.value.tutor_gender_pref
  if (pref === 'any') return false
  return tutorGender.value !== pref
})

const detailRows = computed(() => {
  if (!job.value) return []
  const j = job.value
  const typeMap = { home:'Home Tutoring', online:'Online Tutoring', group:'Group Tutoring', home_and_online:'Home & Online' }
  const genderMap = { male:'Male', female:'Female', any:'Any' }
  const rows = [
    { label: 'Tuition Type',    value: typeMap[j.tuition_type] ?? j.tuition_type },
    { label: 'Student Gender',  value: genderMap[j.student_gender] ?? j.student_gender },
    { label: 'Preferred Tutor', value: genderMap[j.tutor_gender_pref] ?? j.tutor_gender_pref },
    { label: 'Salary',          value: j.offered_salary ? `${j.offered_salary.toLocaleString()} BDT` : '—' },
    { label: 'No. of Students', value: j.num_students ?? '—' },
    { label: 'Location',        value: locationStr.value || '—' },
  ]
  if (j.tutoring_time)          rows.push({ label: 'Tutoring Time',  value: formatTime(j.tutoring_time) })
  if (j.tutoring_days_per_week) rows.push({ label: 'Tutoring Days',  value: `${j.tutoring_days_per_week} Days / Week` })
  if (j.hire_date)              rows.push({ label: 'Hire Date',      value: formatDate(j.hire_date) })
  if (j.student_institute)      rows.push({ label: 'Student Institute', value: j.student_institute })
  return rows
})

function statusLabel(s) {
  return { applied:'Submitted', shortlisted:'Shortlisted', appointed:'Appointed for Demo', connected:'Confirmed', removed:'Removed' }[s] ?? s
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' }) : '—'
}

function formatTime(t) {
  if (!t) return '—'
  const [h, m] = t.split(':').map(Number)
  const period = h >= 12 ? 'PM' : 'AM'
  const hour   = h % 12 || 12
  return `${String(hour).padStart(2,'0')}:${String(m).padStart(2,'0')} ${period}`
}

async function doApply() {
  applying.value = true
  try {
    await tutorJobsApi.apply(publicId)
    showApplyDialog.value = false
    toast.success('Application submitted!')
    job.value.my_application = { status: 'applied', applied_at: new Date().toISOString() }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to apply.')
  } finally {
    applying.value = false
  }
}

onMounted(async () => {
  try {
    const [jobRes, infoRes] = await Promise.all([
      tutorJobsApi.show(publicId),
      tutorApi.getPersonalInfo().catch(() => null),
    ])
    job.value         = jobRes.data.data
    tutorGender.value = infoRes?.data?.data?.gender ?? null
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
