<template>
  <DefaultLayout>
    <main class="profile-page relative isolate overflow-hidden bg-white">
      <div class="pointer-events-none absolute inset-0 -z-10 profile-grid"></div>

    <div v-if="loading" class="mx-auto max-w-6xl px-4 py-16 md:py-24">
      <div class="reveal rounded-md border border-paper-200 bg-white/90 px-4 py-16 text-center font-body text-paper-500 shadow-sm backdrop-blur-sm">
        <div class="mx-auto mb-3 h-9 w-9 rounded-full border-4 border-navy-100 border-t-navy-700 animate-spin"></div>
        Loading…
      </div>
    </div>
    <div v-else-if="tutor" class="max-w-6xl mx-auto px-4 py-6 md:py-10 space-y-5 md:space-y-6">

      <!-- ── Header card ── -->
      <div class="profile-card reveal">
        <div class="grid gap-5 md:grid-cols-[auto_minmax(0,1fr)_auto] md:items-start">
          <div class="w-24 h-24 rounded-lg bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden ring-4 ring-white shadow-lg mx-auto md:mx-0">
            <img v-if="tutor.user?.avatar_url" :src="tutor.user.avatar_url" class="w-full h-full object-cover" />
            <span v-else class="font-display font-bold text-3xl text-navy-700">{{ initials }}</span>
          </div>
          <div class="min-w-0 text-center md:text-left">
            <div class="flex items-center justify-center md:justify-start gap-2 flex-wrap">
              <h1 class="font-display font-bold text-2xl md:text-3xl text-navy-900 break-words">{{ tutor.user?.name }}</h1>
              <span v-if="tutor.is_verified"
                class="status-pill bg-emerald-50 text-emerald-700 border-emerald-200">
                ✓ Verified
              </span>
              <span v-if="tutor.tutor_id"
                class="status-pill text-navy-500 bg-navy-50 border-navy-200">
                {{ tutor.tutor_id }}
              </span>
            </div>
            <p v-if="tutor.personal_info?.gender" class="text-paper-500 text-sm mt-1 font-body capitalize">
              {{ tutor.personal_info.gender }}
            </p>
            <div class="flex justify-center md:justify-start mt-2">
              <StarRating :rating="tutor.rating" :count="tutor.review_count" />
            </div>
          </div>
          <div class="shrink-0 w-full md:w-64">
            <div class="rounded-md border border-paper-200 bg-white px-4 py-3 text-center shadow-xs md:text-left">
              <p class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1">Expected salary</p>
              <p class="font-display font-bold text-xl text-navy-700 leading-tight whitespace-nowrap">
                {{ formatSalaryRange(tutor.tuition_preference?.expected_salary_min, tutor.tuition_preference?.expected_salary_max) }}
              </p>
              <p class="text-xs text-paper-500 font-body">per month</p>
            </div>
            <button v-if="!auth.isTutor" @click="toggleShortlist" :disabled="shortlistLoading"
              class="mt-3 w-full text-sm py-2.5 px-4 inline-flex items-center justify-center gap-1.5 rounded-md font-semibold font-display border transition-colors disabled:opacity-60"
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
        <div v-if="tutor.bio" class="mt-5 rounded-md border border-paper-200 bg-paper-50/80 p-4">
          <p class="text-box">{{ tutor.bio }}</p>
        </div>
      </div>

      <!-- ── 1. Educational Information ── -->
      <div v-if="tutor.education_entries?.length" class="profile-card reveal">
        <h2 class="section-title">Educational Information</h2>
        <div class="grid md:grid-cols-2 gap-3">
          <div v-for="edu in tutor.education_entries" :key="edu.id"
            class="info-card border-l-4 border-l-gold-400">
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
      <div v-if="tutor.tuition_preference" class="profile-card reveal">
        <h2 class="section-title">Tuition Related Information</h2>

        <!-- Teaching approach -->
        <div v-if="tutor.tuition_preference.tutoring_method_description" class="info-section">
          <p class="info-label mb-1">Teaching approach</p>
          <p class="note-box text-box">{{ tutor.tuition_preference.tutoring_method_description }}</p>
        </div>

        <!-- Subjects -->
        <div v-if="uniqueSubjects.length" class="info-section">
          <p class="info-label">Subjects</p>
          <div class="chip-list">
            <span v-for="s in uniqueSubjects" :key="s.key"
              class="chip">
              {{ s.name }}
            </span>
          </div>
        </div>

        <!-- Preferred classes -->
        <div v-if="tutor.tuition_preference.preferred_classes?.length" class="info-section">
          <p class="info-label">Classes</p>
          <div class="chip-list">
            <span v-for="cls in tutor.tuition_preference.preferred_classes" :key="cls"
              class="chip">
              {{ cls.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase()) }}
            </span>
          </div>
        </div>

        <!-- Preferred curricula -->
        <div v-if="asArray(tutor.tuition_preference.preferred_curricula).length" class="info-section">
          <p class="info-label">Curriculum</p>
          <div class="chip-list">
            <span v-for="c in asArray(tutor.tuition_preference.preferred_curricula)" :key="c"
              class="chip capitalize">
              {{ c.replace(/_/g,' ') }}
            </span>
          </div>
        </div>

        <!-- Preferred locations -->
        <div v-if="tutor.tuition_preference.district" class="info-section">
          <p class="info-label">Preferred district</p>
          <span class="chip">
            {{ tutor.tuition_preference.district.name }}
          </span>
        </div>

        <div v-if="tutor.tuition_preference.locations?.length" class="info-section">
          <p class="info-label">Preferred locations</p>
          <div class="chip-list">
            <span v-for="loc in tutor.tuition_preference.locations" :key="loc.id"
              class="chip">
              {{ loc.area?.name }}
            </span>
          </div>
        </div>

        <!-- Place of tutoring -->
        <div v-if="asArray(tutor.tuition_preference.place_of_tutoring).length" class="info-section">
          <p class="info-label">Place of tutoring</p>
          <div class="chip-list">
            <span v-for="p in asArray(tutor.tuition_preference.place_of_tutoring)" :key="p"
              class="chip">
              {{ PLACE_LABELS[p] || p.replace(/_/g, ' ') }}
            </span>
          </div>
        </div>

        <!-- Key details grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div v-if="tutor.tuition_preference.days_per_week" class="info-card">
            <p class="info-label">Days per week</p>
            <p class="info-value">{{ tutor.tuition_preference.days_per_week }} days</p>
          </div>
          <div v-if="tutor.tuition_preference.hours_per_day" class="info-card">
            <p class="info-label">Hours per session</p>
            <p class="info-value">{{ tutor.tuition_preference.hours_per_day }} hrs</p>
          </div>
          <div v-if="tutor.tuition_preference.total_experience_years != null" class="info-card">
            <p class="info-label">Experience</p>
            <p class="info-value">
              {{ tutor.tuition_preference.total_experience_years >= 21 ? '20+' : tutor.tuition_preference.total_experience_years }}
              year{{ tutor.tuition_preference.total_experience_years === 1 ? '' : 's' }}
            </p>
          </div>
          <div class="info-card">
            <p class="info-label">Expected salary</p>
            <p class="info-value">
              {{ formatSalaryRange(tutor.tuition_preference.expected_salary_min, tutor.tuition_preference.expected_salary_max) }}/mo
            </p>
          </div>
        </div>

        <!-- Available days -->
        <div v-if="tutor.tuition_preference.days?.length" class="mt-4">
          <p class="info-label">Available days</p>
          <div class="chip-list">
            <span v-for="d in tutor.tuition_preference.days" :key="d.day"
              class="chip capitalize">
              {{ d.day }}
            </span>
          </div>
        </div>

        <!-- Preferred time -->
        <div v-if="asArray(tutor.tuition_preference.preferred_time).length" class="mt-4">
          <p class="info-label">Preferred time</p>
          <div class="chip-list">
            <span v-for="t in asArray(tutor.tuition_preference.preferred_time)" :key="t"
              class="chip">
              {{ TIME_MAP[t] || t }}
            </span>
          </div>
        </div>

        <!-- Tutoring styles -->
        <div v-if="asArray(tutor.tuition_preference.tutoring_styles).length" class="mt-4">
          <p class="info-label">Tutoring style</p>
          <div class="chip-list">
            <span v-for="style in asArray(tutor.tuition_preference.tutoring_styles)" :key="style"
              class="chip capitalize">
              {{ style.replace(/_/g,' ') }}
            </span>
          </div>
        </div>

        <!-- Experience details -->
        <div v-if="tutor.tuition_preference.experience_details" class="mt-4">
          <p class="info-label mb-1">Experience details</p>
          <p class="note-box text-box">{{ tutor.tuition_preference.experience_details }}</p>
        </div>
      </div>

      <!-- ── 3. Personal Information (no email / phone) ── -->
      <div v-if="tutor.personal_info" class="profile-card reveal">
        <h2 class="section-title">Personal Information</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div v-if="tutor.personal_info.gender" class="info-card">
            <p class="info-label">Gender</p>
            <p class="info-value capitalize">{{ tutor.personal_info.gender }}</p>
          </div>
          <div v-if="tutor.personal_info.religion" class="info-card">
            <p class="info-label">Religion</p>
            <p class="info-value capitalize">{{ tutor.personal_info.religion }}</p>
          </div>
          <div v-if="tutor.personal_info.nationality" class="info-card">
            <p class="info-label">Nationality</p>
            <p class="info-value">{{ tutor.personal_info.nationality }}</p>
          </div>
          <div v-if="tutor.personal_info.fathers_name" class="info-card">
            <p class="info-label">Father's name</p>
            <p class="info-value">{{ tutor.personal_info.fathers_name }}</p>
          </div>
          <div v-if="tutor.personal_info.mothers_name" class="info-card">
            <p class="info-label">Mother's name</p>
            <p class="info-value">{{ tutor.personal_info.mothers_name }}</p>
          </div>
        </div>
      </div>

      <!-- ── Teaching videos ── -->
      <div class="profile-card reveal">
        <h2 class="section-title">Teaching Videos</h2>
        <div v-if="tutor.teaching_videos?.length" class="grid md:grid-cols-2 gap-4">
          <div v-for="vid in tutor.teaching_videos" :key="vid.id" class="info-card">
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
      <div class="profile-card reveal">
        <div class="flex items-center justify-between mb-4">
          <h2 class="section-title !mb-0">Reviews{{ tutor.review_count ? ` (${tutor.review_count})` : '' }}</h2>
          <button v-if="canWriteReview && !showReviewForm" @click="showReviewForm = true"
            class="text-sm font-semibold font-display text-navy-700 border border-navy-200 bg-navy-50 hover:bg-navy-100 px-3 py-1.5 rounded-md transition-colors">
            Write A Review
          </button>
        </div>

        <!-- Review submission form -->
        <div v-if="showReviewForm" class="mb-5 rounded-lg border border-gold-300 bg-gold-50 p-4 space-y-3">
          <p class="text-sm font-semibold font-display text-navy-800">Your review</p>

          <!-- Star picker -->
          <div class="flex items-center gap-1">
            <button v-for="star in 5" :key="star" type="button"
              @click="reviewRating = star"
              @mouseover="reviewHover = star"
              @mouseleave="reviewHover = 0"
              class="focus:outline-none transition-transform hover:scale-110">
              <svg class="w-7 h-7 transition-colors" viewBox="0 0 24 24" fill="currentColor"
                :class="star <= (reviewHover || reviewRating) ? 'text-gold-400' : 'text-paper-300'">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
            </button>
            <span class="ml-2 text-sm text-paper-500 font-body">{{ reviewRating ? STAR_LABELS[reviewRating] : 'Select a rating' }}</span>
          </div>

          <textarea v-model="reviewText" rows="3" placeholder="Share your experience with this tutor (optional)…"
            class="input text-sm w-full resize-none" maxlength="1000" />
          <p class="text-xs text-paper-400 font-body text-right">{{ reviewText.length }}/1000</p>

          <div class="flex gap-2 justify-end">
            <button type="button" @click="cancelReview"
              class="text-sm font-semibold font-display border border-paper-300 text-paper-600 hover:bg-paper-100 px-4 py-2 rounded-md transition-colors">
              Cancel
            </button>
            <button type="button" @click="submitReview" :disabled="!reviewRating || reviewSubmitting"
              class="text-sm font-semibold font-display bg-navy-700 text-white hover:bg-navy-800 px-4 py-2 rounded-md transition-colors disabled:opacity-50">
              {{ reviewSubmitting ? 'Submitting…' : 'Submit Review' }}
            </button>
          </div>
        </div>

        <!-- Submitted notice -->
        <div v-if="reviewSubmitted" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 font-body">
          Your review has been submitted and will appear here after moderation. Thank you!
        </div>

        <div v-if="tutor.reviews?.length" class="space-y-3">
          <div v-for="review in tutor.reviews" :key="review.id" class="info-card">
            <div class="flex items-center justify-between mb-1">
              <p class="font-display font-semibold text-sm text-navy-900">{{ review.guardian_profile?.user?.name }}</p>
              <StarRating :rating="review.rating" :showCount="false" />
            </div>
            <p class="text-sm text-paper-600 font-body leading-relaxed">{{ review.review_text }}</p>
          </div>
        </div>
        <p v-else-if="!showReviewForm && !reviewSubmitted" class="text-paper-400 text-sm font-body italic">No reviews yet.</p>
      </div>

    </div>
    <div v-else class="mx-auto max-w-6xl px-4 py-16 md:py-24">
      <div class="reveal rounded-md border border-paper-200 bg-white/90 px-4 py-16 text-center font-body text-paper-500 shadow-sm backdrop-blur-sm">
        Tutor not found.
      </div>
    </div>
    </main>
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

