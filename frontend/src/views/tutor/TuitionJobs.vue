<template>
  <DefaultLayout>
    <main class="public-grid-page relative isolate overflow-x-clip bg-paper-50">
      <div class="pointer-events-none absolute inset-0 -z-10 public-grid"></div>
      <div class="mx-auto max-w-[84rem] px-4 py-6 md:py-10">

        <!-- ── Page header ───────────────────────────────────────── -->
        <div class="reveal mb-5 flex items-start justify-between gap-3 md:mb-7">
          <div>
            <p class="font-display text-xs font-bold uppercase text-gold-600">Tuition Jobs</p>
            <h1 class="mt-1 font-display font-bold text-2xl md:text-4xl text-navy-900 leading-tight">Browse Jobs</h1>
            <p class="mt-2 hidden max-w-2xl font-body text-sm leading-relaxed text-paper-600 md:block">
              Browse tuition jobs posted by guardians and apply directly.
            </p>
          </div>

          <!-- Mobile filter button -->
          <button @click="drawerOpen = true"
            class="relative lg:hidden flex min-h-[44px] items-center justify-center gap-1.5 rounded-sm border border-paper-300 bg-white px-3.5 text-xs font-semibold font-display text-navy-700 shadow-sm transition-colors hover:bg-navy-50 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 9h10M11 14h2M9 19h6"/>
            </svg>
            Filters
            <span v-if="activeCount > 0"
              class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-navy-700 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
              {{ activeCount }}
            </span>
          </button>
        </div>

        <!-- ── Active chips (mobile) ──────────────────────────────── -->
        <div v-if="activeChips.length" class="reveal flex gap-2 flex-wrap mb-4 lg:hidden">
          <span v-for="chip in activeChips" :key="chip.key"
            class="inline-flex items-center gap-1 bg-navy-50 text-navy-700 text-xs font-semibold font-display px-2.5 py-1 rounded-pill border border-navy-100">
            {{ chip.label }}
            <button @click="removeChip(chip.key)" class="ml-0.5 text-navy-400 hover:text-navy-700 leading-none">&times;</button>
          </span>
        </div>

        <!-- ── Main layout ────────────────────────────────────────── -->
        <div class="flex items-stretch gap-7">

          <!-- Desktop sidebar -->
          <aside class="reveal hidden w-72 shrink-0 self-stretch lg:block">
            <div class="sticky top-24 rounded-md border border-paper-200 bg-white p-5 shadow-lg">
              <TuitionJobFilters ref="filtersRef" :model-value="lastFilters" @search="handleSearch" />
            </div>
          </aside>

          <!-- Results area -->
          <section class="min-w-0 flex-1">

            <!-- Result meta -->
            <div class="reveal hidden lg:flex items-center justify-between mb-4">
              <div class="rounded-md border border-paper-200 bg-white px-4 py-3 shadow-sm">
                <p class="font-display text-base font-bold text-navy-900">
                  {{ meta.total }} job{{ meta.total !== 1 ? 's' : '' }} found
                </p>
                <p v-if="meta.total > 0" class="mt-0.5 font-body text-xs text-paper-500">
                  Showing {{ shownFrom }}–{{ shownTo }} of {{ meta.total }}
                </p>
              </div>
            </div>

            <!-- Loading -->
            <div v-if="loading"
              class="reveal rounded-md border border-paper-200 bg-white/88 flex flex-col items-center justify-center py-24 gap-3 shadow-sm backdrop-blur-sm">
              <div class="w-9 h-9 border-4 border-navy-100 border-t-navy-700 rounded-full animate-spin"></div>
              <p class="text-sm text-paper-500 font-body">Searching jobs…</p>
            </div>

            <!-- Empty -->
            <div v-else-if="!jobs.length"
              class="reveal rounded-md border border-paper-200 bg-white/90 flex flex-col items-center justify-center py-20 text-center px-4 shadow-sm">
              <div class="w-16 h-16 bg-navy-50 rounded-lg flex items-center justify-center mb-5">
                <svg class="w-8 h-8 text-navy-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25a2.25 2.25 0 01-2.25 2.25h-12a2.25 2.25 0 01-2.25-2.25v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>
                </svg>
              </div>
              <p class="font-display font-semibold text-navy-700 text-lg mb-2">No jobs found</p>
              <p class="text-paper-500 text-sm font-body max-w-xs leading-relaxed">
                Try broadening your filters — a wider district, different class level, or higher salary range.
              </p>
              <button @click="filtersRef?.clearFilters?.()" class="mt-5 btn-outline text-sm py-2 px-5">Clear Filters</button>
            </div>

            <!-- Job cards -->
            <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-4 lg:gap-5">
              <div v-for="(job, index) in jobs" :key="job.id"
                class="job-card reveal flex h-full min-h-[22rem] flex-col rounded-md border border-paper-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-navy-200 hover:shadow-lg sm:p-6"
                :style="{ animationDelay: `${Math.min(index, 8) * 45}ms` }">

                <div class="min-h-[6.75rem]">
                  <RouterLink :to="`/jobs/${job.public_id}`"
                    class="job-title-clamp block max-w-3xl font-display text-xl font-semibold leading-snug text-navy-900 transition-colors hover:text-navy-700">
                    {{ job.title }}
                  </RouterLink>
                  <p class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-paper-500 font-body">
                    <span>Job ID : <span class="font-medium text-paper-600">{{ job.public_id }}</span></span>
                    <span class="hidden h-5 w-px bg-paper-400 sm:block"></span>
                    <span>Posted Date : <span class="font-medium text-paper-600">{{ formatDate(job.created_at) }}</span></span>
                  </p>
                </div>

                <div class="mt-6 flex flex-1 flex-col">
                  <div class="grid gap-x-8 gap-y-7 sm:grid-cols-2">
                    <div class="job-info-row">
                      <svg class="job-info-icon" fill="none" stroke="currentColor" stroke-width="1.85" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 4.75h5.5v5.5h-5.5v-5.5ZM13.75 4.75h5.5v5.5h-5.5v-5.5ZM4.75 13.75h5.5v5.5h-5.5v-5.5ZM13.75 13.75h5.5v5.5h-5.5v-5.5Z"/>
                      </svg>
                      <span class="min-w-0">
                        <span class="job-info-label">Tuition Type</span>
                        <span class="job-info-value">{{ typeLabel(job.tuition_type) }}</span>
                      </span>
                    </div>

                    <div class="job-info-row">
                      <svg class="job-info-icon" fill="none" stroke="currentColor" stroke-width="1.85" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/>
                      </svg>
                      <span class="min-w-0">
                        <span class="job-info-label">Salary</span>
                        <span class="job-info-value">{{ job.offered_salary?.toLocaleString() || 'Negotiable' }} BDT</span>
                      </span>
                    </div>

                    <div class="job-info-row">
                      <svg class="job-info-icon" fill="none" stroke="currentColor" stroke-width="1.85" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0z"/>
                      </svg>
                      <span class="min-w-0">
                        <span class="job-info-label">Location</span>
                        <span class="job-info-value">{{ job.area?.name ? `${job.area.name}, ${job.district?.name}` : (job.district?.name || 'Location not set') }}</span>
                      </span>
                    </div>

                    <div class="job-info-row">
                      <svg class="job-info-icon" fill="none" stroke="currentColor" stroke-width="1.85" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                      </svg>
                      <span class="min-w-0">
                        <span class="job-info-label">Subjects</span>
                        <span class="job-info-value job-info-clamp">{{ job.subjects?.map(s => s.name).join(', ') || 'Not specified' }}</span>
                      </span>
                    </div>
                  </div>

                  <div class="mt-auto flex flex-col gap-4 pt-8 sm:flex-row sm:items-center sm:justify-between">
                    <span v-if="job.tutor_gender_pref && job.tutor_gender_pref !== 'any'"
                      class="inline-flex items-center gap-1.5 text-sm font-semibold font-display"
                      :class="job.tutor_gender_pref === 'female' ? 'text-pink-600' : 'text-emerald-700'">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/>
                      </svg>
                      {{ genderLabel(job.tutor_gender_pref) }} preferred
                    </span>
                    <span v-else class="text-sm font-semibold font-display text-paper-500">Any tutor preferred</span>

                    <div class="flex items-center gap-3 sm:ml-auto">
                      <span v-if="job.already_applied"
                        class="inline-flex items-center rounded-pill border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold font-display text-emerald-700">
                        Applied
                      </span>
                    <RouterLink :to="`/jobs/${job.public_id}`"
                      class="inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold font-display text-navy-700 transition-colors hover:bg-navy-50">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                      </svg>
                      Details
                    </RouterLink>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="meta.last_page > 1 && !loading"
              class="reveal mt-8 flex items-center justify-center gap-1 flex-wrap">
              <button @click="goPage(currentPage - 1)" :disabled="currentPage === 1"
                class="w-9 h-9 flex items-center justify-center rounded-md border border-paper-300 bg-white text-navy-700 shadow-xs hover:bg-navy-50 disabled:opacity-35 disabled:cursor-not-allowed transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
              </button>
              <button v-for="p in meta.last_page" :key="p"
                @click="goPage(p)"
                class="w-9 h-9 flex items-center justify-center rounded-md text-sm font-semibold font-display border shadow-xs transition-colors"
                :class="p === currentPage ? 'bg-navy-700 text-white border-navy-700' : 'border-paper-300 bg-white text-navy-700 hover:bg-navy-50'">
                {{ p }}
              </button>
              <button @click="goPage(currentPage + 1)" :disabled="currentPage === meta.last_page"
                class="w-9 h-9 flex items-center justify-center rounded-md border border-paper-300 bg-white text-navy-700 shadow-xs hover:bg-navy-50 disabled:opacity-35 disabled:cursor-not-allowed transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
              </button>
            </div>

          </section>
        </div>
      </div>
    </main>

    <!-- ── Mobile filter drawer ─────────────────────────────── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="drawerOpen" class="fixed inset-0 z-[200] bg-navy-900/50 lg:hidden"
          style="backdrop-filter:blur(2px)" @click="drawerOpen = false" />
      </Transition>
      <Transition name="slide-up">
        <div v-if="drawerOpen"
          class="fixed bottom-0 left-0 right-0 z-[201] bg-white rounded-t-lg lg:hidden flex flex-col"
          style="max-height:92dvh;max-height:92vh" @click.stop>
          <div class="flex justify-center pt-3 pb-1 shrink-0">
            <div class="w-10 h-1 bg-paper-300 rounded-full"></div>
          </div>
          <div class="flex items-center justify-between px-5 py-3 border-b border-paper-200 shrink-0">
            <div class="flex items-center gap-2">
              <h2 class="font-display font-semibold text-navy-700 text-base">Filters</h2>
              <span v-if="activeCount > 0"
                class="text-xs font-bold font-display bg-navy-700 text-white px-2 py-0.5 rounded-pill">
                {{ activeCount }} active
              </span>
            </div>
            <button @click="drawerOpen = false"
              class="w-8 h-8 flex items-center justify-center rounded-full text-paper-500 hover:bg-paper-100 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="overflow-y-auto overscroll-contain flex-1 px-5 py-4">
            <TuitionJobFilters ref="mobileFiltersRef" :model-value="lastFilters" @search="handleMobileSearch" />
          </div>
          <div class="shrink-0 px-5 pb-6 pt-3 border-t border-paper-200 bg-white">
            <button @click="submitMobileFilters" class="btn-primary w-full py-3.5 text-sm">
              Show Results
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </DefaultLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { tutorJobsApi } from '@/api/jobs.js'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import TuitionJobFilters from './TuitionJobFilters.vue'

