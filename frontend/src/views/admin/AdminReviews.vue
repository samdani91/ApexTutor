<template>
  <div>
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="font-display font-bold text-2xl text-navy-900">Reviews</h1>
        <p class="mt-1 text-sm font-body text-paper-500">Moderate guardian feedback before it appears publicly.</p>
      </div>
      <p v-if="meta.total" class="text-xs font-semibold font-display text-paper-500">
        {{ meta.total }} {{ activeTab === 'pending' ? 'pending' : 'reviews' }}
      </p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-5 border-b border-paper-200">
      <button @click="switchTab('pending')"
        class="px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors"
        :class="activeTab === 'pending' ? 'bg-white border border-b-white border-paper-200 -mb-px text-navy-900' : 'text-paper-500 hover:text-navy-700'">
        Pending moderation
      </button>
      <button @click="switchTab('all')"
        class="px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors"
        :class="activeTab === 'all' ? 'bg-white border border-b-white border-paper-200 -mb-px text-navy-900' : 'text-paper-500 hover:text-navy-700'">
        All reviews
      </button>
    </div>

    <!-- All reviews: search + status filter -->
    <div v-if="activeTab === 'all'" class="card mb-5">
      <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_200px_auto] md:items-end">
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Search</span>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-paper-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input v-model="allFilters.search" @input="debouncedLoadAll" type="search"
              placeholder="Search by tutor or guardian name..." class="input pl-9 text-sm" />
          </div>
        </div>
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Status</span>
          <DropSelect v-model="allFilters.status" :options="reviewStatusOptions" placeholder="All statuses"
            @update:modelValue="() => loadAll()" />
        </div>
        <button v-if="allFilters.search || allFilters.status" @click="clearAllFilters"
          class="min-h-[44px] rounded-sm bg-red-600 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-red-700">
          Clear
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>

    <div v-else-if="!reviews.length" class="card text-center py-12 text-paper-500 font-body">
      {{ activeTab === 'pending' ? 'No reviews pending moderation.' : 'No reviews found.' }}
    </div>

    <div v-else class="space-y-4">
      <div v-for="review in reviews" :key="review.id" class="card space-y-3">

        <!-- Header: names + status badge (all tab) -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
          <div>
            <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1">Tutor</p>
            <p class="font-display font-semibold text-navy-900">{{ review.tutor_profile?.user?.name }}</p>
          </div>
          <div class="flex items-start gap-3 sm:text-right">
            <span v-if="activeTab === 'all'"
              class="text-xs font-semibold px-2 py-0.5 rounded-pill border"
              :class="statusClass(review.moderation_status)">
              {{ review.moderation_status }}
            </span>
            <div>
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1">Guardian</p>
              <p class="font-display font-semibold text-navy-900">{{ review.guardian_profile?.user?.name }}</p>
            </div>
          </div>
        </div>

        <!-- Stars -->
        <div class="flex items-center gap-1">
          <span v-for="i in 5" :key="i" class="text-xl leading-none"
            :class="i <= review.rating ? 'text-gold-400' : 'text-paper-300'">★</span>
          <span class="ml-2 text-sm font-semibold font-display text-navy-700">{{ review.rating }} / 5</span>
        </div>

        <!-- Review text -->
        <p v-if="review.review_text"
          class="text-sm text-paper-700 font-body leading-relaxed rounded-lg border border-paper-200 bg-paper-50 p-3">
          {{ review.review_text }}
        </p>
        <p v-else class="text-sm text-paper-400 font-body italic">No written review.</p>

        <!-- Moderation note (all tab, rejected) -->
        <div v-if="activeTab === 'all' && review.moderation_note"
          class="rounded-lg border border-red-100 bg-red-50 px-3 py-2">
          <p class="text-xs font-semibold font-display text-red-600 uppercase tracking-wide mb-0.5">Rejection note</p>
          <p class="text-sm text-red-800 font-body">{{ review.moderation_note }}</p>
        </div>

        <!-- Actions (only on pending tab) -->
        <template v-if="activeTab === 'pending'">
          <div v-if="rejectingId === review.id" class="space-y-2">
            <textarea v-model="rejectNote" rows="2" placeholder="Reason for rejection (required)…"
              class="input text-sm w-full resize-none" />
          </div>

          <div class="flex items-center gap-2 pt-1 border-t border-paper-100">
            <button v-if="rejectingId !== review.id"
              @click="approveTarget = review"
              :disabled="!!actionLoading[review.id]"
              class="text-sm font-semibold font-display bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-2 rounded-md transition-colors disabled:opacity-50">
              {{ actionLoading[review.id] === 'approve' ? 'Approving…' : 'Approve' }}
            </button>

            <template v-if="rejectingId === review.id">
              <button @click="confirmReject(review)"
                :disabled="!rejectNote.trim() || !!actionLoading[review.id]"
                class="text-sm font-semibold font-display bg-red-600 text-white hover:bg-red-700 px-4 py-2 rounded-md transition-colors disabled:opacity-50">
                {{ actionLoading[review.id] === 'reject' ? 'Rejecting…' : 'Confirm reject' }}
              </button>
              <button @click="rejectingId = null; rejectNote = ''"
                class="text-sm font-semibold font-display border border-paper-300 text-paper-600 hover:bg-paper-100 px-4 py-2 rounded-md transition-colors">
                Cancel
              </button>
            </template>

            <button v-else
              @click="rejectingId = review.id; rejectNote = ''"
              :disabled="!!actionLoading[review.id]"
              class="text-sm font-semibold font-display border border-red-200 text-red-600 hover:bg-red-50 px-4 py-2 rounded-md transition-colors disabled:opacity-50">
              Reject
            </button>
          </div>
        </template>
      </div>
    </div>

    <AdminPagination :meta="meta" @page="handlePage" />

    <AdminConfirmDialog
      :show="!!approveTarget"
      title="Approve Review?"
      :message="`Publish this review for ${approveTarget?.tutor_profile?.user?.name}? It will become visible on their profile.`"
      confirm-label="Approve"
      @confirm="doApprove"
      @cancel="approveTarget = null"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import AdminPagination from '@/components/admin/AdminPagination.vue'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'
