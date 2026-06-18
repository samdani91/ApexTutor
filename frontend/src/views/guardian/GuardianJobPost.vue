<template>
  <div class="space-y-6">

    <!-- Page header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="font-display text-xs font-bold uppercase text-gold-600">Guardian · Post Job</p>
        <h1 class="mt-1 font-display font-bold text-2xl text-navy-900">Post a Tuition Job</h1>
        <p class="mt-1 text-sm font-body text-paper-500">
          Fill in the details below. Tutors will apply and you can shortlist from there.
        </p>
      </div>
      <RouterLink to="/guardian/jobs" class="back-btn self-start sm:self-auto shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
        My Jobs
      </RouterLink>
    </div>

    <form @submit.prevent="submit" novalidate class="space-y-5">

      <!-- ── Row 1: Basic + Location ──────────────────────────────── -->
      <div class="grid gap-5 lg:grid-cols-2">

        <!-- Basic Details -->
        <div class="card space-y-5">
          <div class="card-section-header">
            <span class="step-badge">1</span>
            <h2 class="card-section-title">Basic Details</h2>
          </div>

          <div>
            <label class="field-label">Place of Tutoring <span class="req">*</span></label>
            <div class="choice-group mt-2">
              <button type="button" v-for="p in placeOptions" :key="p.value"
                @click="form.tuition_type = p.value"
                class="choice-btn" :class="form.tuition_type === p.value ? 'choice-btn-active' : 'choice-btn-idle'">
                {{ p.label }}
              </button>
            </div>
            <p v-if="errors.tuition_type" class="field-error">{{ errors.tuition_type }}</p>
          </div>

          <div>
            <label class="field-label">Medium <span class="req">*</span></label>
            <div class="choice-group mt-2">
              <button type="button" v-for="m in mediumOptions" :key="m.value"
                @click="form.medium = form.medium === m.value ? '' : m.value"
                class="choice-btn" :class="form.medium === m.value ? 'choice-btn-active' : 'choice-btn-idle'">
                {{ m.label }}
              </button>
            </div>
            <p v-if="errors.medium" class="field-error">{{ errors.medium }}</p>
          </div>

          <div>
            <label class="field-label">Tutoring Style <span class="req">*</span></label>
            <div class="choice-group mt-2">
              <button type="button" v-for="s in styleOptions" :key="s.value"
                @click="form.tutoring_style = form.tutoring_style === s.value ? '' : s.value"
                class="choice-btn" :class="form.tutoring_style === s.value ? 'choice-btn-active' : 'choice-btn-idle'">
                {{ s.label }}
              </button>
            </div>
            <p v-if="errors.tutoring_style" class="field-error">{{ errors.tutoring_style }}</p>
          </div>

          <div>
            <label class="field-label">Offered Salary <span class="req">*</span></label>
            <div class="relative mt-1">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-paper-500 text-sm font-semibold select-none">৳</span>
              <input v-model.number="form.offered_salary" type="number" min="500" max="100000"
                placeholder="e.g. 6000" class="input pl-8" />
            </div>
            <div class="flex gap-2 mt-2 flex-wrap">
              <button v-for="p in salaryPresets" :key="p" type="button"
                @click="form.offered_salary = form.offered_salary === p ? '' : p"
                class="text-xs font-semibold font-display border px-2.5 py-1 rounded-pill transition-colors"
                :class="form.offered_salary === p ? 'bg-navy-700 text-white border-navy-700' : 'border-paper-200 text-navy-600 hover:bg-navy-50 bg-white'">
                ৳{{ p.toLocaleString() }}
              </button>
            </div>
            <p v-if="errors.offered_salary" class="field-error">{{ errors.offered_salary }}</p>
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="field-label">Days Per Week <span class="req">*</span></label>
              <div class="flex flex-wrap gap-1.5 mt-2">
                <button type="button" v-for="d in [1,2,3,4,5,6,7]" :key="d"
                  @click="form.tutoring_days_per_week = form.tutoring_days_per_week === d ? null : d"
                  class="w-9 h-9 rounded-sm text-sm font-semibold font-display border transition-colors"
                  :class="form.tutoring_days_per_week === d ? 'bg-navy-700 text-white border-navy-700' : 'bg-white text-navy-700 border-paper-300 hover:border-navy-400 hover:bg-navy-50'">
                  {{ d }}
                </button>
              </div>
              <p v-if="errors.tutoring_days_per_week" class="field-error mt-1">{{ errors.tutoring_days_per_week }}</p>
            </div>
            <div>
              <label class="field-label">Preferred Time <span class="req">*</span></label>
              <div class="flex gap-1.5 mt-1">
                <select v-model="timeHour" class="input flex-1 text-sm">
                  <option value="">HH</option>
                  <option v-for="h in hours" :key="h" :value="h">{{ String(h).padStart(2,'0') }}</option>
                </select>
                <select v-model="timeMinute" class="input flex-1 text-sm">
                  <option v-for="m in ['00','15','30','45']" :key="m" :value="m">{{ m }}</option>
                </select>
                <select v-model="timePeriod" class="input flex-1 text-sm">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
              <p v-if="errors.tutoring_time" class="field-error mt-1">{{ errors.tutoring_time }}</p>
            </div>
          </div>

          <div>
            <label class="field-label">Expected Hire Date <span class="req">*</span></label>
            <input v-model="form.hire_date" type="date" class="input mt-1 w-full" :min="today" />
            <p v-if="errors.hire_date" class="field-error mt-1">{{ errors.hire_date }}</p>
          </div>
        </div>

        <!-- Location -->
        <div class="card space-y-5">
          <div class="card-section-header">
            <span class="step-badge">2</span>
            <h2 class="card-section-title">Location</h2>
          </div>

          <div>
            <label class="field-label">District <span class="req">*</span></label>
            <DropSelect v-model="form.district_id" :options="districtOpts" placeholder="Select district"
              class="mt-1" @update:modelValue="onDistrictChange" />
            <p v-if="errors.district_id" class="field-error">{{ errors.district_id }}</p>
          </div>

          <div>
            <label class="field-label">Area <span class="req">*</span></label>
            <p v-if="!form.district_id" class="text-xs text-paper-400 font-body mt-1">Select a district first.</p>
            <template v-else>
              <p v-if="areasLoading" class="text-xs text-paper-400 font-body mt-1">Loading areas…</p>
              <DropSelect v-else v-model="form.area_id" :options="areaOpts" placeholder="Select area" class="mt-1" />
            </template>
            <p v-if="errors.area_id" class="field-error">{{ errors.area_id }}</p>
          </div>

          <div>
            <label class="field-label">Address Details <span class="req">*</span></label>
            <input v-model="form.address_details" type="text"
              placeholder="Street, landmark, building name…" class="input mt-1 w-full" maxlength="500" />
            <p class="text-xs text-paper-400 font-body mt-1">Helps shortlisted tutors understand the area better.</p>
            <p v-if="errors.address_details" class="field-error">{{ errors.address_details }}</p>
          </div>
        </div>
      </div>

      <!-- ── Row 2: Student + Requirements ────────────────────────── -->
      <div class="grid gap-5 lg:grid-cols-2">

        <!-- Student Info -->
        <div class="card space-y-5">
          <div class="card-section-header">
            <span class="step-badge">3</span>
            <h2 class="card-section-title">Student Information</h2>
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="field-label">Class <span class="req">*</span></label>
              <DropSelect v-model="form.class_level" :options="classOpts" placeholder="Select class"
                class="mt-1" @update:modelValue="onClassChange" />
              <p v-if="errors.class_level" class="field-error">{{ errors.class_level }}</p>
            </div>
            <div>
              <label class="field-label">No. of Students <span class="req">*</span></label>
              <input v-model.number="form.num_students" type="number" min="1" max="20" class="input mt-1 w-full" />
              <p v-if="errors.num_students" class="field-error">{{ errors.num_students }}</p>
            </div>
          </div>

          <div>
            <label class="field-label">Subjects <span class="req">*</span></label>
            <p v-if="!form.class_level" class="text-xs text-paper-400 font-body mt-1">Select a class first.</p>
            <p v-else-if="!subjects.length" class="text-xs text-paper-400 font-body mt-1">No subjects found for this class.</p>
            <div v-else class="choice-group mt-2">
              <button type="button" v-for="s in subjects" :key="s.id"
                @click="toggleSubject(s.id)"
                class="choice-btn" :class="form.subject_ids.includes(s.id) ? 'choice-btn-active' : 'choice-btn-idle'">
                {{ s.name }}
              </button>
            </div>
            <p v-if="errors.subject_ids" class="field-error mt-1">{{ errors.subject_ids }}</p>
          </div>

          <div>
            <label class="field-label">Student Gender <span class="req">*</span></label>
            <div class="choice-group mt-2">
              <button type="button" v-for="g in genderOptions" :key="g.value"
                @click="form.student_gender = g.value"
                class="choice-btn" :class="form.student_gender === g.value ? 'choice-btn-active' : 'choice-btn-idle'">
                {{ g.label }}
              </button>
            </div>
          </div>

          <div>
            <label class="field-label">Student's Institute <span class="req">*</span></label>
            <input v-model="form.student_institute" type="text"
              placeholder="e.g. Viqarunnisa Noon School" class="input mt-1 w-full" maxlength="255" />
            <p v-if="errors.student_institute" class="field-error mt-1">{{ errors.student_institute }}</p>
          </div>
        </div>

        <!-- Requirements -->
        <div class="card space-y-5">
          <div class="card-section-header">
            <span class="step-badge">4</span>
            <h2 class="card-section-title">Tutor Requirements</h2>
          </div>

          <div>
            <label class="field-label">Preferred Tutor Gender <span class="req">*</span></label>
            <div class="choice-group mt-2">
              <button type="button" v-for="g in tutorGenderOptions" :key="g.value"
                @click="form.tutor_gender_pref = g.value"
                class="choice-btn" :class="form.tutor_gender_pref === g.value ? 'choice-btn-active' : 'choice-btn-idle'">
                {{ g.label }}
              </button>
            </div>
          </div>

          <div>
            <label class="field-label">Additional Requirements <span class="optional-tag">optional</span></label>
            <textarea v-model="form.extra_requirements" rows="6"
              placeholder="Describe any special skills, teaching style, or other preferences…"
              class="input mt-1 resize-none w-full" maxlength="1000"></textarea>
            <p class="text-xs text-paper-400 font-body mt-1 text-right">
              {{ (form.extra_requirements || '').length }}/1000
            </p>
          </div>

          <!-- Submit inside this card so it sits adjacent to the last section -->
          <div class="pt-2 border-t border-paper-100 flex flex-wrap gap-3">
            <button type="submit" :disabled="submitting"
              class="btn-primary py-2.5 px-8 text-sm disabled:opacity-50 flex-1 sm:flex-none">
              {{ submitting ? 'Posting…' : 'Post Job' }}
            </button>
            <RouterLink to="/guardian/jobs"
              class="py-2.5 px-6 text-sm font-semibold font-display border border-paper-300 rounded-sm text-paper-700 hover:bg-paper-100 transition-colors text-center flex-1 sm:flex-none">
              Cancel
            </RouterLink>
          </div>
        </div>

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
import DropSelect from '@/components/search/DropSelect.vue'
import { MEDIUMS, PLACE_OF_TUTORING, TUTORING_STYLES } from '@/utils/constants.js'

