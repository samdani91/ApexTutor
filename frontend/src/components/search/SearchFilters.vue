<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h2 class="font-display font-semibold text-navy-700 text-base">Filters</h2>
      <button v-if="activeCount > 0" @click="clearFilters"
        class="rounded-sm bg-red-600 px-3 py-1.5 text-xs font-semibold font-display text-white shadow-sm transition-colors hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
        Clear all ({{ activeCount }})
      </button>
    </div>

    <!-- Medium -->
    <FilterSection label="Medium">
      <DropSelect v-model="filters.medium" :options="mediumOpts" placeholder="Any medium" />
    </FilterSection>

    <!-- Class level -->
    <FilterSection label="Class">
      <DropSelect v-model="filters.class_level" :options="classOpts" placeholder="Any class" />
    </FilterSection>

    <!-- Subjects -->
    <FilterSection label="Subjects">
      <div v-if="!filters.class_level" class="text-xs text-paper-400 font-body py-2">
        Select a class to choose relevant subjects.
      </div>
      <div v-else-if="subjectsLoading" class="text-xs text-paper-400 font-body py-2">Loading subjects…</div>
      <DropSelect v-else v-model="filters.subject_ids" :options="subjectOpts" placeholder="Any subject" multiple />
    </FilterSection>

    <!-- District -->
    <FilterSection label="District">
      <DropSelect v-model="filters.district_id" :options="districtOpts" placeholder="Any district"
        @update:modelValue="onDistrictChange" />
    </FilterSection>

    <!-- Area — only shown when a district is selected -->
    <FilterSection v-if="filters.district_id" label="Area">
      <div v-if="areasLoading" class="text-xs text-paper-400 font-body py-2">Loading areas…</div>
      <DropSelect v-else v-model="filters.area_id" :options="areaOpts" placeholder="Any area" />
    </FilterSection>

    <!-- Tutor gender -->
    <FilterSection label="Tutor gender">
      <DropSelect v-model="filters.tutor_gender" :options="genderOpts" placeholder="No preference" />
    </FilterSection>

    <!-- Days per week -->
    <FilterSection label="Days per week">
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="d in [1,2,3,4,5,6,7]" :key="d"
          @click="toggleNum('days_per_week', d)"
          class="w-9 h-9 rounded-sm text-sm font-semibold font-display border transition-colors focus:outline-none"
          :class="filters.days_per_week === d ? 'choice-btn-active' : 'choice-btn-idle'">
          {{ d }}
        </button>
      </div>
    </FilterSection>

    <!-- Hours per session -->
    <FilterSection label="Hours per session">
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="h in HOURS" :key="h.value"
          @click="toggleNum('hours_per_day', h.value)"
          class="pill-btn"
          :class="filters.hours_per_day === h.value ? 'pill-btn-active' : 'pill-btn-idle'">
          {{ h.label }}
        </button>
      </div>
    </FilterSection>

    <!-- Place of tutoring -->
    <FilterSection label="Place of tutoring">
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="p in PLACE_OPTIONS" :key="p.value"
          @click="toggleMulti('place_of_tutoring', p.value)"
          class="pill-btn"
          :class="filters.place_of_tutoring.includes(p.value) ? 'pill-btn-active' : 'pill-btn-idle'">
          {{ p.label }}
        </button>
      </div>
    </FilterSection>

    <!-- Tutoring style -->
    <FilterSection label="Tutoring style">
      <div class="flex flex-wrap gap-2">
        <button type="button" v-for="s in STYLE_OPTIONS" :key="s.value"
          @click="toggleMulti('tutoring_styles', s.value)"
          class="pill-btn"
          :class="filters.tutoring_styles.includes(s.value) ? 'pill-btn-active' : 'pill-btn-idle'">
          {{ s.label }}
        </button>
      </div>
    </FilterSection>

    <!-- Budget -->
    <FilterSection label="Max budget (BDT/mo)">
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-paper-500 text-sm font-semibold select-none">৳</span>
        <input v-model.number="filters.salary_max" type="number" placeholder="e.g. 5000"
          class="input text-sm pl-8" min="0" step="500" />
      </div>
      <div class="flex gap-2 mt-2 flex-wrap">
        <button type="button" v-for="preset in BUDGET_PRESETS" :key="preset"
          @click="filters.salary_max = filters.salary_max === preset ? '' : preset"
          class="text-xs font-semibold font-display border px-2.5 py-1 rounded-pill transition-colors"
          :class="filters.salary_max === preset
            ? 'bg-navy-700 text-white border-navy-700'
            : 'border-paper-200 text-navy-600 hover:bg-navy-50 bg-white'">
          ৳{{ preset.toLocaleString() }}
        </button>
      </div>
    </FilterSection>

    <!-- Min rating -->
    <FilterSection label="Min rating">
      <div class="flex gap-2 flex-wrap">
        <button type="button" v-for="r in RATING_OPTIONS" :key="r.value"
          @click="toggleNum('min_rating', r.value)"
          class="pill-btn"
          :class="filters.min_rating === r.value ? 'pill-btn-active' : 'pill-btn-idle'">
          {{ r.label }}
        </button>
      </div>
    </FilterSection>

    <!-- Verified only -->
    <FilterSection label="Verification">
      <label class="flex items-center gap-2.5 cursor-pointer">
        <input type="checkbox" v-model="filters.verified_only"
          class="w-4 h-4 rounded accent-navy-700 cursor-pointer" />
        <span class="text-sm font-body text-navy-700">Verified tutors only</span>
      </label>
    </FilterSection>

  </div>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue'
