<template>
  <div>
    <!-- Profile header -->
    <div class="card mb-6">
      <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
        <!-- Avatar -->
        <div class="relative shrink-0">
          <div class="w-20 h-20 rounded-xl bg-navy-100 flex items-center justify-center overflow-hidden ring-4 ring-white shadow-md">
            <img v-if="avatarUrl" :src="avatarUrl" :alt="auth.user?.name" class="w-full h-full object-cover" />
            <span v-else class="font-display font-bold text-3xl text-navy-700">{{ initials }}</span>
          </div>
          <label class="absolute bottom-0 right-0 w-7 h-7 bg-navy-700 rounded-full flex items-center justify-center cursor-pointer hover:bg-navy-800 transition-colors shadow-md">
            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
            </svg>
            <input type="file" class="hidden" accept="image/jpeg,image/png,image/webp"
              @change="uploadAvatar" :disabled="uploadingAvatar" />
          </label>
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0 text-center sm:text-left">
          <p class="font-display font-bold text-navy-900 text-lg leading-tight truncate">{{ auth.user?.name }}</p>
          <p class="text-xs text-paper-500 font-body mt-0.5 truncate">{{ auth.user?.email }}</p>
          <div class="flex items-center justify-center sm:justify-start gap-2 mt-1.5 flex-wrap">
            <span v-if="stats.tutor_id"
              class="text-xs font-semibold font-display text-navy-700 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill">
              {{ stats.tutor_id }}
            </span>
            <p v-if="uploadingAvatar" class="text-xs text-navy-500 font-body">Uploading…</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats grid -->
    <div v-if="loading" class="text-paper-500 font-body text-sm">Loading…</div>
    <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-6">
      <div class="card text-center p-4">
        <p class="font-display font-bold text-2xl sm:text-3xl text-navy-700">{{ stats.profile_completion }}%</p>
        <p class="text-xs sm:text-sm text-paper-500 font-body mt-1">Profile complete</p>
        <div class="mt-2 h-1.5 bg-paper-200 rounded-full overflow-hidden">
          <div class="h-full bg-gold-400 transition-all" :style="`width:${stats.profile_completion}%`"></div>
        </div>
      </div>
      <div class="card text-center p-4">
        <p class="font-display font-bold text-2xl sm:text-3xl text-navy-700">{{ stats.rating || '—' }}</p>
        <p class="text-xs sm:text-sm text-paper-500 font-body mt-1">Average rating</p>
      </div>
      <div class="card text-center p-4">
        <p class="font-display font-bold text-2xl sm:text-3xl text-navy-700">{{ stats.connection_requests_count }}</p>
        <p class="text-xs sm:text-sm text-paper-500 font-body mt-1">Connections</p>
      </div>
      <div class="card text-center p-4">
        <p class="font-display font-bold text-2xl sm:text-3xl text-navy-700">{{ stats.profile_views }}</p>
        <p class="text-xs sm:text-sm text-paper-500 font-body mt-1">Profile views</p>
      </div>
    </div>

    <!-- Profile status card -->
    <div v-if="!loading" class="card">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
        <h2 class="font-display font-semibold text-navy-700 text-lg">Profile status</h2>
        <span v-if="stats.verification_status" :class="verificationBadgeClass"
          class="text-xs font-semibold px-3 py-1 rounded-pill self-start sm:self-auto">
          {{ profileStatusLabel }}
        </span>
      </div>

      <!-- Pending changes notice -->
      <div v-if="stats.has_pending_changes"
        class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-4">
        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
          <p class="font-display font-semibold text-blue-800 text-sm">Profile update pending review</p>
          <p class="text-xs text-blue-700 font-body mt-0.5 leading-relaxed">
            Your recent edits have been saved and are awaiting admin approval. They'll go live once reviewed.
          </p>
        </div>
      </div>

      <div v-if="pendingDiffRows.length" class="mb-4 rounded-lg border border-paper-200 overflow-hidden">
        <div class="bg-paper-50 px-4 py-2.5">
          <p class="font-display font-semibold text-navy-700 text-sm">Pending changes</p>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-xs font-semibold font-display text-paper-400 uppercase tracking-wide border-b border-paper-100">
                <th class="py-2.5 px-4 w-1/4">Field</th>
                <th class="py-2.5 px-4 w-[37.5%]">Current value</th>
                <th class="py-2.5 px-4 w-[37.5%]">Proposed change</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-paper-100">
              <tr v-for="row in pendingDiffRows" :key="row.key">
                <td class="py-2.5 px-4 font-display font-semibold text-navy-700 text-xs align-top">{{ row.label }}</td>
                <td class="py-2.5 px-4 text-xs text-paper-500 font-body align-top leading-relaxed">{{ row.old }}</td>
                <td class="py-2.5 px-4 text-xs font-semibold text-navy-800 font-body align-top leading-relaxed">{{ row.new }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Rejection note from previous pending changes -->
      <div v-if="stats.pending_note && !stats.has_pending_changes"
        class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4">
        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
        <div>
          <p class="font-display font-semibold text-red-800 text-sm">Previous changes not approved</p>
          <p class="text-xs text-red-700 font-body mt-0.5">{{ stats.pending_note }}</p>
        </div>
      </div>

      <!-- Status description -->
      <p class="text-sm text-paper-500 font-body mb-4">
        <template v-if="stats.is_verified">
          Your profile is verified and visible to students. You can edit your profile anytime — changes will be reviewed by the admin before going live.
        </template>
        <template v-else-if="stats.verification_status === 'pending'">
          Your profile is awaiting review. Complete all steps to speed up the process.
        </template>
        <template v-else-if="stats.verification_status === 'rejected'">
          Your profile was not approved. Update your information and documents, then resubmit.
        </template>
        <template v-else>
          Your profile is being reviewed by our team.
        </template>
      </p>

      <RouterLink to="/tutor/profile" class="btn-primary text-sm py-2.5 px-5 inline-block">
        Edit profile
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { tutorApi } from '@/api/tutor.js'
import { useAuthStore } from '@/stores/auth.js'
import { getInitials } from '@/utils/helpers.js'
import { toast } from 'vue-sonner'

const auth = useAuthStore()

const stats           = ref({})
const loading         = ref(true)
const uploadingAvatar = ref(false)

const initials  = computed(() => getInitials(auth.user?.name))
const avatarUrl = computed(() => auth.user?.avatar_url || null)

const verificationBadgeClass = computed(() => {
  if (stats.value.has_pending_changes) return 'bg-blue-50 text-blue-700 border border-blue-200'

  const s = stats.value.verification_status
  if (s === 'approved')     return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
  if (s === 'pending')      return 'bg-amber-50 text-amber-700 border border-amber-200'
  if (s === 'rejected')     return 'bg-red-50 text-red-700 border border-red-200'
  if (s === 'under_review') return 'bg-blue-50 text-blue-700 border border-blue-200'
  return 'bg-paper-100 text-paper-500 border border-paper-200'
})

const pendingDiffRows = computed(() => buildPendingDiff(stats.value))

const profileStatusLabel = computed(() => {
  if (stats.value.has_pending_changes) return 'Profile changes pending review'

  const status = stats.value.verification_status
  if (status === 'approved') return 'Profile approved'
  if (status === 'pending') return 'Profile pending review'
  if (status === 'rejected') return 'Profile rejected'
  if (status === 'under_review') return 'Profile under review'
  if (status === 'not_started') return 'Profile not started'
  return `Profile ${String(status || '').replace(/_/g, ' ')}`
})

onMounted(async () => {
  try {
    const { data } = await tutorApi.getDashboard()
    stats.value = data.data
  } finally {
    loading.value = false
  }
})

async function uploadAvatar(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (!file) return
  uploadingAvatar.value = true
  try {
    const fd = new FormData()
    fd.append('avatar', file)
    await auth.uploadAvatar(fd)
    toast.success('Profile picture updated!')
  } catch {
    toast.error('Upload failed. Max 2 MB, JPG/PNG/WebP.')
  } finally {
    uploadingAvatar.value = false
  }
}

const PREF_FIELDS = [
  { key: '_subject_names',         label: 'Subjects' },
  { key: '_district_name',         label: 'Preferred district' },
  { key: '_location_names',        label: 'Preferred areas' },
  { key: 'expected_salary_min',    label: 'Min salary (BDT)' },
  { key: 'expected_salary_max',    label: 'Max salary (BDT)' },
  { key: 'total_experience_years', label: 'Experience (years)' },
  { key: 'days_per_week',          label: 'Days per week' },
  { key: 'hours_per_day',          label: 'Hours per day' },
  { key: 'preferred_classes',      label: 'Preferred classes' },
  { key: 'tutoring_methods',       label: 'Tutoring methods' },
  { key: 'place_of_tutoring',      label: 'Place of tutoring' },
  { key: 'preferred_time',         label: 'Preferred time' },
]

const PERSONAL_FIELDS = [
  { key: 'gender',            label: 'Gender' },
  { key: 'date_of_birth',     label: 'Date of birth' },
  { key: 'religion',          label: 'Religion' },
  { key: 'nationality',       label: 'Nationality' },
  { key: 'present_address',   label: 'Present address' },
  { key: 'permanent_address', label: 'Permanent address' },
  { key: 'additional_phone',  label: 'Additional phone' },
  { key: 'national_id',       label: 'National ID' },
  { key: 'facebook_url',      label: 'Facebook' },
  { key: 'linkedin_url',      label: 'LinkedIn' },
  { key: 'fathers_name',      label: "Father's name" },
  { key: 'fathers_phone',     label: "Father's phone" },
  { key: 'mothers_name',      label: "Mother's name" },
  { key: 'mothers_phone',     label: "Mother's phone" },
]

const EMERGENCY_FIELDS = [
  { key: 'name',     label: 'Emergency contact name' },
  { key: 'relation', label: 'Emergency contact relation' },
  { key: 'phone',    label: 'Emergency contact phone' },
  { key: 'address',  label: 'Emergency contact address' },
]

function buildPendingDiff(data) {
  const pending = data.pending_changes || {}
  const live = data.live_profile || {}
  const rows = []

  for (const { key, label } of [{ key: 'bio', label: 'Bio' }, { key: 'status', label: 'Status' }]) {
    if (pending[key] === undefined) continue
    if (norm(live[key]) === norm(pending[key])) continue
    rows.push({ key, label, old: display(live[key]), new: display(pending[key]) })
  }

  if (pending.preferences) {
    const livePrefs = live.preferences || {}
    for (const { key, label } of PREF_FIELDS) {
      if (key === '_subject_names') {
        const liveValue = (livePrefs.subjects || []).map(subject => subject.name)
        const pendingValue = pending.preferences._subject_names || []
        if (norm(liveValue) !== norm(pendingValue)) {
          rows.push({ key: `preferences.${key}`, label, old: display(liveValue), new: display(pendingValue) })
        }
        continue
      }
      if (key === '_location_names') {
        const liveValue = (livePrefs.locations || []).map(location => location.area?.name).filter(Boolean)
        const pendingValue = pending.preferences._location_names || []
        if (norm(liveValue) !== norm(pendingValue)) {
          rows.push({ key: `preferences.${key}`, label, old: display(liveValue), new: display(pendingValue) })
        }
        continue
      }
      if (key === '_district_name') {
        const liveValue = livePrefs.district?.name ?? null
        const pendingValue = pending.preferences._district_name ?? null
        if (norm(liveValue) !== norm(pendingValue)) {
          rows.push({ key: `preferences.${key}`, label, old: display(liveValue), new: display(pendingValue) })
        }
        continue
      }
      if (!(key in pending.preferences)) continue
      const liveValue = livePrefs[key]
      const pendingValue = pending.preferences[key]
      if (norm(liveValue) === norm(pendingValue)) continue
      rows.push({ key: `preferences.${key}`, label, old: display(liveValue), new: display(pendingValue) })
    }
  }

  addFieldRows(rows, 'personal_info', PERSONAL_FIELDS, pending.personal_info, live.personal_info)
  addFieldRows(rows, 'emergency_contact', EMERGENCY_FIELDS, pending.emergency_contact, live.emergency_contact)

  return rows
}

function addFieldRows(rows, prefix, fields, pendingValues, liveValues = {}) {
  if (!pendingValues) return
  for (const { key, label } of fields) {
    if (!(key in pendingValues)) continue
    const liveValue = liveValues?.[key]
    const pendingValue = pendingValues[key]
    if (norm(liveValue) === norm(pendingValue)) continue
    rows.push({ key: `${prefix}.${key}`, label, old: display(liveValue), new: display(pendingValue) })
  }
}

function display(val) {
  if (val === null || val === undefined || val === '') return '—'
  if (Array.isArray(val)) {
    if (!val.length) return '—'
    const first = val[0]
    if (first !== null && typeof first === 'object') {
      if ('name' in first) return val.map(item => item.name).join(', ')
      if ('day' in first) return val.map(item => item.day).join(', ')
      if ('area_name' in first) return val.map(item => item.area_name).join(', ')
      return '—'
    }
    return val.join(', ')
  }
  return String(val)
}

function norm(val) {
  if (val === null || val === undefined || val === '') return '\0empty'
  if (Array.isArray(val)) {
    if (!val.length) return '\0empty'
    const first = val[0]
    if (first !== null && typeof first === 'object') {
      if ('name' in first) return [...val].map(item => item.name).sort().join('|')
      if ('day' in first) return [...val].map(item => item.day).sort().join('|')
      if ('area_name' in first) return [...val].map(item => item.area_name).sort().join('|')
      return '\0empty'
    }
    return [...val].sort().join('|')
  }
  return String(val)
}
</script>
