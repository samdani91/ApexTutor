import { computed, reactive } from 'vue'
import { searchApi } from '@/api/search.js'

export function useLandingStats() {
  const statTargets = reactive({
    tutors: 500,
    districts: 64,
    students: 1200,
  })

  const heroTutorCount = computed(() => `${Math.round(statTargets.tutors).toLocaleString()}+`)

  const authStats = computed(() => [
    {
      label: 'Verified Tutors',
      value: Math.round(statTargets.tutors).toLocaleString(),
      suffix: '+',
    },
    {
      label: 'Districts Covered',
      value: Math.round(statTargets.districts).toLocaleString(),
      suffix: '',
    },
    {
      label: 'Student Matches',
      value: Math.round(statTargets.students).toLocaleString(),
      suffix: '+',
    },
  ])

  async function loadLandingStats() {
    try {
      const { data } = await searchApi.landingStats()
      statTargets.tutors = Number(data.data?.verified_tutors ?? statTargets.tutors)
      statTargets.districts = Number(data.data?.districts ?? statTargets.districts)
      statTargets.students = Number(data.data?.student_matches ?? statTargets.students)
      return true
    } catch {
      return false
    }
  }

  return {
    statTargets,
    heroTutorCount,
    authStats,
    loadLandingStats,
  }
}
