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

    <!-- Verification / lock card -->
    <div v-if="!loading" class="card">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
        <h2 class="font-display font-semibold text-navy-700 text-lg">Verification status</h2>
        <div class="flex items-center gap-2 flex-wrap">
          <span v-if="stats.is_verified"
            class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-pill bg-amber-50 text-amber-700 border border-amber-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
            </svg>
            Profile locked
          </span>
          <span v-if="stats.verification_status" :class="verificationBadgeClass" class="text-xs font-semibold px-3 py-1 rounded-pill capitalize">
            {{ stats.verification_status === 'under_review' ? 'Under review' : stats.verification_status }}
          </span>
        </div>
      </div>

      <!-- ── State: Profile verified & locked ── -->
      <template v-if="stats.is_verified">
        <p class="text-sm text-paper-500 font-body mb-4">
          Your profile is verified and visible to students. All sections are locked — submit a change request below to make edits.
        </p>

        <!-- Existing pending/rejected request card -->
        <div v-if="changeRequest && ['pending','rejected'].includes(changeRequest.status)"
          class="rounded-lg border p-3 mb-4 text-sm font-body"
          :class="{
            'border-amber-200 bg-amber-50 text-amber-800': changeRequest.status === 'pending',
            'border-red-200 bg-red-50 text-red-700':       changeRequest.status === 'rejected',
          }">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="font-semibold font-display mb-0.5">
                Change request: <span class="capitalize">{{ changeRequest.status }}</span>
              </p>
              <p class="text-xs opacity-80">{{ changeRequest.reason }}</p>
              <p v-if="changeRequest.admin_note" class="text-xs mt-1 italic opacity-70">
                Admin note: {{ changeRequest.admin_note }}
              </p>
            </div>
            <button v-if="changeRequest.status === 'pending'"
              @click="showCancelDialog = true"
              class="shrink-0 text-xs font-semibold font-display bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg transition-colors">
              Cancel request
            </button>
          </div>
        </div>

        <!-- Submit form: show when there is no actively-pending request -->
        <div v-if="!changeRequest || changeRequest.status !== 'pending'">
          <p class="text-xs text-paper-400 font-body mb-1.5">Describe what you need to change and why:</p>
          <textarea v-model="changeReason"
            placeholder="e.g. I need to update my education details and add new subjects…"
            rows="3"
            class="input text-sm w-full mb-3 resize-none"
          />
          <button @click="submitChangeRequest"
            :disabled="submittingRequest"
            class="btn-primary text-sm py-2.5 px-5 w-full sm:w-auto">
            {{ submittingRequest ? 'Submitting…' : 'Request profile unlock' }}
          </button>
        </div>
      </template>

      <!-- ── State: Unlocked — tutor can edit ── -->
      <template v-else-if="changeRequest?.status === 'approved'">
        <p class="text-sm text-paper-500 font-body mb-4">
          Your profile has been unlocked. Make your changes and click <strong class="text-navy-700">Done editing</strong> when you're finished so we can re-review and re-verify your profile.
        </p>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 mb-4 text-sm font-body text-emerald-800">
          <p class="font-semibold font-display mb-0.5">Profile unlocked for editing</p>
          <p class="text-xs opacity-80">{{ changeRequest.reason }}</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
          <RouterLink to="/tutor/profile"
            class="btn-primary text-sm py-2.5 px-5 text-center">
            Edit profile
          </RouterLink>
          <button @click="showDoneDialog = true"
            class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold font-display py-2.5 px-5 rounded-md transition-colors">
            Done editing
          </button>
        </div>
      </template>

      <!-- ── State: Submitted for re-review ── -->
      <template v-else-if="changeRequest?.status === 'review_pending'">
        <p class="text-sm text-paper-500 font-body mb-4">
          Your updated profile has been submitted for review. You'll be re-verified once the admin approves.
        </p>
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm font-body text-blue-800">
          <p class="font-semibold font-display mb-0.5">Under re-review</p>
          <p class="text-xs opacity-80">Waiting for admin to review your updated profile.</p>
        </div>
      </template>

      <!-- ── State: Not verified, no active change request ── -->
      <template v-else>
        <p class="text-sm text-paper-500 font-body mb-4">
          <template v-if="stats.verification_status === 'pending'">Your profile is awaiting review. Complete all steps to speed up the process.</template>
          <template v-else-if="stats.verification_status === 'rejected'">Your profile was not approved. Update your information and documents.</template>
          <template v-else>Your profile is being reviewed by our team.</template>
        </p>
        <RouterLink to="/tutor/profile"
          class="btn-outline block sm:inline-block text-sm py-2 px-4 text-center sm:text-left">
          Edit profile
        </RouterLink>
      </template>
    </div>

    <!-- Cancel change request confirm -->
    <ConfirmDialog
      :show="showCancelDialog"
      title="Cancel change request?"
      message="Are you sure you want to cancel your pending profile unlock request? You can submit a new one anytime."
      confirm-label="Yes, cancel it"
      danger
      @confirm="confirmCancel"
      @cancel="showCancelDialog = false"
    />

    <!-- Done editing confirm -->
    <ConfirmDialog
      :show="showDoneDialog"
      title="Submit for re-review?"
      message="This will notify the admin to review your updated profile. You won't be able to make further edits until it's approved."
      confirm-label="Yes, submit"
      @confirm="confirmDoneEditing"
      @cancel="showDoneDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { tutorApi } from '@/api/tutor.js'