const router = useRouter()

const form = ref({
  tuition_type:           '',
  medium:                 '',
  tutoring_style:         '',
  district_id:            '',
  area_id:                null,
  address_details:        '',
  class_level:            '',
  subject_ids:            [],
  student_gender:         'male',
  num_students:           1,
  tutor_gender_pref:      'any',
  offered_salary:         '',
  hire_date:              '',
  tutoring_days_per_week: null,
  student_institute:      '',
  extra_requirements:     '',
  tutoring_time:          '',
})

const errors       = ref({})
const submitting   = ref(false)
const districts    = ref([])
const areas        = ref([])
const subjects     = ref([])
const areasLoading = ref(false)
const timeHour   = ref('')
const timeMinute = ref('00')
const timePeriod = ref('PM')

const today        = new Date().toISOString().split('T')[0]
const hours        = Array.from({ length: 12 }, (_, i) => i + 1)
const salaryPresets = [3000, 5000, 8000, 12000, 15000]

const placeOptions    = PLACE_OF_TUTORING
const mediumOptions   = MEDIUMS
const styleOptions    = TUTORING_STYLES
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
const genderOptions      = [{ value:'male',label:'Male'},{value:'female',label:'Female'}]
const tutorGenderOptions = [{ value:'male',label:'Male'},{value:'female',label:'Female'},{value:'any',label:'Any'}]

