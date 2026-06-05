<template>
  <div>
    <!-- Page header -->
    <div class="mb-6 flex items-center gap-3">
      <h1 class="font-display font-bold text-2xl text-navy-900">My Profile</h1>
      <span v-if="profile?.guardian_id"
        class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill">
        {{ profile.guardian_id }}
      </span>
    </div>

    <div v-if="loading" class="text-paper-500 font-body text-sm">Loading…</div>

    <!-- Two-column on large screens, single column on small -->
    <div v-else class="grid lg:grid-cols-5 gap-6 items-start">

      <!-- ── Left column (form cards) ── -->
      <div class="lg:col-span-3 space-y-5">

        <!-- Profile Information -->
        <div class="card">
          <h2 class="section-title">Profile Information</h2>

          <!-- Avatar -->
          <div class="flex items-center gap-4 mb-5">
            <div class="relative shrink-0">
              <div class="w-20 h-20 rounded-xl bg-navy-100 flex items-center justify-center overflow-hidden ring-4 ring-white shadow-md">
                <img v-if="avatarUrl" :src="avatarUrl" :alt="form.name" class="w-full h-full object-cover" />
                <span v-else class="font-display font-bold text-3xl text-navy-700">{{ initials }}</span>
              </div>
              <!-- Pending avatar badge -->
              <span v-if="auth.user?.pending_avatar_url"
                class="absolute -top-1 -right-1 bg-amber-400 text-amber-900 text-[9px] font-bold font-display px-1.5 py-0.5 rounded-pill shadow-sm border border-amber-300 leading-tight">
                Pending
              </span>
              <label v-else class="absolute bottom-0 right-0 w-7 h-7 bg-navy-700 rounded-full flex items-center justify-center cursor-pointer hover:bg-navy-800 transition-colors shadow-md">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                </svg>
                <input type="file" class="hidden" accept="image/jpeg,image/png,image/webp"
                  @change="uploadAvatar" :disabled="uploadingAvatar" />
              </label>
            </div>
            <div>
              <p class="font-display font-semibold text-navy-900 text-sm leading-tight">{{ form.name || 'Your name' }}</p>
              <p class="text-xs text-paper-500 font-body mt-0.5">{{ profile?.user?.email }}</p>
              <p v-if="uploadingAvatar" class="text-xs text-navy-500 font-body mt-1">Uploading…</p>
              <p v-else-if="auth.user?.pending_avatar_url" class="text-xs text-amber-600 font-body mt-1">Photo pending admin approval</p>
              <p v-else class="text-xs text-paper-400 font-body mt-1">JPG, PNG, WebP · max 2 MB</p>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <label class="field-label">Full name</label>
              <input v-model="form.name" type="text" class="input text-sm" placeholder="Your full name" />
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="field-label">Email address</label>
                <input :value="profile?.user?.email" type="email"
                  class="input text-sm bg-paper-50 text-paper-400 cursor-not-allowed" disabled />
                <p class="text-xs text-paper-400 font-body mt-1">Email cannot be changed here.</p>
              </div>
              <div>
                <label class="field-label">Phone number</label>
                <input v-model="form.phone" type="tel" class="input text-sm" placeholder="+880…" />
              </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="field-label">Occupation</label>
                <input v-model="form.occupation" type="text" class="input text-sm"
                  placeholder="e.g. Engineer, Teacher, Businessman" />
              </div>
              <div>
                <label class="field-label">Relationship to student</label>
                <DropSelect v-model="form.relationship_to_student" :options="relationshipOptions" placeholder="— Select —" />
              </div>
            </div>

            <div>
              <label class="field-label">Address</label>
              <input v-model="form.address" type="text" class="input text-sm" placeholder="Your home address" />
            </div>
          </div>

          <button @click="saveConfirmOpen = true" :disabled="saving"
            class="btn-primary mt-5 text-sm py-2.5 px-6">
            {{ saving ? 'Saving…' : 'Save Changes' }}
          </button>
        </div>

      </div>

      <!-- ── Right column (NID) ── -->
      <div class="lg:col-span-2">
        <div class="card">
          <h2 class="section-title">Identity Verification</h2>

          <!-- Status badge -->
          <div class="mb-4">
            <span v-if="nidDocUrl"
              class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-pill bg-emerald-50 text-emerald-700 border border-emerald-200">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              Document uploaded
            </span>
            <span v-else
              class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-pill bg-amber-50 text-amber-700 border border-amber-200">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
              </svg>
              Not verified
            </span>
          </div>

          <p class="text-xs text-paper-500 font-body mb-4 leading-relaxed">
            Upload your National ID card to help us verify your identity. Accepted: JPG, PNG, PDF — max 4 MB.
          </p>

          <div class="space-y-4">
            <!-- NID number -->
            <div>
              <label class="field-label">NID / Birth registration number</label>
              <input v-model="form.nid_number" type="text" class="input text-sm" maxlength="30"
                placeholder="Enter your NID number" />
            </div>

            <!-- Existing document -->
            <div v-if="nidDocUrl" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
              <div class="flex items-start gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold font-display text-emerald-800">NID document uploaded</p>
                  <p class="text-xs text-emerald-600 font-body mt-0.5">Document is on file and visible to admin.</p>
                </div>
              </div>
              <div class="flex gap-2">
                <a :href="nidDocUrl" target="_blank"
                  class="flex-1 flex items-center justify-center gap-2 text-sm font-semibold font-display bg-emerald-700 text-white rounded-lg py-2 px-4 hover:bg-emerald-800 transition-colors">
                  <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  View document
                </a>
                <button @click="showRemoveNidDialog = true" :disabled="removingNid"
                  class="flex items-center justify-center gap-1.5 text-sm font-semibold font-display bg-red-600 text-white hover:bg-red-700 rounded-lg py-2 px-4 transition-colors disabled:opacity-50">
                  Remove
                </button>
              </div>
            </div>

            <!-- Upload area -->
            <template v-else>
              <label
                class="flex flex-col items-center justify-center border-2 border-dashed border-paper-300 rounded-lg p-6 cursor-pointer hover:border-navy-400 hover:bg-navy-50/40 transition-colors"
                :class="{ 'border-navy-500 bg-navy-50': isDragging }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="onDrop">
                <div class="w-10 h-10 rounded-lg bg-paper-100 flex items-center justify-center mb-3">
                  <svg class="w-5 h-5 text-paper-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                  </svg>
                </div>
                <p class="text-sm font-semibold font-display text-navy-700">Click to upload</p>
                <p class="text-xs text-paper-400 font-body mt-1">or drag & drop here</p>
                <p class="text-xs text-paper-300 font-body mt-0.5">JPG · PNG · PDF · max 4 MB</p>
                <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf" @change="onFileChange" ref="fileInput" />
              </label>

              <!-- Selected file -->
              <div v-if="selectedFile" class="flex items-center gap-3 p-3 rounded-lg border border-navy-200 bg-navy-50">
                <svg class="w-5 h-5 text-navy-600 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold font-display text-navy-800 truncate">{{ selectedFile.name }}</p>
                  <p class="text-xs text-paper-400 font-body">{{ (selectedFile.size / 1024).toFixed(0) }} KB</p>
                </div>
                <button @click="selectedFile = null" class="text-xs font-semibold text-red-500 hover:text-red-700">✕</button>
              </div>

              <button v-if="selectedFile" @click="uploadNid" :disabled="uploadingNid"
                class="btn-primary w-full text-sm py-2.5">
                {{ uploadingNid ? 'Uploading…' : 'Upload NID document' }}
              </button>
            </template>

            <!-- Save NID number when only number changed -->
            <button v-if="!selectedFile && form.nid_number !== (profile?.nid_number ?? '')"
              @click="saveNidNumber" :disabled="saving"
              class="btn-primary w-full text-sm py-2.5">
              {{ saving ? 'Saving…' : 'Save NID number' }}
            </button>
          </div>
        </div>
      </div>

    </div>
    <!-- NID remove confirmation dialog -->
    <Teleport to="body">
      <Transition name="dialog">
        <div v-if="showRemoveNidDialog" class="fixed inset-0 z-[200] flex items-center justify-center px-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showRemoveNidDialog = false" />
          <div class="relative bg-white rounded-sm shadow-xl w-full max-w-xs overflow-hidden">
            <div class="bg-red-600 px-6 pt-8 pb-7 text-center">
              <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
              </div>
              <h3 class="font-display font-bold text-white text-xl leading-tight">Remove document?</h3>
              <p class="text-red-100 text-xs font-body mt-1 tracking-wide">Identity Verification</p>
            </div>
            <!-- Body -->
            <div class="px-6 py-5">
              <p class="text-paper-600 font-body text-sm text-center leading-relaxed mb-5">
                Are you sure you want to remove your NID document? You will need to upload it again to complete identity verification.
              </p>
              <div class="flex gap-3">
                <button @click="showRemoveNidDialog = false"
                  class="btn-outline flex-1 py-2.5 text-sm">
                  Cancel
                </button>
                <button @click="confirmRemoveNid"
                  class="flex-1 inline-flex items-center justify-center bg-red-600 text-white font-semibold font-display py-2.5 rounded-md text-sm hover:bg-red-700 transition-colors">
                  {{ removingNid ? 'Removing…' : 'Yes, remove' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <ConfirmDialog
      :show="saveConfirmOpen"
      title="Save Profile Changes?"
      message="Update your profile with the information entered?"
      confirm-label="Save Changes"
      @confirm="saveConfirmOpen = false; saveProfile()"
      @cancel="saveConfirmOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { guardianApi } from '@/api/guardian.js'
import { authApi } from '@/api/auth.js'
import { useAuthStore } from '@/stores/auth.js'
import { getInitials } from '@/utils/helpers.js'
import { toast } from 'vue-sonner'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const auth               = useAuthStore()
const profile            = ref(null)
const loading            = ref(true)
const saving             = ref(false)
const saveConfirmOpen    = ref(false)
const uploadingAvatar     = ref(false)
const uploadingNid        = ref(false)
const removingNid         = ref(false)
const showRemoveNidDialog = ref(false)
const isDragging          = ref(false)
const selectedFile        = ref(null)
const fileInput           = ref(null)

const avatarUrl = computed(() => auth.user?.avatar_url || null)
const initials  = computed(() => getInitials(form.name || auth.user?.name))
const nidDocUrl = computed(() => profile.value?.nid_document_url ?? null)
const relationshipOptions = [
  { value: '', label: '— Select —' },
  { value: 'father', label: 'Father' },
  { value: 'mother', label: 'Mother' },
  { value: 'brother', label: 'Brother' },
  { value: 'sister', label: 'Sister' },
  { value: 'uncle', label: 'Uncle' },
  { value: 'aunt', label: 'Aunt' },
  { value: 'grandfather', label: 'Grandfather' },
  { value: 'grandmother', label: 'Grandmother' },
  { value: 'self', label: 'Self (student)' },
  { value: 'other', label: 'Other' },
]

const form = reactive({
  name:                    '',
  phone:                   '',
  address:                 '',
  occupation:              '',
  relationship_to_student: '',
  nid_number:              '',
})

onMounted(async () => {
  try {
    const { data } = await guardianApi.getProfile()
    profile.value = data.data
    fillForm(data.data)
  } finally {
    loading.value = false
  }
})

function fillForm(p) {
  form.name                    = p?.user?.name || ''
  form.phone                   = p?.user?.phone || ''
  form.address                 = p?.user?.address || ''
  form.occupation              = p?.occupation || ''
  form.relationship_to_student = p?.relationship_to_student || ''
  form.nid_number              = p?.nid_number || ''
}

async function saveProfile() {
  saving.value = true
  try {
    const { data } = await guardianApi.updateProfile({
      name:                    form.name,
      phone:                   form.phone,
      address:                 form.address,
      occupation:              form.occupation,
      relationship_to_student: form.relationship_to_student,
    })
    profile.value = data.data
    auth.user = data.data.user
    toast.success('Profile saved.')
  } catch (e) {
    const msg = e.response?.data?.message
      || Object.values(e.response?.data?.errors || {})[0]?.[0]
      || 'Could not save profile.'
    toast.error(msg)
  } finally {
    saving.value = false
  }
}

async function uploadAvatar(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (!file) return
  uploadingAvatar.value = true
  try {
    const fd = new FormData()
    fd.append('avatar', file)
    const result = await auth.uploadAvatar(fd)
    if (result.pending) {
      toast.info('Photo submitted for admin approval. Your current photo stays until it\'s approved.')
    } else {
      toast.success('Profile picture updated!')
    }
  } catch {
    toast.error('Upload failed. Max 2 MB, JPG/PNG/WebP.')
  } finally {
    uploadingAvatar.value = false
  }
}

async function saveNidNumber() {
  saving.value = true
  try {
    await guardianApi.updateProfile({ nid_number: form.nid_number })
    profile.value.nid_number = form.nid_number
    toast.success('NID number saved.')
  } catch {
    toast.error('Could not save NID number.')
  } finally {
    saving.value = false
  }
}

function onFileChange(e) {
  const file = e.target.files?.[0]
  if (file) selectedFile.value = file
}

function onDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files?.[0]
  if (file) selectedFile.value = file
}

async function uploadNid() {
  if (!selectedFile.value) return
  uploadingNid.value = true
  try {
    const fd = new FormData()
    fd.append('nid_document', selectedFile.value)
    const { data } = await guardianApi.uploadNid(fd)
    profile.value.nid_document     = 'uploaded'
    profile.value.nid_document_url = data.data.nid_document_url
    selectedFile.value = null
    toast.success('NID document uploaded.')
  } catch (e) {
    const msg = e.response?.data?.message
      || Object.values(e.response?.data?.errors || {})[0]?.[0]
      || 'Upload failed.'
    toast.error(msg)
  } finally {
    uploadingNid.value = false
  }
}

async function confirmRemoveNid() {
  showRemoveNidDialog.value = false
  removingNid.value = true
  try {
    await guardianApi.deleteNid()
    profile.value.nid_document     = null
    profile.value.nid_document_url = null
    toast.success('NID document removed.')
  } catch {
    toast.error('Could not remove document.')
  } finally {
    removingNid.value = false
  }
}
</script>

<style scoped>
.section-title { @apply font-display font-semibold text-navy-700 text-base mb-4 pb-2 border-b border-paper-100; }
.field-label   { @apply block text-xs font-semibold font-display text-navy-700 mb-1; }

.dialog-enter-active { transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.dialog-leave-active { transition: all 0.15s ease-in; }
.dialog-enter-from   { opacity: 0; transform: scale(0.88) translateY(8px); }
.dialog-leave-to     { opacity: 0; transform: scale(0.95) translateY(4px); }
</style>
