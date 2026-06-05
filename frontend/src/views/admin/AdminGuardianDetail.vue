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

    <!-- Avatar replace confirm -->
    <AdminConfirmDialog
      :show="!!avatarReplaceFile"
      title="Replace Profile Photo?"
      :message="`Replace ${guardian?.user?.name}'s current photo?`"
      confirm-label="Yes, Replace"
      @confirm="confirmAvatarReplace"
      @cancel="avatarReplaceFile = null"
    />

    <!-- Avatar remove confirm -->
    <AdminConfirmDialog
      :show="avatarRemoveConfirm"
      title="Remove Profile Photo?"
      :message="`Remove ${guardian?.user?.name}'s profile photo? This cannot be undone.`"
      confirm-label="Remove Photo"
      :danger="true"
      @confirm="confirmAvatarRemove"
      @cancel="avatarRemoveConfirm = false"
    />

    <AdminConfirmDialog
      :show="saveConfirmOpen"
      title="Save Guardian Profile?"
      message="Apply these changes to the guardian's account directly?"
      confirm-label="Save Changes"
      @confirm="saveConfirmOpen = false; saveEdit()"
      @cancel="saveConfirmOpen = false"
    />
    <AdminConfirmDialog
      :show="!!statusConfirmTarget"
      :title="statusConfirmTarget?.isActive ? 'Reactivate Account?' : 'Suspend Account?'"
      :message="statusConfirmTarget?.isActive
        ? `Reactivate ${guardian?.user?.name}'s account? They will regain full platform access.`
        : `Suspend ${guardian?.user?.name}'s account? They will lose access until reactivated.`"
      :confirm-label="statusConfirmTarget?.isActive ? 'Reactivate' : 'Suspend'"
      :danger="!statusConfirmTarget?.isActive"
      @confirm="doToggleStatus"
      @cancel="statusConfirmTarget = null"
    />

    <template v-if="!loading && guardian">
      <!-- Header card -->
      <div class="card mb-5">
        <div class="flex gap-5 items-start flex-wrap">
          <!-- Avatar + admin controls -->
          <div class="flex flex-col items-center gap-2 shrink-0">
            <div class="w-20 h-20 rounded-xl bg-navy-100 flex items-center justify-center overflow-hidden ring-2 ring-white shadow relative">
              <img v-if="guardian.user?.avatar_url" :src="guardian.user.avatar_url" class="w-full h-full object-cover" />
              <span v-else class="font-display font-bold text-2xl text-navy-700">{{ initials }}</span>
              <span v-if="guardian.user?.pending_avatar_url"
                class="absolute top-0.5 right-0.5 bg-amber-400 text-amber-900 text-[8px] font-bold font-display px-1 py-0.5 rounded leading-tight">
                Pending
              </span>
            </div>
            <div v-if="editing" class="flex gap-1">
              <label class="cursor-pointer inline-flex items-center gap-1 text-xs font-semibold font-display px-2 py-1 rounded-sm border border-paper-300 bg-white text-navy-700 hover:bg-navy-50 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4-4m0 0L8 12m4-4v12"/>
                </svg>
                Replace
                <input type="file" class="hidden" accept="image/jpeg,image/png,image/webp"
                  @change="onAvatarSelected" />
              </label>
              <button v-if="guardian.user?.avatar_url" @click="avatarRemoveConfirm = true"
                class="inline-flex items-center gap-1 text-xs font-semibold font-display px-2 py-1 rounded-sm bg-red-600 text-white hover:bg-red-700 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Remove
              </button>
            </div>
          </div>
          <!-- Basic info -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <h1 class="font-display font-bold text-xl text-navy-900">{{ guardian.user?.name }}</h1>
              <button @click="editing ? (editing = false) : (startEdit(), editing = true)"
                class="text-xs font-semibold font-display px-3 py-1 rounded-md border transition-colors"
                :class="editing ? 'bg-paper-100 border-paper-300 text-paper-600' : 'bg-navy-700 text-white border-navy-700 hover:bg-navy-900'">
                {{ editing ? 'Cancel Edit' : 'Edit Profile' }}
              </button>
              <span v-if="guardian.guardian_id"
                class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill">
                {{ guardian.guardian_id }}
              </span>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize bg-blue-50 text-blue-700">
                {{ guardian.account_type || 'guardian' }}
              </span>
            </div>
            <p class="text-sm text-paper-500 font-body">{{ guardian.user?.email }}</p>
            <p class="text-sm text-paper-500 font-body">{{ guardian.user?.phone }}</p>
            <p v-if="guardian.user?.address" class="text-sm text-paper-500 font-body">{{ guardian.user.address }}</p>
            <p class="text-xs text-paper-400 font-body mt-1">
              Member since {{ formatDate(guardian.user?.created_at) }}
            </p>
          </div>
          <!-- Extra details -->
          <div class="w-full mt-4 pt-4 border-t border-paper-100 grid sm:grid-cols-3 gap-x-6 gap-y-3">
            <div v-if="guardian.occupation">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Occupation</p>
              <p class="text-sm font-body text-navy-800 mt-0.5">{{ guardian.occupation }}</p>
            </div>
            <div v-if="guardian.relationship_to_student">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Relationship</p>
              <p class="text-sm font-body text-navy-800 mt-0.5 capitalize">{{ guardian.relationship_to_student }}</p>
            </div>
            <div v-if="guardian.nid_number">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">NID number</p>
              <p class="text-sm font-body text-navy-800 mt-0.5">{{ guardian.nid_number }}</p>
            </div>
            <div v-if="guardian.nid_document_url" class="sm:col-span-3">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1">NID document</p>
              <a :href="guardian.nid_document_url" target="_blank"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-navy-700 hover:text-navy-900 underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                View NID document
              </a>
            </div>
            <div v-if="!guardian.nid_document_url" class="sm:col-span-3">
              <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-pill bg-amber-50 text-amber-700 border border-amber-200">
                NID not uploaded
              </span>
            </div>
            <!-- Account status -->
            <div class="sm:col-span-3 pt-3 border-t border-paper-100">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-2">Account status</p>
              <div class="flex items-center gap-3 flex-wrap">
                <span class="text-sm font-semibold font-display px-3 py-1 rounded-pill"
                  :class="guardian.user?.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                  {{ guardian.user?.is_active ? 'Active' : 'Suspended' }}
                </span>
                <button v-if="guardian.user?.is_active" @click="statusConfirmTarget = { isActive: false }"
                  :disabled="statusSaving"
                  class="text-sm font-semibold font-display px-4 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700 transition-colors disabled:opacity-50">
                  {{ statusSaving ? 'Saving…' : 'Suspend account' }}
                </button>
                <button v-else @click="statusConfirmTarget = { isActive: true }"
                  :disabled="statusSaving"
                  class="text-sm font-semibold font-display px-4 py-1.5 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 transition-colors disabled:opacity-50">
                  {{ statusSaving ? 'Saving…' : 'Reactivate account' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Inline edit form -->
        <div v-if="editing" class="mt-5 pt-5 border-t border-paper-200 space-y-4">
          <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Edit profile</p>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Full name</label>
              <input v-model="editForm.user.name" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Email</label>
              <input v-model="editForm.user.email" type="email" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Phone</label>
              <input v-model="editForm.user.phone" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Address</label>
              <input v-model="editForm.user.address" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Occupation</label>
              <input v-model="editForm.profile.occupation" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Relationship to student</label>
              <input v-model="editForm.profile.relationship_to_student" class="input text-sm w-full" placeholder="e.g. parent, sibling" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">NID number</label>
              <input v-model="editForm.profile.nid_number" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Account type</label>
              <select v-model="editForm.profile.account_type" class="input text-sm w-full">
                <option value="guardian">Guardian</option>
                <option value="student">Student</option>
              </select>
            </div>
          </div>
          <div class="flex gap-3 pt-1">
            <button @click="saveConfirmOpen = true" :disabled="editSaving"
              class="text-sm font-semibold font-display px-5 py-2 rounded-md bg-navy-700 text-white hover:bg-navy-900 transition-colors disabled:opacity-50">
              {{ editSaving ? 'Saving…' : 'Save Changes' }}
            </button>
            <button @click="editing = false"
              class="text-sm font-semibold font-display px-4 py-2 rounded-md border border-paper-300 text-paper-600 hover:bg-paper-100 transition-colors">
              Cancel
            </button>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-5">

        <!-- Connection Requests -->
        <div class="card">
          <h2 class="section-title">
            Connection requests
            <span class="text-paper-400 font-body font-normal">({{ guardian.connection_requests?.length || 0 }})</span>
          </h2>
          <div v-if="guardian.connection_requests?.length" class="divide-y divide-paper-100">
            <div v-for="conn in guardian.connection_requests" :key="conn.id" class="py-2.5 flex items-center justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-display font-semibold text-navy-900 truncate">
                  {{ conn.tutor_profile?.user?.name || 'Unknown tutor' }}
                </p>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span v-if="conn.tutor_profile?.tutor_id"
                    class="text-xs font-semibold font-display text-navy-500 bg-navy-50 border border-navy-200 px-1.5 py-0 rounded-pill">
                    {{ conn.tutor_profile.tutor_id }}
                  </span>
                  <RouterLink v-if="conn.tutor_profile?.tutor_id"
                    :to="{ name: 'admin-tutor-detail', params: { tutorId: conn.tutor_profile.tutor_id } }"
                    class="text-xs text-navy-700 hover:underline font-semibold">View tutor</RouterLink>
                </div>
                <p class="text-xs text-paper-400 font-body mt-0.5">{{ formatDate(conn.created_at) }}</p>
              </div>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize shrink-0"
                :class="connStatusClass(conn.status)">{{ conn.status.replace(/_/g,' ') }}</span>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No connection requests.</p>
        </div>

        <!-- Tuition Requirements -->
        <div class="card">
          <h2 class="section-title">
            Tuition requirements
            <span class="text-paper-400 font-body font-normal">({{ guardian.tuition_requirements?.length || 0 }})</span>
          </h2>
          <div v-if="guardian.tuition_requirements?.length" class="space-y-4">
            <div v-for="req in guardian.tuition_requirements" :key="req.id"
              class="border border-paper-200 rounded-lg p-3">
              <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                <p class="font-display font-semibold text-navy-900 text-sm">
                  {{ req.student_name || 'Unnamed student' }}
                </p>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize"
                  :class="req.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-paper-100 text-paper-500'">
                  {{ req.status }}
                </span>
              </div>
              <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs">
                <div v-if="req.medium">
                  <dt class="text-paper-400">Medium</dt>
                  <dd class="text-navy-800 capitalize">{{ req.medium.replace(/_/g,' ') }}</dd>
                </div>
                <div v-if="req.class_level">
                  <dt class="text-paper-400">Class</dt>
                  <dd class="text-navy-800">{{ req.class_level }}</dd>
                </div>
                <div v-if="req.city">
                  <dt class="text-paper-400">Location</dt>
                  <dd class="text-navy-800">{{ req.city }}</dd>
                </div>
                <div v-if="req.days_per_week">
                  <dt class="text-paper-400">Days/week</dt>
                  <dd class="text-navy-800">{{ req.days_per_week }}</dd>
                </div>
                <div v-if="req.salary_min || req.salary_max">
                  <dt class="text-paper-400">Budget</dt>
                  <dd class="text-navy-800">৳{{ req.salary_min || 0 }} – ৳{{ req.salary_max || 0 }}</dd>
                </div>
                <div v-if="req.preferred_tutor_gender">
                  <dt class="text-paper-400">Tutor gender</dt>
                  <dd class="text-navy-800 capitalize">{{ req.preferred_tutor_gender }}</dd>
                </div>
              </dl>
              <div v-if="req.subjects?.length" class="mt-2 flex flex-wrap gap-1">
                <span v-for="s in req.subjects" :key="s.id"
                  class="text-xs bg-navy-50 text-navy-700 px-1.5 py-0.5 rounded-pill font-semibold">{{ s.name }}</span>
              </div>
              <p v-if="req.special_notes" class="mt-2 text-xs text-paper-500 font-body italic">{{ req.special_notes }}</p>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No requirements posted.</p>
        </div>

        <!-- Shortlisted Tutors -->
        <div class="card lg:col-span-2">
          <h2 class="section-title">
            Shortlisted tutors
            <span class="text-paper-400 font-body font-normal">({{ guardian.shortlists?.length || 0 }})</span>
          </h2>
          <div v-if="guardian.shortlists?.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div v-for="sl in guardian.shortlists" :key="sl.id"
              class="flex items-center justify-between gap-3 border border-paper-200 rounded-lg px-3 py-2.5">
              <div class="min-w-0">
                <p class="font-display font-semibold text-navy-900 text-sm truncate">
                  {{ sl.tutor_profile?.user?.name || 'Unknown' }}
                </p>
                <span v-if="sl.tutor_profile?.tutor_id"
                  class="text-xs font-semibold font-display text-navy-500">
                  {{ sl.tutor_profile.tutor_id }}
                </span>
              </div>
              <RouterLink v-if="sl.tutor_profile?.tutor_id"
                :to="{ name: 'admin-tutor-detail', params: { tutorId: sl.tutor_profile.tutor_id } }"
                class="text-xs font-semibold font-display text-navy-700 hover:text-navy-900 underline shrink-0">
                View
              </RouterLink>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No tutors shortlisted.</p>
        </div>

      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { getInitials } from '@/utils/helpers.js'
import { toast } from 'vue-sonner'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'

const route              = useRoute()
const guardian           = ref(null)
const loading            = ref(true)
const statusSaving       = ref(false)
const editing            = ref(false)
const editSaving         = ref(false)
const saveConfirmOpen    = ref(false)
const statusConfirmTarget = ref(null) // { isActive: bool }
const editForm           = reactive({ user: { name: '', email: '', phone: '', address: '' }, profile: { occupation: '', relationship_to_student: '', nid_number: '', account_type: '' } })

const avatarReplaceFile   = ref(null)
const avatarRemoveConfirm = ref(false)
const avatarSaving        = ref(false)

function onAvatarSelected(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (file) avatarReplaceFile.value = file
}

async function confirmAvatarReplace() {
  const file = avatarReplaceFile.value
  avatarReplaceFile.value = null
  avatarSaving.value = true
  try {
    const fd = new FormData()
    fd.append('avatar', file)
    const { data } = await adminApi.replaceUserAvatar(guardian.value.user.id, fd)
    guardian.value.user.avatar_url = data.avatar_url
    guardian.value.user.pending_avatar_url = null
    toast.success('Profile photo updated.')
  } catch {
    toast.error('Failed to update photo.')
  } finally {
    avatarSaving.value = false
  }
}

async function confirmAvatarRemove() {
  avatarRemoveConfirm.value = false
  avatarSaving.value = true
  try {
    await adminApi.removeUserAvatar(guardian.value.user.id)
    guardian.value.user.avatar_url = null
    guardian.value.user.pending_avatar_url = null
    toast.success('Profile photo removed.')
  } catch {
    toast.error('Failed to remove photo.')
  } finally {
    avatarSaving.value = false
  }
}

const initials = computed(() => getInitials(guardian.value?.user?.name))

onMounted(async () => {
  try {
    const { data } = await adminApi.getGuardian(route.params.guardianId)
    guardian.value = data.data
  } finally {
    loading.value = false
  }
})

function startEdit() {
  const g = guardian.value
  Object.assign(editForm.user, { name: g.user?.name ?? '', email: g.user?.email ?? '', phone: g.user?.phone ?? '', address: g.user?.address ?? '' })
  Object.assign(editForm.profile, { occupation: g.occupation ?? '', relationship_to_student: g.relationship_to_student ?? '', nid_number: g.nid_number ?? '', account_type: g.account_type ?? 'guardian' })
}

async function saveEdit() {
  editSaving.value = true
  try {
    await adminApi.updateGuardian(route.params.guardianId, { ...editForm })
    // Refresh
    const { data } = await adminApi.getGuardian(route.params.guardianId)
    guardian.value = data.data
    editing.value = false
    toast.success('Guardian profile updated.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not save changes.')
  } finally {
    editSaving.value = false
  }
}

async function doToggleStatus() {
  const { isActive } = statusConfirmTarget.value
  statusConfirmTarget.value = null
  statusSaving.value = true
  try {
    await adminApi.updateGuardianStatus(route.params.guardianId, { is_active: isActive })
    guardian.value.user.is_active = isActive
    toast.success(isActive ? 'Guardian reactivated.' : 'Guardian suspended.')
  } catch {
    toast.error('Could not update status.')
  } finally {
    statusSaving.value = false
  }
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function connStatusClass(status) {
  if (status === 'confirmed') return 'bg-emerald-50 text-emerald-700'
  if (status === 'pending')   return 'bg-amber-50 text-amber-700'
  if (status === 'declined')  return 'bg-red-50 text-red-700'
  return 'bg-blue-50 text-blue-700'
}
</script>

<style scoped>
.section-title {
  @apply font-display font-semibold text-navy-700 text-base mb-3 pb-2 border-b border-paper-100;
}
</style>
