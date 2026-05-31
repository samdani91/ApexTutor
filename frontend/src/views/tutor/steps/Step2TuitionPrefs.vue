<template>
  <div>
    <h2 class="font-display font-semibold text-navy-700 text-lg mb-5">Tuition preferences</h2>

    <!-- Basic info -->
    <div class="grid sm:grid-cols-2 gap-5">
      <div>
        <label class="block text-sm font-semibold font-display text-navy-800 mb-1">Total experience</label>
        <DropSelect v-model="form.total_experience_years" :options="experienceOptions" placeholder="Less than 1 year" />
      </div>
      <div>
        <label class="block text-sm font-semibold font-display text-navy-800 mb-1">Min salary (BDT/mo)</label>
        <input v-model.number="form.expected_salary_min" type="number" min="0" class="input text-sm" />
      </div>
      <div>
        <label class="block text-sm font-semibold font-display text-navy-800 mb-1">Max salary (BDT/mo)</label>
        <input v-model.number="form.expected_salary_max" type="number" min="0" class="input text-sm" />
      </div>
    </div>

    <!-- Available days of the week -->
    <div class="mt-5">
      <label class="block text-sm font-semibold font-display text-navy-800 mb-2">Available days</label>
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
      <label class="block text-sm font-semibold font-display text-navy-800 mb-2">Days per week</label>
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
      <label class="block text-sm font-semibold font-display text-navy-800 mb-2">Hours per session</label>
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
      <label class="block text-sm font-semibold font-display text-navy-800 mb-2">Preferred time</label>
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
      <label class="block text-sm font-semibold font-display text-navy-800 mb-2">Place of tutoring</label>
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
      <label class="block text-sm font-semibold font-display text-navy-800 mb-2">Tutoring style</label>
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
      <label class="block text-sm font-semibold font-display text-navy-800 mb-2">Preferred subjects</label>
      <div v-if="subjectsLoading" class="text-xs text-paper-400 font-body">Loading subjects…</div>
      <div v-else-if="subjectGroups.length" class="flex flex-wrap gap-2">
        <button type="button" v-for="group in subjectGroups" :key="group.name"
          @click="toggleSubjectGroup(group)"
          class="choice-btn"
          :class="isSubjectGroupSelected(group) ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ group.name }}
        </button>
      </div>
      <p v-else class="text-xs text-paper-400 font-body">No subjects available.</p>
    </div>

    <!-- Preferred district + areas -->
    <div class="mt-5">
      <label class="block text-sm font-semibold font-display text-navy-800 mb-1.5">Preferred district</label>
      <DropSelect v-model="form.district_id" :options="districtOptions" placeholder="Select a district…"
        @update:modelValue="onDistrictChange" />
      <p class="text-xs text-paper-400 font-body mt-1">The city / district you are available to tutor in</p>
    </div>

    <div v-if="form.district_id" class="mt-5">
      <label class="block text-sm font-semibold font-display text-navy-800 mb-2">Preferred areas</label>
      <div v-if="areasLoading" class="text-xs text-paper-400 font-body py-2">Loading areas…</div>
      <div v-else-if="allAreas.length" class="flex flex-wrap gap-2">
        <button type="button" v-for="a in allAreas" :key="a.id"
          @click="toggleArea(a.id)"
          class="choice-btn"
          :class="form.location_ids.includes(a.id) ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ a.name }}
        </button>
      </div>
      <p class="text-xs text-paper-400 font-body mt-1.5">Pick all the areas you're willing to travel to</p>
    </div>

    <!-- Preferred curricula -->
    <div class="mt-5">
      <label class="block text-sm font-semibold font-display text-navy-800 mb-2">Preferred Curriculum</label>
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
      <label class="block text-sm font-semibold font-display text-navy-800 mb-2">Preferred Classes</label>
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
      <label class="block text-sm font-semibold font-display text-navy-800 mb-1">Describe your tutoring method</label>
      <textarea v-model="form.tutoring_method_description" rows="4" class="input text-sm resize-none"
        placeholder="Explain how you typically conduct sessions — your approach, techniques, use of examples, practice problems, etc." />
      <p class="text-xs text-paper-400 font-body mt-1">Helps students and guardians understand how you teach.</p>
    </div>

    <!-- Experience details -->
    <div class="mt-5">
      <label class="block text-sm font-semibold font-display text-navy-800 mb-1">Experience details</label>
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
import { reactive, ref, computed, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'
import { searchApi } from '@/api/search.js'
import { toast } from 'vue-sonner'
import { MEDIUMS, CLASS_LEVELS, PLACE_OF_TUTORING, TUTORING_STYLES, DAYS, PREFERRED_TIMES } from '@/utils/constants.js'

const emit = defineEmits(['saved'])
const saving         = ref(false)
const subjectsLoading = ref(false)
const areasLoading   = ref(false)
const allSubjects    = ref([])
const allDistricts   = ref([])
const allAreas       = ref([])

const HOURS = [
  { value: 1,   label: '1 hr' },
  { value: 1.5, label: '1.5 hr' },
  { value: 2,   label: '2 hr' },
  { value: 3,   label: '3 hr' },
]
const experienceOptions = [
  { value: 0, label: 'Less than 1 year' },
  ...Array.from({ length: 20 }, (_, i) => ({ value: i + 1, label: `${i + 1} year${i + 1 > 1 ? 's' : ''}` })),
  { value: 21, label: '20+ years' },
]
const districtOptions = computed(() => [
  { value: null, label: 'Select a district…' },
  ...allDistricts.value.map(d => ({ value: d.id, label: d.name })),
])
const subjectGroups = computed(() => {
  const groups = new Map()
  for (const subject of allSubjects.value) {
    const name = String(subject.name || '').trim()
    if (!name) continue
    if (!groups.has(name)) groups.set(name, { name, ids: [] })
    groups.get(name).ids.push(subject.id)
  }
  return [...groups.values()].sort((a, b) => a.name.localeCompare(b.name))
})

const form = reactive({
  district_id: null,
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
  location_ids: [],
})

async function loadAreas(districtId) {
  if (!districtId) { allAreas.value = []; return }
  areasLoading.value = true
  try {
    const { data } = await searchApi.areas(districtId)
    allAreas.value = data.data || []
  } finally {
    areasLoading.value = false
  }
}

// Called only by user interaction — clears selections when district changes
async function onDistrictChange() {
  form.location_ids = []
  await loadAreas(form.district_id)
}

onMounted(async () => {
  subjectsLoading.value = true
  try {
    const [prefRes, subjRes, distRes] = await Promise.all([
      tutorApi.getPreferences(),
      searchApi.subjects(),
      searchApi.districts(),
    ])
    allSubjects.value  = subjRes.data.data || []
    allDistricts.value = distRes.data.data || []

    const d = prefRes.data.data
    if (d) {
      Object.assign(form, {
        district_id:                d.district_id ?? null,
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
        location_ids:               (d.locations || []).map(x => x.area_id).filter(Boolean),
      })
      // Load areas for already-selected district without clearing location_ids
      if (d.district_id) {
        areasLoading.value = true
        try {
          const { data } = await searchApi.areas(d.district_id)
          allAreas.value = data.data || []
        } finally {
          areasLoading.value = false
        }
      }
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

function isSubjectGroupSelected(group) {
  return group.ids.some(id => form.subject_ids.includes(id))
}

function toggleSubjectGroup(group) {
  if (isSubjectGroupSelected(group)) {
    form.subject_ids = form.subject_ids.filter(id => !group.ids.includes(id))
    return
  }
  const next = new Set(form.subject_ids)
  group.ids.forEach(id => next.add(id))
  form.subject_ids = [...next]
}

function toggleArea(id) {
  const i = form.location_ids.indexOf(id)
  if (i === -1) form.location_ids.push(id)
  else form.location_ids.splice(i, 1)
}

function nullIfEmpty(val) {
  return val === '' || val === undefined ? null : val
}

async function save() {
  saving.value = true
  try {
    const res = await tutorApi.savePreferences({
      district_id:                 form.district_id,
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
      location_ids:                form.location_ids,
    })
    emit('saved', !!res.data?.pending)
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
