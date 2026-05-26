<template>
  <div>
    <h2 class="font-display font-semibold text-navy-700 text-lg mb-2">Documents</h2>
    <p class="text-sm text-paper-500 font-body mb-6">
      Upload all four documents for verification. Max 5 MB per file — PDF, JPG, or PNG accepted.
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
          <div class="flex items-center gap-2 shrink-0">
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
              @click="deleteDoc(uploaded(slot.type).id)"
              class="text-red-500 hover:text-red-600 text-xs font-semibold font-display px-2 py-1.5 rounded-sm hover:bg-red-50 transition-colors">
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'

const emit = defineEmits(['saved'])

const DOC_SLOTS = [
  { type: 'university_id',  label: 'University ID',            hint: 'Current student ID card from your university' },
  { type: 'nid',            label: 'National ID (NID)',         hint: 'Bangladesh National Identity Card' },
  { type: 'ssc_marksheet',  label: 'SSC / O Level Marksheet',  hint: 'Secondary school certificate or O Level result sheet' },
  { type: 'hsc_marksheet',  label: 'HSC / A Level Marksheet',  hint: 'Higher secondary certificate or A Level result sheet' },
]

const documents = ref([])
const uploading = ref(null)  // type string while uploading

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

  // If a doc of this type already exists, delete it first
  const existing = uploaded(type)
  if (existing) {
    await tutorApi.deleteDocument(existing.id)
    documents.value = documents.value.filter(d => d.id !== existing.id)
  }

  uploading.value = type
  try {
    const fd = new FormData()
    fd.append('type', type)
    fd.append('file', file)
    const { data } = await tutorApi.uploadDocument(fd)
    documents.value.push(data.data)
    emit('saved')
  } finally {
    uploading.value = null
  }
}

async function deleteDoc(id) {
  await tutorApi.deleteDocument(id)
  documents.value = documents.value.filter(d => d.id !== id)
}
</script>