const TIME_MAP    = Object.fromEntries(PREFERRED_TIMES.map(t => [t.value, `${t.label} (${t.hint})`]))
const PLACE_LABELS = { student_home: "Student's home", tutor_home: "Tutor's home", online: 'Online' }

const route   = useRoute()
const router  = useRouter()
const auth    = useAuthStore()
const tutor   = ref(null)
const loading = ref(true)

const isShortlisted   = ref(false)
const shortlistLoading = ref(false)

const canWriteReview    = ref(false)
const reviewConnectionId = ref(null)
const showReviewForm    = ref(false)
const reviewSubmitted   = ref(false)
const reviewRating      = ref(0)
const reviewHover       = ref(0)
const reviewText        = ref('')
const reviewSubmitting  = ref(false)

const STAR_LABELS = { 1: 'Poor', 2: 'Fair', 3: 'Good', 4: 'Very good', 5: 'Excellent' }

const initials = computed(() => getInitials(tutor.value?.user?.name))
const uniqueSubjects = computed(() => {
  const seen = new Set()

  return (tutor.value?.tuition_preference?.subjects || [])
    .map(subject => ({
      ...subject,
      name: String(subject.name || '').trim(),
    }))
    .filter(subject => {
      if (!subject.name) return false
      const key = subject.name.toLowerCase()
      if (seen.has(key)) return false
      seen.add(key)
      subject.key = key
      return true
    })
})

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

    try {
      const { data } = await guardianApi.reviewEligibility(tutor.value.id)
      canWriteReview.value    = data.data.eligible
      reviewConnectionId.value = data.data.connection_request_id
    } catch { /* silently ignore */ }
  }
})

