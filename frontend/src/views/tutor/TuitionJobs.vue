<template>
  <div class="space-y-5">
    <div>
      <h1 class="font-display font-bold text-2xl text-navy-900">Tuition Jobs</h1>
      <p class="mt-1 text-sm font-body text-paper-500">Browse open tuition jobs posted by guardians and apply directly.</p>
    </div>

    <!-- Filters -->
    <div class="card space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="font-display font-semibold text-navy-800 text-sm">Filter Jobs</h2>
        <button @click="clearFilters" class="text-xs text-navy-600 font-semibold font-display hover:underline">Clear Filters</button>
      </div>

      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <label class="filter-label">Search</label>
          <input v-model="filters.q" @input="debouncedLoad" type="text" placeholder="Title or keywords…" class="input text-sm mt-1" />
        </div>
        <div>
          <label class="filter-label">District</label>
          <select v-model="filters.district_id" @change="filters.area_id = ''; loadAreas(); load()" class="input text-sm mt-1">
            <option value="">All Districts</option>
            <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
          </select>
        </div>
        <div>
          <label class="filter-label">Area</label>
          <select v-model="filters.area_id" @change="load()" class="input text-sm mt-1" :disabled="!filters.district_id">
            <option value="">All Areas</option>
            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }}</option>
          </select>
        </div>
        <div>
          <label class="filter-label">Class Level</label>
          <select v-model="filters.class_level" @change="filters.subject_id = ''; loadSubjects(); load()" class="input text-sm mt-1">
            <option value="">All Classes</option>
            <option v-for="c in classOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
          </select>
        </div>
        <div>
          <label class="filter-label">Subject</label>
          <select v-model="filters.subject_id" @change="load()" class="input text-sm mt-1" :disabled="!filters.class_level">
            <option value="">All Subjects</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="filter-label">Tuition Type</label>
          <select v-model="filters.tuition_type" @change="load()" class="input text-sm mt-1">
            <option value="">All Types</option>
            <option value="home">Home Tutoring</option>
            <option value="online">Online</option>
            <option value="group">Group</option>
            <option value="home_and_online">Home & Online</option>
          </select>
        </div>
        <div>
          <label class="filter-label">Tutor Preference</label>
          <select v-model="filters.tutor_gender_pref" @change="load()" class="input text-sm mt-1">
            <option value="">Any</option>
            <option value="male">Male Preferred</option>
            <option value="female">Female Preferred</option>
          </select>
        </div>
        <div>
          <label class="filter-label">Min Salary (BDT)</label>
          <input v-model.number="filters.salary_min" @change="load()" type="number" min="0" placeholder="e.g. 3000" class="input text-sm mt-1" />
        </div>
        <div>
          <label class="filter-label">Max Salary (BDT)</label>
          <input v-model.number="filters.salary_max" @change="load()" type="number" min="0" placeholder="e.g. 10000" class="input text-sm mt-1" />
        </div>
      </div>
    </div>

    <!-- Results -->
    <div v-if="loading" class="card py-16 text-center text-paper-400 font-body text-sm">Loading jobs…</div>

    <div v-else-if="!jobs.length" class="card py-16 text-center">
      <p class="font-display font-semibold text-navy-700">No open jobs found</p>
      <p class="text-sm text-paper-500 font-body mt-1">Try adjusting your filters or check back later.</p>
    </div>

    <template v-else>
      <p class="text-xs text-paper-400 font-body">Showing {{ meta.total }} open job{{ meta.total !== 1 ? 's' : '' }}</p>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="job in jobs" :key="job.id"
          class="card flex flex-col gap-3 hover:-translate-y-0.5 hover:shadow-md transition-all duration-150">

          <div>
            <div class="flex items-start justify-between gap-2">
              <p class="font-display font-bold text-navy-900 text-sm leading-snug flex-1">{{ job.title }}</p>
              <span v-if="job.already_applied"
                class="text-[10px] font-bold font-display shrink-0 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-pill whitespace-nowrap">
                Applied
              </span>
            </div>
            <p class="text-xs text-paper-400 font-body mt-1">
              Job ID: #{{ job.public_id }} &middot; {{ formatDate(job.created_at) }}
            </p>
          </div>

          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="bg-paper-50 rounded-sm py-2 px-1">
              <p class="text-[10px] text-paper-400 font-body uppercase tracking-wide">Type</p>
              <p class="text-xs font-semibold font-display text-navy-700 mt-0.5 leading-snug">{{ typeLabel(job.tuition_type) }}</p>
            </div>
            <div class="bg-paper-50 rounded-sm py-2 px-1">
              <p class="text-[10px] text-paper-400 font-body uppercase tracking-wide">Salary</p>
              <p class="text-xs font-semibold font-display text-navy-700 mt-0.5">{{ job.offered_salary?.toLocaleString() }} BDT</p>
            </div>
            <div class="bg-paper-50 rounded-sm py-2 px-1">
              <p class="text-[10px] text-paper-400 font-body uppercase tracking-wide">Location</p>
              <p class="text-xs font-semibold font-display text-navy-700 mt-0.5 leading-snug truncate">
                {{ job.area?.name || job.district?.name }}
              </p>
            </div>
          </div>

          <div class="flex flex-wrap gap-1">
            <span v-for="s in job.subjects" :key="s.id"
              class="text-xs bg-navy-50 text-navy-700 border border-navy-100 px-2 py-0.5 rounded-pill font-body">
              {{ s.name }}
            </span>
          </div>

          <div class="flex items-center justify-between mt-auto pt-1">
            <span v-if="job.tutor_gender_pref !== 'any'"
              class="text-xs font-body text-paper-500 capitalize">
              {{ job.tutor_gender_pref === 'female' ? '♀' : '♂' }} Tutor Pref
            </span>
            <span v-else class="text-xs opacity-0">–</span>
            <RouterLink :to="`/jobs/${job.public_id}`"
              class="rounded-sm bg-navy-700 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-navy-800 transition-colors">
              Detail
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="flex justify-center gap-2">
        <button v-for="page in meta.last_page" :key="page"
          @click="filters.page = page; load()"
          class="w-9 h-9 rounded-sm text-sm font-semibold font-display border transition-colors"
          :class="filters.page === page ? 'bg-navy-700 text-white border-navy-700' : 'border-paper-300 text-paper-600 hover:bg-paper-100'">
          {{ page }}
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { tutorJobsApi } from '@/api/jobs.js'
import { searchApi } from '@/api/search.js'

