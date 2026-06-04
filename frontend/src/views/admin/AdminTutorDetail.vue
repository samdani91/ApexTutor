<template>
  <div>
    <!-- Back -->
    <RouterLink to="/admin/users" class="inline-flex items-center gap-1.5 text-sm font-semibold font-display text-navy-700 hover:text-navy-900 mb-5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
      </svg>
      Back to users
    </RouterLink>

    <div v-if="loading" class="text-paper-500 font-body py-12 text-center">Loading…</div>

    <template v-else-if="tutor">
      <div class="card mb-5">
        <div class="grid gap-4 lg:grid-cols-[auto_minmax(0,1fr)_220px_auto] lg:items-start">
          <!-- Avatar -->
          <div class="w-20 h-20 rounded-xl bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden ring-2 ring-white shadow mx-auto sm:mx-0">
            <img v-if="tutor.user?.avatar_url" :src="tutor.user.avatar_url" class="w-full h-full object-cover" />
            <span v-else class="font-display font-bold text-2xl text-navy-700">{{ initials }}</span>
          </div>
          <!-- Basic info -->
          <div class="min-w-0 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
              <h1 class="font-display font-bold text-xl text-navy-900 break-words">{{ tutor.user?.name }}</h1>
              <span v-if="tutor.tutor_id"
                class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill">
                {{ tutor.tutor_id }}
              </span>
              <span v-if="tutor.is_verified" class="badge-verified text-xs">✓ Verified</span>
              <span v-if="tutor.is_verified"
                class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-pill bg-amber-50 text-amber-700 border border-amber-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
                Locked
              </span>
            </div>
            <p class="text-sm text-paper-500 font-body break-all">{{ tutor.user?.email }}</p>
            <p class="text-sm text-paper-500 font-body">{{ tutor.user?.phone }}</p>
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-3">
              <span class="status-chip capitalize"
                :class="verificationClass(tutor.verification_status)">
                {{ tutor.verification_status }}
              </span>
              <span class="status-chip capitalize"
                :class="tutor.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                {{ tutor.status }}
              </span>
            </div>
          </div>
          <div class="rounded-lg border border-paper-200 bg-paper-50 px-4 py-3 w-full">
            <div class="flex items-center justify-between gap-3 mb-2">
              <div>
                <p class="text-xs font-display font-semibold text-paper-500 uppercase tracking-wide">Profile completion</p>
                <p class="text-xs text-paper-400 font-body mt-0.5">Verification readiness</p>
              </div>
              <p class="text-lg font-display font-bold text-navy-800">{{ tutor.profile_completion_percent }}%</p>
            </div>
            <div class="h-2 rounded-full bg-paper-200 overflow-hidden">
              <div class="h-full bg-gold-400" :style="`width:${tutor.profile_completion_percent}%`"></div>
            </div>
          </div>
          <!-- Admin actions -->
          <div class="flex flex-col gap-2 w-full lg:w-36">
            <RouterLink :to="{ name: 'admin-tutor-edit', params: { id: tutor.id } }"
              class="text-sm font-semibold font-display px-4 py-1.5 rounded-md bg-navy-700 text-white hover:bg-navy-900 transition-colors text-center w-full">
              Edit Profile
            </RouterLink>
            <template v-if="tutor.verification_status === 'pending'">
              <button @click="openApprove" :disabled="acting"
                class="btn-primary text-sm py-1.5 px-4 w-full">Approve</button>
              <button @click="openReject"
                class="bg-red-600 text-white text-sm font-semibold font-display py-1.5 px-4 rounded-md hover:bg-red-700 transition-colors w-full">
                Reject
              </button>
            </template>
            <DropSelect :model-value="statusValue" :options="accountStatusOptions" placeholder="Active"
              @update:modelValue="onStatusChange" />
          </div>
        </div>

        <!-- Rejection reason -->
        <div v-if="tutor.rejection_reason" class="mt-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 font-body">
          <span class="font-semibold font-display">Rejection reason:</span> {{ tutor.rejection_reason }}
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-5">

        <div class="card lg:col-span-2">
          <h2 class="section-title">Personal information</h2>
          <div v-if="tutor.bio" class="rounded-lg bg-paper-50 border border-paper-100 p-3 mb-4">
            <p class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1">About</p>
            <p class="text-sm text-navy-800 font-body leading-relaxed">{{ tutor.bio }}</p>
          </div>
          <template v-if="tutor.personal_info">
            <div class="responsive-info-grid">
              <div v-for="row in personalRows" :key="row.label" class="info-row">
                <span class="info-label">{{ row.label }}</span>
                <span class="info-value" :class="row.capitalize ? 'capitalize' : ''">{{ row.value }}</span>
              </div>
            </div>
            <div v-if="tutor.personal_info.facebook_url || tutor.personal_info.linkedin_url" class="mt-4 flex flex-wrap gap-2">
              <a v-if="tutor.personal_info.facebook_url" :href="tutor.personal_info.facebook_url" target="_blank" class="link-pill">Facebook</a>
              <a v-if="tutor.personal_info.linkedin_url" :href="tutor.personal_info.linkedin_url" target="_blank" class="link-pill">LinkedIn</a>
            </div>
          </template>
          <p v-else class="empty-state">No personal info submitted.</p>
        </div>

        <div class="card lg:col-span-2">
          <h2 class="section-title">Guardian information</h2>
          <template v-if="tutor.personal_info">
            <div class="responsive-info-grid">
              <div v-for="row in guardianRows" :key="row.label" class="info-row">
                <span class="info-label">{{ row.label }}</span>
                <span class="info-value">{{ row.value }}</span>
              </div>
            </div>
          </template>
          <p v-else class="empty-state">No guardian info submitted.</p>
        </div>

        <div class="card lg:col-span-2">
          <h2 class="section-title">Emergency contact</h2>
          <template v-if="tutor.emergency_contact">
            <div class="responsive-info-grid">
              <div v-for="row in emergencyRows" :key="row.label" class="info-row">
                <span class="info-label">{{ row.label }}</span>
                <span class="info-value" :class="row.capitalize ? 'capitalize' : ''">{{ row.value }}</span>
              </div>
            </div>
          </template>
          <p v-else class="empty-state">No emergency contact submitted.</p>
        </div>

        <div class="card lg:col-span-2">
          <h2 class="section-title">Tuition preferences</h2>
          <template v-if="tutor.tuition_preference">
            <div class="responsive-info-grid mb-4">
              <div v-for="row in preferenceRows" :key="row.label" class="info-row">
                <span class="info-label">{{ row.label }}</span>
                <span class="info-value">{{ row.value }}</span>
              </div>
            </div>

            <div v-if="tutor.tuition_preference.days?.length" class="mt-3">
              <p class="chip-label">Available days</p>
              <div class="chip-list"><span v-for="d in tutor.tuition_preference.days" :key="d.day" class="chip uppercase">{{ d.day }}</span></div>
            </div>

            <div v-if="tutor.tuition_preference.subjects?.length" class="mt-3">
              <p class="chip-label">Subjects</p>
              <div class="chip-list"><span v-for="s in tutor.tuition_preference.subjects" :key="s.id" class="chip">{{ s.name }}</span></div>
            </div>

            <div v-if="tutor.tuition_preference.locations?.length" class="mt-3">
              <p class="chip-label">Preferred locations</p>
              <div class="chip-list"><span v-for="loc in tutor.tuition_preference.locations" :key="loc.id" class="chip">{{ loc.area_name || loc.area?.name }}</span></div>
            </div>

            <div v-if="tutor.tuition_preference.preferred_curricula?.length" class="mt-3">
              <p class="chip-label">Curriculum</p>
              <div class="chip-list"><span v-for="c in tutor.tuition_preference.preferred_curricula" :key="c" class="chip capitalize">{{ c.replace(/_/g,' ') }}</span></div>
            </div>

            <div v-if="tutor.tuition_preference.preferred_classes?.length" class="mt-3">
              <p class="chip-label">Preferred classes</p>
              <div class="chip-list"><span v-for="cls in tutor.tuition_preference.preferred_classes" :key="cls" class="chip">{{ titleize(cls) }}</span></div>
            </div>

            <div v-if="tutor.tuition_preference.tutoring_method_description" class="mt-3">
              <p class="chip-label">Tutoring method</p>
              <p class="note-box">{{ tutor.tuition_preference.tutoring_method_description }}</p>
            </div>

            <div v-if="tutor.tuition_preference.experience_details" class="mt-3">
              <p class="chip-label">Experience details</p>
              <p class="note-box">{{ tutor.tuition_preference.experience_details }}</p>
            </div>
          </template>
          <p v-else class="empty-state">No preferences saved.</p>
        </div>

        <div class="card">
          <h2 class="section-title">Education</h2>
          <div v-if="tutor.education_entries?.length" class="space-y-2">
            <div v-for="edu in tutor.education_entries" :key="edu.id"
              class="rounded-lg border border-paper-200 bg-paper-50 px-3 py-2.5">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="font-display font-semibold text-navy-900 text-sm">{{ edu.degree_title }}</p>
                  <p class="text-xs text-paper-500 font-body mt-0.5">{{ edu.institute_name }}</p>
                </div>
                <span v-if="edu.year_of_passing" class="status-chip bg-white text-paper-500 border border-paper-200">{{ edu.year_of_passing }}</span>
              </div>
              <p v-if="edu.major_group || edu.result" class="text-xs text-paper-500 font-body mt-2">
                {{ [edu.major_group, edu.result].filter(Boolean).join(' · ') }}
              </p>
            </div>
          </div>
          <p v-else class="empty-state">No education entries.</p>
        </div>

        <div class="card">
          <h2 class="section-title">Documents</h2>
          <div v-if="tutor.documents?.length" class="space-y-2">
            <div v-for="doc in tutor.documents" :key="doc.id"
              class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 rounded-lg border border-paper-200 bg-paper-50">
              <div class="min-w-0">
                <p class="text-xs font-semibold font-display text-navy-800 capitalize">
                  {{ doc.type.replace(/_/g, ' ') }}
                </p>
                <p class="text-xs text-paper-400 font-body mt-0.5">{{ doc.file_name || 'Uploaded document' }} · {{ formatSize(doc.file_size) }}</p>
              </div>
              <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <span v-if="doc.review_status === 'rejected'"
                  class="status-chip bg-red-50 text-red-700">
                  Rejected
                </span>
                <a :href="doc.file_url" target="_blank"
                  class="text-xs font-semibold font-display text-navy-700 hover:text-navy-900 underline underline-offset-2">
                  View
                </a>
              </div>
            </div>
          </div>
          <p v-else class="empty-state">No documents uploaded.</p>
        </div>

        <div class="card lg:col-span-2">
          <h2 class="section-title">
            Teaching videos
            <span v-if="tutor.teaching_videos?.length" class="text-paper-400 font-body font-normal">({{ tutor.teaching_videos.length }})</span>
            <span v-if="pendingVideoCount" class="ml-2 text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-pill">
              {{ pendingVideoCount }} pending
            </span>
          </h2>
          <div v-if="tutor.teaching_videos?.length" class="grid md:grid-cols-2 gap-4">
            <div v-for="vid in tutor.teaching_videos" :key="vid.id"
              class="rounded-lg border bg-paper-50 p-3"
              :class="vid.review_status === 'pending' ? 'border-amber-300' : 'border-paper-200'">
              <video :src="vid.file_url" controls
                class="w-full rounded-lg bg-black max-h-60" preload="metadata" />
              <div class="mt-2">
                <p v-if="vid.title" class="font-display font-semibold text-sm text-navy-900 mb-1.5">{{ vid.title }}</p>
                <div class="flex items-center flex-wrap gap-2 mb-2">
                  <span v-if="vid.subject" class="chip">{{ vid.subject }}</span>
                  <span v-if="vid.class_level" class="chip">Class {{ vid.class_level }}</span>
                  <span v-if="vid.medium" class="chip capitalize">{{ vid.medium.replace('_', ' & ') }}</span>
                  <span class="status-chip capitalize"
                    :class="vid.review_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : vid.review_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'">
                    {{ vid.review_status }}
                  </span>
                  <span class="text-xs text-paper-400 font-body">{{ formatSize(vid.file_size) }}</span>
                </div>
                <p v-if="vid.review_note" class="text-xs text-paper-500 font-body italic mb-2">Note: {{ vid.review_note }}</p>
                <div v-if="vid.review_status !== 'approved'" class="flex gap-2 pt-1 border-t border-paper-100">
                  <button @click="openApproveVideoDialog(vid)" :disabled="videoActing === vid.id"
                    class="flex-1 text-xs font-semibold font-display py-1.5 rounded-sm bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                    Approve
                  </button>
                  <button v-if="vid.review_status !== 'rejected'" @click="openRejectVideoDialog(vid)" :disabled="videoActing === vid.id"
                    class="flex-1 text-xs font-semibold font-display py-1.5 rounded-sm bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 transition-colors">
                    Reject
                  </button>
                </div>
              </div>
            </div>
          </div>
          <p v-else class="empty-state">No teaching videos uploaded.</p>
        </div>

        <!-- Video approve dialog -->
        <AdminConfirmDialog
          :show="showApproveVideoDialog"
          title="Approve Video?"
          :message="pendingApproveVideo ? `Approve &quot;${pendingApproveVideo.title}&quot;? It will appear on the tutor's public profile.` : ''"
          confirm-label="Yes, Approve"
          @confirm="confirmApproveVideo"
          @cancel="showApproveVideoDialog = false"
        />

        <!-- Video reject dialog -->
        <AdminConfirmDialog
          :show="showRejectVideoDialog"
          title="Reject Video"
          message="The tutor will see this reason on their profile."
          confirm-label="Reject Video"
          :danger="true"
          :with-input="true"
          :input-required="false"
          input-label="Rejection reason"
          input-placeholder="e.g. Poor audio quality, unrelated content…"
          @confirm="confirmRejectVideo"
          @cancel="showRejectVideoDialog = false"
        />

        <div class="card">
          <h2 class="section-title">Connection requests <span class="text-paper-400 font-body font-normal">({{ tutor.connection_requests?.length || 0 }})</span></h2>
          <div v-if="tutor.connection_requests?.length" class="space-y-2">
            <div v-for="conn in tutor.connection_requests" :key="conn.id" class="rounded-lg border border-paper-200 bg-paper-50 p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-display font-semibold text-navy-900">{{ conn.guardian_profile?.user?.name }}</p>
                <p class="text-xs text-paper-400 font-body mt-0.5 break-all">{{ conn.guardian_profile?.user?.email }}</p>
              </div>
              <span class="status-chip capitalize"
                :class="connStatusClass(conn.status)">{{ conn.status.replace(/_/g,' ') }}</span>
            </div>
          </div>
          <p v-else class="empty-state">No connection requests.</p>
        </div>

        <div class="card">
          <h2 class="section-title">Reviews <span class="text-paper-400 font-body font-normal">({{ tutor.reviews?.length || 0 }})</span></h2>
          <div v-if="tutor.reviews?.length" class="space-y-2">
            <div v-for="rev in tutor.reviews" :key="rev.id" class="rounded-lg border border-paper-200 bg-paper-50 p-3">
              <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-display font-semibold text-navy-900">{{ rev.guardian_profile?.user?.name }}</p>
                <span class="status-chip bg-gold-50 text-gold-700">★ {{ rev.rating }}</span>
              </div>
              <p class="text-xs text-paper-600 font-body leading-relaxed">{{ rev.review_text }}</p>
            </div>
          </div>
          <p v-else class="empty-state">No reviews yet.</p>
        </div>

      </div>
    </template>

    <!-- Approve confirm -->
    <AdminConfirmDialog
      :show="showApproveDialog"
      title="Approve Tutor?"
      :message="tutor ? `Approve ${tutor.user?.name} and mark their profile as verified?` : ''"
      confirm-label="Yes, Approve"
      @confirm="confirmApprove"
      @cancel="showApproveDialog = false"
    />

    <!-- Reject confirm -->
    <AdminConfirmDialog
      :show="showRejectDialog"
      title="Reject Tutor?"
      :message="tutor ? `Reject ${tutor.user?.name}'s verification request.` : ''"
      confirm-label="Reject"
      danger
      with-input
      input-label="Rejection reason"
      input-placeholder="Explain why this profile is being rejected…"
      @confirm="confirmReject"
      @cancel="showRejectDialog = false"
    />

    <!-- Status change confirm -->
    <AdminConfirmDialog
      :show="!!pendingStatus"
      title="Update Account Status?"
      :message="pendingStatus ? `Change ${tutor?.user?.name}'s status to &quot;${pendingStatus}&quot;?` : ''"
      confirm-label="Yes, Update"
      @confirm="confirmStatusChange"
      @cancel="cancelStatusChange"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import { getInitials } from '@/utils/helpers.js'
