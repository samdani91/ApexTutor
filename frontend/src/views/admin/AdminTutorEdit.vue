<template>
  <div class="w-full">
    <RouterLink :to="{ name: 'admin-tutor-detail', params: { tutorId: route.params.tutorId } }"
      class="inline-flex items-center gap-1.5 text-sm font-semibold font-display text-navy-700 hover:text-navy-900 mb-5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
      </svg>
      Back to profile
    </RouterLink>

    <div v-if="loading" class="text-paper-500 font-body py-12 text-center">Loading…</div>

    <template v-else>
      <div class="flex flex-col gap-3 mb-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h1 class="font-display font-bold text-2xl text-navy-900">Edit tutor profile</h1>
          <p class="text-sm text-paper-400 font-body mt-0.5">{{ tutor?.user?.name }} · {{ tutor?.tutor_id }}</p>
        </div>
        <button @click="saveConfirmOpen = true" :disabled="saving"
          class="btn-primary text-sm px-5 py-2.5 disabled:opacity-50">
          {{ saving ? 'Saving…' : 'Save all changes' }}
        </button>
      </div>

      <!-- ── Account info ─────────────────────────────────────── -->
      <div class="grid gap-4 xl:grid-cols-2">
      <section class="card">
        <h2 class="section-title">Account info</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <field label="Full name"><input v-model="form.user.name" class="input text-sm w-full" /></field>
          <field label="Email"><input v-model="form.user.email" type="email" class="input text-sm w-full" /></field>
          <field label="Phone"><input v-model="form.user.phone" class="input text-sm w-full" /></field>
          <field label="Address"><input v-model="form.user.address" class="input text-sm w-full" /></field>
        </div>
      </section>

      <!-- ── Bio ─────────────────────────────────────────────── -->
      <section class="card">
        <h2 class="section-title">Bio</h2>
        <textarea v-model="form.profile.bio" rows="4" class="input text-sm w-full resize-none"
          placeholder="Tutor bio…" maxlength="2000" />
        <p class="text-xs text-paper-400 font-body mt-1 text-right">{{ (form.profile.bio || '').length }}/2000</p>
      </section>
      </div>

      <!-- ── Profile photo ──────────────────────────────────── -->
      <section class="card mt-4">
        <h2 class="section-title">Profile photo</h2>
        <div class="flex items-center gap-5">
          <div class="w-20 h-20 rounded-xl bg-navy-100 flex items-center justify-center overflow-hidden ring-2 ring-white shadow shrink-0 relative">
            <img v-if="tutor?.user?.avatar_url" :src="tutor.user.avatar_url" class="w-full h-full object-cover" alt="Current photo" />
            <span v-else class="font-display font-bold text-2xl text-navy-700">{{ tutor?.user?.name?.charAt(0)?.toUpperCase() }}</span>
            <span v-if="tutor?.user?.pending_avatar_url"
              class="absolute top-0.5 right-0.5 bg-amber-400 text-amber-900 text-[8px] font-bold font-display px-1 py-0.5 rounded leading-tight">
              Pending
            </span>
          </div>
          <div class="flex flex-col gap-2">
            <div v-if="tutor?.user?.pending_avatar_url" class="flex items-center gap-2">
              <img :src="tutor.user.pending_avatar_url" class="w-12 h-12 rounded-lg object-cover ring-2 ring-amber-400" alt="Pending photo" />
              <span class="text-xs font-body text-amber-700">Pending approval photo</span>
            </div>
            <div class="flex gap-2 flex-wrap">
              <label class="cursor-pointer inline-flex items-center gap-1.5 text-sm font-semibold font-display px-3 py-1.5 rounded-md border border-paper-300 bg-white text-navy-700 hover:bg-navy-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4-4m0 0L8 12m4-4v12"/>
                </svg>
                Replace photo
                <input type="file" class="hidden" accept="image/jpeg,image/png,image/webp" @change="onAvatarSelected" />
              </label>
              <button v-if="tutor?.user?.avatar_url" @click="avatarRemoveConfirm = true"
                class="inline-flex items-center gap-1.5 text-sm font-semibold font-display px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Remove photo
              </button>
            </div>
            <p class="text-xs text-paper-400 font-body">JPG, PNG or WebP. Max 2 MB.</p>
          </div>
        </div>
      </section>

      <!-- ── Personal info ────────────────────────────────────── -->
      <section class="card mt-4">
        <h2 class="section-title">Personal information</h2>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
          <field label="Gender">
            <DropSelect v-model="form.personal_info.gender" :options="GENDER_OPTIONS" placeholder="Not specified" />
          </field>
          <field label="Date of birth"><input v-model="form.personal_info.date_of_birth" type="date" class="input text-sm w-full" /></field>
          <field label="Nationality"><input v-model="form.personal_info.nationality" class="input text-sm w-full" /></field>
          <field label="Religion"><input v-model="form.personal_info.religion" class="input text-sm w-full" /></field>
          <field label="National ID"><input v-model="form.personal_info.national_id" class="input text-sm w-full" /></field>
          <field label="Additional phone"><input v-model="form.personal_info.additional_phone" class="input text-sm w-full" /></field>
          <field label="Present address" class="sm:col-span-2"><input v-model="form.personal_info.present_address" class="input text-sm w-full" /></field>
          <field label="Permanent address" class="sm:col-span-2"><input v-model="form.personal_info.permanent_address" class="input text-sm w-full" /></field>
          <field label="Facebook URL"><input v-model="form.personal_info.facebook_url" class="input text-sm w-full" /></field>
          <field label="LinkedIn URL"><input v-model="form.personal_info.linkedin_url" class="input text-sm w-full" /></field>
          <field label="Father's name"><input v-model="form.personal_info.fathers_name" class="input text-sm w-full" /></field>
          <field label="Father's phone"><input v-model="form.personal_info.fathers_phone" class="input text-sm w-full" /></field>
          <field label="Mother's name"><input v-model="form.personal_info.mothers_name" class="input text-sm w-full" /></field>
          <field label="Mother's phone"><input v-model="form.personal_info.mothers_phone" class="input text-sm w-full" /></field>
        </div>
      </section>

      <!-- ── Education ────────────────────────────────────────── -->
      <section class="card mt-4">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-paper-100">
          <h2 class="font-display font-semibold text-navy-800 text-base">Education</h2>
          <button @click="addEduEntry"
            class="text-xs font-semibold font-display px-3 py-1.5 rounded-sm border border-navy-200 text-navy-700 hover:bg-navy-50 transition-colors">
            + Add education
          </button>
        </div>

        <p v-if="eduUniLoading" class="text-sm text-paper-400 font-body italic">Loading universities…</p>
        <p v-else-if="!eduEntries.length" class="text-sm text-paper-400 font-body italic">No education entries.</p>

        <div v-else class="space-y-4">
          <div v-for="(entry, i) in eduEntries" :key="entry.id ?? `new-${i}`"
            class="rounded-sm border border-paper-200 bg-paper-50 p-4">
            <div class="grid sm:grid-cols-2 gap-3">
              <field label="Degree">
                <DropSelect v-model="entry.level" :options="EDU_LEVELS" placeholder="Select degree"
                  @update:modelValue="onEduLevelChange(entry)" />
              </field>

              <field v-if="isUniLevel(entry.level)" label="Degree type">
                <DropSelect v-model="entry.degree_title" :options="degreeTypeOptions(entry.level)"
                  placeholder="Select degree type" />
              </field>

              <field label="Institute" :class="isUniLevel(entry.level) ? 'sm:col-span-2' : ''">
                <DropSelect v-if="isUniLevel(entry.level)" v-model="entry.university_id" :options="eduUniversityOptions"
                  placeholder="Select university" @update:modelValue="onEduUniversityChange(entry)" />
                <input v-else v-model="entry.institute_name" type="text" class="input text-sm w-full"
                  placeholder="School / College / Institute name" />
              </field>

              <field v-if="isUniLevel(entry.level)" label="Subject / Major">
                <input v-model="entry.major_group" type="text" class="input text-sm w-full"
                  placeholder="e.g. Software Engineering" />
              </field>

              <field label="Year of passing">
                <input v-model.number="entry.year_of_passing" type="number" min="1970" :max="currentYear"
                  class="input text-sm w-full" placeholder="2020" />
              </field>

              <field label="Result / GPA">
                <input v-model="entry.result" type="text" class="input text-sm w-full" placeholder="3.75 / CGPA 4.0" />
              </field>
            </div>

            <div class="flex gap-2 mt-3">
              <button @click="saveEduEntry(entry, i)" :disabled="!entry.level || entry._saving"
                class="text-xs font-semibold font-display bg-navy-700 text-white px-3 py-1.5 rounded-sm hover:bg-navy-900 disabled:opacity-50 transition-colors">
                {{ entry._saving ? 'Saving…' : 'Save' }}
              </button>
              <button @click="confirmDeleteEdu(entry, i)" :disabled="entry._saving"
                class="text-xs font-semibold font-display bg-red-600 text-white px-3 py-1.5 rounded-sm hover:bg-red-700 disabled:opacity-50 transition-colors">
                Remove
              </button>
            </div>
          </div>
        </div>
      </section>

      <div class="grid gap-4 mt-4 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
      <!-- ── Emergency contact ────────────────────────────────── -->
      <section class="card">
        <h2 class="section-title">Emergency contact</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <field label="Name"><input v-model="form.emergency_contact.name" class="input text-sm w-full" /></field>
          <field label="Relation"><input v-model="form.emergency_contact.relation" class="input text-sm w-full" /></field>
          <field label="Phone"><input v-model="form.emergency_contact.phone" class="input text-sm w-full" /></field>
          <field label="Address"><input v-model="form.emergency_contact.address" class="input text-sm w-full" /></field>
        </div>
      </section>

      <!-- ── Tuition preferences ──────────────────────────────── -->
      <section class="card">
        <h2 class="section-title">Tuition preferences</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <field label="Min salary (৳/mo)"><input v-model.number="form.preference.expected_salary_min" type="number" min="0" class="input text-sm w-full" /></field>
          <field label="Max salary (৳/mo)"><input v-model.number="form.preference.expected_salary_max" type="number" min="0" class="input text-sm w-full" /></field>
          <field label="Experience (years)"><input v-model.number="form.preference.total_experience_years" type="number" min="0" class="input text-sm w-full" /></field>
          <field label="Days per week"><input v-model.number="form.preference.days_per_week" type="number" min="1" max="7" class="input text-sm w-full" /></field>
          <field label="Hours per session"><input v-model.number="form.preference.hours_per_day" type="number" min="0.5" max="12" step="0.5" class="input text-sm w-full" /></field>
          <field label="Experience details" class="sm:col-span-2">
            <textarea v-model="form.preference.experience_details" rows="3" class="input text-sm w-full resize-none" maxlength="2000" />
          </field>
          <field label="Teaching method description" class="sm:col-span-2">
            <textarea v-model="form.preference.tutoring_method_description" rows="3" class="input text-sm w-full resize-none" maxlength="1000" />
          </field>
        </div>
      </section>
      </div>

      <!-- ── Documents ───────────────────────────────────────────── -->
      <section class="card mt-4">
        <h2 class="section-title">Documents</h2>

        <div v-if="!documents.length" class="text-sm text-paper-400 font-body italic mb-4">No documents uploaded.</div>

        <div v-else class="grid gap-3 sm:grid-cols-2 mb-4">
          <div v-for="doc in documents" :key="doc.id"
            class="flex items-start justify-between gap-3 rounded-sm border border-paper-200 bg-paper-50 px-3 py-2.5">
            <div class="min-w-0">
              <p class="text-xs font-semibold font-display text-navy-700 uppercase tracking-wide">
                {{ docLabel(doc.type) }}
              </p>
              <p class="text-xs text-paper-500 font-body truncate mt-0.5">{{ doc.file_name }}</p>
              <a v-if="doc.file_url" :href="doc.file_url" target="_blank"
                class="text-xs font-semibold font-display text-navy-600 hover:underline mt-0.5 inline-block">
                View document
              </a>
            </div>
            <button @click="confirmDeleteDoc(doc)"
              :disabled="docDeleting[doc.id]"
              class="shrink-0 text-xs font-semibold font-display px-2.5 py-1.5 rounded-sm bg-red-600 text-white hover:bg-red-700 transition-colors disabled:opacity-50">
              {{ docDeleting[doc.id] ? '…' : 'Remove' }}
            </button>
          </div>
        </div>

        <!-- Upload new document -->
        <div class="border-t border-paper-100 pt-4">
          <p class="text-xs font-semibold font-display text-paper-500 uppercase tracking-wide mb-3">Upload / replace document</p>
          <div class="grid sm:grid-cols-3 gap-3 items-end">
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Document type</label>
              <DropSelect v-model="docUpload.type" :options="DOC_TYPE_OPTIONS" placeholder="Select type…" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">File (PDF / JPG / PNG, max 5 MB)</label>
              <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="onDocFile" class="input text-sm w-full" />
            </div>
            <button @click="uploadDoc"
              :disabled="!docUpload.type || !docUpload.file || docUploading"
              class="inline-flex min-h-[42px] items-center justify-center rounded-sm bg-navy-700 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-navy-900 disabled:opacity-50">
              {{ docUploading ? 'Uploading…' : 'Upload' }}
            </button>
          </div>
        </div>
      </section>

      <!-- ── Teaching videos ──────────────────────────────────────── -->
      <section class="card mt-4">
        <h2 class="section-title">Teaching videos</h2>

        <div v-if="!videos.length" class="text-sm text-paper-400 font-body italic">No teaching videos uploaded.</div>

        <div v-else class="space-y-3">
          <div v-for="vid in videos" :key="vid.id"
            class="rounded-sm border border-paper-200 bg-paper-50 p-3">
            <div class="flex items-start gap-3">
              <!-- Thumbnail / play icon -->
              <div class="w-24 h-16 shrink-0 rounded bg-navy-100 flex items-center justify-center overflow-hidden">
                <img v-if="vid.thumbnail_url" :src="vid.thumbnail_url" class="w-full h-full object-cover" />
                <svg v-else class="w-7 h-7 text-navy-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                </svg>
              </div>

              <div class="flex-1 min-w-0">
                <template v-if="videoEditing[vid.id]">
                  <div class="grid sm:grid-cols-2 gap-2 mb-2">
                    <input v-model="videoEditing[vid.id].title"       placeholder="Title"       class="input text-xs w-full" />
                    <input v-model="videoEditing[vid.id].subject"     placeholder="Subject"     class="input text-xs w-full" />
                    <input v-model="videoEditing[vid.id].class_level" placeholder="Class level" class="input text-xs w-full" />
                    <input v-model="videoEditing[vid.id].medium"      placeholder="Medium"      class="input text-xs w-full" />
                  </div>
                  <div class="flex gap-2">
                    <button @click="saveVideo(vid)" :disabled="videoSaving[vid.id]"
                      class="text-xs font-semibold font-display bg-navy-700 text-white px-3 py-1.5 rounded-sm hover:bg-navy-900 disabled:opacity-50 transition-colors">
                      {{ videoSaving[vid.id] ? 'Saving…' : 'Save' }}
                    </button>
                    <button @click="cancelEditVideo(vid)"
                      class="text-xs font-semibold font-display border border-paper-300 text-paper-600 px-3 py-1.5 rounded-sm hover:bg-paper-100 transition-colors">
                      Cancel
                    </button>
                  </div>
                </template>
                <template v-else>
                  <p class="text-sm font-semibold font-display text-navy-900 truncate">{{ vid.title }}</p>
                  <p class="text-xs text-paper-500 font-body mt-0.5">
                    {{ vid.subject }} · {{ vid.class_level }} · {{ vid.medium }}
                  </p>
                  <a v-if="vid.file_url" :href="vid.file_url" target="_blank"
                    class="text-xs font-semibold font-display text-navy-600 hover:underline mt-1 inline-block">
                    Play video
                  </a>
                </template>
              </div>

              <div class="flex flex-col gap-1.5 shrink-0">
                <button v-if="!videoEditing[vid.id]" @click="startEditVideo(vid)"
                  class="text-xs font-semibold font-display px-2.5 py-1.5 rounded-sm border border-navy-200 text-navy-700 hover:bg-navy-50 transition-colors">
                  Edit
                </button>
                <button @click="confirmDeleteVideo(vid)" :disabled="videoDeleting[vid.id]"
                  class="text-xs font-semibold font-display px-2.5 py-1.5 rounded-sm bg-red-600 text-white hover:bg-red-700 transition-colors disabled:opacity-50">
                  {{ videoDeleting[vid.id] ? '…' : 'Remove' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Save button (bottom) -->
      <div class="flex flex-col-reverse gap-3 mt-4 sm:flex-row sm:items-center sm:justify-end">
        <button @click="saveConfirmOpen = true" :disabled="saving"
          class="btn-primary text-sm px-6 py-2.5 disabled:opacity-50">
          {{ saving ? 'Saving…' : 'Save all changes' }}
        </button>
        <button @click="cancelConfirmOpen = true"
          class="text-sm font-semibold font-display px-4 py-2.5 rounded-sm border border-paper-300 bg-paper-100 text-paper-700 hover:bg-paper-200 transition-colors">
          Cancel
        </button>
      </div>

      <AdminConfirmDialog
        :show="saveConfirmOpen"
        title="Save Tutor Profile Changes?"
        message="Apply these edits directly to the tutor profile?"
        confirm-label="Save Changes"
        @confirm="save"
        @cancel="saveConfirmOpen = false"
      />

      <AdminConfirmDialog
        :show="cancelConfirmOpen"
        title="Cancel Tutor Edits?"
        message="Leave this page and discard unsaved tutor profile edits?"
        confirm-label="Leave Page"
        danger
        @confirm="confirmCancel"
        @cancel="cancelConfirmOpen = false"
      />

      <AdminConfirmDialog
        :show="!!deleteDocTarget"
        title="Delete document?"
        :message="`Permanently delete the ${deleteDocTarget ? docLabel(deleteDocTarget.type) : ''} document? This cannot be undone.`"
        confirm-label="Delete"
        danger
        @confirm="doDeleteDoc"
        @cancel="deleteDocTarget = null"
      />

      <AdminConfirmDialog
        :show="!!deleteVideoTarget"
        title="Delete video?"
        :message="`Permanently delete '${deleteVideoTarget?.title}'? This cannot be undone.`"
        confirm-label="Delete"
        danger
        @confirm="doDeleteVideo"
        @cancel="deleteVideoTarget = null"
      />

      <AdminConfirmDialog
        :show="!!deleteEduTarget"
        title="Delete education entry?"
        message="Permanently delete this education entry? This cannot be undone."
        confirm-label="Delete"
        danger
        @confirm="doDeleteEdu"
        @cancel="deleteEduTarget = null"
      />

      <AdminConfirmDialog
        :show="!!avatarReplaceFile"
        title="Replace Profile Photo?"
        :message="`Replace ${tutor?.user?.name}'s current photo? Any pending avatar will also be cleared.`"
        confirm-label="Yes, Replace"
        @confirm="confirmAvatarReplace"
        @cancel="avatarReplaceFile = null"
      />

      <AdminConfirmDialog
        :show="avatarRemoveConfirm"
        title="Remove Profile Photo?"
        :message="`Remove ${tutor?.user?.name}'s profile photo? This cannot be undone.`"
        confirm-label="Remove Photo"
        :danger="true"
        @confirm="confirmAvatarRemove"
        @cancel="avatarRemoveConfirm = false"
      />
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, defineComponent, h } from 'vue'

const field = defineComponent({
  props: ['label'],
  setup(props, { slots, attrs }) {
    return () => h('div', { class: attrs.class }, [
      h('label', { class: 'block text-xs font-semibold font-display text-paper-500 mb-1' }, props.label),
      slots.default?.(),
    ])
  },
})
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { searchApi } from '@/api/search.js'
import { toast } from 'vue-sonner'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'
import DropSelect from '@/components/search/DropSelect.vue'

const route  = useRoute()
const router = useRouter()
const tutor  = ref(null)
const loading = ref(true)
const saving  = ref(false)
const saveConfirmOpen = ref(false)
const cancelConfirmOpen = ref(false)

// ── Avatar management ─────────────────────────────────────
const avatarReplaceFile   = ref(null)
const avatarRemoveConfirm = ref(false)
const avatarSaving        = ref(false)

function onAvatarSelected(e) {
  const file = e.target.files?.[0]
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
    const { data } = await adminApi.replaceUserAvatar(tutor.value.user.id, fd)
    tutor.value.user.avatar_url         = data.avatar_url
    tutor.value.user.pending_avatar_url = null
    toast.success('Photo replaced.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not replace photo.')
  } finally {
    avatarSaving.value = false
  }
}