const jobs            = ref([])
const loading         = ref(false)
const drawerOpen      = ref(false)
const currentPage     = ref(1)
const meta            = ref({ total: 0, last_page: 1, per_page: 10 })
const filtersRef      = ref(null)
const mobileFiltersRef = ref(null)

const PER_PAGE = 10

const lastFilters = ref({})

const shownFrom = computed(() => Math.min((currentPage.value - 1) * PER_PAGE + 1, meta.value.total))
const shownTo   = computed(() => Math.min(currentPage.value * PER_PAGE, meta.value.total))
const activeCount = computed(() => filtersRef.value?.activeCount ?? 0)

const activeChips = computed(() => {
  const f = lastFilters.value
  const chips = []
  if (f.q)                chips.push({ key: 'q',                label: `"${f.q}"` })
  if (f.district_id)      chips.push({ key: 'district_id',      label: 'District' })
  if (f.area_id)          chips.push({ key: 'area_id',          label: 'Area' })
  if (f.class_level)      chips.push({ key: 'class_level',      label: f.class_level.replace('_', ' ').toUpperCase() })
  if (f.subject_id)       chips.push({ key: 'subject_id',       label: 'Subject' })
  if (f.tuition_type)     chips.push({ key: 'tuition_type',     label: typeLabel(f.tuition_type) })
  if (f.tutor_gender_pref) chips.push({ key: 'tutor_gender_pref', label: f.tutor_gender_pref === 'male' ? 'Male Pref' : 'Female Pref' })
  if (f.salary_min)       chips.push({ key: 'salary_min',       label: `Min ৳${Number(f.salary_min).toLocaleString()}` })
  if (f.salary_max)       chips.push({ key: 'salary_max',       label: `Max ৳${Number(f.salary_max).toLocaleString()}` })
  return chips
})

