<template>
  <DefaultLayout>
    <main class="public-grid-page relative isolate overflow-hidden bg-paper-50">
      <div class="pointer-events-none absolute inset-0 -z-10 public-grid"></div>
      <div class="max-w-7xl mx-auto px-4 py-6 md:py-10">

      <!-- ── Page header row ─────────────────────────────────── -->
      <div class="reveal mb-5 flex items-start justify-between gap-3 md:mb-7">
        <div>
          <p class="font-display text-xs font-bold uppercase text-gold-600">Tutor search</p>
          <h1 class="mt-1 font-display font-bold text-2xl md:text-4xl text-navy-900 leading-tight">
            Find Tutors
          </h1>
          <p class="mt-2 hidden max-w-2xl font-body text-sm leading-relaxed text-paper-600 md:block">
            Filter verified tutor profiles by subject, class, area, salary and teaching preference.
          </p>
        </div>

        <!-- Mobile: filter + sort inline -->
        <div class="ml-auto grid min-w-0 grid-cols-[minmax(8.75rem,1fr)_auto] items-stretch gap-2 lg:hidden">
          <DropSelect v-model="currentSort" :options="mobileSortOptions" placeholder="Best match"
            class="min-w-0 shadow-sm"
            @update:modelValue="onSortChange" />

          <button @click="openDrawer"
            class="relative flex min-h-[44px] items-center justify-center gap-1.5 rounded-sm border border-paper-300 bg-white px-3.5 text-xs font-semibold font-display text-navy-700 shadow-sm transition-colors hover:border-navy-200 hover:bg-navy-50 shrink-0">
            <!-- filter icon -->
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 9h10M11 14h2M9 19h6"/>
            </svg>
            Filters
            <span v-if="activeFilterCount > 0"
              class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-navy-700 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
              {{ activeFilterCount }}
            </span>
          </button>
        </div>
      </div>

      <!-- ── Active filter chips (mobile) ───────────────────── -->
      <div v-if="activeChips.length" class="reveal flex gap-2 flex-wrap mb-4 lg:hidden">
        <span v-for="chip in activeChips" :key="chip.key"
          class="inline-flex items-center gap-1 bg-navy-50 text-navy-700 text-xs font-semibold font-display px-2.5 py-1 rounded-pill border border-navy-100">
          {{ chip.label }}
          <button @click="removeChip(chip.key)" class="ml-0.5 text-navy-400 hover:text-navy-700 leading-none">&times;</button>
        </span>
      </div>

      <!-- ── Main layout ─────────────────────────────────────── -->
      <div class="flex items-stretch gap-7">

        <!-- Desktop sidebar -->
        <aside class="reveal hidden w-80 shrink-0 self-stretch lg:block">
          <div class="h-full rounded-md border border-paper-200 bg-white p-5 shadow-lg">
            <SearchFilters ref="filtersRef" :model-value="lastFilters" @search="handleSearch" />
          </div>
        </aside>

        <!-- Results area -->
        <section class="min-w-0 flex-1">

          <!-- Desktop result meta + sort -->
          <div class="reveal hidden lg:flex items-center justify-between mb-4 gap-4">
            <div class="rounded-md border border-paper-200 bg-white px-4 py-3 shadow-sm">
              <p class="font-display text-base font-bold text-navy-900">
                {{ totalTutors }} tutor{{ totalTutors === 1 ? '' : 's' }} found
              </p>
              <p v-if="totalTutors" class="mt-0.5 font-body text-xs text-paper-500">
                Showing {{ shownFrom }}-{{ shownTo }} of {{ totalTutors }}
              </p>
            </div>
            <div class="w-52">
              <DropSelect v-model="currentSort" :options="sortOptions" placeholder="Best match"
                @update:modelValue="onSortChange" />
            </div>
          </div>

          <!-- Loading spinner -->
          <div v-if="searchStore.loading"
            class="reveal rounded-md border border-paper-200 bg-white/88 flex flex-col items-center justify-center py-24 gap-3 shadow-sm backdrop-blur-sm">
            <div class="w-9 h-9 border-4 border-navy-100 border-t-navy-700 rounded-full animate-spin"></div>
            <p class="text-sm text-paper-500 font-body">Searching tutors…</p>
          </div>

          <!-- Empty state -->
          <div v-else-if="!searchStore.results.length"
            class="reveal rounded-md border border-paper-200 bg-white/90 flex flex-col items-center justify-center py-20 text-center px-4 shadow-sm backdrop-blur-sm">
            <div class="w-16 h-16 bg-navy-50 rounded-lg flex items-center justify-center mb-5">
              <svg class="w-8 h-8 text-navy-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/>
              </svg>
            </div>
            <p class="font-display font-semibold text-navy-700 text-lg mb-2">No tutors found</p>
            <p class="text-paper-500 text-sm font-body max-w-xs leading-relaxed">
              Try broadening your filters — fewer subjects, a wider district, or a higher budget.
            </p>
            <button @click="clearAll" class="mt-5 btn-outline text-sm py-2 px-5">Clear Filters</button>
          </div>

          <!-- Tutor grid — 1 col mobile, 2 col tablet, 3 col desktop -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 md:gap-4">
            <TutorCard
              v-for="(tutor, index) in searchStore.results"
              :key="tutor.id"
              :tutor="tutor"
              class="reveal"
              :style="{ animationDelay: `${Math.min(index, 8) * 45}ms` }"
            />
          </div>

          <!-- Pagination -->
          <div v-if="pagination.last_page > 1 && !searchStore.loading"
            class="reveal mt-8 md:mt-10 flex items-center justify-center gap-1 flex-wrap">
            <button @click="goPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
              class="w-9 h-9 flex items-center justify-center rounded-md border border-paper-300 bg-white text-navy-700 shadow-xs hover:bg-navy-50 disabled:opacity-35 disabled:cursor-not-allowed transition-colors"
              aria-label="Previous page">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
              </svg>
            </button>

            <template v-for="page in pageButtons" :key="page">
              <span v-if="page === '...'"
                class="w-8 h-8 flex items-center justify-center text-paper-400 text-sm font-body select-none">
                …
              </span>
              <button v-else @click="goPage(page)"
                class="w-9 h-9 flex items-center justify-center rounded-md text-sm font-semibold font-display border shadow-xs transition-colors"
                :class="page === pagination.current_page
                  ? 'bg-navy-700 text-white border-navy-700'
                  : 'border-paper-300 bg-white text-navy-700 hover:bg-navy-50'">
                {{ page }}
              </button>
            </template>

            <button @click="goPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
              class="w-9 h-9 flex items-center justify-center rounded-md border border-paper-300 bg-white text-navy-700 shadow-xs hover:bg-navy-50 disabled:opacity-35 disabled:cursor-not-allowed transition-colors"
              aria-label="Next page">
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
      <!-- Scrim: separate fade transition -->
      <Transition name="fade">
        <div v-if="drawerOpen"
          class="fixed inset-0 z-[200] bg-navy-900/50 lg:hidden"
          style="backdrop-filter: blur(2px)"
          @click="closeDrawer" />
      </Transition>

      <!-- Panel: separate slide-up transition -->
      <Transition name="slide-up">
        <div v-if="drawerOpen"
          class="fixed bottom-0 left-0 right-0 z-[201] bg-white rounded-t-lg lg:hidden flex flex-col"
          style="max-height: 92dvh; max-height: 92vh"
          @click.stop>

          <!-- Drag handle -->
          <div class="flex justify-center pt-3 pb-1 shrink-0">
            <div class="w-10 h-1 bg-paper-300 rounded-full"></div>
          </div>

          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-3 border-b border-paper-200 shrink-0">
            <div class="flex items-center gap-2">
              <h2 class="font-display font-semibold text-navy-700 text-base">Filters</h2>
              <span v-if="activeFilterCount > 0"
                class="text-xs font-bold font-display bg-navy-700 text-white px-2 py-0.5 rounded-pill">
                {{ activeFilterCount }} active
              </span>
            </div>
            <button @click="closeDrawer"
              class="w-8 h-8 flex items-center justify-center rounded-full text-paper-500 hover:bg-paper-100 hover:text-navy-700 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Scrollable filter content -->
          <div class="overflow-y-auto overscroll-contain flex-1 px-5 py-4">
            <SearchFilters ref="mobileFiltersRef" :model-value="lastFilters" @search="handleMobileSearch" />
          </div>

          <!-- Sticky bottom action bar -->
          <div class="shrink-0 px-5 pb-6 pt-3 border-t border-paper-200 bg-white">
            <button @click="submitMobileFilters"
              class="btn-primary w-full py-3.5 text-sm">
              Show Results
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </DefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import SearchFilters from '@/components/search/SearchFilters.vue'
import TutorCard from '@/components/tutor/TutorCard.vue'
import { useSearchStore } from '@/stores/search.js'
import { MEDIUMS, CLASS_LEVELS } from '@/utils/constants.js'