import { useAuthStore } from '@/stores/auth.js'
import { getInitials } from '@/utils/helpers.js'
import { toast } from 'vue-sonner'
import ConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'

const auth = useAuthStore()

const stats             = ref({})
const loading           = ref(true)
const uploadingAvatar   = ref(false)
const changeRequest     = ref(null)
const changeReason      = ref('')
const submittingRequest = ref(false)
const cancellingRequest = ref(false)
const showCancelDialog  = ref(false)
const showDoneDialog    = ref(false)

const initials  = computed(() => getInitials(auth.user?.name))
const avatarUrl = computed(() => auth.user?.avatar_url || null)

const verificationBadgeClass = computed(() => {
  const s = stats.value.verification_status
  if (s === 'approved')     return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
  if (s === 'pending')      return 'bg-amber-50 text-amber-700 border border-amber-200'
  if (s === 'rejected')     return 'bg-red-50 text-red-700 border border-red-200'
  if (s === 'under_review') return 'bg-blue-50 text-blue-700 border border-blue-200'
  return 'bg-paper-100 text-paper-500 border border-paper-200'
})

onMounted(async () => {
  try {
    const [dashRes, crRes] = await Promise.all([
      tutorApi.getDashboard(),
      tutorApi.getChangeRequest(),
    ])
    stats.value         = dashRes.data.data
    changeRequest.value = crRes.data.data
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

async function confirmCancel() {
  showCancelDialog.value  = false
  cancellingRequest.value = true
  try {
    await tutorApi.cancelChangeRequest()
    changeRequest.value = null
    toast.success('Change request cancelled.')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to cancel request.')
  } finally {
    cancellingRequest.value = false
  }
}

async function submitChangeRequest() {
  if (!changeReason.value.trim()) {
    toast.error('Please describe what you need to change before submitting.')
    return
  }
  submittingRequest.value = true
  try {
    const { data } = await tutorApi.submitChangeRequest({ reason: changeReason.value })
    changeRequest.value = data.data
    changeReason.value  = ''
    // Also refresh stats so verification badges stay in sync
    const dashRes = await tutorApi.getDashboard()
    stats.value = dashRes.data.data
    toast.success('Change request submitted.')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to submit request.')
  } finally {
    submittingRequest.value = false
  }
}

async function confirmDoneEditing() {
  showDoneDialog.value = false
  try {
    const { data } = await tutorApi.doneEditing()
    changeRequest.value = data.data
    toast.success('Profile submitted for re-review.')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to submit for review.')
  }
}
</script>
