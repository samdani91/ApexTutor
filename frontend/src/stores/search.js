import { defineStore } from 'pinia'
import { searchApi } from '@/api/search.js'

export const useSearchStore = defineStore('search', {
  state: () => ({
    results: [],
    pagination: null,
    filters: {},
    loading: false,
    districts: [],
    subjects: [],
  }),
  actions: {
    async search(filters = {}) {
      this.loading = true
      this.filters = filters
      try {
        const { data } = await searchApi.tutors(filters)
        this.results = data.data.data || data.data
        this.pagination = normalizePagination(data.data)
      } finally {
        this.loading = false
      }
    },
    async loadDistricts() {
      if (this.districts.length) return
      const { data } = await searchApi.districts()
      this.districts = data.data
    },
    async loadSubjects(classLevel) {
      const { data } = await searchApi.subjects({ class_level: classLevel })
      this.subjects = data.data
    },
  },
})

function normalizePagination(payload) {
  if (!payload || Array.isArray(payload)) return null
  if (payload.meta) return payload.meta
  return {
    current_page: payload.current_page,
    last_page: payload.last_page,
    next_page_url: payload.next_page_url,
    per_page: payload.per_page,
    total: payload.total,
  }
}
