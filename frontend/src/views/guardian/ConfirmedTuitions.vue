<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-6">Confirmed Tuitions</h1>

    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>

    <div v-else-if="!tuitions.length" class="card text-center py-12">
      <div class="w-14 h-14 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
        </svg>
      </div>
      <p class="font-display font-semibold text-navy-700 text-lg mb-2">No confirmed tuitions yet</p>
      <p class="text-paper-500 text-sm font-body mb-4">
        Once a connection request is confirmed by admin, your tutor will appear here.
      </p>
      <RouterLink to="/guardian/shortlist" class="btn-primary text-sm py-2 px-5">Go to Shortlist</RouterLink>
    </div>

    <div v-else class="grid gap-4">
      <div v-for="tuition in tuitions" :key="tuition.id"
        class="overflow-hidden rounded-sm border border-paper-200 bg-white shadow-sm transition-all duration-150 hover:border-emerald-200 hover:shadow-md">
        <div class="h-1 bg-emerald-500"></div>

        <div class="p-4 md:p-5">
          <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
            <RouterLink :to="`/tutors/${tuition.tutor_profile?.public_id}`"
              class="flex min-w-0 items-start gap-3 transition-opacity hover:opacity-85">
              <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-emerald-100 bg-emerald-50 md:h-[72px] md:w-[72px]">
                <img v-if="tuition.tutor_profile?.user?.avatar_url" :src="tuition.tutor_profile.user.avatar_url"
                  class="w-full h-full object-cover" />
                <span v-else class="font-display font-bold text-xl text-emerald-700 md:text-2xl">
                  {{ initials(tuition.tutor_profile?.user?.name) }}
                </span>
              </div>
              <div class="min-w-0 flex-1">
                <p class="truncate font-display text-base font-semibold leading-snug text-navy-900 md:text-lg">
                  {{ tuition.tutor_profile?.user?.name }}
                </p>
                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                  <span v-if="tuition.tutor_profile?.tutor_id"
                    class="rounded-pill border border-paper-200 bg-paper-50 px-2 py-0.5 font-display text-[11px] font-semibold text-paper-600">
                    {{ tuition.tutor_profile.tutor_id }}
                  </span>
                  <span class="rounded-pill border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-display text-[11px] font-semibold text-emerald-700">
                    Confirmed
                  </span>
                </div>
                <div class="mt-2 flex items-center gap-0.5">
                  <span v-for="i in 5" :key="i" class="text-sm leading-none"
                    :class="i <= Math.round(Number(tuition.tutor_profile?.rating)) ? 'text-gold-400' : 'text-paper-300'">★</span>
                  <span class="ml-1 text-xs text-paper-500">({{ tuition.tutor_profile?.review_count ?? 0 }})</span>
                </div>
              </div>
            </RouterLink>

            <RouterLink :to="`/tutors/${tuition.tutor_profile?.public_id}`"
              class="inline-flex min-h-[40px] items-center justify-center rounded-sm bg-emerald-600 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-emerald-700 lg:shrink-0">
              View profile
            </RouterLink>
          </div>

          <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div v-if="tuition.tutor_profile?.user?.phone" class="rounded-sm border border-paper-200 bg-paper-50 px-3 py-2.5">
              <p class="font-display text-[11px] font-semibold uppercase tracking-wide text-paper-500">Tutor mobile</p>
              <a :href="`tel:${tuition.tutor_profile.user.phone}`"
                class="mt-0.5 block font-display text-sm font-semibold text-emerald-700 hover:text-emerald-900">
                {{ tuition.tutor_profile.user.phone }}
              </a>
            </div>
            <div v-if="tuition.tutor_profile?.tuition_preference?.expected_salary_min || tuition.tutor_profile?.tuition_preference?.expected_salary_max"
              class="rounded-sm border border-navy-100 bg-navy-50 px-3 py-2.5">
              <p class="font-display text-[11px] font-semibold uppercase tracking-wide text-paper-500">Expected salary</p>
              <p class="mt-0.5 font-display text-sm font-bold text-navy-800">
                {{ formatSalaryRange(tuition.tutor_profile.tuition_preference.expected_salary_min, tuition.tutor_profile.tuition_preference.expected_salary_max) }}
                <span class="font-body text-xs font-normal text-paper-500">/mo</span>
              </p>
            </div>
            <div v-if="tuition.confirmed_at" class="rounded-sm border border-emerald-100 bg-emerald-50 px-3 py-2.5">
              <p class="font-display text-[11px] font-semibold uppercase tracking-wide text-emerald-600">Confirmed on</p>
              <p class="mt-0.5 font-display text-sm font-bold text-emerald-800">{{ formatDate(tuition.confirmed_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { guardianApi } from '@/api/guardian.js'
import { getInitials, formatSalaryRange } from '@/utils/helpers.js'

const tuitions = ref([])
const loading  = ref(true)

const initials = (name) => getInitials(name)

onMounted(async () => {
  try {
    const { data } = await guardianApi.getConfirmedTuitions()
    tuitions.value = data.data || []
  } finally {
    loading.value = false
  }
})

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
