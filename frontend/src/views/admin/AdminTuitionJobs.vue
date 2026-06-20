<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="font-display text-xs font-bold uppercase text-gold-600">Admin</p>
        <h1 class="mt-1 font-display font-bold text-2xl text-navy-900 md:text-3xl">Tuition Jobs</h1>
        <p class="mt-1 text-sm font-body text-paper-500">Monitor job posts, applicants, guardians, and current hiring status.</p>
      </div>
      <div v-if="meta" class="grid grid-cols-2 gap-3 sm:flex">
        <div class="rounded-md border border-paper-200 bg-white px-4 py-3 shadow-sm">
          <p class="text-[11px] font-bold font-display uppercase text-paper-500">Total Jobs</p>
          <p class="mt-0.5 text-xl font-bold font-display text-navy-900">{{ meta.total }}</p>
        </div>
        <div class="rounded-md border border-paper-200 bg-white px-4 py-3 shadow-sm">
          <p class="text-[11px] font-bold font-display uppercase text-paper-500">Page</p>
          <p class="mt-0.5 text-xl font-bold font-display text-navy-900">{{ meta.current_page || page }}</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card">
      <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_12rem_auto] lg:items-end">
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Search</span>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-paper-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <input v-model="filters.q" @input="debouncedLoad" type="search" placeholder="Title or Job ID…" class="input pl-9 text-sm" />
          </div>
        </div>
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Status</span>
          <DropSelect v-model="filters.status" :options="statusOptions" placeholder="All statuses"
            @update:modelValue="load()" />
        </div>
        <button v-if="filters.q || filters.status" @click="clearFilters"
          class="min-h-[44px] rounded-md bg-red-600 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-red-700">
          Clear
        </button>
      </div>
    </div>

    <div v-if="loading" class="card py-16 text-center text-paper-400 font-body text-sm">Loading…</div>

    <div v-else-if="!jobs.length" class="card py-16 text-center">
      <p class="font-display font-semibold text-navy-700">No jobs found</p>
      <p class="text-sm text-paper-500 font-body mt-1">
        {{ filters.q || filters.status ? 'Try adjusting your filters.' : 'No tuition jobs have been posted yet.' }}
      </p>
    </div>

    <div v-else class="grid gap-4 xl:grid-cols-2">
      <div v-for="job in jobs" :key="job.id"
        class="admin-job-card flex h-full min-h-[20rem] flex-col rounded-md border border-paper-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-navy-200 hover:shadow-md">

        <div class="min-h-[6.5rem]">
          <div class="mb-3 flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold font-display px-2.5 py-1 rounded-pill border"
                :class="job.status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200'">
              {{ job.status === 'open' ? 'Open' : 'Closed' }}
            </span>
            <span class="text-xs font-semibold text-paper-500 font-body">#{{ job.public_id }}</span>
            <span class="h-1 w-1 rounded-full bg-paper-300"></span>
            <span class="text-xs text-paper-500 font-body">{{ formatDate(job.created_at) }}</span>
          </div>

          <RouterLink :to="`/admin/tuition-jobs/${job.public_id}`"
            class="job-title-clamp block font-display text-lg font-bold leading-snug text-navy-900 transition-colors hover:text-navy-700">
            {{ job.title }}
          </RouterLink>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
          <div class="job-info-box">
            <span class="job-info-label">Location</span>
            <span class="job-info-value">{{ locationLabel(job) }}</span>
          </div>
          <div class="job-info-box">
            <span class="job-info-label">Salary</span>
            <span class="job-info-value">{{ job.offered_salary ? `৳${job.offered_salary.toLocaleString()}/mo` : '—' }}</span>
          </div>
          <div class="job-info-box">
            <span class="job-info-label">Applicants</span>
            <span class="job-info-value">{{ job.applications_count ?? 0 }} applicant{{ (job.applications_count ?? 0) === 1 ? '' : 's' }}</span>
          </div>
          <div class="job-info-box">
            <span class="job-info-label">Guardian</span>
            <span class="job-info-value truncate">{{ job.guardian_profile?.user?.name ?? '—' }}</span>
          </div>
        </div>

        <div class="mt-4 min-h-[2rem]">
          <div v-if="job.subjects?.length" class="flex flex-wrap gap-1.5">
            <span v-for="s in job.subjects.slice(0, 4)" :key="s.id"
              class="text-xs bg-navy-50 text-navy-600 border border-navy-100 px-2.5 py-1 rounded-pill font-semibold font-display">
              {{ s.name }}
            </span>
            <span v-if="job.subjects.length > 4"
              class="text-xs bg-paper-100 text-paper-600 border border-paper-200 px-2.5 py-1 rounded-pill font-semibold font-display">
              +{{ job.subjects.length - 4 }}
            </span>
          </div>
        </div>

        <div class="mt-auto flex flex-col gap-3 border-t border-paper-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="min-w-0 text-xs font-body text-paper-500">
            <p class="truncate">{{ job.guardian_profile?.user?.email || 'No guardian email' }}</p>
            <p v-if="job.guardian_profile?.guardian_id" class="mt-0.5">
              Guardian ID: <span class="font-semibold text-navy-600">{{ job.guardian_profile.guardian_id }}</span>
            </p>
          </div>
          <RouterLink :to="`/admin/tuition-jobs/${job.public_id}`"
            class="inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-md bg-navy-700 px-4 py-2 text-sm font-semibold font-display text-white transition-colors hover:bg-navy-800">
            Manage
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5"/>
            </svg>
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="meta && meta.last_page > 1" class="mt-6 flex items-center justify-center gap-2">
      <button @click="goPage(meta.current_page - 1)" :disabled="meta.current_page === 1"
        class="rounded-sm border border-paper-300 bg-white px-3 py-2 text-sm font-semibold font-display text-navy-700 hover:bg-paper-100 disabled:opacity-40 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
      </button>
      <span class="text-sm font-body text-paper-500">
        Page {{ meta.current_page }} of {{ meta.last_page }}
      </span>
      <button @click="goPage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
        class="rounded-sm border border-paper-300 bg-white px-3 py-2 text-sm font-semibold font-display text-navy-700 hover:bg-paper-100 disabled:opacity-40 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import DropSelect from '@/components/search/DropSelect.vue'

