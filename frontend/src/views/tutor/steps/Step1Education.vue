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
      <button @click="removeEntry(i)" class="mt-3 text-red-500 text-xs font-semibold hover:underline">Remove</button>
    </div>
    <div class="flex flex-col sm:flex-row gap-3 mt-2">
      <button @click="addEntry" class="btn-outline text-sm py-2 px-4">+ Add education</button>
      <button @click="save" :disabled="saving" class="btn-primary text-sm py-2.5 px-6">{{ saving ? 'Saving…' : 'Save education' }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'

const emit = defineEmits(['saved'])
const saving = ref(false)
const entries = ref([])
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
function removeEntry(i) { entries.value.splice(i, 1) }

async function save() {
  saving.value = true
  try {
    for (const entry of entries.value) {
      if (entry.id) await tutorApi.updateEducation(entry.id, entry)
      else if (entry.institute_name) await tutorApi.addEducation(entry)
    }
    emit('saved')
  } finally {
    saving.value = false
  }
}
</script>
