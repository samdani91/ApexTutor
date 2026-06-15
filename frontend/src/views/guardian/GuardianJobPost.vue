<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <RouterLink to="/guardian/jobs" class="text-xs font-semibold font-display text-navy-600 hover:underline">← Back to My Jobs</RouterLink>
      <h1 class="mt-3 font-display font-bold text-2xl text-navy-900">Post a Tuition Job</h1>
      <p class="mt-1 text-sm font-body text-paper-500">Fill in the details. Tutors will apply and you can shortlist from there.</p>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- Tuition Type -->
      <div class="card space-y-5">
        <h2 class="font-display font-semibold text-navy-800 text-base">Basic Details</h2>

        <div>
          <label class="field-label">Tuition Type <span class="text-red-500">*</span></label>
          <div class="flex flex-wrap gap-2 mt-1.5">
            <button type="button" v-for="t in tuitionTypes" :key="t.value"
              @click="form.tuition_type = t.value"
              class="choice-btn"
              :class="form.tuition_type === t.value ? 'choice-btn-active' : 'choice-btn-idle'">
              {{ t.label }}
            </button>
          </div>
          <p v-if="errors.tuition_type" class="field-error">{{ errors.tuition_type }}</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="field-label">District <span class="text-red-500">*</span></label>
            <select v-model="form.district_id" @change="form.area_id = null; loadAreas()" class="input mt-1">
              <option value="">Select district</option>
              <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <p v-if="errors.district_id" class="field-error">{{ errors.district_id }}</p>
          </div>
          <div>
            <label class="field-label">Area</label>
            <select v-model="form.area_id" class="input mt-1" :disabled="!form.district_id">
              <option value="">Select area (optional)</option>
              <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="field-label">Address Details</label>
          <input v-model="form.address_details" type="text" placeholder="Street, landmark, building name…" class="input mt-1" maxlength="500" />
        </div>
      </div>

      <!-- Student Info -->
      <div class="card space-y-5">
        <h2 class="font-display font-semibold text-navy-800 text-base">Student Information</h2>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="field-label">Class Level <span class="text-red-500">*</span></label>
            <select v-model="form.class_level" @change="form.subject_ids = []; loadSubjects()" class="input mt-1">
              <option value="">Select class</option>
              <option v-for="c in classOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
            </select>
            <p v-if="errors.class_level" class="field-error">{{ errors.class_level }}</p>
          </div>
          <div>
            <label class="field-label">No. of Students <span class="text-red-500">*</span></label>
            <input v-model.number="form.num_students" type="number" min="1" max="20" class="input mt-1" />
            <p v-if="errors.num_students" class="field-error">{{ errors.num_students }}</p>
          </div>
        </div>

        <div>
          <label class="field-label">Subjects <span class="text-red-500">*</span></label>
          <p class="text-xs text-paper-400 font-body mb-2">Select class first, then pick subjects.</p>
          <div v-if="subjects.length" class="flex flex-wrap gap-2">
            <button type="button" v-for="s in subjects" :key="s.id"
              @click="toggleSubject(s.id)"
              class="choice-btn"
              :class="form.subject_ids.includes(s.id) ? 'choice-btn-active' : 'choice-btn-idle'">
              {{ s.name }}
            </button>
          </div>
          <p v-else-if="form.class_level" class="text-sm text-paper-400 font-body">No subjects found for this class.</p>
          <p v-else class="text-sm text-paper-400 font-body">Select a class level first.</p>
          <p v-if="errors.subject_ids" class="field-error mt-1">{{ errors.subject_ids }}</p>
        </div>

        <div>
          <label class="field-label">Student Gender <span class="text-red-500">*</span></label>
          <div class="flex flex-wrap gap-2 mt-1.5">
            <button type="button" v-for="g in genderOptions" :key="g.value"
              @click="form.student_gender = g.value"
              class="choice-btn"
              :class="form.student_gender === g.value ? 'choice-btn-active' : 'choice-btn-idle'">
              {{ g.label }}
            </button>
          </div>
        </div>

        <div>
          <label class="field-label">Student's Institute</label>
          <input v-model="form.student_institute" type="text" placeholder="e.g. Viqarunnisa Noon School" class="input mt-1" maxlength="255" />
        </div>
      </div>

      <!-- Requirements -->
      <div class="card space-y-5">
        <h2 class="font-display font-semibold text-navy-800 text-base">Tutoring Requirements</h2>

        <div>
          <label class="field-label">Preferred Tutor Gender <span class="text-red-500">*</span></label>
          <div class="flex flex-wrap gap-2 mt-1.5">
            <button type="button" v-for="g in tutorGenderOptions" :key="g.value"
              @click="form.tutor_gender_pref = g.value"
              class="choice-btn"
              :class="form.tutor_gender_pref === g.value ? 'choice-btn-active' : 'choice-btn-idle'">
              {{ g.label }}
            </button>
          </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="field-label">Offered Salary (BDT/month) <span class="text-red-500">*</span></label>
            <input v-model.number="form.offered_salary" type="number" min="500" max="100000" class="input mt-1" placeholder="e.g. 6000" />
            <p v-if="errors.offered_salary" class="field-error">{{ errors.offered_salary }}</p>
          </div>
          <div>
            <label class="field-label">Days Per Week</label>
            <div class="flex flex-wrap gap-1.5 mt-1.5">
              <button type="button" v-for="d in [1,2,3,4,5,6,7]" :key="d"
                @click="form.tutoring_days_per_week = form.tutoring_days_per_week === d ? null : d"
                class="w-9 h-9 rounded-sm text-sm font-semibold font-display border transition-colors"
                :class="form.tutoring_days_per_week === d ? 'choice-btn-active' : 'choice-btn-idle'">
                {{ d }}
              </button>
            </div>
          </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="field-label">Preferred Start Time</label>
            <div class="flex gap-2 mt-1">
              <select v-model="timeHour" class="input flex-1">
                <option value="">HH</option>
                <option v-for="h in hours" :key="h" :value="h">{{ String(h).padStart(2,'0') }}</option>
              </select>
              <select v-model="timeMinute" class="input flex-1">
                <option value="">MM</option>
                <option v-for="m in ['00','15','30','45']" :key="m" :value="m">{{ m }}</option>
              </select>
              <select v-model="timePeriod" class="input flex-1">
                <option value="AM">AM</option>
                <option value="PM">PM</option>
              </select>
            </div>
          </div>
          <div>
            <label class="field-label">Expected Hire Date</label>
            <input v-model="form.hire_date" type="date" class="input mt-1" :min="today" />
          </div>
        </div>

        <div>
          <label class="field-label">Additional Requirements</label>
          <textarea v-model="form.extra_requirements" rows="4"
            placeholder="Any additional details about the tuition…"
            class="input mt-1 resize-none" maxlength="1000"></textarea>
          <p class="text-xs text-paper-400 font-body mt-1 text-right">{{ (form.extra_requirements || '').length }}/1000</p>
        </div>
      </div>

      <div class="flex gap-3">
        <button type="submit" :disabled="submitting" class="btn-primary py-2.5 px-6 text-sm disabled:opacity-50">
          {{ submitting ? 'Posting…' : 'Post Job' }}
        </button>
        <RouterLink to="/guardian/jobs" class="py-2.5 px-6 text-sm font-semibold font-display border border-paper-300 rounded-sm text-paper-700 hover:bg-paper-100 transition-colors">
          Cancel
        </RouterLink>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { guardianJobsApi } from '@/api/jobs.js'