async function confirmAvatarRemove() {
  avatarRemoveConfirm.value = false
  avatarSaving.value = true
  try {
    await adminApi.removeUserAvatar(tutor.value.user.id)
    tutor.value.user.avatar_url         = null
    tutor.value.user.pending_avatar_url = null
    toast.success('Photo removed.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not remove photo.')
  } finally {
    avatarSaving.value = false
  }
}

// ── Documents ─────────────────────────────────────────────
const documents       = ref([])
const docUpload       = reactive({ type: '', file: null })
const docUploading    = ref(false)
const docDeleting     = reactive({})
const deleteDocTarget = ref(null)

// ── Teaching videos ───────────────────────────────────────
const videos            = ref([])
const videoEditing      = reactive({})   // { [id]: { title, subject, class_level, medium } }
const videoSaving       = reactive({})
const videoDeleting     = reactive({})
const deleteVideoTarget = ref(null)

const DOC_LABELS = {
  nid: 'National ID (NID)',
  ssc_marksheet: 'SSC / O Level Marksheet',
  hsc_marksheet: 'HSC / A Level Marksheet',
  emergency_contact_nid: 'Emergency Contact NID',
}
function docLabel(type) {
  return DOC_LABELS[type] ?? String(type || 'Document').replace(/_/g, ' ')
}
const DOC_TYPE_OPTIONS = Object.entries(DOC_LABELS).map(([value, label]) => ({ value, label }))

