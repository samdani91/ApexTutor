<template>
  <div @click="handleNav"
    class="group relative block h-full cursor-pointer rounded-md border border-paper-200 bg-white p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-navy-200 hover:shadow-lg md:p-5">
    <img v-if="topUniversityLogo" :src="topUniversityLogo" alt=""
      class="absolute top-3 right-3 h-14 w-14 rounded object-contain bg-white" />
    <div class="flex h-full flex-col gap-4">
      <div class="flex items-start gap-3">
        <div
          class="h-24 w-20 shrink-0 overflow-hidden rounded-md border border-navy-100 bg-navy-50 flex items-center justify-center shadow-xs md:w-[84px]">
          <img v-if="tutor.user?.avatar_url" :src="tutor.user.avatar_url" :alt="tutor.user?.name"
            class="h-full w-full object-cover" />
          <span v-else class="font-display text-xl font-bold text-navy-700 md:text-2xl">{{ initials }}</span>
        </div>

        <div class="min-w-0 flex-1">
          <div class="min-w-0">
            <h3 :title="tutor.user?.name || 'Tutor'"
              class="line-clamp-2 min-h-[2.75rem] break-words font-display text-base font-semibold leading-snug text-navy-900">
              {{ tutor.user?.name || 'Tutor' }}
            </h3>
            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
              <span v-if="tutor.tutor_id"
                class="rounded-pill border border-paper-200 bg-paper-50 px-2 py-0.5 font-display text-[11px] font-semibold text-paper-600">
                {{ tutor.tutor_id }}
              </span>
              <VerifiedBadge v-if="tutor.is_verified" size="sm" />
            </div>
          </div>

          <StarRating class="mt-2" :rating="tutor.rating" :count="tutor.review_count" />
        </div>
      </div>

      <div class="rounded-md border border-navy-100 bg-navy-50 px-3 py-2.5">
        <p class="font-display text-[11px] font-semibold uppercase tracking-wide text-paper-500">Expected salary</p>
        <p class="mt-0.5 font-display text-sm font-bold leading-snug text-navy-800">
          {{ formatSalaryRange(tutor.tuition_preference?.expected_salary_min, tutor.tuition_preference?.expected_salary_max) }}
        </p>
      </div>

      <div class="min-h-[7rem] space-y-3">
        <div v-if="allSubjects.length">
          <p class="mb-1.5 font-display text-[11px] font-semibold uppercase tracking-wide text-paper-400">Subjects</p>
          <!-- below lg: 4 chips -->
          <div class="lg:hidden flex flex-nowrap items-center gap-1.5">
            <span v-for="subject in subjectTagsSm" :key="subject"
              class="shrink-0 rounded-pill border border-gold-100 bg-gold-50 px-2.5 py-1 font-display text-xs font-semibold text-gold-800">
              {{ subject }}
            </span>
            <span v-if="extraSubjectCountSm > 0"
              class="shrink-0 rounded-pill border border-paper-200 bg-paper-100 px-2.5 py-1 font-display text-xs font-semibold text-paper-500">
              +{{ extraSubjectCountSm }}
            </span>
          </div>
          <!-- lg and up: 2 chips -->
          <div class="hidden lg:flex flex-nowrap items-center gap-1.5">
            <span v-for="subject in subjectTags" :key="subject"
              class="shrink-0 rounded-pill border border-gold-100 bg-gold-50 px-2.5 py-1 font-display text-xs font-semibold text-gold-800">
              {{ subject }}
            </span>
            <span v-if="extraSubjectCount > 0"
              class="shrink-0 rounded-pill border border-paper-200 bg-paper-100 px-2.5 py-1 font-display text-xs font-semibold text-paper-500">
              +{{ extraSubjectCount }}
            </span>
          </div>
        </div>

        <div v-if="allClasses.length">
          <p class="mb-1.5 font-display text-[11px] font-semibold uppercase tracking-wide text-paper-400">Classes</p>
          <!-- below lg: 4 chips -->
          <div class="lg:hidden flex flex-nowrap items-center gap-1.5">
            <span v-for="cls in classTagsSm" :key="cls"
              class="shrink-0 rounded-pill border border-navy-100 bg-navy-50 px-2.5 py-1 font-display text-xs font-semibold text-navy-700">
              {{ cls }}
            </span>
            <span v-if="extraClassCountSm > 0"
              class="shrink-0 rounded-pill border border-paper-200 bg-paper-100 px-2.5 py-1 font-display text-xs font-semibold text-paper-500">
              +{{ extraClassCountSm }}
            </span>
          </div>
          <!-- lg and up: 2 chips -->
          <div class="hidden lg:flex flex-nowrap items-center gap-1.5">
            <span v-for="cls in classTags" :key="cls"
              class="shrink-0 rounded-pill border border-navy-100 bg-navy-50 px-2.5 py-1 font-display text-xs font-semibold text-navy-700">
              {{ cls }}
            </span>
            <span v-if="extraClassCount > 0"
              class="shrink-0 rounded-pill border border-paper-200 bg-paper-100 px-2.5 py-1 font-display text-xs font-semibold text-paper-500">
              +{{ extraClassCount }}
            </span>
          </div>
        </div>
      </div>

      <div class="mt-auto space-y-3 pt-1">
        <div class="flex min-h-[2.5rem] items-start gap-2 text-xs text-paper-600">
          <template v-if="locationTags.length">
            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-navy-500" fill="none" stroke="currentColor" stroke-width="1.8"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            <p class="line-clamp-2 font-body leading-relaxed">{{ locationTags.join(', ') }}</p>
          </template>
        </div>

        <div class="flex flex-wrap items-center gap-1.5">
          <span v-for="p in placeTags" :key="p.value"
            class="rounded-pill border border-navy-100 bg-white px-2.5 py-1 font-display text-xs font-semibold text-navy-700">
            {{ p.label }}
          </span>
          <span v-if="experienceLabel"
            class="rounded-pill border border-paper-200 bg-paper-50 px-2.5 py-1 font-display text-xs font-semibold text-paper-600">
            {{ experienceLabel }}
          </span>
          <span v-if="visitingLabel"
            class="inline-flex items-center gap-1 rounded-pill border border-teal-200 bg-teal-50 px-2.5 py-1 font-display text-xs font-semibold text-teal-700">
            <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
            </svg>
            {{ visitingLabel }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import StarRating from '@/components/common/StarRating.vue'
import VerifiedBadge from '@/components/common/VerifiedBadge.vue'
import { getInitials, formatSalaryRange } from '@/utils/helpers.js'

const props = defineProps({ tutor: { type: Object, required: true } })
const emit  = defineEmits(['needs-auth'])

const router = useRouter()
const auth   = useAuthStore()

function handleNav() {
  if (auth.isAuthenticated) {
    router.push(`/tutors/${props.tutor.public_id}`)
  } else {
    emit('needs-auth')
  }
}
const initials = computed(() => getInitials(props.tutor.user?.name))

const LEVEL_ORDER = { phd: 3, masters: 2, bachelor: 1 }
const topUniversityLogo = computed(() => {
  const entries = props.tutor.education_entries || []
  return entries
    .filter(e => e.university?.logo_url)
    .sort((a, b) => (LEVEL_ORDER[b.level] || 0) - (LEVEL_ORDER[a.level] || 0))[0]
    ?.university?.logo_url || null
})

const allSubjects = computed(() => {
  const seen = new Set()
  return (props.tutor.tuition_preference?.subjects || [])
    .map(s => String(s.name || '').trim())
    .filter(name => {
      if (!name) return false
      const key = name.toLowerCase()
      if (seen.has(key)) return false
      seen.add(key)
      return true
    })
})
const subjectTags        = computed(() => allSubjects.value.slice(0, 2))
const extraSubjectCount  = computed(() => Math.max(0, allSubjects.value.length - 2))
const subjectTagsSm      = computed(() => allSubjects.value.slice(0, 4))
const extraSubjectCountSm = computed(() => Math.max(0, allSubjects.value.length - 4))

const allClasses = computed(() => (props.tutor.tuition_preference?.preferred_classes || [])
  .map(c => c.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())))
