<template>
  <div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <h2 class="font-display font-semibold text-navy-700 text-base">Filters</h2>
      <button v-if="activeCount > 0" @click="clearFilters"
        class="rounded-sm bg-red-600 px-3 py-1.5 text-xs font-semibold font-display text-white shadow-sm transition-colors hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
        Clear All ({{ activeCount }})
      </button>
    </div>

    <!-- Medium -->
    <FilterSection label="Medium">
      <DropSelect v-model="filters.medium" :options="mediumOpts" placeholder="Any medium" />
    </FilterSection>

    <!-- District -->
    <FilterSection label="District">
      <DropSelect v-model="filters.district_id" :options="districtOpts" placeholder="Any district"
        @update:modelValue="onDistrictChange" />
    </FilterSection>

    <!-- Area -->
    <FilterSection v-if="filters.district_id" label="Area">
      <div v-if="areasLoading" class="text-xs text-paper-400 font-body py-2">Loading areas…</div>
      <DropSelect v-else v-model="filters.area_ids" :options="areaOpts" placeholder="Any area" multiple />
      <div v-if="filters.area_ids.length" class="flex flex-wrap gap-1.5 mt-2">
        <span v-for="id in filters.area_ids" :key="id"
          class="inline-flex items-center gap-1 bg-navy-50 text-navy-700 border border-navy-100 text-xs font-semibold font-display px-2 py-0.5 rounded-pill">
          {{ areaOpts.find(a => a.value === id)?.label }}
          <button type="button" @click="filters.area_ids = filters.area_ids.filter(a => a !== id)"
            class="text-navy-400 hover:text-navy-700 leading-none ml-0.5">&times;</button>
        </span>
      </div>
    </FilterSection>

    <!-- Class -->
    <FilterSection label="Class">
      <DropSelect v-model="filters.class_level" :options="classOpts" placeholder="Any class"
        @update:modelValue="onClassChange" />
    </FilterSection>

    <!-- Subject -->
    <FilterSection v-if="filters.class_level" label="Subject">
      <div v-if="subjectsLoading" class="text-xs text-paper-400 font-body py-2">Loading subjects…</div>
      <DropSelect v-else v-model="filters.subject_id" :options="subjectOpts" placeholder="Any subject" />
    </FilterSection>

    <!-- Place of Tutoring -->
    <FilterSection label="Place of tutoring">
      <div class="flex flex-wrap gap-2">
        <button v-for="p in PLACE_OPTIONS" :key="p.value" type="button"
          @click="toggle('tuition_type', p.value)"
          class="pill-btn"
          :class="filters.tuition_type === p.value ? 'pill-btn-active' : 'pill-btn-idle'">
          {{ p.label }}
        </button>
      </div>
    </FilterSection>

    <!-- Tutor Preference -->
    <FilterSection label="Tutor Preference">
      <div class="flex flex-wrap gap-2">
        <button v-for="g in GENDER_PREFS" :key="g.value" type="button"
          @click="toggle('tutor_gender_pref', g.value)"
          class="pill-btn"
          :class="filters.tutor_gender_pref === g.value ? 'pill-btn-active' : 'pill-btn-idle'">
          {{ g.label }}
        </button>
      </div>
    </FilterSection>

    <!-- Min Salary -->
    <FilterSection label="Min Salary (BDT/mo)">
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-paper-500 text-sm font-semibold select-none">৳</span>
        <input v-model.number="filters.salary_min" type="number" min="0" step="500" placeholder="e.g. 2000"
          class="input text-sm pl-8 w-full" />
      </div>
    </FilterSection>

    <!-- Max Salary -->
    <FilterSection label="Max Salary (BDT/mo)">
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-paper-500 text-sm font-semibold select-none">৳</span>
        <input v-model.number="filters.salary_max" type="number" min="0" step="500" placeholder="e.g. 10000"
          class="input text-sm pl-8 w-full" />
      </div>
      <div class="flex gap-2 mt-2 flex-wrap">
        <button v-for="preset in SALARY_PRESETS" :key="preset" type="button"
          @click="filters.salary_max = filters.salary_max === preset ? '' : preset"
          class="text-xs font-semibold font-display border px-2.5 py-1 rounded-pill transition-colors"
          :class="filters.salary_max === preset
            ? 'bg-navy-700 text-white border-navy-700'
            : 'border-paper-200 text-navy-600 hover:bg-navy-50 bg-white'">
          ৳{{ preset.toLocaleString() }}
        </button>
      </div>
    </FilterSection>

  </div>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue'
import { searchApi } from '@/api/search.js'
import { MEDIUMS, CLASS_LEVELS, PLACE_OF_TUTORING } from '@/utils/constants.js'
import FilterSection from '@/components/search/FilterSection.vue'
import DropSelect from '@/components/search/DropSelect.vue'

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['search'])