import { PREFERRED_TIMES } from '@/utils/constants.js'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'

const TIME_MAP = Object.fromEntries(PREFERRED_TIMES.map(t => [t.value, `${t.label} (${t.hint})`]))

const route  = useRoute()
const tutor  = ref(null)
const loading = ref(true)
const acting  = ref(false)
const statusValue      = ref('active')
const showApproveDialog = ref(false)
const showRejectDialog  = ref(false)
const pendingStatus     = ref(null)

const videoActing           = ref(null)
const showApproveVideoDialog = ref(false)
const pendingApproveVideo    = ref(null)
const showRejectVideoDialog  = ref(false)
const pendingRejectVideo     = ref(null)

const pendingVideoCount = computed(() =>
  tutor.value?.teaching_videos?.filter(v => v.review_status === 'pending').length ?? 0
)
const accountStatusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'suspended', label: 'Suspended' },
]

const initials = computed(() => getInitials(tutor.value?.user?.name))

const personalRows = computed(() => {
  const info = tutor.value?.personal_info
  if (!info) return []
  return [
    { label: 'Gender', value: info.gender || 'Not given', capitalize: true },
    { label: 'Date of birth', value: formatDate(info.date_of_birth) || 'Not given' },
    { label: 'Religion', value: info.religion || 'Not given', capitalize: true },
    { label: 'Nationality', value: info.nationality || 'Not given' },
    { label: 'Additional phone', value: info.additional_phone || 'Not given' },
    { label: 'National ID', value: info.national_id || 'Not given' },
    { label: 'Present address', value: info.present_address || 'Not given' },
    { label: 'Permanent address', value: info.permanent_address || 'Not given' },
  ]
})