function removeChip(key) {
  const next = { ...lastFilters.value }
  delete next[key]
  if (key === 'district_id') delete next.area_id
  if (key === 'class_level') delete next.subject_id
  lastFilters.value = next
  currentPage.value = 1
  doLoad(next)
}

function handleSearch(params) {
  lastFilters.value = { ...params }
  currentPage.value = 1
  doLoad(params)
}

function handleMobileSearch(params) {
  lastFilters.value = { ...params }
}

function submitMobileFilters() {
  drawerOpen.value = false
  currentPage.value = 1
  doLoad(lastFilters.value)
}

async function doLoad(params = {}) {
  loading.value = true
  const query = { ...params, page: currentPage.value, per_page: PER_PAGE }
  try {
    const { data } = await tutorJobsApi.list(query)
    const d = data.data
    jobs.value = d?.data || d || []
    meta.value = {
      total:     d?.total     ?? jobs.value.length,
      last_page: d?.last_page ?? 1,
      per_page:  d?.per_page  ?? PER_PAGE,
    }
  } finally {
    loading.value = false
  }
}

function goPage(p) {
  if (p < 1 || p > meta.value.last_page) return
  currentPage.value = p
  doLoad(lastFilters.value)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function typeLabel(t) {
  return {
    student_home:    "Student's Home",
    tutor_home:      "Tutor's Home",
    online:          'Online',
    home:            'Home',
    group:           'Group',
    home_and_online: 'Home & Online',
  }[t] ?? t
}

function genderBadgeClass(pref) {
  if (pref === 'male')   return 'bg-emerald-50 text-emerald-700 border-emerald-200'
  if (pref === 'female') return 'bg-pink-50 text-pink-600 border-pink-200'
  return 'bg-navy-50 text-navy-700 border-navy-100'
}

function genderLabel(pref) {
  if (pref === 'male')   return 'Male tutor'
  if (pref === 'female') return 'Female tutor'
  return 'Any tutor'
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' }) : ''
}

// Initial load
doLoad(lastFilters.value)
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.25s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); }

.job-card {
  contain: layout paint;
}

.job-title-clamp {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.job-fact {
  @apply flex min-w-0 items-center gap-2 rounded-md border border-paper-200 bg-paper-50/80 px-3 py-2.5;
}

.job-fact-icon {
  @apply flex h-8 w-8 shrink-0 items-center justify-center rounded-sm bg-white text-navy-600 shadow-xs;
}

.job-fact-label {
  @apply block text-[10px] font-bold font-display uppercase text-paper-400;
}

.job-fact-value {
  @apply block text-sm font-bold font-display leading-snug text-navy-800;
}

.job-info-row {
  @apply flex min-w-0 items-start gap-3;
}

.job-info-icon {
  @apply mt-1 h-4 w-4 shrink-0 text-navy-600;
}

.job-info-label {
  @apply block text-sm font-body text-paper-500;
}

.job-info-value {
  @apply mt-0.5 block text-sm font-semibold font-body leading-relaxed text-paper-700;
}

.job-info-clamp {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.public-grid {
  background-image:
    linear-gradient(rgba(15, 46, 92, 0.038) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 46, 92, 0.038) 1px, transparent 1px);
  background-size: 34px 34px;
}
</style>
