<template>
  <div>
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="font-display font-bold text-2xl text-navy-900">Tuition Jobs</h1>
        <p class="mt-1 text-sm font-body text-paper-500">Monitor and manage all posted tuition jobs.</p>
      </div>
      <div v-if="meta" class="flex gap-3 text-sm font-display">
        <span class="px-2.5 py-1 rounded-full bg-navy-50 text-navy-700 border border-navy-200 font-semibold">
          {{ meta.total }} total
        </span>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-5">
      <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[180px]">
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Search</span>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-paper-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <input v-model="filters.q" @input="debouncedLoad" type="search" placeholder="Title or Job ID…" class="input pl-9 text-sm" />
          </div>
        </div>
        <div class="w-44">
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Status</span>
          <DropSelect v-model="filters.status" :options="statusOptions" placeholder="All statuses"
            @update:modelValue="load()" />
        </div>
        <button v-if="filters.q || filters.status" @click="clearFilters"
          class="min-h-[44px] rounded-sm bg-red-600 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-red-700">
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

    <div v-else class="space-y-3">
      <div v-for="job in jobs" :key="job.id" class="card">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start">

          <!-- Status + Title -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border"
                :class="job.status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-paper-100 text-paper-500 border-paper-200'">
                {{ job.status === 'open' ? 'Open' : 'Closed' }}
              </span>
              <span class="text-xs text-paper-400 font-body">#{{ job.public_id }}</span>
              <span class="text-xs text-paper-400 font-body">·</span>
              <span class="text-xs text-paper-400 font-body">{{ job.applications_count ?? 0 }} applicants</span>
            </div>
            <p class="font-display font-semibold text-navy-900 text-sm">{{ job.title }}</p>
            <p class="text-xs text-paper-400 font-body mt-0.5">
              <span v-if="job.area">{{ job.area.name }}, </span>{{ job.district?.name }}
              <template v-if="job.offered_salary"> · ৳{{ job.offered_salary.toLocaleString() }}/mo</template>
            </p>
          </div>

          <!-- Guardian -->
          <div class="shrink-0 text-sm">
            <p class="font-semibold font-display text-navy-700 text-xs">Posted by</p>
            <p class="font-body text-paper-700 text-xs mt-0.5">{{ job.guardian_profile?.user?.name ?? '—' }}</p>
            <p class="font-body text-paper-400 text-xs">{{ job.guardian_profile?.user?.email }}</p>
          </div>

          <!-- Date -->
          <div class="shrink-0 text-xs text-paper-400 font-body">
            {{ formatDate(job.created_at) }}
          </div>

          <!-- Actions -->
          <div class="flex gap-2 shrink-0">
            <button v-if="job.status === 'open'" @click="closeJob(job)"
              :disabled="acting[job.id]"
              class="rounded-sm border border-red-200 px-3 py-1.5 text-xs font-semibold font-display text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50">
              Close
            </button>
            <button v-else @click="reopenJob(job)"
              :disabled="acting[job.id]"
              class="rounded-sm border border-emerald-200 px-3 py-1.5 text-xs font-semibold font-display text-emerald-700 hover:bg-emerald-50 transition-colors disabled:opacity-50">
              Reopen
            </button>
          </div>
        </div>

        <!-- Subjects -->
        <div v-if="job.subjects?.length" class="mt-3 flex flex-wrap gap-1.5">
          <span v-for="s in job.subjects" :key="s.id"
            class="text-xs bg-navy-50 text-navy-600 border border-navy-100 px-2.5 py-0.5 rounded-pill font-body">
            {{ s.name }}
          </span>
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
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import DropSelect from '@/components/search/DropSelect.vue'

const jobs    = ref([])
const loading = ref(true)
const meta    = ref(null)
const acting  = ref({})
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

async function closeJob(job) {
  acting.value[job.id] = true
  try {
    await adminApi.closeTuitionJob(job.public_id)
    job.status = 'closed'
    toast.success('Job closed.')
  } catch {
    toast.error('Failed to close job.')
  } finally {
    delete acting.value[job.id]
  }
}

async function reopenJob(job) {
  acting.value[job.id] = true
  try {
    await adminApi.reopenTuitionJob(job.public_id)
    job.status = 'open'
    toast.success('Job reopened.')
  } catch {
    toast.error('Failed to reopen job.')
  } finally {
    delete acting.value[job.id]
  }
}

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(load)
</script>