const router = useRouter()
const route  = useRoute()

const jobs     = ref([])
const loading  = ref(false)
const meta     = ref({ total: 0, last_page: 1 })
const districts = ref([])
const areas     = ref([])
const subjects  = ref([])

const filters = reactive({
  q:               route.query.q         || '',
  district_id:     route.query.district_id ? Number(route.query.district_id) : '',
  area_id:         route.query.area_id    ? Number(route.query.area_id)    : '',
  class_level:     route.query.class_level || '',
  subject_id:      route.query.subject_id  ? Number(route.query.subject_id)  : '',
  tuition_type:    route.query.tuition_type || '',
  tutor_gender_pref: route.query.tutor_gender_pref || '',
  salary_min:      route.query.salary_min ? Number(route.query.salary_min) : '',
  salary_max:      route.query.salary_max ? Number(route.query.salary_max) : '',
  page:            route.query.page ? Number(route.query.page) : 1,
})

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

let debounceTimer = null
function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(load, 400)
}

async function load() {
  loading.value = true
  const params = {}
  Object.entries(filters).forEach(([k, v]) => { if (v !== '' && v != null) params[k] = v })
  router.replace({ query: params }).catch(() => {})
  try {
    const { data } = await tutorJobsApi.list(params)
    jobs.value = data.data?.data || data.data || []
    const m = data.data
    meta.value = { total: m?.total ?? jobs.value.length, last_page: m?.last_page ?? 1 }
  } finally {
    loading.value = false
  }
}

async function loadAreas() {
  if (!filters.district_id) { areas.value = []; return }
  const { data } = await searchApi.areas(filters.district_id)
  areas.value = data.data || []
}

async function loadSubjects() {
  if (!filters.class_level) { subjects.value = []; return }
  const { data } = await searchApi.subjects({ class_level: filters.class_level })
  subjects.value = data.data || []
}

function clearFilters() {
  Object.assign(filters, { q:'', district_id:'', area_id:'', class_level:'', subject_id:'',
    tuition_type:'', tutor_gender_pref:'', salary_min:'', salary_max:'', page: 1 })
  areas.value = []
  subjects.value = []
  load()
}

function typeLabel(t) {
  return { home:'Home Tutoring', online:'Online', group:'Group', home_and_online:'Home & Online' }[t] ?? t
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' }) : ''
}

onMounted(async () => {
  const [distRes] = await Promise.all([searchApi.districts()])
  districts.value = distRes.data.data || []
  if (filters.district_id) loadAreas()
  if (filters.class_level) loadSubjects()
  load()
})
</script>

<style scoped>
.filter-label { @apply block text-xs font-semibold font-display text-paper-600 uppercase tracking-wide; }
</style>
