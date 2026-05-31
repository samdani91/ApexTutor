<template>
  <div>
    <h2 class="font-display font-semibold text-navy-700 text-lg mb-1">Teaching videos</h2>
    <p class="text-sm text-paper-500 font-body mb-6">
      Upload up to 4 videos showcasing your teaching style. Each video needs a title, subject, class, and medium.
      Max 150 MB per video — MP4, WebM, or MOV.
    </p>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center gap-2 py-6">
      <div class="w-4 h-4 border-2 border-navy-200 border-t-navy-700 rounded-full animate-spin"></div>
      <span class="text-sm text-paper-500 font-body">Loading…</span>
    </div>

    <template v-else>
      <!-- Uploaded videos list -->
      <div v-if="videos.length" class="space-y-4 mb-6">
        <div v-for="vid in videos" :key="vid.id" class="border border-paper-200 rounded-sm overflow-hidden bg-white shadow-xs">
          <!-- Video player -->
          <video :src="vid.file_url" controls
            class="w-full bg-black max-h-52" preload="metadata" />

          <!-- Metadata display / edit -->
          <div class="p-4">
            <div v-if="editingId !== vid.id">
              <!-- View mode -->
              <div class="flex items-start justify-between gap-3 flex-wrap">
                <div>
                  <p class="font-display font-semibold text-navy-900 text-sm mb-2">{{ vid.title || '(No title)' }}</p>
                  <div class="flex flex-wrap gap-1.5">
                    <span v-if="vid.subject" class="text-xs bg-blue-50 text-blue-700 border border-blue-100 px-2 py-0.5 rounded-pill font-body">
                      {{ vid.subject }}
                    </span>
                    <span v-if="vid.class_level" class="text-xs bg-navy-50 text-navy-700 border border-navy-100 px-2 py-0.5 rounded-pill font-body">
                      Class {{ vid.class_level }}
                    </span>
                    <span v-if="vid.medium" class="text-xs bg-paper-100 text-paper-600 border border-paper-200 px-2 py-0.5 rounded-pill font-body capitalize">
                      {{ vid.medium }}
                    </span>
                    <span :class="vid.review_status === 'approved' ? 'badge-verified' : 'badge-pending'" class="text-xs">
                      {{ vid.review_status }}
                    </span>
                  </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <button @click="startEdit(vid)"
                    class="text-xs font-semibold font-display border border-paper-300 px-3 py-1.5 rounded-sm text-navy-700 hover:bg-navy-50 transition-colors">
                    Edit info
                  </button>
                  <button @click="openRemoveDialog(vid)"
                    class="text-xs font-semibold font-display px-3 py-1.5 rounded-sm bg-red-600 text-white hover:bg-red-700 transition-colors">
                    Remove
                  </button>
                </div>
              </div>
            </div>

            <div v-else>
              <!-- Edit mode -->
              <form @submit.prevent="saveEdit(vid.id)" class="space-y-3">
                <div>
                  <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Video title</label>
                  <input v-model="editForm.title" type="text" class="input text-sm" placeholder="e.g. Introduction to Algebra" required maxlength="200" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Subject</label>
                    <input v-model="editForm.subject" type="text" class="input text-sm" placeholder="e.g. Mathematics" required maxlength="100" />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Class / Grade</label>
                    <input v-model="editForm.class_level" type="text" class="input text-sm" placeholder="e.g. 9–10" required maxlength="100" />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Medium</label>
                    <DropSelect v-model="editForm.medium" :options="mediumOptions" placeholder="Select…" />
                  </div>
                </div>
                <div class="flex gap-2 pt-1">
                  <button type="submit" :disabled="saving" class="btn-primary text-xs py-1.5 px-4">
                    {{ saving ? 'Saving…' : 'Save' }}
                  </button>
                  <button type="button" @click="editingId = null" class="btn-outline text-xs py-1.5 px-4">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Upload new video (shown if < 4 videos) -->
      <div v-if="videos.length < 4">
        <p v-if="videos.length" class="text-xs font-semibold font-display text-navy-600 mb-3">
          Add another video ({{ videos.length }}/4)
        </p>

        <!-- Step: pick file first, then fill metadata -->
        <div v-if="!pendingFile">
          <label
            class="flex flex-col items-center justify-center border-2 border-dashed rounded-sm p-8 cursor-pointer transition-colors"
            :class="dragging ? 'border-navy-400 bg-navy-50' : 'border-paper-300 bg-paper-50 hover:border-navy-300 hover:bg-navy-50'"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop">
            <svg class="w-9 h-9 text-navy-200 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
            </svg>
            <p class="font-display font-semibold text-navy-700 text-sm mb-1">Click to select or drag & drop</p>
            <p class="text-xs text-paper-400 font-body">MP4, WebM, MOV · max 150 MB</p>
            <input type="file" class="hidden" accept="video/mp4,video/webm,video/quicktime"
              @change="onFileSelected" />
          </label>
        </div>

        <!-- Step: fill metadata before uploading -->
        <div v-else class="border border-navy-200 rounded-sm p-5 bg-navy-50/30">
          <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-navy-600 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
            </svg>
            <div class="min-w-0">
              <p class="font-display font-semibold text-sm text-navy-900 truncate">{{ pendingFile.name }}</p>
              <p class="text-xs text-paper-400 font-body">{{ formatSize(pendingFile.size) }}</p>
            </div>
            <button @click="pendingFile = null" class="ml-auto text-paper-400 hover:text-red-500 transition-colors shrink-0" type="button">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <form @submit.prevent="uploadWithMeta" class="space-y-3">
            <div>
              <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Video title <span class="text-red-500">*</span></label>
              <input v-model="newForm.title" type="text" class="input text-sm" placeholder="e.g. Solving Quadratic Equations" required maxlength="200" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Subject <span class="text-red-500">*</span></label>
                <input v-model="newForm.subject" type="text" class="input text-sm" placeholder="e.g. Mathematics" required maxlength="100" />
              </div>
              <div>
                <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Class / Grade <span class="text-red-500">*</span></label>
                <input v-model="newForm.class_level" type="text" class="input text-sm" placeholder="e.g. 9–10" required maxlength="100" />
              </div>
              <div>
                <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Medium <span class="text-red-500">*</span></label>
                <DropSelect v-model="newForm.medium" :options="mediumOptions" placeholder="Select…" />
              </div>
            </div>

            <!-- Upload progress -->
            <div v-if="uploading" class="flex items-center gap-3 pt-1">
              <div class="w-4 h-4 border-2 border-navy-200 border-t-navy-700 rounded-full animate-spin shrink-0"></div>
              <div class="flex-1">
                <div class="h-1.5 bg-paper-200 rounded-full overflow-hidden">
                  <div class="h-full bg-navy-700 rounded-full transition-all duration-300" :style="`width:${uploadProgress}%`"></div>
                </div>
              </div>
              <span class="text-xs text-paper-500 font-body shrink-0">{{ uploadProgress }}%</span>
            </div>

            <div class="flex gap-2 pt-1">
              <button type="submit" :disabled="uploading" class="btn-primary text-sm py-2 px-5">
                {{ uploading ? 'Uploading…' : 'Upload video' }}
              </button>
              <button type="button" @click="pendingFile = null" :disabled="uploading" class="btn-outline text-sm py-2 px-4">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Max reached notice -->
      <div v-if="videos.length >= 4" class="mt-2 text-xs text-paper-500 font-body text-center">
        Maximum 4 videos uploaded. Remove one to add another.
      </div>
    </template>

    <Teleport to="body">
      <Transition name="dialog">
        <div v-if="removeTarget" class="fixed inset-0 z-[220] flex items-center justify-center px-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="removeTarget = null" />
          <div class="relative w-full max-w-sm overflow-hidden rounded-sm bg-white shadow-xl">
            <div class="bg-red-600 px-6 pt-8 pb-7 text-center">
              <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white/20">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
              </div>
              <h3 class="font-display text-xl font-bold text-white">Remove video?</h3>
              <p class="mt-1 text-xs font-body text-red-100">This action will remove the uploaded teaching video.</p>
            </div>
            <div class="px-6 py-5">
              <p class="mb-5 text-center font-body text-sm text-paper-600">
                Remove <strong>{{ removeTarget?.title || 'this video' }}</strong>?
              </p>
              <div class="flex gap-3">
                <button @click="removeTarget = null" class="btn-outline flex-1 py-2.5 text-sm">Cancel</button>
                <button @click="confirmRemoveVideo" :disabled="removing"
                  class="flex-1 rounded-md bg-red-600 py-2.5 font-display text-sm font-semibold text-white transition-colors hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60">
                  {{ removing ? 'Removing…' : 'Remove' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'
import { toast } from 'vue-sonner'

const emit = defineEmits(['saved'])

const videos          = ref([])
const loading         = ref(true)
const uploading       = ref(false)
const uploadProgress  = ref(0)
const dragging        = ref(false)
const pendingFile     = ref(null)
const editingId       = ref(null)
const saving          = ref(false)
const removeTarget    = ref(null)
const removing        = ref(false)

const newForm  = reactive({ title: '', subject: '', class_level: '', medium: '' })
const editForm = reactive({ title: '', subject: '', class_level: '', medium: '' })
const mediumOptions = [
  { value: '', label: 'Select…' },
  { value: 'bangla', label: 'Bangla' },
  { value: 'english', label: 'English' },
  { value: 'bangla_english', label: 'Bangla & English' },
]

onMounted(async () => {
  try {
    const { data } = await tutorApi.getVideos()
    videos.value = data.data ?? []
  } finally {
    loading.value = false
  }
})

function formatSize(bytes) {
  if (!bytes) return ''
  return bytes < 1048576
    ? `${(bytes / 1024).toFixed(1)} KB`
    : `${(bytes / 1048576).toFixed(1)} MB`
}

function onDrop(e) {
  dragging.value = false
  const file = e.dataTransfer.files[0]
  if (file) selectFile(file)
}

function onFileSelected(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (file) selectFile(file)
}

function selectFile(file) {
  if (file.size > 157286400) {
    toast.error('File exceeds 150 MB limit.')
    return
  }
  pendingFile.value = file
}

async function uploadWithMeta() {
  if (!pendingFile.value) return
  uploading.value      = true
  uploadProgress.value = 0

  const fd = new FormData()
  fd.append('video',       pendingFile.value)
  fd.append('title',       newForm.title)
  fd.append('subject',     newForm.subject)
  fd.append('class_level', newForm.class_level)
  fd.append('medium',      newForm.medium)

  try {
    const { data } = await tutorApi.uploadVideo(fd, {
      onUploadProgress: (e) => {
        uploadProgress.value = Math.round((e.loaded / e.total) * 100)
      },
    })
    videos.value.push(data.data)
    pendingFile.value = null
    Object.assign(newForm, { title: '', subject: '', class_level: '', medium: '' })
    emit('saved')
    toast.success('Video uploaded successfully.')
  } catch (err) {
    const msg = err.response?.data?.message || err.response?.data?.errors?.video?.[0] || 'Upload failed. Please try again.'
    toast.error(msg)
  } finally {
    uploading.value = false
  }
}

function startEdit(vid) {
  editingId.value = vid.id
  Object.assign(editForm, {
    title:       vid.title       ?? '',
    subject:     vid.subject     ?? '',
    class_level: vid.class_level ?? '',
    medium:      vid.medium      ?? '',
  })
}

async function saveEdit(id) {
  saving.value = true
  try {
    const { data } = await tutorApi.updateVideo(id, { ...editForm })
    const idx = videos.value.findIndex(v => v.id === id)
    if (idx !== -1) videos.value[idx] = data.data
    editingId.value = null
    toast.success('Video info updated.')
  } catch {
    toast.error('Failed to save changes.')
  } finally {
    saving.value = false
  }
}

function openRemoveDialog(video) {
  removeTarget.value = video
}

async function confirmRemoveVideo() {
  if (!removeTarget.value) return
  const id = removeTarget.value.id
  removeTarget.value = null
  await removeVideo(id)
}

async function removeVideo(id) {
  removing.value = true
  try {
    await tutorApi.deleteVideo(id)
    videos.value = videos.value.filter(v => v.id !== id)
    toast.success('Video removed.')
  } catch {
    toast.error('Failed to remove video.')
  } finally {
    removing.value = false
  }
}
</script>

<style scoped>
.dialog-enter-active { transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.dialog-leave-active { transition: all 0.15s ease-in; }
.dialog-enter-from   { opacity: 0; transform: scale(0.88) translateY(8px); }
.dialog-leave-to     { opacity: 0; transform: scale(0.95) translateY(4px); }
</style>