const classTags        = computed(() => allClasses.value.slice(0, 2))
const extraClassCount  = computed(() => Math.max(0, allClasses.value.length - 2))
const classTagsSm      = computed(() => allClasses.value.slice(0, 4))
const extraClassCountSm = computed(() => Math.max(0, allClasses.value.length - 4))

const locationTags = computed(() => (props.tutor.tuition_preference?.locations || [])
  .map(loc => loc.area_name || loc.area?.name)
  .filter(Boolean)
  .slice(0, 3))

const PLACE_LABELS = { student_home: "Student's home", tutor_home: "Tutor's home", online: 'Online' }
const placeTags = computed(() =>
  (props.tutor.tuition_preference?.place_of_tutoring || [])
    .filter(v => PLACE_LABELS[v])
    .map(v => ({ value: v, label: PLACE_LABELS[v] }))
)

const experienceLabel = computed(() => {
  const years = props.tutor.tuition_preference?.total_experience_years
  if (years === null || years === undefined || years === '') return ''
  return `${Number(years) >= 21 ? '20+' : years} yr exp`
})

const visitingLabel = computed(() => {
  const entries = props.tutor.travel_availabilities || []
  if (!entries.length) return null
  if (entries.length === 1) return `Visiting ${entries[0].district?.name || 'area'}`
  return `Visiting ${entries.length} districts`
})
</script>