import { searchApi } from '@/api/search.js'
import { toast } from 'vue-sonner'

const router = useRouter()

const form = ref({
  tuition_type:           '',
  district_id:            '',
  area_id:                null,
  address_details:        '',
  class_level:            '',
  subject_ids:            [],
  student_gender:         'any',
  num_students:           1,
  tutor_gender_pref:      'any',
  offered_salary:         '',
  hire_date:              '',
  tutoring_days_per_week: null,
  student_institute:      '',
  extra_requirements:     '',
  tutoring_time:          '',
})

const errors    = ref({})
const submitting = ref(false)
const districts  = ref([])
const areas      = ref([])
const subjects   = ref([])
const timeHour   = ref('')
const timeMinute = ref('00')
const timePeriod = ref('PM')

const today = new Date().toISOString().split('T')[0]
const hours = Array.from({ length: 12 }, (_, i) => i + 1)

const tuitionTypes = [
  { value: 'home',          label: 'Home Tutoring' },
  { value: 'online',        label: 'Online' },
  { value: 'group',         label: 'Group' },
  { value: 'home_and_online', label: 'Home & Online' },
]
const classOptions = [
  { value: 'class_1', label: 'Class 1' }, { value: 'class_2', label: 'Class 2' },
  { value: 'class_3', label: 'Class 3' }, { value: 'class_4', label: 'Class 4' },
  { value: 'class_5', label: 'Class 5' }, { value: 'class_6', label: 'Class 6' },
  { value: 'class_7', label: 'Class 7' }, { value: 'class_8', label: 'Class 8' },
  { value: 'class_9', label: 'Class 9' }, { value: 'class_10', label: 'Class 10' },
  { value: 'ssc', label: 'SSC' }, { value: 'hsc', label: 'HSC' },
  { value: 'o_level', label: 'O Level' }, { value: 'a_level', label: 'A Level' },
  { value: 'admission_test', label: 'Admission Test' },
]
const genderOptions = [
  { value: 'male', label: 'Male' }, { value: 'female', label: 'Female' }, { value: 'any', label: 'Any' },
]
const tutorGenderOptions = [
  { value: 'male', label: 'Male' }, { value: 'female', label: 'Female' }, { value: 'any', label: 'Any' },
]