const searchStore = useSearchStore()
const route = useRoute()

const filtersRef = ref(null)
const mobileFiltersRef = ref(null)
const drawerOpen = ref(false)
const currentSort = ref('relevance')
const lastFilters = ref({})
const PER_PAGE = 9
const sortOptions = [
  { value: 'relevance', label: 'Best match' },
  { value: 'rating', label: 'Highest rated' },
  { value: 'newest', label: 'Newest' },
  { value: 'salary_asc', label: 'Salary: low to high' },
  { value: 'salary_desc', label: 'Salary: high to low' },
]
const mobileSortOptions = [
  { value: 'relevance', label: 'Best match' },
  { value: 'rating', label: 'Top rated' },
  { value: 'newest', label: 'Newest' },
  { value: 'salary_asc', label: 'Salary ↑' },
  { value: 'salary_desc', label: 'Salary ↓' },
]

// ── Drawer helpers ──────────────────────────────────────────
function openDrawer() {
  drawerOpen.value = true
  document.body.style.overflow = 'hidden'
}
function closeDrawer() {
  drawerOpen.value = false
  document.body.style.overflow = ''
}
onUnmounted(() => { document.body.style.overflow = '' })

// ── Active filter count (from exposed ref) ──────────────────
const activeFilterCount = computed(() => {
  const ref = filtersRef.value || mobileFiltersRef.value
  return ref?.activeCount ?? 0
})

