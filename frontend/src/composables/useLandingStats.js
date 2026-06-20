import { computed, reactive } from 'vue'
import { searchApi } from '@/api/search.js'

export function useLandingStats() {
  const statTargets = reactive({
    tutors: 0,
    districts: 0,
    students: 0,
    rating: 0,
    openJobs: 0,
    confirmedViaJobs: 0,
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
      statTargets.tutors           = Number(data.data?.verified_tutors   ?? statTargets.tutors)
      statTargets.districts        = Number(data.data?.districts         ?? statTargets.districts)
      statTargets.students         = Number(data.data?.student_matches   ?? statTargets.students)
      statTargets.openJobs         = Number(data.data?.open_tuition_jobs ?? statTargets.openJobs)
      statTargets.confirmedViaJobs = Number(data.data?.confirmed_via_jobs ?? statTargets.confirmedViaJobs)
      if (data.data?.avg_rating != null) statTargets.rating = Number(data.data.avg_rating)
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
