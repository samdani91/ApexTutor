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

    <!-- Class level -->
    <FilterSection label="Class">
      <DropSelect v-model="filters.class_level" :options="classOpts" placeholder="Any class" />
    </FilterSection>

    <!-- Group — only for class levels that split into groups -->
    <FilterSection v-if="hasGroups(filters.class_level)" label="Group">
      <DropSelect v-model="filters.group" :options="groupOpts" placeholder="Any group" />
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

    <!-- University — only shown when a district is selected -->
    <FilterSection v-if="filters.district_id" label="University">
      <div v-if="uniLoading" class="text-xs text-paper-400 font-body py-2">Loading universities…</div>
      <DropSelect v-else v-model="filters.university_id" :options="universityOpts" placeholder="Any university" />
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

  </div>
</template>

<script setup>
import { reactive, ref, computed, watch, nextTick } from 'vue'
import { useSearchStore } from '@/stores/search.js'
import { searchApi } from '@/api/search.js'
import { MEDIUMS, classLevelsFor, hasGroups, GROUPS, PLACE_OF_TUTORING, TUTORING_STYLES } from '@/utils/constants.js'
import FilterSection from './FilterSection.vue'
import DropSelect from './DropSelect.vue'

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['search'])
const searchStore = useSearchStore()

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
const classOpts    = computed(() => [
  { value: '', label: 'Any class' },
  ...classLevelsFor(filters.medium).map(c => ({ value: c.value, label: c.label })),
])
const groupOpts    = [{ value: '', label: 'Any group' }, ...GROUPS]
const genderOpts   = [{ value: '', label: 'No preference' }, { value: 'male', label: 'Male' }, { value: 'female', label: 'Female' }]
const districtOpts = computed(() => [
  { value: '', label: 'Any district' },
  ...searchStore.districts.map(d => ({ value: d.id, label: d.name })),
])
const areaOpts = computed(() => [
  { value: null, label: 'Any area' },
  ...allAreas.value.map(a => ({ value: a.id, label: a.name })),
])
// Group narrowing happens client-side: the class's full subject list is loaded
// once (each row carries its group) and the visible options are derived from it.
// No extra HTTP round-trip, nothing to go stale.
const visibleSubjects = computed(() =>
  allSubjects.value.filter(s => !filters.group || !s.group || s.group === filters.group)
)
const subjectOpts = computed(() => visibleSubjects.value.map(s => ({ value: s.id, label: s.name })))

const allUniversities = ref([])
const uniLoading      = ref(false)
const universityOpts  = computed(() => [
  { value: null, label: 'Any university' },
  ...allUniversities.value.map(u => ({ value: u.id, label: u.name })),
])

const filters = reactive({
  medium: '',
  class_level: '',
  group: '',
  subject_ids: [],
  district_id: '',
  area_id: null,
  university_id: null,
  tutor_gender: '',
  place_of_tutoring: [],
  tutoring_styles: [],
  salary_max: '',
  min_rating: null,
})

async function onDistrictChange(newId) {
  filters.district_id  = newId
  filters.area_id      = null
  filters.university_id = null
  allUniversities.value = []
  await Promise.all([loadAreas(newId), loadUniversities(newId)])
}

async function loadUniversities(districtId) {
  allUniversities.value = []
  if (!districtId) return
  const district = searchStore.districts.find(d => d.id === districtId)
  if (!district) return
  uniLoading.value = true
  try {
    const { data } = await searchApi.universities({ district: district.name })
    allUniversities.value = data.data || []
  } finally { uniLoading.value = false }
}

async function loadSubjectsForClass(classLevel) {
  allSubjects.value = []
  if (!classLevel) return
  subjectsLoading.value = true
  try {
    const { data } = await searchApi.subjects({
      class_level: classLevel,
      medium: filters.medium || undefined,
    })
    allSubjects.value = data.data || []
    pruneHiddenSubjects()
  } finally {
    subjectsLoading.value = false
  }
}

const activeCount = computed(() => {
  const singles = [
    filters.medium, filters.class_level, filters.group, filters.district_id, filters.area_id,
    filters.university_id, filters.tutor_gender, filters.salary_max, filters.min_rating,
  ].filter(v => v !== '' && v !== null).length
  const arrays = filters.subject_ids.length + filters.place_of_tutoring.length + filters.tutoring_styles.length
  return singles + arrays
})

// Drop selected subjects no longer offered (class reload or group narrowing).
// Reassigns only on a real change — a same-content reassignment would trip the
// deep watcher and fire a needless search.
function pruneHiddenSubjects() {
  const visible = new Set(visibleSubjects.value.map(s => s.id))
  const pruned = filters.subject_ids.filter(id => visible.has(Number(id)))
  if (pruned.length !== filters.subject_ids.length) filters.subject_ids = pruned
}