const districtOpts = computed(() => [
  { value: '', label: 'Select district' },
  ...districts.value.map(d => ({ value: d.id, label: d.name })),
])
const areaOpts = computed(() => [
  { value: '', label: 'Select area' },
  ...areas.value.map(a => ({ value: a.id, label: a.name })),
])
const classOpts = [
  { value: '', label: 'Select class' },
  { value: 'class_1', label: 'Class 1' }, { value: 'class_2', label: 'Class 2' },
  { value: 'class_3', label: 'Class 3' }, { value: 'class_4', label: 'Class 4' },
  { value: 'class_5', label: 'Class 5' }, { value: 'class_6', label: 'Class 6' },
  { value: 'class_7', label: 'Class 7' }, { value: 'class_8', label: 'Class 8' },
  { value: 'class_9', label: 'Class 9' }, { value: 'class_10', label: 'Class 10' },
  { value: 'ssc', label: 'SSC' }, { value: 'hsc', label: 'HSC' },
  { value: 'o_level', label: 'O Level' }, { value: 'a_level', label: 'A Level' },
  { value: 'admission_test', label: 'Admission Test' },
]

async function onDistrictChange(id) {
  form.value.district_id = id
  form.value.area_id = null
  areas.value = []
  if (!id) return
  areasLoading.value = true
  try {
    const { data } = await searchApi.areas(id)
    areas.value = data.data || []
  } finally { areasLoading.value = false }
}

