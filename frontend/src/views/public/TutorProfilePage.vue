<template>
  <DefaultLayout>
    <div v-if="loading" class="text-center py-20 text-paper-500 font-body">Loading…</div>
    <div v-else-if="tutor" class="max-w-4xl mx-auto px-4 py-10 space-y-6">

      <!-- ── Header card ── -->
      <div class="card">
        <div class="flex gap-5 items-start flex-wrap">
          <div class="w-20 h-20 rounded-xl bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden ring-2 ring-white shadow">
            <img v-if="tutor.user?.avatar_url" :src="tutor.user.avatar_url" class="w-full h-full object-cover" />
            <span v-else class="font-display font-bold text-2xl text-navy-700">{{ initials }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="font-display font-bold text-2xl text-navy-900">{{ tutor.user?.name }}</h1>
              <span v-if="tutor.is_verified"
                class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-pill bg-emerald-50 text-emerald-700 border border-emerald-200">
                ✓ Verified
              </span>
              <span v-if="tutor.tutor_id"
                class="text-xs font-semibold font-display text-navy-500 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill">
                {{ tutor.tutor_id }}
              </span>
            </div>
            <p class="text-paper-500 text-sm mt-1 font-body">
              <template v-if="tutor.tuition_preference?.city">{{ tutor.tuition_preference.city }}</template>
              <template v-if="tutor.tuition_preference?.city && tutor.personal_info?.gender"> · </template>
              <span v-if="tutor.personal_info?.gender" class="capitalize">{{ tutor.personal_info.gender }}</span>
            </p>
            <StarRating class="mt-2" :rating="tutor.rating" :count="tutor.review_count" />
          </div>
          <div class="shrink-0 text-right">
            <p class="font-display font-bold text-xl text-navy-700">
              {{ formatSalaryRange(tutor.tuition_preference?.expected_salary_min, tutor.tuition_preference?.expected_salary_max) }}
            </p>
            <p class="text-xs text-paper-500 font-body">per month</p>
            <button @click="toggleShortlist" :disabled="shortlistLoading"
              class="mt-3 text-sm py-2 px-4 inline-flex items-center gap-1.5 rounded-lg font-semibold font-display border transition-colors disabled:opacity-60"
              :class="isShortlisted
                ? 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100'
                : 'bg-navy-700 text-white border-navy-700 hover:bg-navy-800'">
              <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
              </svg>
              {{ isShortlisted ? 'Shortlisted' : 'Shortlist' }}
            </button>
          </div>
        </div>

        <!-- Bio -->
        <div v-if="tutor.bio" class="mt-4 pt-4 border-t border-paper-100">
          <p class="font-body text-paper-700 leading-relaxed text-sm text-justify">{{ tutor.bio }}</p>
        </div>
      </div>

      <!-- ── 1. Educational Information ── -->
      <div v-if="tutor.education_entries?.length" class="card">
        <h2 class="section-title">Educational Information</h2>
        <div class="space-y-4">
          <div v-for="edu in tutor.education_entries" :key="edu.id"
            class="border-l-2 border-gold-400 pl-4">
            <p class="text-xs font-semibold font-display text-blue-700 uppercase tracking-wide mb-1">
              {{ formatLevel(edu.level) }}
            </p>
            <p class="font-display font-semibold text-navy-900 text-sm">{{ edu.degree_title }}</p>
            <p class="text-sm text-paper-500 font-body mt-0.5">{{ edu.institute_name }}</p>
            <div class="flex items-center gap-3 mt-1 flex-wrap">
              <span v-if="edu.result" class="text-xs text-paper-400 font-body">GPA / Result: {{ edu.result }}</span>
              <span v-if="edu.year_of_passing" class="text-xs text-paper-400 font-body">{{ edu.year_of_passing }}</span>
              <span v-if="edu.is_current" class="text-xs font-semibold text-emerald-600">Currently enrolled</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ── 2. Tuition Related Information ── -->
      <div v-if="tutor.tuition_preference" class="card">
        <h2 class="section-title">Tuition Related Information</h2>

        <!-- Subjects -->
        <div v-if="tutor.tuition_preference.subjects?.length" class="mb-5">
          <p class="info-label">Subjects</p>
          <div class="flex flex-wrap gap-1.5 mt-1.5">
            <span v-for="s in tutor.tuition_preference.subjects" :key="s.id"
              class="text-sm font-semibold font-display text-navy-700 bg-navy-50 border border-navy-100 px-3 py-1 rounded-full">
              {{ s.name }}
            </span>
          </div>
        </div>

        <!-- Preferred classes -->
        <div v-if="tutor.tuition_preference.preferred_classes?.length" class="mb-5">
          <p class="info-label">Classes</p>
          <div class="flex flex-wrap gap-1.5 mt-1.5">
            <span v-for="cls in tutor.tuition_preference.preferred_classes" :key="cls"
              class="text-sm font-semibold font-display text-navy-700 bg-navy-50 border border-navy-100 px-3 py-1 rounded-full">
              {{ cls.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase()) }}
            </span>
          </div>
        </div>

        <!-- Preferred curricula -->
        <div v-if="asArray(tutor.tuition_preference.preferred_curricula).length" class="mb-5">
          <p class="info-label mb-1.5">Curriculum</p>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="c in asArray(tutor.tuition_preference.preferred_curricula)" :key="c"
              class="text-sm font-semibold font-display text-navy-700 bg-navy-50 border border-navy-100 px-3 py-1 rounded-full capitalize">
              {{ c.replace(/_/g,' ') }}
            </span>
          </div>
        </div>

        <!-- Preferred locations -->
        <div v-if="tutor.tuition_preference.locations?.length" class="mb-5">
          <p class="info-label mb-1.5">Preferred locations</p>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="loc in tutor.tuition_preference.locations" :key="loc.id"
              class="text-sm font-semibold font-display text-navy-700 bg-navy-50 border border-navy-100 px-3 py-1 rounded-full">
              {{ loc.area_name }}
            </span>
          </div>
        </div>

        <!-- Key details grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-3">
          <div v-if="tutor.tuition_preference.city">
            <p class="info-label">Location</p>
            <p class="info-value">{{ tutor.tuition_preference.city }}</p>
          </div>
          <div v-if="tutor.tuition_preference.place_of_tutoring?.length">
            <p class="info-label">Tutoring place</p>
            <p class="info-value capitalize">{{ asArray(tutor.tuition_preference.place_of_tutoring).map(v => v.replace(/_/g,' ')).join(', ') }}</p>
          </div>
          <div v-if="tutor.tuition_preference.days_per_week">
            <p class="info-label">Days per week</p>
            <p class="info-value">{{ tutor.tuition_preference.days_per_week }} days</p>
          </div>
          <div v-if="tutor.tuition_preference.hours_per_day">
            <p class="info-label">Hours per session</p>
            <p class="info-value">{{ tutor.tuition_preference.hours_per_day }} hrs</p>
          </div>
          <div v-if="tutor.tuition_preference.total_experience_years != null">
            <p class="info-label">Experience</p>
            <p class="info-value">
              {{ tutor.tuition_preference.total_experience_years >= 21 ? '20+' : tutor.tuition_preference.total_experience_years }}
              year{{ tutor.tuition_preference.total_experience_years === 1 ? '' : 's' }}
            </p>
          </div>
          <div>
            <p class="info-label">Expected salary</p>
            <p class="info-value">
              {{ formatSalaryRange(tutor.tuition_preference.expected_salary_min, tutor.tuition_preference.expected_salary_max) }}/mo
            </p>
          </div>
        </div>

        <!-- Available days -->
        <div v-if="tutor.tuition_preference.days?.length" class="mt-4">
          <p class="info-label mb-1.5">Available days</p>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="d in tutor.tuition_preference.days" :key="d.day"
              class="text-xs font-semibold font-display text-navy-700 bg-navy-50 border border-navy-100 px-2.5 py-1 rounded-full capitalize">
              {{ d.day }}
            </span>
          </div>
        </div>

        <!-- Preferred time -->
        <div v-if="asArray(tutor.tuition_preference.preferred_time).length" class="mt-4">
          <p class="info-label mb-1.5">Preferred time</p>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="t in asArray(tutor.tuition_preference.preferred_time)" :key="t"
              class="text-xs font-semibold font-display text-navy-700 bg-navy-50 border border-navy-100 px-2.5 py-1 rounded-full">
              {{ TIME_MAP[t] || t }}
            </span>
          </div>
        </div>

        <!-- Tutoring styles -->
        <div v-if="asArray(tutor.tuition_preference.tutoring_styles).length" class="mt-4">
          <p class="info-label mb-1.5">Tutoring style</p>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="style in asArray(tutor.tuition_preference.tutoring_styles)" :key="style"
              class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-100 px-2.5 py-1 rounded-full capitalize">
              {{ style.replace(/_/g,' ') }}
            </span>
          </div>
        </div>

        <!-- Teaching approach -->
        <div v-if="tutor.tuition_preference.tutoring_method_description" class="mt-4 pt-4 border-t border-paper-100">
          <p class="info-label mb-1">Teaching approach</p>
          <p class="text-sm font-body text-paper-700 leading-relaxed text-justify">{{ tutor.tuition_preference.tutoring_method_description }}</p>
        </div>

        <!-- Experience details -->
        <div v-if="tutor.tuition_preference.experience_details" class="mt-4 pt-4 border-t border-paper-100">
          <p class="info-label mb-1">Experience details</p>
          <p class="text-sm font-body text-paper-700 leading-relaxed text-justify">{{ tutor.tuition_preference.experience_details }}</p>
        </div>
      </div>

      <!-- ── 3. Personal Information (no email / phone) ── -->
      <div v-if="tutor.personal_info" class="card">
        <h2 class="section-title">Personal Information</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-3">
          <div v-if="tutor.personal_info.gender">
            <p class="info-label">Gender</p>
            <p class="info-value capitalize">{{ tutor.personal_info.gender }}</p>
          </div>
          <div v-if="tutor.personal_info.religion">
            <p class="info-label">Religion</p>
            <p class="info-value capitalize">{{ tutor.personal_info.religion }}</p>
          </div>
          <div v-if="tutor.personal_info.nationality">
            <p class="info-label">Nationality</p>
            <p class="info-value">{{ tutor.personal_info.nationality }}</p>
          </div>
          <div v-if="tutor.personal_info.fathers_name">
            <p class="info-label">Father's name</p>
            <p class="info-value">{{ tutor.personal_info.fathers_name }}</p>
          </div>
          <div v-if="tutor.personal_info.mothers_name">
            <p class="info-label">Mother's name</p>
            <p class="info-value">{{ tutor.personal_info.mothers_name }}</p>
          </div>
        </div>
      </div>

      <!-- ── Teaching videos ── -->
      <div class="card">
        <h2 class="section-title">Teaching Videos</h2>
        <div v-if="tutor.teaching_videos?.length" class="space-y-5">
          <div v-for="vid in tutor.teaching_videos" :key="vid.id">
            <video :src="vid.file_url" controls
              class="w-full rounded-lg bg-black max-h-72" preload="metadata" />
            <div class="mt-2">
              <p v-if="vid.title" class="font-display font-semibold text-sm text-navy-900 mb-1.5">{{ vid.title }}</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-if="vid.subject" class="text-xs bg-blue-50 text-blue-700 border border-blue-100 px-2 py-0.5 rounded-pill font-body">
                  {{ vid.subject }}
                </span>
                <span v-if="vid.class_level" class="text-xs bg-navy-50 text-navy-700 border border-navy-100 px-2 py-0.5 rounded-pill font-body">
                  Class {{ vid.class_level }}
                </span>
                <span v-if="vid.medium" class="text-xs bg-paper-100 text-paper-600 border border-paper-200 px-2 py-0.5 rounded-pill font-body capitalize">
                  {{ vid.medium.replace('_', ' & ') }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <p v-else class="text-paper-400 text-sm font-body italic">No teaching videos available.</p>
      </div>

      <!-- ── Reviews ── -->
      <div v-if="tutor.reviews?.length" class="card">
        <h2 class="section-title">Reviews ({{ tutor.review_count }})</h2>
        <div v-for="review in tutor.reviews" :key="review.id"
          class="border-b border-paper-100 pb-4 mb-4 last:border-0 last:mb-0">
          <div class="flex items-center justify-between mb-1">
            <p class="font-display font-semibold text-sm text-navy-900">{{ review.guardian_profile?.user?.name }}</p>
            <StarRating :rating="review.rating" :showCount="false" />
          </div>
          <p class="text-sm text-paper-600 font-body leading-relaxed">{{ review.review_text }}</p>
        </div>
      </div>

    </div>
    <div v-else class="text-center py-20 text-paper-500 font-body">Tutor not found.</div>
  </DefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import StarRating from '@/components/common/StarRating.vue'
import { searchApi } from '@/api/search.js'
import { guardianApi } from '@/api/guardian.js'
import { useAuthStore } from '@/stores/auth.js'
import { toast } from 'vue-sonner'
import { getInitials, formatSalaryRange } from '@/utils/helpers.js'
import { PREFERRED_TIMES } from '@/utils/constants.js'

const TIME_MAP = Object.fromEntries(PREFERRED_TIMES.map(t => [t.value, `${t.label} (${t.hint})`]))

const route   = useRoute()
const router  = useRouter()
const auth    = useAuthStore()
const tutor   = ref(null)
const loading = ref(true)

const isShortlisted   = ref(false)
const shortlistLoading = ref(false)

const initials = computed(() => getInitials(tutor.value?.user?.name))

const LEVEL_LABELS = {
  ssc:      'Secondary (SSC)',
  hsc:      'Higher Secondary (HSC)',
  o_level:  'O Level',
  a_level:  'A Level',
  diploma:  'Diploma',
  bachelor: 'Bachelor / Honors',
  honors:   'Bachelor / Honors',
  masters:  'Masters',
  mphil:    'M.Phil',
  phd:      'PhD / Doctorate',
}

function asArray(val) {
  if (!val) return []
  return Array.isArray(val) ? val : [val]
}

function formatLevel(level) {
  return LEVEL_LABELS[level?.toLowerCase()] ?? (level ?? 'Other')
}

onMounted(async () => {
  try {
    const { data } = await searchApi.getTutor(route.params.publicId)
    tutor.value = data.data
  } finally {
    loading.value = false
  }

  if (auth.isGuardian && tutor.value) {
    try {
      const { data } = await guardianApi.getShortlist()
      const list = data.data || []
      isShortlisted.value = list.some(s => s.tutor_profile_id === tutor.value.id)
    } catch { /* silently ignore */ }
  }
})

async function toggleShortlist() {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }
  if (!auth.isGuardian) {
    toast.error('Only guardians can shortlist tutors.')
    return
  }

  shortlistLoading.value = true
  try {
    if (isShortlisted.value) {
      await guardianApi.removeShortlist(tutor.value.id)
      isShortlisted.value = false
      toast.success('Removed from shortlist.')
    } else {
      await guardianApi.addShortlist(tutor.value.id)
      isShortlisted.value = true
      toast.success('Added to shortlist! Admin will contact you shortly.')
    }
  } catch {
    toast.error('Could not update shortlist. Please try again.')
  } finally {
    shortlistLoading.value = false
  }
}
</script>

<style scoped>
.section-title { @apply font-display font-semibold text-navy-800 text-lg mb-4 pb-2 border-b border-paper-100; }
.info-label    { @apply text-xs font-semibold font-display text-paper-400 uppercase tracking-wide; }
.info-value    { @apply text-sm font-body text-navy-800 mt-0.5; }
</style>
