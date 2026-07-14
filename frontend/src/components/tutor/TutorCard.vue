<template>
  <div @click="handleNav"
    class="group relative block h-full cursor-pointer overflow-hidden rounded-lg border border-paper-200 bg-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-navy-200 hover:shadow-lg">
    <div v-if="topUniversity" class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-[0.08]">
      <img :src="topUniversity.logo_url" alt="" class="h-3/4 w-3/4 object-contain" />
    </div>
    <div class="flex h-full flex-col">
      <div class="h-[8.25rem] p-4 pb-3 md:p-5 md:pb-4">
        <div class="flex items-start gap-3">
        <div
          class="relative flex h-24 w-20 shrink-0 items-center justify-center rounded-md border border-navy-100 bg-navy-50 shadow-xs md:w-[84px]">
          <img v-if="tutor.user?.avatar_url" :src="tutor.user.avatar_url" :alt="tutor.user?.name"
            class="h-full w-full rounded-md object-cover" />
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
              <span v-if="topUniversity?.short_name"
                class="rounded-pill border border-gold-200 bg-gold-50 px-2 py-0.5 font-display text-[11px] font-bold text-gold-800">
                {{ topUniversity.short_name }}
              </span>
            </div>
          </div>

          <StarRating class="mt-2" :rating="tutor.rating" :count="tutor.review_count" />
        </div>
        </div>
      </div>

      <div class="grid grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] gap-2 bg-white px-4 pb-2 md:px-5">
        <div class="min-w-0 rounded-md border border-gold-100 bg-gold-50 px-2 py-2">
          <div class="flex items-center gap-1.5 text-gold-700">
            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3.75-9.75h5.25a2.25 2.25 0 010 4.5h-3a2.25 2.25 0 000 4.5h5.25" />
            </svg>
            <span class="font-display text-[10px] font-semibold uppercase tracking-wide">Salary</span>
          </div>
          <p :title="salaryLabel" class="mt-1 whitespace-nowrap font-display text-xs font-bold leading-tight text-navy-900">{{ salaryLabel }}</p>
        </div>
        <div class="min-w-0 rounded-md border border-navy-100 bg-white px-2.5 py-2">
          <div class="flex h-full items-center gap-1.5 text-navy-700">
            <svg aria-hidden="true" class="h-[1.125rem] w-[1.125rem] shrink-0 text-navy-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <p :title="daysPerWeekLabel" class="truncate font-display text-sm font-bold">{{ daysPerWeekLabel }}</p>
          </div>
        </div>
      </div>

      <div class="grid h-[6.25rem] grid-rows-2 gap-3 p-4 pt-0 md:p-5 md:pt-0">
        <div class="min-h-0">
          <p class="mb-1.5 px-1 font-display text-[11px] font-semibold uppercase tracking-wide text-paper-400">Subjects</p>
          <div v-if="allSubjects.length" class="lg:hidden flex flex-nowrap items-center gap-1.5 overflow-hidden">
            <span v-for="subject in subjectTagsSm" :key="subject" :title="subject"
              class="max-w-[5rem] shrink-0 truncate rounded-pill border border-gold-100 bg-gold-50 px-2.5 py-1 font-display text-xs font-semibold text-gold-800">
              {{ subject }}
            </span>
            <span v-if="extraSubjectCountSm > 0"
              class="shrink-0 rounded-pill border border-paper-200 bg-paper-100 px-2.5 py-1 font-display text-xs font-semibold text-paper-500">
              +{{ extraSubjectCountSm }}
            </span>
          </div>
          <div v-if="allSubjects.length" class="hidden lg:flex flex-nowrap items-center gap-1.5 overflow-hidden">
            <span v-for="subject in subjectTags" :key="subject" :title="subject"
              class="max-w-[7rem] shrink-0 truncate rounded-pill border border-gold-100 bg-gold-50 px-2.5 py-1 font-display text-xs font-semibold text-gold-800">
              {{ subject }}
            </span>
            <span v-if="extraSubjectCount > 0"
              class="shrink-0 rounded-pill border border-paper-200 bg-paper-100 px-2.5 py-1 font-display text-xs font-semibold text-paper-500">
              +{{ extraSubjectCount }}
            </span>
          </div>
        </div>

        <div class="min-h-0 pt-2">
          <p class="mb-1.5 px-1 font-display text-[11px] font-semibold uppercase tracking-wide text-paper-400">Classes</p>
          <div v-if="allClasses.length" class="lg:hidden flex flex-nowrap items-center gap-1.5 overflow-hidden">
            <span v-for="cls in classTagsSm" :key="cls" :title="cls"
              class="max-w-[5rem] shrink-0 truncate rounded-pill border border-navy-100 bg-navy-50 px-2.5 py-1 font-display text-xs font-semibold text-navy-700">
              {{ cls }}
            </span>
            <span v-if="extraClassCountSm > 0"
              class="shrink-0 rounded-pill border border-paper-200 bg-paper-100 px-2.5 py-1 font-display text-xs font-semibold text-paper-500">
              +{{ extraClassCountSm }}
            </span>
          </div>
          <div v-if="allClasses.length" class="hidden lg:flex flex-nowrap items-center gap-1.5 overflow-hidden">
            <span v-for="cls in classTags" :key="cls" :title="cls"
              class="max-w-[7rem] shrink-0 truncate rounded-pill border border-navy-100 bg-navy-50 px-2.5 py-1 font-display text-xs font-semibold text-navy-700">
              {{ cls }}
            </span>
            <span v-if="extraClassCount > 0"
              class="shrink-0 rounded-pill border border-paper-200 bg-paper-100 px-2.5 py-1 font-display text-xs font-semibold text-paper-500">
              +{{ extraClassCount }}
            </span>
          </div>
        </div>
      </div>

      <div class="mt-auto grid h-[7.5rem] grid-rows-[1.75rem_minmax(0,1fr)] gap-1 overflow-hidden p-4 pb-3 pt-3 md:p-5 md:pb-3 md:pt-4">
        <div class="flex h-full items-start gap-2 text-xs text-paper-600">
          <template v-if="locationTags.length">
            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-navy-500" fill="none" stroke="currentColor" stroke-width="1.8"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            <p :title="locationTags.join(', ')" class="line-clamp-1 font-body leading-relaxed">{{ locationTags.join(', ') }}</p>
          </template>
        </div>

        <div class="flex h-full flex-wrap content-start items-start gap-1.5 overflow-hidden">
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
const topUniversity = computed(() => {
  const entries = props.tutor.education_entries || []
  return entries
    .filter(e => e.university?.logo_url)
    .sort((a, b) => (LEVEL_ORDER[b.level] || 0) - (LEVEL_ORDER[a.level] || 0))[0]
    ?.university || null
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

const salaryLabel = computed(() =>
  formatSalaryRange(props.tutor.tuition_preference?.expected_salary_min, props.tutor.tuition_preference?.expected_salary_max)
)

const daysPerWeekLabel = computed(() => {
  const days = props.tutor.tuition_preference?.days_per_week
  if (!days) return '—'
  return `${days} day${Number(days) === 1 ? '' : 's'}`
})
</script>
