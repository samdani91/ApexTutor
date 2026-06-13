<template>
  <DefaultLayout>
    <main class="public-grid-page relative isolate overflow-hidden bg-paper-50">
      <div class="pointer-events-none absolute inset-0 -z-10 public-grid"></div>
      <div class="mx-auto max-w-[84rem] px-4 py-6 md:py-10">

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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import SearchFilters from '@/components/search/SearchFilters.vue'
import TutorCard from '@/components/tutor/TutorCard.vue'
import { useTutorSearch } from '@/composables/useTutorSearch.js'
import { searchApi } from '@/api/search.js'

const route = useRoute()
const {
  searchStore, lastFilters, currentSort,
  pagination, totalTutors, shownFrom, shownTo, pageButtons,
  handleSearch, removeChip, clearAll, onSortChange, goPage, buildActiveChips, init,
} = useTutorSearch()

const filtersRef       = ref(null)
const mobileFiltersRef = ref(null)
const drawerOpen       = ref(false)

const activeChips = computed(() => buildActiveChips(filtersRef.value, mobileFiltersRef.value))

const sortOptions = [
  { value: 'relevance', label: 'Best match' },
  { value: 'rating', label: 'Highest rated' },
  { value: 'salary_asc', label: 'Salary: low to high' },
  { value: 'salary_desc', label: 'Salary: high to low' },
]
const mobileSortOptions = [
  { value: 'relevance', label: 'Best match' },
  { value: 'rating', label: 'Top rated' },
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
  const f = filtersRef.value || mobileFiltersRef.value
  return f?.activeCount ?? 0
})

function handleMobileSearch(filters) {
  handleSearch(filters)
}

function submitMobileFilters() {
  mobileFiltersRef.value?.applyFilters?.()
  closeDrawer()
}

onMounted(async () => {
  const filters = {}
  // Tag links pass exact filter params directly.
  if (route.query.class_level) filters.class_level = route.query.class_level
  if (route.query.medium)      filters.medium      = route.query.medium

  // Free-text search bar passes ?q=… — resolve it into real filters
  // (subject, area, district, class, medium) so the panel auto-selects them.
  if (route.query.q) {
    try {
      const { data } = await searchApi.resolve(route.query.q)
      Object.assign(filters, data.data || {})
    } catch {
      // resolution is best-effort; fall through with whatever we have
    }
  }

  init(filters)
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