function toggleNum(key, val) {
  filters[key] = filters[key] === val ? null : val
}
function toggleMulti(key, val) {
  const arr = filters[key]
  const i = arr.indexOf(val)
  if (i === -1) arr.push(val)
  else arr.splice(i, 1)
}

// Registered ahead of the class watcher so a pruned class cascades into it
// (clearing subjects) before the deep watcher below gets a chance to fire.
watch(() => filters.medium, () => {
  if (syncingFromParent.value) return
  if (!filters.class_level) return
  // Each medium only offers certain classes, so drop a stale pick rather than
  // search for an impossible pair like madrasha + o_level.
  const stillValid = classLevelsFor(filters.medium).some(level => level.value === filters.class_level)
  if (!stillValid) {
    filters.class_level = ''   // cascades into the class watcher, which reloads subjects
    return
  }
  // Class survives the medium switch, so the class watcher won't fire — but the
  // subject set is medium-specific (e.g. English Medium Class 1), so reload here.
  loadSubjectsForClass(filters.class_level)
})

// Class watcher registered first so the flag is set before the deep watcher fires
watch(() => filters.class_level, async (classLevel, oldClassLevel) => {
  if (syncingFromParent.value) return
  classLevelChanging.value = true
  if (classLevel !== oldClassLevel) {
    filters.subject_ids = []
  }
  // The group filter only applies to some classes; drop a stale pick. This runs
  // while classLevelChanging is true, so the group watcher below stays inert.
  if (!hasGroups(classLevel)) filters.group = ''
  await loadSubjectsForClass(classLevel)
  classLevelChanging.value = false
  // The deep watcher below skips while classLevelChanging is true, and no further
  // filters mutation happens once subjects finish loading — so a class-only change
  // (no subject picked) needs to trigger the search itself here.
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 400)
})

// Group narrowing is synchronous (visibleSubjects handles the display); this
// just drops picks the new group hides. Group is also a real tutor filter now
// (preferred_groups), so the deep watcher firing a search on the change is the
// desired behaviour, not a side effect.
watch(() => filters.group, () => {
  if (syncingFromParent.value) return
  pruneHiddenSubjects()
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
    group: value.group || '',
    subject_ids: value.class_level ? normalizeIdArray(value.subject_ids) : [],
    district_id: value.district_id || '',
    area_id: value.area_id ?? null,
    university_id: value.university_id ?? null,
    tutor_gender: value.tutor_gender || '',
    place_of_tutoring: Array.isArray(value.place_of_tutoring) ? [...value.place_of_tutoring] : [],
    tutoring_styles: Array.isArray(value.tutoring_styles) ? [...value.tutoring_styles] : [],
    salary_max: value.salary_max ?? '',
    min_rating: value.min_rating ?? null,
  })

  if (filters.district_id) {
    await Promise.all([loadAreas(filters.district_id), loadUniversities(filters.district_id)])
  } else {
    allAreas.value = []
    allUniversities.value = []
  }
  await loadSubjectsForClass(filters.class_level)
  // Hold the flag through a tick so every watcher queued by the assigns above
  // flushes while still suppressed — otherwise a sync with no awaits would
  // re-emit and loop (this exact bug hit TuitionJobFilters).
  await nextTick()
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
    medium: '', class_level: '', group: '', subject_ids: [], district_id: '', area_id: null,
    university_id: null, tutor_gender: '', place_of_tutoring: [], tutoring_styles: [], salary_max: '',
    min_rating: null,
  })
  allAreas.value = []
  allUniversities.value = []
  allSubjects.value = []
  emit('search', {})
}

function applyFilters() {
  const payload = {}
  Object.entries(filters).forEach(([k, v]) => {
    if (v === '' || v === null || v === false) return
    if (Array.isArray(v) && !v.length) return
    // `group` is kept in the payload so it survives the parent's round-trip back
    // into modelValue (otherwise the dropdown clears itself). The backend ignores
    // it — it only refines the subject picker; the chosen subject_ids do the work.
    if (k === 'subject_ids') {
      payload[k] = v.join(',')
      return
    }
    payload[k] = v
  })
  emit('search', payload)
}

defineExpose({ activeCount, filters, allAreas, allSubjects, allUniversities, areaOpts, applyFilters })
</script>

<style scoped>
.pill-btn {
  @apply inline-flex items-center justify-center min-h-[34px] px-3 py-1.5 rounded-pill text-sm font-semibold font-display border transition-colors cursor-pointer focus:outline-none;
}
.pill-btn:focus { box-shadow: 0 0 0 4px rgba(244,185,66,0.24); }
.pill-btn-idle   { @apply bg-white border-paper-300 text-navy-700 hover:bg-navy-50 hover:border-navy-200; }
.pill-btn-active { @apply bg-navy-700 text-white border-navy-700 hover:bg-navy-600; }
</style>