function cancelReview() {
  showReviewForm.value = false
  reviewRating.value   = 0
  reviewText.value     = ''
}

async function submitReview() {
  if (!reviewRating.value) return
  reviewSubmitting.value = true
  try {
    await guardianApi.submitReview({
      tutor_profile_id:      tutor.value.id,
      connection_request_id: reviewConnectionId.value,
      rating:                reviewRating.value,
      review_text:           reviewText.value || null,
    })
    showReviewForm.value  = false
    canWriteReview.value  = false
    reviewSubmitted.value = true
    toast.success('Review submitted! It will appear after moderation.')
  } catch {
    toast.error('Could not submit review. Please try again.')
  } finally {
    reviewSubmitting.value = false
  }
}

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
.profile-grid {
  background-image:
    linear-gradient(rgba(15, 46, 92, 0.038) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 46, 92, 0.038) 1px, transparent 1px);
  background-size: 34px 34px;
}

.profile-card {
  @apply rounded-md border border-paper-200 p-5 shadow-lg backdrop-blur-sm md:p-6;
  background-color: rgba(255, 255, 255, 0.92);
}
.section-title { @apply font-display font-bold text-navy-900 text-xl mb-4; }
.info-label    { @apply text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1; }
.info-value    { @apply text-sm font-body text-navy-800; }
.info-card     { @apply rounded-md border border-paper-200 bg-white p-3 shadow-xs; }
.info-section  { @apply mb-4; }
.chip-list     { @apply flex flex-wrap gap-1.5 mt-1.5; }
.chip          { @apply text-xs sm:text-sm font-semibold font-display text-navy-700 bg-navy-50 border border-navy-100 px-2.5 sm:px-3 py-1 rounded-pill; }
.status-pill   { @apply inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-pill border; }
.note-box      { @apply rounded-md border border-paper-200 bg-paper-50/80 p-3 text-sm font-body text-paper-700 leading-relaxed; }
.text-box      { @apply font-body text-paper-700 leading-relaxed text-sm text-justify; }

.reveal {
  animation: reveal-up 0.56s ease both;
}

@keyframes reveal-up {
  from {
    opacity: 0;
    transform: translateY(14px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .reveal {
    animation: none;
  }
}
</style>