import { useSearchStore } from '@/stores/search.js'
import { searchApi } from '@/api/search.js'
import { MEDIUMS, CLASS_LEVELS, PLACE_OF_TUTORING, TUTORING_STYLES } from '@/utils/constants.js'
import FilterSection from './FilterSection.vue'
import DropSelect from './DropSelect.vue'

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['search'])
const searchStore = useSearchStore()

const HOURS = [
  { value: 1, label: '1 hr' },
  { value: 1.5, label: '1.5 hr' },
  { value: 2, label: '2 hr' },
  { value: 3, label: '3 hr' },
]
const PLACE_OPTIONS  = PLACE_OF_TUTORING
const STYLE_OPTIONS  = TUTORING_STYLES
const BUDGET_PRESETS = [2000, 3000, 5000, 8000]
const RATING_OPTIONS = [
  { value: 3,   label: '3+ ★' },
  { value: 4,   label: '4+ ★' },
  { value: 4.5, label: '4.5+ ★' },
]

const allAreas     = ref([])
const areasLoading = ref(false)
const allSubjects  = ref([])
const subjectsLoading = ref(false)
const syncingFromParent = ref(false)
const classLevelChanging = ref(false)

// ── Option arrays for DropSelect ────────────────────────────────────────────
const mediumOpts   = [{ value: '', label: 'Any medium' }, ...MEDIUMS.map(m => ({ value: m.value, label: m.label }))]
const classOpts    = [{ value: '', label: 'Any class'  }, ...CLASS_LEVELS.map(c => ({ value: c.value, label: c.label }))]
const genderOpts   = [{ value: '', label: 'No preference' }, { value: 'male', label: 'Male' }, { value: 'female', label: 'Female' }]
const districtOpts = computed(() => [
  { value: '', label: 'Any district' },
  ...searchStore.districts.map(d => ({ value: d.id, label: d.name })),
])
const areaOpts = computed(() => [
  { value: null, label: 'Any area' },
  ...allAreas.value.map(a => ({ value: a.id, label: a.name })),
])
const subjectOpts = computed(() => allSubjects.value.map(s => ({ value: s.id, label: s.name })))

const filters = reactive({
  medium: '',
  class_level: '',
  subject_ids: [],
  district_id: '',
  area_id: null,
  tutor_gender: '',
  days_per_week: null,
  hours_per_day: null,
  place_of_tutoring: [],
  tutoring_styles: [],
  salary_max: '',
  min_rating: null,
  verified_only: false,
})

async function onDistrictChange(newId) {
  filters.district_id = newId
  filters.area_id     = null
  await loadAreas(newId)
}

async function loadSubjectsForClass(classLevel) {
  allSubjects.value = []
  if (!classLevel) return
  subjectsLoading.value = true
  try {
    const { data } = await searchApi.subjects({ class_level: classLevel })
    allSubjects.value = data.data || []
    const validIds = new Set(allSubjects.value.map(subject => subject.id))
    filters.subject_ids = filters.subject_ids.filter(id => validIds.has(Number(id)))
  } finally {
    subjectsLoading.value = false
  }
}