const jobs    = ref([])
const loading = ref(true)
const meta    = ref(null)
const page    = ref(1)

const filters = reactive({ q: '', status: '' })

const statusOptions = [
  { value: '', label: 'All statuses' },
  { value: 'open',   label: 'Open'   },
  { value: 'closed', label: 'Closed' },
]


async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: 15 }
    if (filters.q)      params.q      = filters.q
    if (filters.status) params.status = filters.status
    const { data } = await adminApi.getTuitionJobs(params)
    jobs.value = data.data.data || []
    meta.value = data.data
  } catch {
    toast.error('Failed to load tuition jobs.')
  } finally {
    loading.value = false
  }
}

function clearFilters() {
  filters.q = ''
  filters.status = ''
  page.value = 1
  load()
}

function goPage(p) {
  page.value = p
  load()
}

let searchTimer = null
function debouncedLoad() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 400)
}

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function locationLabel(job) {
  return job.area?.name ? `${job.area.name}, ${job.district?.name}` : (job.district?.name || '—')
}

onMounted(load)
</script>

<style scoped>
.admin-job-card {
  contain: layout paint;
}

.job-title-clamp {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.job-info-box {
  @apply min-w-0 rounded-md border border-paper-200 bg-paper-50/80 px-3 py-2.5;
}

.job-info-label {
  @apply block text-[10px] font-bold font-display uppercase text-paper-500;
}

.job-info-value {
  @apply mt-0.5 block text-sm font-semibold font-display leading-snug text-navy-800;
}
</style>
