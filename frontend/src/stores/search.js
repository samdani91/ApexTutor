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
        this.pagination = data.data.meta || null
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
