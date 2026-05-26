<template>
  <div>
    <h2 class="font-display font-semibold text-navy-700 text-lg mb-5">Tuition preferences</h2>

    <!-- Basic info -->
    <div class="grid sm:grid-cols-2 gap-5">
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">City</label>
        <input v-model="form.city" type="text" class="input text-sm" placeholder="e.g. Mirpur, Dhaka" />
      </div>
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Total experience</label>
        <select v-model.number="form.total_experience_years" class="input text-sm">
          <option :value="0">Less than 1 year</option>
          <option v-for="y in 20" :key="y" :value="y">{{ y }} year{{ y > 1 ? 's' : '' }}</option>
          <option :value="21">20+ years</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Min salary (BDT/mo)</label>
        <input v-model.number="form.expected_salary_min" type="number" min="0" class="input text-sm" />
      </div>
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Max salary (BDT/mo)</label>
        <input v-model.number="form.expected_salary_max" type="number" min="0" class="input text-sm" />
      </div>
    </div>

    <!-- Available days of the week -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-2">Available days</label>
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="d in DAYS" :key="d.value"
          @click="toggleArray(form.selectedDays, d.value)"
          class="choice-btn"
          :class="form.selectedDays.includes(d.value) ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ d.label }}
        </button>
      </div>
      <p class="text-xs text-paper-400 mt-1.5 font-body">Select the days of the week you're available to tutor</p>
    </div>

    <!-- Days per week -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-2">Days per week</label>
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="d in [1,2,3,4,5,6,7]" :key="d"
          @click="toggleNum('days_per_week', d)"
          class="w-10 h-10 rounded-sm text-sm font-semibold font-display border transition-colors focus:outline-none"
          :class="form.days_per_week === d ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ d }}
        </button>
      </div>
      <p class="text-xs text-paper-400 mt-1.5 font-body">How many days per week you can tutor</p>
    </div>

    <!-- Hours per session -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-2">Hours per session</label>
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="h in HOURS" :key="h.value"
          @click="toggleNum('hours_per_day', h.value)"
          class="choice-btn"
          :class="form.hours_per_day === h.value ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ h.label }}
        </button>
      </div>
    </div>

    <!-- Preferred time -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-2">Preferred time</label>
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="t in PREFERRED_TIMES" :key="t.value"
          @click="toggleArray(form.preferred_time, t.value)"
          class="flex flex-col items-center min-h-[48px] px-4 py-2 rounded-sm text-sm font-semibold font-display border transition-colors focus:outline-none"
          :class="form.preferred_time.includes(t.value) ? 'choice-btn-active' : 'choice-btn-idle'">
          <span>{{ t.label }}</span>
          <span class="text-xs font-normal mt-0.5 opacity-70">{{ t.hint }}</span>
        </button>
      </div>
      <p class="text-xs text-paper-400 mt-1.5 font-body">Select all time slots you're available for tutoring</p>
    </div>

    <!-- Place of tutoring -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-2">Place of tutoring</label>
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="p in PLACE_OF_TUTORING" :key="p.value"
          @click="toggleArray(form.place_of_tutoring, p.value)"
          class="choice-btn"
          :class="form.place_of_tutoring.includes(p.value) ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ p.label }}
        </button>
      </div>
    </div>

    <!-- Tutoring style -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-2">Tutoring style</label>
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="s in TUTORING_STYLES" :key="s.value"
          @click="toggleArray(form.tutoring_styles, s.value)"
          class="choice-btn"
          :class="form.tutoring_styles.includes(s.value) ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ s.label }}
        </button>
      </div>
    </div>

    <!-- Preferred subjects -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-2">Preferred subjects</label>
      <div v-if="subjectsLoading" class="text-xs text-paper-400 font-body">Loading subjects…</div>
      <div v-else-if="allSubjects.length" class="flex flex-wrap gap-2">
        <button type="button" v-for="s in allSubjects" :key="s.id"
          @click="toggleArray(form.subject_ids, s.id)"
          class="choice-btn"
          :class="form.subject_ids.includes(s.id) ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ s.name }}
        </button>
      </div>
      <p v-else class="text-xs text-paper-400 font-body">No subjects available.</p>
    </div>

    <!-- Preferred locations -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-2">Preferred locations</label>
      <div class="flex gap-2 mb-3">
        <input v-model="locationInput" type="text" class="input text-sm flex-1"
          placeholder="e.g. Dhanmondi, Uttara, Gulshan"
          @keydown.enter.prevent="addLocation" />
        <button type="button" @click="addLocation"
          class="btn-primary text-sm py-2 px-4 shrink-0">
          Add
        </button>
      </div>
      <div v-if="form.locations.length" class="flex flex-wrap gap-2">
        <span v-for="(loc, i) in form.locations" :key="i"
          class="inline-flex items-center gap-1.5 bg-navy-50 text-navy-700 border border-navy-200 text-xs font-semibold font-display px-2.5 py-1 rounded-pill">
          {{ loc }}
          <button type="button" @click="removeLocation(i)"
            class="text-navy-400 hover:text-red-600 transition-colors leading-none">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </span>
      </div>
      <p class="text-xs text-paper-400 font-body mt-1.5">Areas or neighbourhoods you're willing to travel to</p>
    </div>

    <!-- Preferred curricula -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-2">Preferred Curriculum</label>
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="m in MEDIUMS" :key="m.value"
          @click="toggleArray(form.preferred_curricula, m.value)"
          class="choice-btn"
          :class="form.preferred_curricula.includes(m.value) ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ m.label }}
        </button>
      </div>
    </div>

    <!-- Preferred classes -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-2">Preferred Classes</label>
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="c in CLASS_LEVELS" :key="c.value"
          @click="toggleArray(form.preferred_classes, c.value)"
          class="choice-btn"
          :class="form.preferred_classes.includes(c.value) ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ c.label }}
        </button>
      </div>
    </div>

    <!-- Tutoring method description -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Describe your tutoring method</label>
      <textarea v-model="form.tutoring_method_description" rows="4" class="input text-sm resize-none"
        placeholder="Explain how you typically conduct sessions — your approach, techniques, use of examples, practice problems, etc." />
      <p class="text-xs text-paper-400 font-body mt-1">Helps students and guardians understand how you teach.</p>
    </div>

    <!-- Experience details -->
    <div class="mt-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Experience details</label>
      <textarea v-model="form.experience_details" rows="3" class="input text-sm resize-none"
        placeholder="Describe your teaching background, institutions you've taught at, notable achievements, etc." />
      <p class="text-xs text-paper-400 font-body mt-1">Give students a clearer picture of your teaching history.</p>
    </div>

    <button @click="save" :disabled="saving" class="btn-primary mt-6 text-sm py-2.5 px-6">
      {{ saving ? 'Saving…' : 'Save preferences' }}
    </button>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'
