import { ref } from 'vue'

/**
 * Manages paginated API list state.
 *
 * Usage:
 *   const { items, meta, loading, load } = usePagination(
 *     (params) => adminApi.getTutors(params)
 *   )
 *   onMounted(() => load())
 *
 * The fetcher receives { page, ...extraParams } and must return an axios response
 * whose data.data is either a paginator object ({ data: [], current_page, ... })
 * or a plain array.
 */
export function usePagination(fetcher, extraParams = {}) {
  const items   = ref([])
  const loading = ref(false)
  const meta    = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

  async function load(page = 1) {
    loading.value = true
    try {
      const { data } = await fetcher({ page, ...extraParams })
      const payload = data.data
      if (Array.isArray(payload)) {
        items.value = payload
      } else {
        items.value = payload.data ?? []
        meta.value  = payload
      }
    } finally {
      loading.value = false
    }
  }

  function goPage(page) {
    if (page >= 1 && page <= meta.value.last_page) {
      load(page)
    }
  }

  return { items, meta, loading, load, goPage }
}
