import { ref, computed } from 'vue'
import { useSearchStore } from '@/stores/search.js'
import { MEDIUMS, CLASS_LEVELS } from '@/utils/constants.js'

const PER_PAGE = 9

/**
 * Encapsulates all search state and handlers for SearchPage.vue.
 *
 * Extracted to reduce SearchPage from ~405 lines to a thin shell that
 * only handles template layout and drawer UI state.
 */
export function useTutorSearch() {
  const searchStore  = useSearchStore()
  const lastFilters  = ref({})
  const currentSort  = ref('relevance')

  // ── Computed pagination helpers ───────────────────────────────────────────

  const pagination  = computed(() => searchStore.pagination || { current_page: 1, last_page: 1 })
  const totalTutors = computed(() => Number(pagination.value.total ?? searchStore.results.length ?? 0))

  const shownFrom = computed(() => {
    if (!totalTutors.value) return 0
    const perPage = Number(pagination.value.per_page || PER_PAGE)
    const page    = Number(pagination.value.current_page || 1)
    return ((page - 1) * perPage) + 1
  })

  const shownTo = computed(() =>
    Math.min(shownFrom.value + searchStore.results.length - 1, totalTutors.value)
  )

  const pageButtons = computed(() => {
    const total   = Number(pagination.value.last_page || 1)
    const current = Number(pagination.value.current_page || 1)
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
    const pages    = []
    const addRange = (from, to) => { for (let i = from; i <= to; i++) pages.push(i) }
    pages.push(1)
    if (current > 3) pages.push('...')
    addRange(Math.max(2, current - 1), Math.min(total - 1, current + 1))
    if (current < total - 2) pages.push('...')
    pages.push(total)
    return pages
  })

  // ── Active filter chips ───────────────────────────────────────────────────

  function buildActiveChips(filtersRef, mobileFiltersRef) {
    const f     = lastFilters.value
    const chips = []
    if (f.medium)        chips.push({ key: 'medium',      label: MEDIUMS.find(m => m.value === f.medium)?.label || f.medium })
    if (f.class_level)   chips.push({ key: 'class_level', label: CLASS_LEVELS.find(c => c.value === f.class_level)?.label || f.class_level })
    if (f.subject_ids?.length) {
      const ids   = normalizeSubjectIds(f.subject_ids)
      const names = (filtersRef?.allSubjects || mobileFiltersRef?.allSubjects || [])
        .filter(s => ids.includes(Number(s.id))).map(s => s.name)
      chips.push({
        key: 'subject_ids',
        label: names.length
          ? `Subjects: ${names.slice(0, 2).join(', ')}${names.length > 2 ? ` +${names.length - 2}` : ''}`
          : `${ids.length} subjects`,
      })
    }
    if (f.district_id) chips.push({ key: 'district_id', label: searchStore.districts.find(d => d.id === f.district_id)?.name || 'District' })
    if (f.area_id) {
      const areas = (filtersRef?.allAreas || mobileFiltersRef?.allAreas || [])
      chips.push({ key: 'area_id', label: areas.find(a => a.id === f.area_id)?.name || 'Area' })
    }
    if (f.tutor_gender)   chips.push({ key: 'tutor_gender',  label: f.tutor_gender === 'male' ? 'Male tutor' : 'Female tutor' })
    if (f.university_id) {
      const unis = (filtersRef?.allUniversities || mobileFiltersRef?.allUniversities || [])
      chips.push({ key: 'university_id', label: unis.find(u => u.id === f.university_id)?.name || 'University' })
    }
    if (f.salary_max)   chips.push({ key: 'salary_max',   label: `≤ ৳${Number(f.salary_max).toLocaleString()}` })
    if (f.verified_only) chips.push({ key: 'verified_only', label: 'Verified only' })
    return chips
  }

  // ── Search handlers ───────────────────────────────────────────────────────

  function handleSearch(filters, page = 1) {
    lastFilters.value = filters
    searchStore.search({ ...filters, sort: currentSort.value, per_page: PER_PAGE, page })
  }

  function removeChip(key) {
    const updated = { ...lastFilters.value }
    if (key === 'verified_only') updated[key] = false
    else if (['salary_max', 'area_id', 'university_id'].includes(key)) updated[key] = null
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

  function init(initialFilters = {}) {
    searchStore.loadDistricts()
    handleSearch(Object.keys(initialFilters).length ? initialFilters : {})
  }

  return {
    searchStore, lastFilters, currentSort,
    pagination, totalTutors, shownFrom, shownTo, pageButtons,
    handleSearch, removeChip, clearAll, onSortChange, goPage, buildActiveChips, init,
  }
}