const PLACE_OPTIONS  = PLACE_OF_TUTORING
const GENDER_PREFS   = [{ value: 'male', label: 'Male' }, { value: 'female', label: 'Female' }, { value: 'any', label: 'Any' }]
const SALARY_PRESETS = [3000, 5000, 8000, 12000]

const allDistricts    = ref([])
const allAreas        = ref([])
const allSubjects     = ref([])
const areasLoading    = ref(false)
const subjectsLoading = ref(false)
const syncing         = ref(false)

const mediumOpts = computed(() => [
  { value: '', label: 'Any medium' },
  ...MEDIUMS.map(m => ({ value: m.value, label: m.label })),
])
const districtOpts = computed(() => [
  { value: '', label: 'Any district' },
  ...allDistricts.value.map(d => ({ value: d.id, label: d.name })),
])
const areaOpts = computed(() => [
  { value: '', label: 'Any area' },
  ...allAreas.value.map(a => ({ value: a.id, label: a.name })),
])
const classOpts = [
  { value: '', label: 'Any class' },
  ...CLASS_LEVELS.map(c => ({ value: c.value, label: c.label })),
]
const subjectOpts = computed(() => [
  { value: '', label: 'Any subject' },
  ...allSubjects.value.map(s => ({ value: s.id, label: s.name })),
])

const filters = reactive({
  medium:            '',
  district_id:       '',
  area_ids:          [],
  class_level:       '',
  subject_id:        '',
  tuition_type:      '',
  tutor_gender_pref: '',
  salary_min:        '',
  salary_max:        '',
})

const activeCount = computed(() =>
  Object.entries(filters).filter(([, v]) => Array.isArray(v) ? v.length > 0 : (v !== '' && v != null)).length
)

function toggle(key, val) {
  filters[key] = filters[key] === val ? '' : val
}

async function onDistrictChange(id) {
  filters.district_id = id
  filters.area_ids = []
  allAreas.value = []
  if (!id) return
  areasLoading.value = true
  try {
    const { data } = await searchApi.areas(id)
    allAreas.value = data.data || []
  } finally { areasLoading.value = false }
}

async function onClassChange(cls) {
  filters.class_level = cls
  filters.subject_id = ''
  allSubjects.value = []
  if (!cls) return
  subjectsLoading.value = true
  try {
    const { data } = await searchApi.subjects({ class_level: cls })
    allSubjects.value = data.data || []
  } finally { subjectsLoading.value = false }
}

function clearFilters() {
  Object.assign(filters, {
    medium: '', district_id: '', area_ids: [], class_level: '', subject_id: '',
    tuition_type: '', tutor_gender_pref: '', salary_min: '', salary_max: '',
  })
  allAreas.value = []
  allSubjects.value = []
  emit('search', {})
}

function applyFilters() {
  const payload = {}
  Object.entries(filters).forEach(([k, v]) => {
    if (Array.isArray(v)) { if (v.length) payload[k] = v }
    else if (v !== '' && v != null) payload[k] = v
  })
  emit('search', payload)
}

let searchTimer = null
watch(filters, () => {
  if (syncing.value) return
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 400)
}, { deep: true })

watch(() => props.modelValue, async (val) => {
  syncing.value = true
  Object.assign(filters, {
    medium:            val.medium            || '',
    district_id:       val.district_id       ? Number(val.district_id) : '',
    area_ids:          Array.isArray(val.area_ids) ? val.area_ids.map(Number) : [],
    class_level:       val.class_level       || '',
    subject_id:        val.subject_id        ? Number(val.subject_id)  : '',
    tuition_type:      val.tuition_type      || '',
    tutor_gender_pref: val.tutor_gender_pref || '',
    salary_min:        val.salary_min        ? Number(val.salary_min)  : '',
    salary_max:        val.salary_max        ? Number(val.salary_max)  : '',
  })
  if (filters.district_id) {
    areasLoading.value = true
    try { const { data } = await searchApi.areas(filters.district_id); allAreas.value = data.data || [] }
    finally { areasLoading.value = false }
  }
  if (filters.class_level) {
    subjectsLoading.value = true
    try { const { data } = await searchApi.subjects({ class_level: filters.class_level }); allSubjects.value = data.data || [] }
    finally { subjectsLoading.value = false }
  }
  syncing.value = false
}, { deep: true })

searchApi.districts().then(({ data }) => { allDistricts.value = data.data || [] }).catch(() => {})

defineExpose({ activeCount, filters, clearFilters })
</script>

<style scoped>
.pill-btn {
  @apply inline-flex items-center justify-center min-h-[34px] px-3 py-1.5 rounded-pill text-sm font-semibold font-display border transition-colors cursor-pointer focus:outline-none;
}
.pill-btn:focus { box-shadow: 0 0 0 4px rgba(244,185,66,0.24); }
.pill-btn-idle   { @apply bg-white border-paper-300 text-navy-700 hover:bg-navy-50 hover:border-navy-200; }
.pill-btn-active { @apply bg-navy-700 text-white border-navy-700 hover:bg-navy-600; }
</style>