const guardianRows = computed(() => {
  const info = tutor.value?.personal_info
  if (!info) return []
  return [
    { label: "Father's name", value: info.fathers_name || 'Not given' },
    { label: "Father's phone", value: info.fathers_phone || 'Not given' },
    { label: "Mother's name", value: info.mothers_name || 'Not given' },
    { label: "Mother's phone", value: info.mothers_phone || 'Not given' },
  ]
})

const emergencyRows = computed(() => {
  const contact = tutor.value?.emergency_contact
  if (!contact) return []
  return [
    { label: 'Name', value: contact.name || 'Not given' },
    { label: 'Relation', value: contact.relation || 'Not given', capitalize: true },
    { label: 'Phone', value: contact.phone || 'Not given' },
    { label: 'Address', value: contact.address || 'Not given' },
  ]
})

const preferenceRows = computed(() => {
  const pref = tutor.value?.tuition_preference
  if (!pref) return []
  return [
    { label: 'Experience', value: pref.total_experience_years != null ? `${pref.total_experience_years >= 21 ? '20+' : pref.total_experience_years} year${pref.total_experience_years === 1 ? '' : 's'}` : 'Not given' },
    { label: 'Days/week', value: pref.days_per_week || 'Not given' },
    { label: 'Hours/session', value: pref.hours_per_day ? `${pref.hours_per_day} hr` : 'Not given' },
    { label: 'Preferred time', value: pref.preferred_time?.length ? pref.preferred_time.map(t => TIME_MAP[t] || t).join(', ') : 'Not given' },
    { label: 'Style', value: formatLabels(pref.tutoring_styles) || 'Not given' },
    { label: 'Place', value: formatLabels(pref.place_of_tutoring) || 'Not given' },
    { label: 'Salary range', value: `৳${pref.expected_salary_min || 0} – ৳${pref.expected_salary_max || 0}` },
  ]
})

