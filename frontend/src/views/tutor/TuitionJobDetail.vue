<template>
  <DefaultLayout>
  <main class="public-grid-page relative isolate overflow-hidden bg-paper-50">
    <div class="pointer-events-none absolute inset-0 -z-10 public-grid"></div>
    <div class="mx-auto max-w-[84rem] px-4 py-6 md:py-10 space-y-5">
    <RouterLink to="/jobs"
      class="inline-flex items-center gap-1.5 rounded-sm border border-paper-300 bg-white px-3.5 py-2 text-sm font-semibold font-display text-navy-700 shadow-sm hover:bg-paper-100 transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
      </svg>
      Browse Jobs
    </RouterLink>

    <div v-if="loading" class="card py-16 text-center text-paper-400 font-body text-sm">Loading…</div>

    <template v-else-if="job">
      <!-- Header -->
      <div class="overflow-hidden rounded-md border border-paper-200 bg-white shadow-sm">
        <div class="bg-gradient-to-br from-white via-navy-50/60 to-gold-50/60 p-5 md:p-7">
          <div class="mb-4 flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold font-display px-2.5 py-1 rounded-pill border"
            :class="job.status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200'">
              {{ job.status === 'open' ? 'Open' : 'Closed' }}
            </span>
            <span class="text-xs font-semibold text-paper-500 font-body">Job ID: #{{ job.public_id }}</span>
            <span class="h-1 w-1 rounded-full bg-paper-300"></span>
            <span class="text-xs text-paper-500 font-body">Posted {{ formatDate(job.created_at) }}</span>
          </div>
          <div class="grid gap-5 lg:items-end">
            <div class="min-w-0">
              <h1 class="w-full break-words font-display font-bold text-2xl leading-tight text-navy-900 md:text-3xl xl:text-4xl">{{ job.title }}</h1>
              <p class="mt-3 flex items-start gap-2 text-sm font-body leading-relaxed text-paper-600">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-navy-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0z"/>
                </svg>
                {{ locationStr || 'Location not set' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        <section class="space-y-5">
          <!-- Details grid -->
          <div class="card">
            <div class="mb-5">
              <h2 class="font-display font-semibold text-navy-800 text-lg">Job Details</h2>
            </div>
            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
              <div v-for="row in detailRows" :key="row.label"
                class="rounded-md border border-paper-200 bg-paper-50/80 p-3.5">
                <dt class="flex items-center gap-1.5 text-[10px] font-bold font-display text-paper-500 uppercase">
                  <svg v-if="row.icon" class="w-4 h-4 shrink-0 text-navy-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path v-for="p in ICON_PATHS[row.icon]" :key="p" stroke-linecap="round" stroke-linejoin="round" :d="p"/>
                  </svg>
                  {{ row.label }}
                </dt>
                <dd class="mt-1.5 break-words text-sm font-semibold font-display leading-snug text-navy-900">{{ row.value }}</dd>
              </div>
            </dl>
          </div>

          <div v-if="job.subjects?.length" class="card">
            <div class="mb-3 flex items-center gap-2">
              <svg class="w-4 h-4 shrink-0 text-navy-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path v-for="p in ICON_PATHS.book" :key="p" stroke-linecap="round" stroke-linejoin="round" :d="p"/>
              </svg>
              <h2 class="font-display font-semibold text-navy-800 text-lg">Subjects</h2>
            </div>
            <div class="flex flex-wrap gap-2">
              <span v-for="s in job.subjects" :key="s.id"
                class="text-sm bg-navy-50 text-navy-700 border border-navy-100 px-3 py-1.5 rounded-pill font-semibold font-display">
                {{ s.name }}
              </span>
            </div>
          </div>

          <div v-if="job.extra_requirements" class="card">
            <p class="text-xs font-bold font-display text-paper-500 uppercase mb-2">Other Requirements</p>
            <p class="text-sm font-body text-paper-700 leading-relaxed">{{ job.extra_requirements }}</p>
          </div>
        </section>

        <!-- Actions -->
        <aside class="card lg:sticky lg:top-24">
          <h2 class="font-display font-semibold text-navy-800 text-lg">Next Step</h2>
          <p class="mt-1 text-sm font-body leading-relaxed text-paper-600">
            Review the job details and apply if the schedule, subject, and location match your preference.
          </p>

          <div class="mt-5 space-y-3">
            <button @click="showMap = true"
              class="btn-outline w-full justify-center py-2.5 text-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
              </svg>
              Location
            </button>
            <a :href="directionsUrl" target="_blank" rel="noopener"
              class="btn-outline w-full justify-center py-2.5 text-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
              </svg>
              Directions
            </a>

            <div v-if="job.my_application"
              class="flex items-center gap-2 rounded-md px-4 py-3 border"
              :class="appStatusIsNegative(job.my_application.status)
                ? 'bg-red-50 border-red-200'
                : 'bg-emerald-50 border-emerald-200'">
              <!-- not selected: X icon -->
              <svg v-if="appStatusIsNegative(job.my_application.status)"
                class="w-5 h-5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <!-- positive: checkmark icon -->
              <svg v-else class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="text-sm font-semibold font-display"
                :class="appStatusIsNegative(job.my_application.status) ? 'text-red-700' : 'text-emerald-700'">
                Application {{ statusLabel(job.my_application.status) }}
              </span>
            </div>
            <!-- Apply is tutor-only (the API enforces role:tutor with a 403 —
                 this keeps non-tutors from ever hitting that wall). -->
            <button v-else-if="job.status === 'open' && auth.isTutor" @click="showApplyDialog = true"
              class="btn-primary w-full py-3 text-sm">
              Apply Now
            </button>
            <div v-else-if="job.status === 'open'"
              class="rounded-md border border-paper-200 bg-paper-50 px-4 py-3 text-sm text-paper-500 font-body">
              Only tutors can apply to tuition jobs.
              <RouterLink v-if="auth.isGuardian" to="/guardian/jobs/post"
                class="mt-1 block font-display font-semibold text-navy-700 hover:underline">
                Post your own tuition job →
              </RouterLink>
            </div>
            <span v-else class="block rounded-md border border-paper-200 bg-paper-50 px-4 py-3 text-sm text-paper-500 font-body">
              This job is closed.
            </span>
          </div>
        </aside>
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
            <div class="relative rounded-sm overflow-hidden border border-paper-200">
              <iframe :src="mapEmbedUrl" width="100%" height="300" style="border:0" allowfullscreen loading="lazy"></iframe>
              <!-- The embed always centers the searched area, so a fixed overlay
                   ring around the center marks the approximate zone without
                   needing a map library or exact coordinates. -->
              <div class="pointer-events-none absolute left-1/2 top-1/2 h-36 w-36 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-red-500 bg-red-500/10"></div>
            </div>
            <p class="text-xs text-paper-400 font-body mt-3">
              Note: The exact location of this tuition job is most probably inside the circled area.
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
  </main>
  </DefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { tutorJobsApi } from '@/api/jobs.js'
import { tutorApi } from '@/api/tutor.js'
import { useAuthStore } from '@/stores/auth.js'
import { toast } from 'vue-sonner'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { mediumLabel } from '@/utils/constants.js'

const route   = useRoute()
const auth    = useAuthStore()
const publicId = route.params.publicId

const ICON_PATHS = {
  briefcase: ['M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0'],
  currency:  ['M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
  user:      ['M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
  users:     ['M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
  location:  ['M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0z', 'M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0z'],
  clock:     ['M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
  calendar:  ['M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5'],
  school:    ['M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5'],
  book:      ['M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
}

const job             = ref(null)
const loading         = ref(true)
const showMap         = ref(false)
const showApplyDialog = ref(false)
const applying        = ref(false)
const tutorGender     = ref(null)

const locationStr = computed(() => {
  if (!job.value) return ''
  const parts = [job.value.area?.name, job.value.district?.name].filter(Boolean)
  return parts.join(', ')
})

const directionsUrl = computed(() => {
  if (!job.value) return '#'
  const q = encodeURIComponent(locationStr.value)
  return `https://www.google.com/maps/dir/?api=1&destination=${q}`
})

const mapEmbedUrl = computed(() => {
  if (!job.value) return ''
  // Area + district only — exact address is never sent to tutors.
  const q = encodeURIComponent(locationStr.value)
  return `https://maps.google.com/maps?q=${q}&z=15&output=embed`
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
  const typeMap   = { student_home:"Student's Home", tutor_home:"Tutor's Home", online:'Online', home:'Home Tutoring', group:'Group Tutoring', home_and_online:'Home & Online' }
  // Shared label helper — a local map here drifted twice (missing madrasha,
  // then test_preparation), so it now reads from constants.js.
  const styleMap  = { one_to_one:'One-to-one', group:'Group', online:'Online', in_person:'In-person' }
  const genderMap = { male:'Male', female:'Female', any:'Any' }
  const rows = [
    { label: 'Place of Tutoring', value: typeMap[j.tuition_type] ?? j.tuition_type,                    icon: 'briefcase' },
    { label: 'Salary',          value: j.offered_salary ? `${j.offered_salary.toLocaleString()} BDT` : '—', icon: 'currency'  },
    { label: 'Location',        value: locationStr.value || '—',                                       icon: 'location'  },
    { label: 'Student Gender',  value: genderMap[j.student_gender] ?? j.student_gender,                icon: 'user'      },
    { label: 'Preferred Tutor', value: genderMap[j.tutor_gender_pref] ?? j.tutor_gender_pref,          icon: 'user'      },
    { label: 'No. of Students', value: j.num_students ?? '—',                                          icon: 'users'     },
  ]
  if (j.medium)                 rows.push({ label: 'Medium',            value: mediumLabel(j.medium),                           icon: 'book'     })
  if (j.tutoring_style)         rows.push({ label: 'Tutoring Style',    value: styleMap[j.tutoring_style] ?? j.tutoring_style,  icon: 'briefcase'})
  if (j.tutoring_time)          rows.push({ label: 'Tutoring Time',     value: formatTime(j.tutoring_time),                     icon: 'clock'    })
  if (j.tutoring_days_per_week) rows.push({ label: 'Tutoring Days',     value: `${j.tutoring_days_per_week} Days / Week`,       icon: 'calendar' })
  if (j.hire_date)              rows.push({ label: 'Hire Date',         value: formatDate(j.hire_date),                         icon: 'calendar' })
  if (j.student_institute)      rows.push({ label: 'Student Institute', value: j.student_institute,                             icon: 'school'   })
  return rows
})

function statusLabel(s) {
  return {
    applied:          'Submitted',
    shortlisted:      'Shortlisted',
    demo_requested:   'Demo Requested',
    appointed:        'Appointed for Demo',
    confirm_requested:'Confirm Requested',
    connected:        'Confirmed',
    not_selected:     'Not Selected',
  }[s] ?? s
}

function appStatusIsNegative(s) {
  return s === 'not_selected'
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
