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
      <p class="text-paper-500 text-sm font-body">
        Confirmed tuitions — whether from a guardian's connection request or a job post you applied to — will appear here.
      </p>
    </div>

    <div v-else class="grid gap-4">
      <div v-for="tuition in tuitions" :key="tuition.id"
        class="overflow-hidden rounded-sm border border-paper-200 bg-white shadow-sm transition-all duration-150 hover:border-emerald-200 hover:shadow-md">
        <div class="h-1 bg-emerald-500"></div>

        <div class="p-4 md:p-5">
          <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
          <div class="flex min-w-0 items-start gap-3">
            <!-- Guardian avatar -->
            <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-emerald-100 bg-emerald-50 md:h-[72px] md:w-[72px]">
              <img v-if="tuition.guardian_profile?.user?.avatar_url"
                :src="tuition.guardian_profile.user.avatar_url" class="w-full h-full object-cover" />
              <span v-else class="font-display font-bold text-xl text-emerald-700 md:text-2xl">
                {{ initials(tuition.guardian_profile?.user?.name) }}
              </span>
            </div>

            <div class="min-w-0 flex-1">
              <p class="truncate font-display text-base font-semibold leading-snug text-navy-900">
                {{ tuition.guardian_profile?.user?.name }}
              </p>
              <div class="mt-1 flex flex-wrap items-center gap-1.5">
                <span v-if="tuition.guardian_profile?.guardian_id"
                  class="rounded-pill border border-paper-200 bg-paper-50 px-2 py-0.5 font-display text-[11px] font-semibold text-paper-600">
                  {{ tuition.guardian_profile.guardian_id }}
                </span>
                <span class="rounded-pill border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-display text-[11px] font-semibold text-emerald-700">
                  Confirmed tuition
                </span>
                <span v-if="tuition.source === 'job'"
                  class="rounded-pill border border-blue-200 bg-blue-50 px-2 py-0.5 font-display text-[11px] font-semibold text-blue-700">
                  Via Job #{{ tuition.job_public_id }}
                </span>
              </div>
            </div>
          </div>
          <div v-if="tuition.confirmed_at"
            class="rounded-sm border border-emerald-100 bg-emerald-50 px-3 py-2.5 lg:min-w-40">
            <p class="font-display text-[11px] font-semibold uppercase tracking-wide text-emerald-600">Confirmed on</p>
            <p class="mt-0.5 font-display text-sm font-bold text-emerald-800">{{ formatDate(tuition.confirmed_at) }}</p>
          </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-[minmax(0,18rem)_minmax(0,1fr)]">
            <div v-if="tuition.guardian_profile?.user?.phone"
              class="rounded-sm border border-paper-200 bg-paper-50 px-3 py-2.5">
              <p class="font-display text-[11px] font-semibold uppercase tracking-wide text-paper-500">Guardian mobile</p>
              <a :href="`tel:${tuition.guardian_profile.user.phone}`"
                class="mt-0.5 block font-display text-sm font-semibold text-emerald-700 hover:text-emerald-900">
                {{ tuition.guardian_profile.user.phone }}
              </a>
            </div>

          <!-- Guardian message -->
          <div v-if="tuition.guardian_message"
            class="rounded-sm border border-blue-100 bg-blue-50 px-3 py-2.5">
            <p class="text-[11px] font-semibold font-display text-blue-600 uppercase tracking-wide mb-1">Guardian's message</p>
            <p class="text-sm text-blue-900 font-body leading-relaxed">{{ tuition.guardian_message }}</p>
          </div>
          <div v-else class="rounded-sm border border-paper-200 bg-paper-50 px-3 py-2.5">
            <p class="font-display text-[11px] font-semibold uppercase tracking-wide text-paper-500">Guardian message</p>
            <p class="mt-0.5 text-sm font-body text-paper-500">No message provided.</p>
          </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'
import { getInitials } from '@/utils/helpers.js'

const tuitions = ref([])
const loading  = ref(true)

const initials = (name) => getInitials(name)

onMounted(async () => {
  try {
    const { data } = await tutorApi.getConfirmedTuitions()
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
