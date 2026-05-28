<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h2 class="font-display font-semibold text-navy-700 text-base">Filters</h2>
      <button v-if="activeCount > 0" @click="clearFilters"
        class="text-xs font-semibold font-display text-gold-600 hover:text-gold-700 transition-colors">
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

    <!-- Sort -->
    <FilterSection label="Sort by">
      <DropSelect v-model="filters.sort" :options="sortOpts" placeholder="Best match" />
    </FilterSection>

    <!-- CTA -->
    <div class="pt-1 space-y-2">
      <button @click="applyFilters" class="btn-primary w-full py-3">Search tutors</button>
      <button v-if="activeCount > 0" @click="clearFilters"
        class="w-full text-sm font-semibold font-display text-paper-500 hover:text-navy-700 transition-colors py-1.5">
        Clear all filters
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useSearchStore } from '@/stores/search.js'
import { searchApi } from '@/api/search.js'
import { MEDIUMS, CLASS_LEVELS, PLACE_OF_TUTORING, TUTORING_STYLES } from '@/utils/constants.js'
import FilterSection from './FilterSection.vue'
import DropSelect from './DropSelect.vue'

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

// ── Option arrays for DropSelect ────────────────────────────────────────────
const mediumOpts   = [{ value: '', label: 'Any medium' }, ...MEDIUMS.map(m => ({ value: m.value, label: m.label }))]
const classOpts    = [{ value: '', label: 'Any class'  }, ...CLASS_LEVELS.map(c => ({ value: c.value, label: c.label }))]
const genderOpts   = [{ value: '', label: 'No preference' }, { value: 'male', label: 'Male' }, { value: 'female', label: 'Female' }]
const sortOpts     = [
  { value: 'relevance', label: 'Best match' },
  { value: 'rating',    label: 'Highest rated' },
  { value: 'newest',    label: 'Newest' },
  { value: 'salary_asc',  label: 'Salary: low to high' },
  { value: 'salary_desc', label: 'Salary: high to low' },
]
const districtOpts = computed(() => [
  { value: '', label: 'Any district' },
  ...searchStore.districts.map(d => ({ value: d.id, label: d.name })),
])
const areaOpts = computed(() => [
  { value: null, label: 'Any area' },
  ...allAreas.value.map(a => ({ value: a.id, label: a.name })),
])

const filters = reactive({
  medium: '',
  class_level: '',
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
  sort: 'relevance',
})

async function onDistrictChange(newId) {
  filters.district_id = newId
  filters.area_id     = null
  allAreas.value      = []
  if (!newId) return
  areasLoading.value  = true
  try {
    const { data } = await searchApi.areas(newId)
    allAreas.value = data.data || []
  } finally {
    areasLoading.value = false
  }
}

const activeCount = computed(() => {
  const singles = [
    filters.medium, filters.class_level, filters.district_id, filters.area_id,
    filters.tutor_gender, filters.days_per_week, filters.hours_per_day,
    filters.salary_max, filters.min_rating,
  ].filter(v => v !== '' && v !== null).length
  const bools  = filters.verified_only ? 1 : 0
  const arrays = filters.place_of_tutoring.length + filters.tutoring_styles.length
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

function clearFilters() {
  Object.assign(filters, {
    medium: '', class_level: '', district_id: '', area_id: null,
    tutor_gender: '', days_per_week: null, hours_per_day: null,
    place_of_tutoring: [], tutoring_styles: [], salary_max: '',
    min_rating: null, verified_only: false, sort: 'relevance',
  })
  allAreas.value = []
  emit('search', {})
}

function applyFilters() {
  const payload = {}
  Object.entries(filters).forEach(([k, v]) => {
    if (v === '' || v === null || v === false) return
    if (Array.isArray(v) && !v.length) return
    payload[k] = v
  })
  emit('search', payload)
}

defineExpose({ activeCount, filters, allAreas, areaOpts, applyFilters })
</script>

<style scoped>
.pill-btn {
  @apply inline-flex items-center justify-center min-h-[34px] px-3 py-1.5 rounded-pill text-sm font-semibold font-display border transition-colors cursor-pointer focus:outline-none;
}
.pill-btn:focus { box-shadow: 0 0 0 4px rgba(244,185,66,0.24); }
.pill-btn-idle   { @apply bg-white border-paper-300 text-navy-700 hover:bg-navy-50 hover:border-navy-200; }
.pill-btn-active { @apply bg-navy-700 text-white border-navy-700 hover:bg-navy-600; }
</style>
