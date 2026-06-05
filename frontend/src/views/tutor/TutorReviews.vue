<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <p class="text-xs font-bold font-display text-gold-600 uppercase tracking-wide mb-1">Feedback</p>
      <h1 class="font-display font-bold text-3xl text-navy-900">My Reviews</h1>
      <p class="text-sm text-paper-500 font-body mt-1">Reviews left by guardians for your tuition sessions.</p>
    </div>

    <!-- Stats row -->
    <div v-if="stats" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
      <div class="card py-4 text-center">
        <p class="text-2xl font-bold font-display text-navy-900">{{ stats.average || '—' }}</p>
        <div class="flex justify-center gap-0.5 my-1">
          <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5"
            :class="i <= Math.round(stats.average) ? 'text-gold-400' : 'text-paper-200'"
            fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
        </div>
        <p class="text-xs text-paper-400 font-body">Average</p>
      </div>
      <div class="card py-4 text-center">
        <p class="text-2xl font-bold font-display text-navy-900">{{ stats.total }}</p>
        <p class="text-xs text-paper-400 font-body mt-1">Total</p>
      </div>
      <div class="card py-4 text-center">
        <p class="text-2xl font-bold font-display text-emerald-600">{{ stats.approved }}</p>
        <p class="text-xs text-paper-400 font-body mt-1">Approved</p>
      </div>
      <div class="card py-4 text-center">
        <p class="text-2xl font-bold font-display text-amber-500">{{ stats.pending }}</p>
        <p class="text-xs text-paper-400 font-body mt-1">Pending</p>
      </div>
    </div>

    <!-- Rating breakdown bar -->
    <div v-if="stats && stats.approved > 0" class="card mb-6">
      <p class="text-xs font-bold font-display text-paper-400 uppercase tracking-wide mb-3">Rating breakdown</p>
      <div class="space-y-2">
        <div v-for="star in [5,4,3,2,1]" :key="star" class="flex items-center gap-3">
          <span class="text-xs font-semibold font-display text-navy-700 w-3 shrink-0">{{ star }}</span>
          <svg class="w-3.5 h-3.5 text-gold-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          <div class="flex-1 bg-paper-100 rounded-full h-2 overflow-hidden">
            <div class="h-2 bg-gold-400 rounded-full transition-all duration-500"
              :style="{ width: stats.approved ? `${((stats.by_rating[star] || 0) / stats.approved) * 100}%` : '0%' }">
            </div>
          </div>
          <span class="text-xs text-paper-400 font-body w-5 text-right shrink-0">{{ stats.by_rating[star] || 0 }}</span>
        </div>
      </div>
    </div>

    <!-- Filter bar -->
    <div class="card mb-5">
      <div class="flex flex-wrap items-end gap-3">
        <div class="w-44">
          <p class="text-xs text-paper-400 font-body mb-1">Status</p>
          <DropSelect
            :modelValue="statusFilter"
            :options="statusOptions"
            @update:modelValue="val => { statusFilter = val; load() }"
          />
        </div>
        <div class="w-44">
          <p class="text-xs text-paper-400 font-body mb-1">Sort</p>
          <DropSelect
            :modelValue="sortOrder"
            :options="sortOptions"
            @update:modelValue="val => { sortOrder = val; load() }"
          />
        </div>
        <div>
          <button v-if="statusFilter || sortOrder !== 'newest'" @click="clearFilters"
            class="rounded-sm bg-red-600 px-3 py-2 text-xs font-semibold font-display text-white hover:bg-red-700 transition-colors">
            Clear
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-24">
      <div class="w-8 h-8 border-2 border-navy-100 border-t-navy-700 rounded-full animate-spin"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!reviews.length"
      class="card flex flex-col items-center justify-center py-20 text-center">
      <div class="w-14 h-14 rounded-2xl bg-gold-50 flex items-center justify-center mb-4">
        <svg class="w-7 h-7 text-gold-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
        </svg>
      </div>
      <p class="font-display font-semibold text-navy-700 text-lg">No reviews yet</p>
      <p class="text-paper-400 text-sm font-body mt-1 max-w-xs">
        {{ statusFilter ? 'No reviews match this filter.' : 'Reviews from guardians will appear here after confirmed sessions.' }}
      </p>
    </div>

    <!-- Review cards -->
    <div v-else class="space-y-4">
      <div v-for="review in reviews" :key="review.id"
        class="card">

        <!-- Top row: avatar + name + date + status badge -->
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 rounded-lg bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden ring-1 ring-inset ring-navy-200">
            <img v-if="review.guardian_profile?.user?.avatar_url"
              :src="review.guardian_profile.user.avatar_url" class="w-full h-full object-cover" />
            <span v-else class="font-display font-bold text-sm text-navy-700">
              {{ initials(review.guardian_profile?.user?.name) }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <p class="font-display font-semibold text-sm text-navy-900">
                {{ review.guardian_profile?.user?.name || 'Guardian' }}
              </p>
              <span class="text-[11px] font-bold font-display uppercase tracking-wide px-2 py-0.5 rounded-pill"
                :class="statusClass(review.moderation_status)">
                {{ review.moderation_status }}
              </span>
            </div>
            <p class="text-xs text-paper-400 font-body mt-0.5">{{ timeAgo(review.created_at) }} · {{ formatDate(review.created_at) }}</p>
          </div>
        </div>

        <!-- Stars -->
        <div class="flex items-center gap-1 mt-3">
          <svg v-for="i in 5" :key="i" class="w-4 h-4"
            :class="i <= review.rating ? 'text-gold-400' : 'text-paper-200'"
            fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          <span class="text-sm font-semibold font-display text-navy-700 ml-1">{{ review.rating }}/5</span>
        </div>

        <!-- Review text -->
        <p v-if="review.review_text"
          class="mt-3 text-sm font-body text-navy-700 leading-relaxed border-l-2 border-paper-200 pl-3">
          "{{ review.review_text }}"
        </p>
        <p v-else class="mt-3 text-sm font-body text-paper-400 italic">No written review.</p>

        <!-- Moderation note (if rejected) -->
        <div v-if="review.moderation_status === 'rejected' && review.moderation_note"
          class="mt-3 flex items-start gap-2 bg-red-50 border border-red-100 rounded-md px-3 py-2">
          <svg class="w-3.5 h-3.5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.05 3.378c.866-1.5 3.032-1.5 3.898 0L21.303 16.126zM12 15.75h.007v.008H12v-.008z"/>
          </svg>
          <div>
            <span class="text-[11px] font-bold font-display text-red-600 uppercase tracking-wide">Moderation note</span>
            <p class="text-xs text-red-700 font-body mt-0.5">{{ review.moderation_note }}</p>
          </div>
        </div>

        <!-- Pending note -->
        <div v-else-if="review.moderation_status === 'pending'"
          class="mt-3 text-xs font-body text-amber-600 bg-amber-50 border border-amber-100 rounded-md px-3 py-2">
          This review is awaiting moderation and is not yet visible on your public profile.
        </div>
      </div>
    </div>

    <AdminPagination :meta="pagination" @page="goPage" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'
import { getInitials } from '@/utils/helpers.js'
import AdminPagination from '@/components/admin/AdminPagination.vue'

const reviews     = ref([])
const stats       = ref(null)
const loading     = ref(true)
const statusFilter = ref('')
const sortOrder    = ref('newest')
const pagination   = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

const statusOptions = [
  { value: '',         label: 'All'      },
  { value: 'approved', label: 'Approved' },
  { value: 'pending',  label: 'Pending'  },
  { value: 'rejected', label: 'Rejected' },
]

const sortOptions = [
  { value: 'newest',  label: 'Newest first'   },
  { value: 'oldest',  label: 'Oldest first'   },
  { value: 'highest', label: 'Highest rating' },
  { value: 'lowest',  label: 'Lowest rating'  },
]

function initials(name) { return getInitials(name) }

function clearFilters() {
  statusFilter.value = ''
  sortOrder.value    = 'newest'
  load()
}

async function load(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (statusFilter.value) params.status = statusFilter.value
    if (sortOrder.value !== 'newest') params.sort = sortOrder.value

    const { data } = await tutorApi.getReviews(params)
    reviews.value    = data.data.data || data.data || []
    pagination.value = data.data
    stats.value      = data.stats
  } finally {
    loading.value = false
  }
}

function goPage(page) {
  if (page < 1 || page > pagination.value.last_page) return
  load(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function statusClass(status) {
  if (status === 'approved') return 'bg-emerald-50 text-emerald-700'
  if (status === 'pending')  return 'bg-amber-50 text-amber-600'
  return 'bg-red-50 text-red-600'
}

function timeAgo(iso) {
  const diff = Math.floor((Date.now() - new Date(iso)) / 1000)
  if (diff < 60)    return 'just now'
  if (diff < 3600)  return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return `${Math.floor(diff / 86400)}d ago`
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString([], { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(() => load())
</script>