const GENDER_OPTIONS = [
  { value: '',       label: 'Not specified' },
  { value: 'male',   label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other',  label: 'Other' },
]

// ── Education ─────────────────────────────────────────────
const eduEntries      = ref([])
const eduUniversities = ref([])
const eduUniLoading   = ref(false)
const deleteEduTarget = ref(null)   // { entry, index }
const currentYear     = new Date().getFullYear()

const eduUniversityOptions = computed(() =>
  eduUniversities.value.map(u => ({ value: u.id, label: u.name }))
)

const UNIVERSITY_LEVELS = ['phd', 'masters', 'bachelor']
const EDU_LEVELS = [
  { value: 'bachelor', label: 'Bachelor / Honours' },
  { value: 'masters',  label: 'Masters' },
  { value: 'phd',      label: 'PhD' },
  { value: 'hsc',      label: 'HSC' },
  { value: 'ssc',      label: 'SSC' },
  { value: 'o_level',  label: 'O Level' },
  { value: 'a_level',  label: 'A Level' },
  { value: 'other',    label: 'Other' },
]
const DEGREE_TYPES = {
  bachelor: [
    { value: 'BSc', label: 'BSc (Bachelor of Science)' },
    { value: 'BA', label: 'BA (Bachelor of Arts)' },
    { value: 'Hons', label: 'Hons (Honours)' },
    { value: 'BBA', label: 'BBA (Business Administration)' },
    { value: 'BEng', label: 'BEng (Engineering)' },
    { value: 'LLB', label: 'LLB (Law)' },
    { value: 'BPharm', label: 'BPharm (Pharmacy)' },
    { value: 'MBBS', label: 'MBBS' },
    { value: 'BArch', label: 'BArch (Architecture)' },
    { value: 'Other', label: 'Other' },
  ],
  masters: [
    { value: 'MSc', label: 'MSc (Master of Science)' },
    { value: 'MA', label: 'MA (Master of Arts)' },
    { value: 'MBA', label: 'MBA (Business Administration)' },
    { value: 'MEng', label: 'MEng (Engineering)' },
    { value: 'MPhil', label: 'MPhil' },
    { value: 'LLM', label: 'LLM (Law)' },
    { value: 'MPH', label: 'MPH (Public Health)' },
    { value: 'Other', label: 'Other' },
  ],
  phd: [
    { value: 'PhD', label: 'PhD (Doctor of Philosophy)' },
  ],
}

function isUniLevel(level) {
  return UNIVERSITY_LEVELS.includes(level)
}
function degreeTypeOptions(level) {
  return DEGREE_TYPES[level] || []
}
function emptyEduEntry() {
  return { id: null, level: '', university_id: null, institute_name: '', degree_title: '', major_group: '', year_of_passing: '', result: '', _saving: false }
}
function onEduLevelChange(entry) {
  entry.degree_title = ''
  entry.major_group  = ''
  if (!isUniLevel(entry.level)) entry.university_id = null
}
function onEduUniversityChange(entry) {
  const uni = eduUniversities.value.find(u => u.id === entry.university_id)
  entry.institute_name = uni?.name || ''
}
function addEduEntry() {
  eduEntries.value.push(emptyEduEntry())
}

async function saveEduEntry(entry, index) {
  if (!entry.level) return
  entry._saving = true
  try {
    const payload = {
      level:           entry.level,
      university_id:   entry.university_id || null,
      institute_name:  entry.institute_name || null,
      degree_title:    entry.degree_title || null,
      major_group:     entry.major_group || null,
      result:          entry.result || null,
      year_of_passing: entry.year_of_passing || null,
    }
    const { data } = entry.id
      ? await adminApi.updateTutorEducation(route.params.tutorId, entry.id, payload)
      : await adminApi.addTutorEducation(route.params.tutorId, payload)
    // Merge the persisted entry back (id, resolved institute_name, etc.) while keeping the UI flag.
    eduEntries.value[index] = { ...emptyEduEntry(), ...data.data, _saving: false }
    toast.success(entry.id ? 'Education entry updated.' : 'Education entry added.')
  } catch (e) {
    entry._saving = false
    toast.error(e.response?.data?.message ?? 'Could not save education entry.')
  }
}

function confirmDeleteEdu(entry, index) {
  // A brand-new unsaved row can just be removed locally.
  if (!entry.id) {
    eduEntries.value.splice(index, 1)
    return
  }
  deleteEduTarget.value = { entry, index }
}

async function doDeleteEdu() {
  const target = deleteEduTarget.value
  deleteEduTarget.value = null
  if (!target) return
  const { entry, index } = target
  try {
    await adminApi.deleteTutorEducation(route.params.tutorId, entry.id)
    eduEntries.value.splice(index, 1)
    toast.success('Education entry deleted.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not delete education entry.')
  }
}

const form = reactive({
  user:              { name: '', email: '', phone: '', address: '' },
  profile:           { bio: '' },
  personal_info:     { gender: '', date_of_birth: '', nationality: '', religion: '', national_id: '', additional_phone: '', present_address: '', permanent_address: '', facebook_url: '', linkedin_url: '', fathers_name: '', fathers_phone: '', mothers_name: '', mothers_phone: '' },
  emergency_contact: { name: '', relation: '', phone: '', address: '' },
  preference:        { expected_salary_min: '', expected_salary_max: '', total_experience_years: '', days_per_week: '', hours_per_day: '', experience_details: '', tutoring_method_description: '' },
})

onMounted(async () => {
  loadEduUniversities()
  try {
    const { data } = await adminApi.getTutor(route.params.tutorId)
    tutor.value = data.data
    populate(data.data)
  } finally {
    loading.value = false
  }
})

async function loadEduUniversities() {
  eduUniLoading.value = true
  try {
    const { data } = await searchApi.universities()
    eduUniversities.value = data.data ?? []
  } finally {
    eduUniLoading.value = false
  }
}

function populate(t) {
  Object.assign(form.user, { name: t.user?.name ?? '', email: t.user?.email ?? '', phone: t.user?.phone ?? '', address: t.user?.address ?? '' })
  Object.assign(form.profile, { bio: t.bio ?? '' })

  const pi = t.personal_info ?? {}
  Object.assign(form.personal_info, { gender: pi.gender ?? '', date_of_birth: pi.date_of_birth ? pi.date_of_birth.split('T')[0] : '', nationality: pi.nationality ?? '', religion: pi.religion ?? '', national_id: pi.national_id ?? '', additional_phone: pi.additional_phone ?? '', present_address: pi.present_address ?? '', permanent_address: pi.permanent_address ?? '', facebook_url: pi.facebook_url ?? '', linkedin_url: pi.linkedin_url ?? '', fathers_name: pi.fathers_name ?? '', fathers_phone: pi.fathers_phone ?? '', mothers_name: pi.mothers_name ?? '', mothers_phone: pi.mothers_phone ?? '' })

  const ec = t.emergency_contact ?? {}
  Object.assign(form.emergency_contact, { name: ec.name ?? '', relation: ec.relation ?? '', phone: ec.phone ?? '', address: ec.address ?? '' })

  const pref = t.tuition_preference ?? {}
  Object.assign(form.preference, { expected_salary_min: pref.expected_salary_min ?? '', expected_salary_max: pref.expected_salary_max ?? '', total_experience_years: pref.total_experience_years ?? '', days_per_week: pref.days_per_week ?? '', hours_per_day: pref.hours_per_day ?? '', experience_details: pref.experience_details ?? '', tutoring_method_description: pref.tutoring_method_description ?? '' })

  documents.value = t.documents ?? []
  videos.value    = t.teaching_videos ?? t.teachingVideos ?? []

  const edu = t.education_entries ?? t.educationEntries ?? []
  eduEntries.value = edu.map(e => ({
    id:              e.id,
    level:           e.level ?? '',
    university_id:   e.university_id ?? null,
    institute_name:  e.institute_name ?? '',
    degree_title:    e.degree_title ?? '',
    major_group:     e.major_group ?? '',
    year_of_passing: e.year_of_passing ?? '',
    result:          e.result ?? '',
    _saving:         false,
  }))
}

// ── Document handlers ─────────────────────────────────────

function onDocFile(e) {
  docUpload.file = e.target.files[0] ?? null
}

async function uploadDoc() {
  if (!docUpload.type || !docUpload.file) return
  docUploading.value = true
  try {
    const fd = new FormData()
    fd.append('type', docUpload.type)
    fd.append('file', docUpload.file)
    const { data } = await adminApi.uploadTutorDocument(route.params.tutorId, fd)
    // Replace any existing doc of the same type, then add the new one
    documents.value = documents.value.filter(d => d.type !== data.data.type)
    documents.value.push(data.data)
    docUpload.type = ''
    docUpload.file = null
    toast.success('Document uploaded.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not upload document.')
  } finally {
    docUploading.value = false
  }
}

function confirmDeleteDoc(doc) {
  deleteDocTarget.value = doc
}

async function doDeleteDoc() {
  const doc = deleteDocTarget.value
  deleteDocTarget.value = null
  if (!doc) return
  docDeleting[doc.id] = true
  try {
    await adminApi.deleteTutorDocument(route.params.tutorId, doc.id)
    documents.value = documents.value.filter(d => d.id !== doc.id)
    toast.success('Document deleted.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not delete document.')
  } finally {
    docDeleting[doc.id] = false
  }
}

// ── Video handlers ────────────────────────────────────────

function startEditVideo(vid) {
  videoEditing[vid.id] = {
    title:       vid.title ?? '',
    subject:     vid.subject ?? '',
    class_level: vid.class_level ?? '',
    medium:      vid.medium ?? '',
  }
}

function cancelEditVideo(vid) {
  delete videoEditing[vid.id]
}

async function saveVideo(vid) {
  videoSaving[vid.id] = true
  try {
    const { data } = await adminApi.updateTutorVideo(route.params.tutorId, vid.id, { ...videoEditing[vid.id] })
    const idx = videos.value.findIndex(v => v.id === vid.id)
    if (idx !== -1) videos.value[idx] = { ...videos.value[idx], ...data.data }
    delete videoEditing[vid.id]
    toast.success('Video updated.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not update video.')
  } finally {
    videoSaving[vid.id] = false
  }
}

function confirmDeleteVideo(vid) {
  deleteVideoTarget.value = vid
}

async function doDeleteVideo() {
  const vid = deleteVideoTarget.value
  deleteVideoTarget.value = null
  if (!vid) return
  videoDeleting[vid.id] = true
  try {
    await adminApi.deleteTutorVideo(route.params.tutorId, vid.id)
    videos.value = videos.value.filter(v => v.id !== vid.id)
    toast.success('Video deleted.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not delete video.')
  } finally {
    videoDeleting[vid.id] = false
  }
}

async function save() {
  saveConfirmOpen.value = false
  saving.value = true
  try {
    await adminApi.updateTutor(route.params.tutorId, { ...form })
    toast.success('Profile updated successfully.')
    router.push({ name: 'admin-tutor-detail', params: { tutorId: route.params.tutorId } })
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not save changes.')
  } finally {
    saving.value = false
  }
}

function confirmCancel() {
  cancelConfirmOpen.value = false
  router.push({ name: 'admin-tutor-detail', params: { tutorId: route.params.tutorId } })
}
</script>

<style scoped>
.section-title { @apply font-display font-semibold text-navy-800 text-base mb-4 pb-2 border-b border-paper-100; }
</style>