async function loadAreas() {
  if (!form.value.district_id) { areas.value = []; return }
  const { data } = await searchApi.areas(form.value.district_id)
  areas.value = data.data || []
}

async function loadSubjects() {
  if (!form.value.class_level) { subjects.value = []; return }
  const { data } = await searchApi.subjects({ class_level: form.value.class_level })
  subjects.value = data.data || []
}

function toggleSubject(id) {
  const idx = form.value.subject_ids.indexOf(id)
  if (idx === -1) form.value.subject_ids.push(id)
  else form.value.subject_ids.splice(idx, 1)
}

function buildPayload() {
  const payload = { ...form.value }
  if (timeHour.value) {
    let h = parseInt(timeHour.value)
    if (timePeriod.value === 'PM' && h !== 12) h += 12
    if (timePeriod.value === 'AM' && h === 12) h = 0
    payload.tutoring_time = `${String(h).padStart(2,'0')}:${timeMinute.value || '00'}`
  }
  if (!payload.area_id) payload.area_id = null
  if (!payload.hire_date) delete payload.hire_date
  if (!payload.tutoring_days_per_week) delete payload.tutoring_days_per_week
  return payload
}

async function submit() {
  errors.value = {}
  if (!form.value.tuition_type)   errors.value.tuition_type  = 'Please select a tuition type.'
  if (!form.value.district_id)    errors.value.district_id   = 'Please select a district.'
  if (!form.value.class_level)    errors.value.class_level   = 'Please select a class level.'
  if (!form.value.subject_ids.length) errors.value.subject_ids = 'Please select at least one subject.'
  if (!form.value.offered_salary) errors.value.offered_salary = 'Please enter the offered salary.'
  if (!form.value.num_students)   errors.value.num_students   = 'Please enter number of students.'
  if (Object.keys(errors.value).length) return

  submitting.value = true
  try {
    await guardianJobsApi.post(buildPayload())
    toast.success('Job posted successfully!')
    router.push('/guardian/jobs')
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) {
      Object.entries(data.errors).forEach(([k, v]) => { errors.value[k] = v[0] })
    } else {
      toast.error(data?.message || 'Failed to post job.')
    }
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  const { data } = await searchApi.districts()
  districts.value = data.data || []
})
</script>

<style scoped>
.field-label { @apply block text-sm font-semibold font-display text-navy-800; }
.field-error { @apply text-xs text-red-600 font-body mt-1; }
.choice-btn  { @apply px-3.5 py-1.5 rounded-sm text-sm font-semibold font-display border transition-colors focus:outline-none; }
.choice-btn-active { @apply bg-navy-700 text-white border-navy-700; }
.choice-btn-idle   { @apply bg-white text-navy-700 border-paper-300 hover:border-navy-400 hover:bg-navy-50; }
</style>