async function onClassChange(cls) {
  form.value.class_level = cls
  form.value.subject_ids = []
  subjects.value = []
  if (!cls) return
  const { data } = await searchApi.subjects({ class_level: cls })
  subjects.value = data.data || []
}

async function loadAreas() {
  areas.value = []
  if (!form.value.district_id) return
  const { data } = await searchApi.areas(form.value.district_id)
  areas.value = data.data || []
}
async function loadSubjects() {
  subjects.value = []
  if (!form.value.class_level) return
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
  return payload
}

async function submit() {
  errors.value = {}
  if (!form.value.tuition_type)        errors.value.tuition_type    = 'Please select a place of tutoring.'
  if (!form.value.medium)              errors.value.medium           = 'Please select a medium.'
  if (!form.value.tutoring_style)      errors.value.tutoring_style   = 'Please select a tutoring style.'
  if (!form.value.district_id)         errors.value.district_id      = 'Please select a district.'
  if (!form.value.area_id)             errors.value.area_id           = 'Please select an area.'
  if (!form.value.address_details?.trim()) errors.value.address_details = 'Please enter the address details.'
  if (!form.value.class_level)         errors.value.class_level      = 'Please select a class.'
  if (!form.value.subject_ids.length)  errors.value.subject_ids      = 'Please select at least one subject.'
  if (!form.value.offered_salary)           errors.value.offered_salary          = 'Please enter the offered salary.'
  if (!form.value.num_students)             errors.value.num_students             = 'Please enter number of students.'
  if (!form.value.tutoring_days_per_week)   errors.value.tutoring_days_per_week  = 'Please select days per week.'
  if (!timeHour.value)                      errors.value.tutoring_time            = 'Please select a preferred time.'
  if (!form.value.hire_date)                errors.value.hire_date                = 'Please select an expected hire date.'
  if (!form.value.student_institute?.trim()) errors.value.student_institute       = "Please enter the student's institute."
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
.field-label  { @apply block text-sm font-semibold font-display text-navy-800; }
.field-error  { @apply text-xs text-red-600 font-body mt-1; }
.req          { @apply text-red-500; }
.optional-tag { @apply text-xs font-normal font-body text-paper-400 ml-1; }

.choice-group { @apply flex flex-wrap gap-2; }
.choice-btn   { @apply px-3.5 py-1.5 rounded-sm text-sm font-semibold font-display border transition-colors focus:outline-none; }
.choice-btn-active { @apply bg-navy-700 text-white border-navy-700; }
.choice-btn-idle   { @apply bg-white text-navy-700 border-paper-300 hover:border-navy-400 hover:bg-navy-50; }

.card-section-header { @apply flex items-center gap-2.5 mb-1; }
.card-section-title  { @apply font-display font-bold text-navy-900 text-base; }
.step-badge {
  @apply flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-navy-700 text-white text-xs font-bold font-display;
}
.back-btn {
  @apply inline-flex items-center gap-1.5 rounded-sm border border-paper-300 bg-white px-3.5 py-2 text-sm font-semibold font-display text-navy-700 shadow-sm transition-colors hover:bg-paper-100;
}
</style>