onMounted(async () => {
  try {
    const { data } = await adminApi.getTutor(route.params.id)
    tutor.value = data.data
    statusValue.value = tutor.value.status || 'active'
  } finally {
    loading.value = false
  }
})

function verificationClass(status) {
  if (status === 'approved') return 'bg-emerald-50 text-emerald-700'
  if (status === 'pending')  return 'bg-amber-50 text-amber-700'
  if (status === 'rejected') return 'bg-red-50 text-red-700'
  return 'bg-blue-50 text-blue-700'
}

function connStatusClass(status) {
  if (status === 'confirmed') return 'bg-emerald-50 text-emerald-700'
  if (status === 'pending')   return 'bg-amber-50 text-amber-700'
  if (status === 'declined')  return 'bg-red-50 text-red-700'
  return 'bg-blue-50 text-blue-700'
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatSize(bytes) {
  if (!bytes) return ''
  return bytes < 1048576 ? `${(bytes / 1024).toFixed(1)} KB` : `${(bytes / 1048576).toFixed(1)} MB`
}

function formatArray(arr) {
  if (!arr?.length) return null
  return Array.isArray(arr) ? arr.join(', ') : arr
}

function formatLabels(arr) {
  if (!arr) return ''
  const items = Array.isArray(arr) ? arr : [arr]
  return items.map(v => titleize(v)).join(', ')
}

function titleize(value) {
  return String(value || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

function openApprove() { showApproveDialog.value = true }
function openReject()  { showRejectDialog.value  = true }

async function confirmApprove() {
  showApproveDialog.value = false
  acting.value = true
  try {
    await adminApi.approveTutor(tutor.value.id)
    tutor.value.verification_status = 'approved'
    tutor.value.is_verified = true
    toast.success('Tutor approved and verified.')
  } catch {
    toast.error('Failed to approve.')
  } finally {
    acting.value = false
  }
}

async function confirmReject(reason) {
  showRejectDialog.value = false
  acting.value = true
  try {
    await adminApi.rejectTutor(tutor.value.id, { rejection_reason: reason })
    tutor.value.verification_status = 'rejected'
    tutor.value.rejection_reason = reason
    toast.success('Tutor rejected.')
  } catch {
    toast.error('Failed to reject.')
  } finally {
    acting.value = false
  }
}

function onStatusChange(value) {
  pendingStatus.value = value
}

async function confirmStatusChange() {
  const newStatus = pendingStatus.value
  pendingStatus.value = null
  try {
    await adminApi.updateTutorStatus(tutor.value.id, { status: newStatus })
    statusValue.value = newStatus
    tutor.value.status = newStatus
    toast.success('Status updated.')
  } catch {
    toast.error('Failed to update status.')
  }
}

function cancelStatusChange() {
  pendingStatus.value = null
}

function openApproveVideoDialog(vid) {
  pendingApproveVideo.value = vid
  showApproveVideoDialog.value = true
}

async function confirmApproveVideo() {
  showApproveVideoDialog.value = false
  const vid = pendingApproveVideo.value
  pendingApproveVideo.value = null
  if (!vid) return
  await reviewVideo(vid, 'approve')
}

async function reviewVideo(vid, action) {
  videoActing.value = vid.id
  try {
    const { data } = await adminApi.reviewTutorVideo(tutor.value.id, vid.id, { action })
    const idx = tutor.value.teaching_videos.findIndex(v => v.id === vid.id)
    if (idx !== -1) tutor.value.teaching_videos[idx] = { ...tutor.value.teaching_videos[idx], ...data.data }
    toast.success(action === 'approve' ? 'Video approved.' : 'Video rejected.')
  } catch {
    toast.error('Failed to update video status.')
  } finally {
    videoActing.value = null
  }
}

function openRejectVideoDialog(vid) {
  pendingRejectVideo.value = vid
  showRejectVideoDialog.value = true
}

async function confirmRejectVideo(reason) {
  showRejectVideoDialog.value = false
  const vid = pendingRejectVideo.value
  pendingRejectVideo.value = null
  if (!vid) return
  videoActing.value = vid.id
  try {
    const { data } = await adminApi.reviewTutorVideo(tutor.value.id, vid.id, { action: 'reject', review_note: reason || null })
    const idx = tutor.value.teaching_videos.findIndex(v => v.id === vid.id)
    if (idx !== -1) tutor.value.teaching_videos[idx] = { ...tutor.value.teaching_videos[idx], ...data.data }
    toast.success('Video rejected.')
  } catch {
    toast.error('Failed to reject video.')
  } finally {
    videoActing.value = null
  }
}
</script>

<style scoped>
.section-title {
  @apply font-display font-semibold text-navy-700 text-base mb-4;
}

.status-chip {
  @apply text-xs font-semibold px-2 py-0.5 rounded-pill font-display whitespace-nowrap;
}

.info-grid {
  @apply grid gap-2;
}

.responsive-info-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2;
}

.info-row {
  @apply rounded-lg border border-paper-200 bg-paper-50 px-3 py-2.5;
}

.info-label {
  @apply block text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1;
}

.info-value {
  @apply block text-sm text-navy-800 font-body leading-relaxed break-words;
}

.chip-label {
  @apply text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1.5;
}

.chip-list {
  @apply flex flex-wrap gap-1.5;
}

.chip {
  @apply text-xs bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill font-semibold font-display;
}

.link-pill {
  @apply text-xs font-semibold font-display text-navy-700 bg-navy-50 border border-navy-200 px-2.5 py-1 rounded-pill hover:bg-navy-100 transition-colors;
}

.note-box {
  @apply rounded-lg bg-paper-50 border border-paper-200 p-3 text-sm font-body text-navy-800 leading-relaxed;
}

.empty-state {
  @apply text-paper-400 text-xs font-body italic rounded-lg border border-dashed border-paper-200 bg-paper-50 px-3 py-3;
}
</style>
