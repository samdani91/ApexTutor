<template>
  <DefaultLayout>
    <div class="max-w-7xl mx-auto px-4 py-5 md:py-8">

      <!-- ── Page header row ─────────────────────────────────── -->
      <div class="flex items-center justify-between gap-3 mb-4 md:mb-6">
        <h1 class="font-display font-bold text-xl md:text-3xl text-navy-900 leading-tight">
          Find tutors
        </h1>

        <!-- Mobile: filter + sort inline -->
        <div class="flex items-center gap-2 lg:hidden">
          <select v-model="currentSort" @change="onSortChange"
            class="status-select h-9 min-w-[8.75rem]">
            <option value="relevance">Best match</option>
            <option value="rating">Top rated</option>
            <option value="newest">Newest</option>
            <option value="salary_asc">Salary ↑</option>
            <option value="salary_desc">Salary ↓</option>
          </select>

          <button @click="openDrawer"
            class="relative flex items-center gap-1.5 border border-paper-300 bg-white px-3 py-2 rounded-sm text-xs font-semibold font-display text-navy-700 hover:bg-navy-50 hover:border-navy-200 transition-colors h-9 shrink-0">
            <!-- filter icon -->
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
      <div v-if="activeChips.length" class="flex gap-2 flex-wrap mb-4 lg:hidden">
        <span v-for="chip in activeChips" :key="chip.key"
          class="inline-flex items-center gap-1 bg-navy-50 text-navy-700 text-xs font-semibold font-display px-2.5 py-1 rounded-pill border border-navy-100">
          {{ chip.label }}
          <button @click="removeChip(chip.key)" class="ml-0.5 text-navy-400 hover:text-navy-700 leading-none">&times;</button>
        </span>
      </div>

      <!-- ── Main layout ─────────────────────────────────────── -->
      <div class="flex gap-7">

        <!-- Desktop sidebar -->
        <aside class="w-80 shrink-0 hidden lg:block">
          <div class="bg-white border border-paper-200 rounded-sm shadow-sm p-5 sticky top-24 max-h-[calc(100vh-7rem)] overflow-y-auto">
            <SearchFilters ref="filtersRef" @search="handleSearch" />
          </div>
        </aside>

        <!-- Results area -->
        <section class="flex-1 min-w-0">

          <!-- Desktop result meta + sort -->
          <div class="hidden lg:flex items-center justify-between mb-4 gap-4">
            <p class="text-sm text-paper-500 font-body">
              <span class="font-semibold text-navy-900">{{ searchStore.results.length }}</span> tutors found
            </p>
            <select v-model="currentSort" @change="onSortChange"
              class="input input-compact min-w-[12rem]">
              <option value="relevance">Best match</option>
              <option value="rating">Highest rated</option>
              <option value="newest">Newest</option>
              <option value="salary_asc">Salary: low to high</option>
              <option value="salary_desc">Salary: high to low</option>
            </select>
          </div>

          <!-- Loading spinner -->
          <div v-if="searchStore.loading"
            class="flex flex-col items-center justify-center py-24 gap-3">
            <div class="w-9 h-9 border-4 border-navy-100 border-t-navy-700 rounded-full animate-spin"></div>
            <p class="text-sm text-paper-500 font-body">Searching tutors…</p>
          </div>

          <!-- Empty state -->
          <div v-else-if="!searchStore.results.length"
            class="flex flex-col items-center justify-center py-20 text-center px-4">
            <div class="w-16 h-16 bg-navy-50 rounded-2xl flex items-center justify-center mb-5">
              <svg class="w-8 h-8 text-navy-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/>
              </svg>
            </div>
            <p class="font-display font-semibold text-navy-700 text-lg mb-2">No tutors found</p>
            <p class="text-paper-500 text-sm font-body max-w-xs leading-relaxed">
              Try broadening your filters — fewer subjects, a wider district, or a higher budget.
            </p>
            <button @click="clearAll" class="mt-5 btn-outline text-sm py-2 px-5">Clear filters</button>
          </div>

          <!-- Tutor grid — 1 col mobile, 2 col tablet, 3 col desktop -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 md:gap-4">
            <TutorCard v-for="tutor in searchStore.results" :key="tutor.id" :tutor="tutor" />
          </div>

          <!-- Load more -->
          <div v-if="searchStore.results.length && !searchStore.loading"
            class="mt-8 md:mt-10 flex justify-center">
            <button class="btn-outline text-sm py-2.5 px-8">Load more</button>
          </div>
        </section>
      </div>
    </div>

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
            <SearchFilters ref="mobileFiltersRef" @search="handleMobileSearch" />
          </div>

          <!-- Sticky bottom action bar -->
          <div class="shrink-0 px-5 pb-6 pt-3 border-t border-paper-200 bg-white">
            <button @click="submitMobileFilters"
              class="btn-primary w-full py-3.5 text-sm">
              Show results
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

// ── Active chips for mobile summary row ────────────────────
const activeChips = computed(() => {
  const f = lastFilters.value
  const chips = []
  if (f.medium) chips.push({ key: 'medium', label: MEDIUMS.find(m => m.value === f.medium)?.label || f.medium })
  if (f.class_level) chips.push({ key: 'class_level', label: CLASS_LEVELS.find(c => c.value === f.class_level)?.label || f.class_level })
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
  else updated[key] = ''
  handleSearch(updated)
}

function clearAll() {
  lastFilters.value = {}
  searchStore.search({})
}

// ── Search handlers ─────────────────────────────────────────
function handleSearch(filters) {
  lastFilters.value = filters
  searchStore.search({ ...filters, sort: currentSort.value })
}

function handleMobileSearch(filters) {
  handleSearch(filters)
  closeDrawer()
}

// Triggered from the "Show results" button inside the drawer
function submitMobileFilters() {
  mobileFiltersRef.value?.applyFilters?.()
}

function onSortChange() {
  searchStore.search({ ...lastFilters.value, sort: currentSort.value })
}

onMounted(() => {
  searchStore.loadDistricts()
  const initial = route.query.q ? { q: route.query.q } : {}
  handleSearch(initial)
})
</script>

<style scoped>
/* Scrim */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Panel slide-up */
.slide-up-enter-active { transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
.slide-up-leave-active { transition: transform 0.22s ease-in; }
.slide-up-enter-from  { transform: translateY(100%); }
.slide-up-leave-to    { transform: translateY(100%); }
</style>