const pagination = computed(() => searchStore.pagination || { current_page: 1, last_page: 1 })
const totalTutors = computed(() => Number(pagination.value.total ?? searchStore.results.length ?? 0))
const shownFrom = computed(() => {
  if (!totalTutors.value) return 0
  const perPage = Number(pagination.value.per_page || PER_PAGE)
  const page = Number(pagination.value.current_page || 1)
  return ((page - 1) * perPage) + 1
})
const shownTo = computed(() => Math.min(
  shownFrom.value + searchStore.results.length - 1,
  totalTutors.value
))
const pageButtons = computed(() => {
  const total = Number(pagination.value.last_page || 1)
  const current = Number(pagination.value.current_page || 1)
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = []
  const addRange = (from, to) => { for (let i = from; i <= to; i++) pages.push(i) }
  pages.push(1)
  if (current > 3) pages.push('...')
  addRange(Math.max(2, current - 1), Math.min(total - 1, current + 1))
  if (current < total - 2) pages.push('...')
  pages.push(total)
  return pages
})

// ── Active chips for mobile summary row ────────────────────
const activeChips = computed(() => {
  const f = lastFilters.value
  const chips = []
  if (f.medium) chips.push({ key: 'medium', label: MEDIUMS.find(m => m.value === f.medium)?.label || f.medium })
  if (f.class_level) chips.push({ key: 'class_level', label: CLASS_LEVELS.find(c => c.value === f.class_level)?.label || f.class_level })
  if (f.subject_ids?.length) {
    const selectedSubjectIds = normalizeSubjectIds(f.subject_ids)
    const subjectNames = (filtersRef.value?.allSubjects || mobileFiltersRef.value?.allSubjects || [])
      .filter(subject => selectedSubjectIds.includes(Number(subject.id)))
      .map(subject => subject.name)
    chips.push({
      key: 'subject_ids',
      label: subjectNames.length ? `Subjects: ${subjectNames.slice(0, 2).join(', ')}${subjectNames.length > 2 ? ` +${subjectNames.length - 2}` : ''}` : `${selectedSubjectIds.length} subjects`,
    })
  }
  if (f.district_id) chips.push({ key: 'district_id', label: searchStore.districts.find(d => d.id === f.district_id)?.name || 'District' })
  if (f.area_id) {
    const areaName = (filtersRef.value?.allAreas || mobileFiltersRef.value?.allAreas || []).find(a => a.id === f.area_id)?.name || 'Area'
    chips.push({ key: 'area_id', label: areaName })
  }
  if (f.tutor_gender) chips.push({ key: 'tutor_gender', label: f.tutor_gender === 'male' ? 'Male tutor' : 'Female tutor' })
  if (f.days_per_week) chips.push({ key: 'days_per_week', label: `${f.days_per_week}d/wk` })
  if (f.salary_max) chips.push({ key: 'salary_max', label: `≤ ৳${Number(f.salary_max).toLocaleString()}` })
  if (f.verified_only) chips.push({ key: 'verified_only', label: 'Verified only' })
  return chips
})

