<template>
  <RouterLink :to="`/tutors/${tutor.public_id}`"
    class="card hover:shadow-md hover:-translate-y-0.5 transition-all duration-150 block cursor-pointer p-4 md:p-6">
    <div class="flex gap-3 md:gap-4">
      <!-- Avatar -->
      <div class="w-14 h-14 md:w-16 md:h-16 rounded-xl bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden">
        <img v-if="tutor.user?.avatar_url" :src="tutor.user.avatar_url" :alt="tutor.user?.name" class="w-full h-full object-cover" />
        <span v-else class="font-display font-bold text-navy-700 text-lg md:text-xl">{{ initials }}</span>
      </div>

      <!-- Info -->
      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <h3 class="font-display font-semibold text-navy-900 text-sm md:text-base truncate">{{ tutor.user?.name }}</h3>
            <p class="text-xs md:text-sm text-paper-500 truncate">
              {{ tutor.tuition_preference?.city || '—' }}<template v-if="districtName">, {{ districtName }}</template>
            </p>
            <span v-if="tutor.tutor_id"
              class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-1.5 py-0.5 rounded-pill">
              {{ tutor.tutor_id }}
            </span>
          </div>
          <span v-if="tutor.is_verified" class="badge-verified shrink-0 text-xs">✓ Verified</span>
        </div>

        <StarRating class="mt-1" :rating="tutor.rating" :count="tutor.review_count" />

        <!-- Class tags -->
        <div class="mt-2 flex flex-wrap gap-1">
          <span v-for="cls in classTags" :key="cls"
            class="text-xs bg-navy-50 text-navy-700 px-2 py-0.5 rounded-pill font-semibold">
            {{ cls }}
          </span>
          <span v-if="extraClassCount > 0"
            class="text-xs bg-paper-100 text-paper-500 px-2 py-0.5 rounded-pill font-semibold">
            +{{ extraClassCount }}
          </span>
        </div>

        <!-- Footer row -->
        <div class="mt-2 flex items-center justify-between gap-2">
          <p class="text-sm font-semibold font-display text-navy-700">
            {{ formatSalaryRange(tutor.tuition_preference?.expected_salary_min, tutor.tuition_preference?.expected_salary_max) }}
          </p>
          <span v-if="tutor.tuition_preference?.total_experience_years"
            class="text-xs text-paper-500 font-body shrink-0">
            {{ tutor.tuition_preference.total_experience_years }} yr exp
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
const districtName = computed(() => props.tutor.tuition_preference?.district?.name || '')

const allClasses = computed(() => (props.tutor.tuition_preference?.preferred_classes || [])
  .map(c => c.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())))
const classTags = computed(() => allClasses.value.slice(0, 3))
const extraClassCount = computed(() => Math.max(0, allClasses.value.length - 3))
</script>
