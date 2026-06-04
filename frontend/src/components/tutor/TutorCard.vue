<template>
  <RouterLink :to="`/tutors/${tutor.public_id}`"
    class="group block h-full cursor-pointer rounded-md border border-paper-200 bg-white p-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-gold-200 hover:shadow-lg md:p-5">
    <div class="flex h-full flex-col gap-4">
      <div class="flex items-start gap-3">
        <div
          class="h-16 w-16 shrink-0 overflow-hidden rounded-md border border-navy-100 bg-navy-50 flex items-center justify-center shadow-xs md:h-[72px] md:w-[72px]">
          <img v-if="tutor.user?.avatar_url" :src="tutor.user.avatar_url" :alt="tutor.user?.name"
            class="h-full w-full object-cover" />
          <span v-else class="font-display text-xl font-bold text-navy-700 md:text-2xl">{{ initials }}</span>
        </div>

        <div class="min-w-0 flex-1">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <h3 class="truncate font-display text-base font-semibold leading-snug text-navy-900 md:text-lg">
                {{ tutor.user?.name || 'Tutor' }}
              </h3>
              <div class="mt-1 flex flex-wrap items-center gap-1.5">
                <span v-if="tutor.tutor_id"
                  class="rounded-pill border border-paper-200 bg-paper-50 px-2 py-0.5 font-display text-[11px] font-semibold text-paper-600">
                  {{ tutor.tutor_id }}
                </span>
                <span v-if="tutor.is_verified"
                  class="rounded-pill border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-display text-[11px] font-semibold text-emerald-700">
                  Verified
                </span>
              </div>
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

      <div class="min-h-[92px] space-y-3">
        <div v-if="subjectTags.length">
          <p class="mb-1.5 font-display text-[11px] font-semibold uppercase tracking-wide text-paper-400">Subjects</p>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="subject in subjectTags" :key="subject"
              class="rounded-pill border border-gold-100 bg-gold-50 px-2.5 py-1 font-display text-xs font-semibold text-gold-800">
              {{ subject }}
            </span>
            <span v-if="extraSubjectCount > 0"
              class="rounded-pill border border-paper-200 bg-paper-100 px-2.5 py-1 font-display text-xs font-semibold text-paper-500">
              +{{ extraSubjectCount }}
            </span>
          </div>
        </div>

        <div v-if="classTags.length">
          <p class="mb-1.5 font-display text-[11px] font-semibold uppercase tracking-wide text-paper-400">Classes</p>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="cls in classTags" :key="cls"
              class="rounded-pill border border-navy-100 bg-navy-50 px-2.5 py-1 font-display text-xs font-semibold text-navy-700">
              {{ cls }}
            </span>
            <span v-if="extraClassCount > 0"
              class="rounded-pill border border-paper-200 bg-paper-100 px-2.5 py-1 font-display text-xs font-semibold text-paper-500">
              +{{ extraClassCount }}
            </span>
          </div>
        </div>
      </div>

      <div class="mt-auto space-y-3 pt-1">
        <div v-if="locationTags.length" class="flex items-start gap-2 text-xs text-paper-600">
          <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-navy-500" fill="none" stroke="currentColor" stroke-width="1.8"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
          </svg>
          <p class="line-clamp-2 font-body leading-relaxed">{{ locationTags.join(', ') }}</p>
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
        </div>
      </div>
    </div>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import StarRating from '@/components/common/StarRating.vue'
import { getInitials, formatSalaryRange } from '@/utils/helpers.js'

const props = defineProps({ tutor: { type: Object, required: true } })
const initials = computed(() => getInitials(props.tutor.user?.name))

const allSubjects = computed(() => {
  const seen = new Set()

  return (props.tutor.tuition_preference?.subjects || [])
    .map(subject => String(subject.name || '').trim())
    .filter(name => {
      if (!name) return false
      const key = name.toLowerCase()
      if (seen.has(key)) return false
      seen.add(key)
      return true
    })
})
const subjectTags = computed(() => allSubjects.value.slice(0, 3))
const extraSubjectCount = computed(() => Math.max(0, allSubjects.value.length - 3))

const allClasses = computed(() => (props.tutor.tuition_preference?.preferred_classes || [])
  .map(c => c.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())))
const classTags = computed(() => allClasses.value.slice(0, 3))
const extraClassCount = computed(() => Math.max(0, allClasses.value.length - 3))

const locationTags = computed(() => (props.tutor.tuition_preference?.locations || [])
  .map(location => location.area_name || location.area?.name)
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
</script>
