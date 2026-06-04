<template>
  <div>
    <h2 class="font-display font-semibold text-navy-700 text-lg mb-5">Education</h2>
    <div v-for="(entry, i) in entries" :key="i" class="border border-paper-200 rounded-lg p-4 mb-4">
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Level</label>
          <DropSelect v-model="entry.level" :options="levelOptions" placeholder="Select level" />
        </div>
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Institute</label>
          <input v-model="entry.institute_name" type="text" class="input text-sm" placeholder="University / School name" />
        </div>
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Degree / Subject</label>
          <input v-model="entry.degree_title" type="text" class="input text-sm" placeholder="B.Sc. Computer Science" />
        </div>
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Year of passing</label>
          <input v-model.number="entry.year_of_passing" type="number" class="input text-sm" placeholder="2020" />
        </div>
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Result / GPA</label>
          <input v-model="entry.result" type="text" class="input text-sm" placeholder="3.75 / CGPA 4.0" />
        </div>
      </div>
      <button @click="openRemoveDialog(i)"
        class="mt-3 inline-flex items-center justify-center rounded-sm bg-red-600 px-3 py-1.5 text-xs font-semibold font-display text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
        Remove
      </button>
    </div>
    <div class="flex flex-col sm:flex-row gap-3 mt-2">
      <button @click="addEntry" class="btn-outline text-sm py-2 px-4">+ Add Education</button>
      <button @click="saveConfirmOpen = true" :disabled="saving" class="btn-primary text-sm py-2.5 px-6">{{ saving ? 'Saving…' : 'Save Education' }}</button>
    </div>

    <Teleport to="body">
      <Transition name="dialog">
        <div v-if="removeIndex !== null" class="fixed inset-0 z-[220] flex items-center justify-center px-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="removeIndex = null" />
          <div class="relative w-full max-w-sm overflow-hidden rounded-sm bg-white shadow-xl">
            <div class="bg-red-600 px-6 pt-8 pb-7 text-center">
              <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white/20">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
              </div>
              <h3 class="font-display text-xl font-bold text-white">Remove education?</h3>
              <p class="mt-1 text-xs font-body text-red-100">This change will be saved after you press Save Education.</p>
            </div>
            <div class="px-6 py-5">
              <p class="mb-5 text-center font-body text-sm text-paper-600">
                Remove this education entry from your profile changes?
              </p>
              <div class="flex gap-3">
                <button @click="removeIndex = null" class="btn-outline flex-1 py-2.5 text-sm">Cancel</button>
                <button @click="confirmRemove"
                  class="flex-1 rounded-md bg-red-600 py-2.5 font-display text-sm font-semibold text-white transition-colors hover:bg-red-700">
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <ConfirmDialog
      :show="saveConfirmOpen"
      title="Save Education?"
      message="Save your education entries? Changes will be submitted for admin review before going live."
      confirm-label="Save Education"
      @confirm="saveConfirmOpen = false; save()"
      @cancel="saveConfirmOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const emit = defineEmits(['saved'])
const saving = ref(false)
const saveConfirmOpen = ref(false)
const entries = ref([])
const deletedEntryIds = ref([])
const removeIndex = ref(null)
const levels = [
  { value: 'phd', label: 'PhD' }, { value: 'masters', label: 'Masters' },
  { value: 'bachelor', label: 'Bachelor' }, { value: 'hsc', label: 'HSC' },
  { value: 'ssc', label: 'SSC' }, { value: 'o_level', label: 'O Level' },
  { value: 'a_level', label: 'A Level' }, { value: 'other', label: 'Other' },
]
const levelOptions = [{ value: '', label: 'Select level' }, ...levels]

onMounted(async () => {
  const { data } = await tutorApi.getEducation()
  entries.value = data.data.length ? data.data : [emptyEntry()]
})

function emptyEntry() {
  return { level: '', institute_name: '', degree_title: '', year_of_passing: '', result: '' }
}
function addEntry() { entries.value.push(emptyEntry()) }
function openRemoveDialog(i) {
  removeIndex.value = i
}

function confirmRemove() {
  if (removeIndex.value === null) return
  removeEntry(removeIndex.value)
  removeIndex.value = null
}

function removeEntry(i) {
  const [entry] = entries.value.splice(i, 1)
  if (entry?.id) deletedEntryIds.value.push(entry.id)
}

async function save() {
  saving.value = true
  try {
    let hasPendingResponse = false
    for (const entry of entries.value) {
      if (entry.id) {
        const res = await tutorApi.updateEducation(entry.id, entry)
        hasPendingResponse = hasPendingResponse || !!res.data?.pending
      } else if (entry.institute_name) {
        const res = await tutorApi.addEducation(entry)
        hasPendingResponse = hasPendingResponse || !!res.data?.pending
      }
    }
    for (const id of deletedEntryIds.value) {
      const res = await tutorApi.deleteEducation(id)
      hasPendingResponse = hasPendingResponse || !!res.data?.pending
    }
    deletedEntryIds.value = []
    emit('saved', hasPendingResponse)
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.dialog-enter-active { transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.dialog-leave-active { transition: all 0.15s ease-in; }
.dialog-enter-from   { opacity: 0; transform: scale(0.88) translateY(8px); }
.dialog-leave-to     { opacity: 0; transform: scale(0.95) translateY(4px); }
</style>