import DropSelect from '@/components/search/DropSelect.vue'

const reviews       = ref([])
const loading       = ref(true)
const actionLoading = reactive({})
const rejectingId   = ref(null)
const rejectNote    = ref('')
const approveTarget = ref(null)
const activeTab     = ref('pending')
const allFilters    = reactive({ search: '', status: '' })
const meta          = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const reviewStatusOptions = [
  { value: '', label: 'All statuses' },
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
]

function statusClass(s) {
  return { pending: 'bg-amber-50 text-amber-700 border-amber-200', approved: 'bg-emerald-50 text-emerald-700 border-emerald-200', rejected: 'bg-red-50 text-red-700 border-red-200' }[s] ?? 'bg-paper-100 text-paper-500 border-paper-200'
}

function switchTab(tab) {
  activeTab.value = tab
  rejectingId.value = null
  rejectNote.value = ''
  if (tab === 'pending') loadReviews()
  else loadAll()
}

let allTimer = null
function debouncedLoadAll() {
  clearTimeout(allTimer)
  allTimer = setTimeout(() => loadAll(), 350)
}

onMounted(loadReviews)

async function loadReviews(page = 1) {
  loading.value = true
  try {
    const { data } = await adminApi.getPendingReviews({ page, per_page: 10 })
    reviews.value = data.data?.data ?? data.data ?? []
    meta.value = data.data
  } finally {
    loading.value = false
  }
}

async function loadAll(page = 1) {
  loading.value = true
  try {
    const { data } = await adminApi.getReviews({ ...allFilters, page, per_page: 10 })
    reviews.value = data.data?.data ?? data.data ?? []
    meta.value = data.data
  } finally {
    loading.value = false
  }
}

function clearAllFilters() {
  allFilters.search = ''
  allFilters.status = ''
  loadAll()
}

function handlePage(page) {
  if (activeTab.value === 'pending') loadReviews(page)
  else loadAll(page)
}

async function doApprove() {
  const review = approveTarget.value
  approveTarget.value = null
  if (!review) return
  actionLoading[review.id] = 'approve'
  try {
    await adminApi.approveReview(review.id)
    reviews.value = reviews.value.filter(r => r.id !== review.id)
    toast.success('Review approved and published.')
  } catch {
    toast.error('Could not approve review.')
  } finally {
    delete actionLoading[review.id]
  }
}

async function confirmReject(review) {
  if (!rejectNote.value.trim()) return
  actionLoading[review.id] = 'reject'
  try {
    await adminApi.rejectReview(review.id, { moderation_note: rejectNote.value })
    reviews.value = reviews.value.filter(r => r.id !== review.id)
    rejectingId.value = null
    rejectNote.value  = ''
    toast.success('Review rejected.')
  } catch {
    toast.error('Could not reject review.')
  } finally {
    delete actionLoading[review.id]
  }
}
</script>