const activeCount = computed(() => {
  const singles = [
    filters.medium, filters.class_level, filters.district_id, filters.area_id,
    filters.tutor_gender, filters.days_per_week, filters.hours_per_day,
    filters.salary_max, filters.min_rating,
  ].filter(v => v !== '' && v !== null).length
  const bools  = filters.verified_only ? 1 : 0
  const arrays = filters.subject_ids.length + filters.place_of_tutoring.length + filters.tutoring_styles.length
  return singles + bools + arrays
})

function toggleNum(key, val) {
  filters[key] = filters[key] === val ? null : val
}
function toggleMulti(key, val) {
  const arr = filters[key]
  const i = arr.indexOf(val)
  if (i === -1) arr.push(val)
  else arr.splice(i, 1)
}

// Class watcher registered first so the flag is set before the deep watcher fires
watch(() => filters.class_level, async (classLevel, oldClassLevel) => {
  if (syncingFromParent.value) return
  classLevelChanging.value = true
  if (classLevel !== oldClassLevel) {
    filters.subject_ids = []
  }
  await loadSubjectsForClass(classLevel)
  classLevelChanging.value = false
})

// Auto-search: fire 400ms after the last filter change (class changes are excluded)
let searchTimer = null
watch(filters, () => {
  if (syncingFromParent.value) return
  if (classLevelChanging.value) return
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 400)
}, { deep: true })

watch(() => props.modelValue, async (value) => {
  await syncFilters(value || {})
}, { deep: true, immediate: true })

async function syncFilters(value) {
  syncingFromParent.value = true
  Object.assign(filters, {
    medium: value.medium || '',
    class_level: value.class_level || '',
    subject_ids: value.class_level ? normalizeIdArray(value.subject_ids) : [],
    district_id: value.district_id || '',
    area_id: value.area_id ?? null,
    tutor_gender: value.tutor_gender || '',
    days_per_week: value.days_per_week ?? null,
    hours_per_day: value.hours_per_day ?? null,
    place_of_tutoring: Array.isArray(value.place_of_tutoring) ? [...value.place_of_tutoring] : [],
    tutoring_styles: Array.isArray(value.tutoring_styles) ? [...value.tutoring_styles] : [],
    salary_max: value.salary_max ?? '',
    min_rating: value.min_rating ?? null,
    verified_only: Boolean(value.verified_only),
  })

  if (filters.district_id) {
    await loadAreas(filters.district_id)
  } else {
    allAreas.value = []
  }
  await loadSubjectsForClass(filters.class_level)
  syncingFromParent.value = false
}

async function loadAreas(districtId) {
  allAreas.value = []
  if (!districtId) return
  areasLoading.value = true
  try {
    const { data } = await searchApi.areas(districtId)
    allAreas.value = data.data || []
  } finally {
    areasLoading.value = false
  }
}

function normalizeIdArray(value) {
  if (Array.isArray(value)) return value.map(Number).filter(Boolean)
  if (typeof value === 'string') return value.split(',').map(Number).filter(Boolean)
  return []
}

function clearFilters() {
  Object.assign(filters, {
    medium: '', class_level: '', subject_ids: [], district_id: '', area_id: null,
    tutor_gender: '', days_per_week: null, hours_per_day: null,
    place_of_tutoring: [], tutoring_styles: [], salary_max: '',
    min_rating: null, verified_only: false,
  })
  allAreas.value = []
  allSubjects.value = []
  emit('search', {})
}

function applyFilters() {
  const payload = {}
  Object.entries(filters).forEach(([k, v]) => {
    if (v === '' || v === null || v === false) return
    if (Array.isArray(v) && !v.length) return
    if (k === 'subject_ids') {
      payload[k] = v.join(',')
      return
    }
    payload[k] = v
  })
  emit('search', payload)
}

defineExpose({ activeCount, filters, allAreas, allSubjects, areaOpts, applyFilters })
</script>

<style scoped>
.pill-btn {
  @apply inline-flex items-center justify-center min-h-[34px] px-3 py-1.5 rounded-pill text-sm font-semibold font-display border transition-colors cursor-pointer focus:outline-none;
}
.pill-btn:focus { box-shadow: 0 0 0 4px rgba(244,185,66,0.24); }
.pill-btn-idle   { @apply bg-white border-paper-300 text-navy-700 hover:bg-navy-50 hover:border-navy-200; }
.pill-btn-active { @apply bg-navy-700 text-white border-navy-700 hover:bg-navy-600; }
</style>