function removeChip(key) {
  const updated = { ...lastFilters.value }
  if (key === 'verified_only') updated[key] = false
  else if (key === 'days_per_week' || key === 'salary_max' || key === 'area_id') updated[key] = null
  else if (key === 'subject_ids') updated[key] = []
  else {
    updated[key] = ''
    if (key === 'class_level') updated.subject_ids = []
  }
  handleSearch(updated)
}

function clearAll() {
  lastFilters.value = {}
  searchStore.search({ per_page: PER_PAGE })
}

// ── Search handlers ─────────────────────────────────────────
function handleSearch(filters, page = 1) {
  lastFilters.value = filters
  searchStore.search({ ...filters, sort: currentSort.value, per_page: PER_PAGE, page })
}

function handleMobileSearch(filters) {
  // Update results in background — drawer stays open so user can keep selecting filters
  handleSearch(filters)
}

// Triggered from the "Show Results" button inside the drawer — flush any pending debounce and close
function submitMobileFilters() {
  mobileFiltersRef.value?.applyFilters?.()
  closeDrawer()
}

function onSortChange() {
  searchStore.search({ ...lastFilters.value, sort: currentSort.value, per_page: PER_PAGE, page: 1 })
}

function goPage(page) {
  if (page < 1 || page > pagination.value.last_page || page === pagination.value.current_page) return
  handleSearch(lastFilters.value, page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function normalizeSubjectIds(value) {
  if (Array.isArray(value)) return value.map(Number).filter(Boolean)
  if (typeof value === 'string') return value.split(',').map(Number).filter(Boolean)
  return []
}

onMounted(() => {
  searchStore.loadDistricts()
  const initial = route.query.q ? { q: route.query.q } : {}
  handleSearch(initial)
})
</script>

<style scoped>
.public-grid {
  background-image:
    linear-gradient(rgba(15, 46, 92, 0.038) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 46, 92, 0.038) 1px, transparent 1px);
  background-size: 34px 34px;
}

.reveal {
  animation: reveal-up 0.56s ease both;
}

@keyframes reveal-up {
  from {
    opacity: 0;
    transform: translateY(14px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Scrim */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Panel slide-up */
.slide-up-enter-active { transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
.slide-up-leave-active { transition: transform 0.22s ease-in; }
.slide-up-enter-from  { transform: translateY(100%); }
.slide-up-leave-to    { transform: translateY(100%); }

@media (prefers-reduced-motion: reduce) {
  .reveal {
    animation: none;
  }
}
</style>
