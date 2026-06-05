<template>
  <div>
    <h2 class="font-display font-semibold text-navy-700 text-lg mb-2">Documents</h2>
    <p class="text-sm text-paper-500 font-body mb-6">
      Upload all five documents for verification. Max 5 MB per file — PDF, JPG, or PNG accepted.
    </p>

    <div class="space-y-4">
      <div v-for="slot in DOC_SLOTS" :key="slot.type"
        class="border rounded-sm p-4 transition-colors shadow-xs"
        :class="uploaded(slot.type) ? 'border-green-200 bg-green-50' : 'border-paper-200 bg-white'">

        <div class="flex items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-display font-semibold text-sm text-navy-900">{{ slot.label }}</p>
            <p class="text-xs text-paper-400 font-body mt-0.5">{{ slot.hint }}</p>

            <!-- Uploaded state -->
            <div v-if="uploaded(slot.type)" class="mt-2 flex items-center gap-2 flex-wrap">
              <span class="text-xs font-body text-navy-700 truncate max-w-[200px]">{{ uploaded(slot.type).file_name }}</span>
              <span v-if="uploaded(slot.type).review_status === 'approved'" class="badge-verified text-xs">Approved</span>
              <span v-else-if="uploaded(slot.type).review_status === 'rejected'"
                class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill bg-red-50 text-red-700">Rejected</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
            <!-- View button (only when uploaded) -->
            <button v-if="uploaded(slot.type)"
              @click="viewDoc(uploaded(slot.type))"
              class="inline-flex items-center gap-1.5 border border-navy-200 bg-navy-50 px-3 py-1.5 rounded-sm text-xs font-semibold font-display text-navy-700 hover:bg-navy-100 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              View
            </button>

            <label class="cursor-pointer">
              <span class="inline-flex items-center gap-1.5 border border-paper-300 bg-white px-3 py-1.5 rounded-sm text-xs font-semibold font-display text-navy-700 hover:bg-navy-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4-4m0 0L8 12m4-4v12"/>
                </svg>
                {{ uploaded(slot.type) ? 'Replace' : 'Upload' }}
              </span>
              <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png"
                @change="e => onFileSelected(slot.type, e)" />
            </label>

            <button v-if="uploaded(slot.type)"
              @click="openRemoveDialog(uploaded(slot.type))"
              class="rounded-sm bg-red-600 px-3 py-1.5 text-xs font-semibold font-display text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
              Remove
            </button>
          </div>
        </div>

        <!-- Upload progress indicator -->
        <div v-if="uploading === slot.type" class="mt-3 flex items-center gap-2">
          <div class="w-4 h-4 border-2 border-navy-200 border-t-navy-700 rounded-full animate-spin"></div>
          <span class="text-xs font-body text-navy-500">Uploading…</span>
        </div>
      </div>
    </div>

    <!-- Image preview modal -->
    <Teleport to="body">
      <Transition name="dialog">
        <div v-if="previewDoc" class="fixed inset-0 z-[230] flex items-center justify-center px-4"
          @click.self="previewDoc = null">
          <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="previewDoc = null" />
          <div class="relative w-full max-w-2xl bg-white rounded-sm shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-3 border-b border-paper-200">
              <div class="min-w-0">
                <p class="font-display font-semibold text-sm text-navy-900 truncate">{{ previewDoc.file_name }}</p>
                <p class="text-xs text-paper-400 font-body mt-0.5 capitalize">{{ previewDoc.type.replace(/_/g, ' ') }}</p>
              </div>
              <button @click="previewDoc = null"
                class="ml-3 shrink-0 w-8 h-8 flex items-center justify-center rounded-md text-paper-400 hover:bg-paper-100 hover:text-navy-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <!-- Image preview -->
            <div class="p-4 flex items-center justify-center bg-paper-50 min-h-[200px] max-h-[70vh] overflow-auto">
              <img :src="previewDoc.file_url" :alt="previewDoc.file_name"
                class="max-w-full max-h-[60vh] object-contain rounded-sm shadow-sm" />
            </div>
            <!-- Footer -->
            <div class="px-5 py-3 border-t border-paper-200 flex justify-end">
              <a :href="previewDoc.file_url" target="_blank" rel="noopener"
                class="inline-flex items-center gap-1.5 text-xs font-semibold font-display text-navy-700 border border-paper-300 bg-white px-3 py-1.5 rounded-sm hover:bg-navy-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
                Open in new tab
              </a>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

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
              <h3 class="font-display text-xl font-bold text-white">Remove document?</h3>
              <p class="mt-1 text-xs font-body text-red-100">Verified profile changes require admin review.</p>
            </div>
            <div class="px-6 py-5">
              <p class="mb-5 text-center font-body text-sm text-paper-600">
                Remove <strong>{{ removeTarget?.file_name || 'this document' }}</strong>?
              </p>
              <div class="flex gap-3">
                <button @click="removeTarget = null" class="btn-outline flex-1 py-2.5 text-sm">Cancel</button>
                <button @click="confirmRemove" :disabled="removing"
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
import { ref, computed, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'

const emit = defineEmits(['saved'])

const DOC_SLOTS = [
  { type: 'nid',                 label: 'National ID (NID)',                  hint: 'Bangladesh National Identity Card' },
  { type: 'ssc_marksheet',       label: 'SSC / O Level Marksheet',           hint: 'Secondary school certificate or O Level result sheet' },
  { type: 'hsc_marksheet',       label: 'HSC / A Level Marksheet',           hint: 'Higher secondary certificate or A Level result sheet' },
  { type: 'emergency_contact_nid', label: 'Emergency Contact NID',           hint: "National ID card of your emergency contact person" },
]

const documents   = ref([])
const uploading   = ref(null)
const removeTarget = ref(null)
const removing    = ref(false)
const previewDoc  = ref(null)

function viewDoc(doc) {
  if (!doc.file_url) return
  // PDFs open in a new tab; images show in the preview modal
  if (doc.mime_type === 'application/pdf') {
    window.open(doc.file_url, '_blank', 'noopener')
  } else {
    previewDoc.value = doc
  }
}

onMounted(async () => {
  const { data } = await tutorApi.getDocuments()
  documents.value = data.data || []
})

const uploaded = (type) => documents.value.find(d => d.type === type) || null

const allUploaded = computed(() => DOC_SLOTS.every(s => uploaded(s.type)))

async function onFileSelected(type, e) {
  const file = e.target.files[0]
  if (!file) return
  e.target.value = ''

  uploading.value = type
  try {
    const fd = new FormData()
    fd.append('type', type)
    fd.append('file', file)
    const { data } = await tutorApi.uploadDocument(fd)
    documents.value = documents.value.filter(doc => doc.type !== type)
    documents.value.push(data.data)
    emit('saved', !!data.pending)
  } finally {
    uploading.value = null
  }
}

function openRemoveDialog(doc) {
  removeTarget.value = doc
}

async function confirmRemove() {
  if (!removeTarget.value) return
  const id = removeTarget.value.id
  removeTarget.value = null
  await deleteDoc(id)
}

async function deleteDoc(id) {
  removing.value = true
  try {
    const { data } = await tutorApi.deleteDocument(id)
  documents.value = documents.value.filter(d => d.id !== id)
    emit('saved', !!data.pending)
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