import { searchApi } from '@/api/search.js'
import { toast } from 'vue-sonner'
import { MEDIUMS, CLASS_LEVELS, PLACE_OF_TUTORING, TUTORING_STYLES, DAYS, PREFERRED_TIMES } from '@/utils/constants.js'

const emit = defineEmits(['saved'])
const saving = ref(false)
const subjectsLoading = ref(false)
const allSubjects = ref([])
const locationInput = ref('')

const HOURS = [
  { value: 1,   label: '1 hr' },
  { value: 1.5, label: '1.5 hr' },
  { value: 2,   label: '2 hr' },
  { value: 3,   label: '3 hr' },
]

const form = reactive({
  city: '',
  total_experience_years: 0,
  experience_details: '',
  expected_salary_min: '',
  expected_salary_max: '',
  days_per_week: null,
  hours_per_day: null,
  place_of_tutoring: [],
  tutoring_styles: [],
  preferred_curricula: [],
  preferred_classes: [],
  tutoring_method_description: '',
  preferred_time: [],
  selectedDays: [],
  subject_ids: [],
  locations: [],
})

onMounted(async () => {
  subjectsLoading.value = true
  try {
    const [prefRes, subjRes] = await Promise.all([
      tutorApi.getPreferences(),
      searchApi.subjects(),
    ])
    allSubjects.value = subjRes.data.data || []

    const d = prefRes.data.data
    if (d) {
      Object.assign(form, {
        city:                       d.city || '',
        total_experience_years:     d.total_experience_years ?? 0,
        experience_details:         d.experience_details || '',
        expected_salary_min:        d.expected_salary_min || '',
        expected_salary_max:        d.expected_salary_max || '',
        days_per_week:              d.days_per_week ?? null,
        hours_per_day:              d.hours_per_day ?? null,
        place_of_tutoring:          d.place_of_tutoring || [],
        tutoring_styles:            d.tutoring_styles || [],
        preferred_curricula:        d.preferred_curricula || [],
        preferred_classes:          d.preferred_classes || [],
        tutoring_method_description: d.tutoring_method_description || '',
        preferred_time:              d.preferred_time || [],
        selectedDays:               (d.days || []).map(x => x.day),
        subject_ids:                (d.subjects || []).map(x => x.id),
        locations:                  (d.locations || []).map(x => x.area_name),
      })
    }
  } finally {
    subjectsLoading.value = false
  }
})

function toggleNum(key, val) {
  form[key] = form[key] === val ? null : val
}

function toggleArray(arr, val) {
  const i = arr.indexOf(val)
  if (i === -1) arr.push(val)
  else arr.splice(i, 1)
}

function addLocation() {
  const val = locationInput.value.trim()
  if (val && !form.locations.includes(val)) {
    form.locations.push(val)
  }
  locationInput.value = ''
}

function removeLocation(index) {
  form.locations.splice(index, 1)
}

function nullIfEmpty(val) {
  return val === '' || val === undefined ? null : val
}

async function save() {
  addLocation() // flush any typed-but-not-yet-added input
  saving.value = true
  try {
    await tutorApi.savePreferences({
      city:                        nullIfEmpty(form.city),
      total_experience_years:      form.total_experience_years,
      experience_details:          nullIfEmpty(form.experience_details),
      expected_salary_min:         nullIfEmpty(form.expected_salary_min),
      expected_salary_max:         nullIfEmpty(form.expected_salary_max),
      days_per_week:               form.days_per_week,
      hours_per_day:               form.hours_per_day,
      preferred_time:              form.preferred_time,
      place_of_tutoring:           form.place_of_tutoring,
      tutoring_styles:             form.tutoring_styles,
      preferred_curricula:         form.preferred_curricula,
      preferred_classes:           form.preferred_classes,
      tutoring_method_description: nullIfEmpty(form.tutoring_method_description),
      days:                        form.selectedDays.map(d => ({ day: d })),
      subject_ids:                 form.subject_ids,
      locations:                   form.locations.map(name => ({ area_name: name })),
    })
    emit('saved')
  } catch (e) {
    const msg = e.response?.data?.message
      || Object.values(e.response?.data?.errors || {})[0]?.[0]
      || 'Failed to save preferences.'
    toast.error(msg)
  } finally {
    saving.value = false
  }
}
</script>
