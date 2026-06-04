<template>
  <div>
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="font-display font-bold text-2xl text-navy-900">Tuition requirements</h1>
        <p class="mt-1 text-sm font-body text-paper-500">Track guardian-posted requirements and update their lifecycle.</p>
      </div>
      <p v-if="meta.total" class="text-xs font-semibold font-display text-paper-500">
        {{ meta.total }} total
      </p>
    </div>

    <!-- Filters -->
    <div class="card mb-5">
      <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_180px_auto] lg:items-end">
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Search</span>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-paper-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input v-model="filters.search" @input="debouncedLoad" type="search" placeholder="Search student or guardian..."
              class="input pl-9 text-sm" />
          </div>
        </div>
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Status</span>
          <DropSelect v-model="filters.status" :options="statusOptions" placeholder="All statuses"
            @update:modelValue="() => load()" />
        </div>
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Class</span>
          <DropSelect v-model="filters.class_level" :options="classOptions" placeholder="All classes"
            @update:modelValue="() => load()" />
        </div>
        <button v-if="filters.search || filters.status || filters.class_level" @click="clearFilters"
          class="min-h-[44px] rounded-sm bg-red-600 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-red-700">
          Clear
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>

    <div v-else-if="!requirements.length" class="card text-center py-12 text-paper-500 font-body">
      No requirements found.
    </div>

    <div v-else class="space-y-3">
      <div v-for="req in requirements" :key="req.id" class="card space-y-3">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
          <div>
            <div class="flex items-center gap-2 flex-wrap mb-1">
              <p class="font-display font-semibold text-navy-900">{{ req.student_name || '—' }}</p>
              <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border"
                :class="statusClass(req.status)">{{ req.status }}</span>
            </div>
            <p class="text-sm text-paper-500 font-body">
              Guardian: <span class="font-semibold text-navy-800">{{ req.guardian_profile?.user?.name }}</span>
              <span class="text-paper-400"> · {{ req.guardian_profile?.user?.email }}</span>
            </p>
          </div>
          <div class="text-right shrink-0">
            <p class="text-sm font-semibold text-navy-700 font-display">{{ req.class_level }} · {{ req.medium }}</p>
            <p class="text-xs text-paper-400 font-body">{{ req.district?.name }}</p>
          </div>
        </div>

        <!-- Details row -->
        <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm font-body text-paper-600 pt-2 border-t border-paper-100">
          <span v-if="req.salary_min || req.salary_max">
            Salary: <strong class="text-navy-800">{{ req.salary_min }}–{{ req.salary_max }} ৳</strong>
          </span>
          <span v-if="req.days_per_week">Days/week: <strong class="text-navy-800">{{ req.days_per_week }}</strong></span>
          <span v-if="req.preferred_tutor_gender">Gender: <strong class="text-navy-800 capitalize">{{ req.preferred_tutor_gender }}</strong></span>
          <span v-if="req.subjects?.length">Subjects: <strong class="text-navy-800">{{ req.subjects.map(s=>s.name).join(', ') }}</strong></span>
        </div>

        <p v-if="req.special_notes" class="text-sm text-paper-600 font-body italic">{{ req.special_notes }}</p>

        <!-- Status change -->
        <div class="flex items-center gap-2 pt-2 border-t border-paper-100">
          <span class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Change status:</span>
          <button v-for="s in ['open','matched','closed']" :key="s" @click="openStatusChange(req, s)"
            :disabled="req.status === s || !!statusSaving[req.id]"
            class="text-xs font-semibold font-display px-3 py-1 rounded-md border transition-colors disabled:opacity-40"
            :class="req.status === s ? 'bg-navy-700 text-white border-navy-700' : 'border-paper-300 text-paper-600 hover:bg-paper-100'">
            {{ s }}
          </button>
        </div>
      </div>
    </div>

    <AdminPagination :meta="meta" @page="load" />

    <AdminConfirmDialog
      :show="!!pendingStatus"
      title="Update Requirement Status?"
      :message="pendingStatus ? `Change ${pendingStatus.requirement.student_name || 'this requirement'} to '${pendingStatus.status}'?` : ''"
      confirm-label="Yes, Update"
      @confirm="confirmStatusChange"
      @cancel="pendingStatus = null"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'
import AdminPagination from '@/components/admin/AdminPagination.vue'
import DropSelect from '@/components/search/DropSelect.vue'

const requirements = ref([])
const loading      = ref(true)
const meta         = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const filters      = reactive({ search: '', status: '', class_level: '' })
const statusSaving = reactive({})
const pendingStatus = ref(null)
const classes = ['Class 1','Class 2','Class 3','Class 4','Class 5','Class 6','Class 7','Class 8','Class 9','Class 10','SSC','HSC','Other']
const statusOptions = [
  { value: '', label: 'All statuses' },
  { value: 'open', label: 'Open' },
  { value: 'matched', label: 'Matched' },
  { value: 'closed', label: 'Closed' },
]
const classOptions = [
  { value: '', label: 'All classes' },
  ...classes.map(c => ({ value: c, label: c })),
]

let searchTimer = null
function debouncedLoad() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => load(), 350)
}

async function load(page = 1) {
  loading.value = true
  try {
    const { data } = await adminApi.getRequirements({ ...filters, page, per_page: 10 })
    requirements.value = data.data.data ?? data.data
    meta.value = data.data
  } finally {
    loading.value = false
  }
}

function clearFilters() {
  filters.search = ''
  filters.status = ''
  filters.class_level = ''
  load()
}

function statusClass(s) {
  return { open: 'bg-emerald-50 text-emerald-700 border-emerald-200', matched: 'bg-blue-50 text-blue-700 border-blue-200', closed: 'bg-paper-100 text-paper-500 border-paper-200' }[s] ?? 'bg-paper-100 text-paper-500 border-paper-200'
}

function openStatusChange(req, status) {
  if (req.status === status) return
  pendingStatus.value = { requirement: req, status }
}

async function confirmStatusChange() {
  const { requirement: req, status } = pendingStatus.value
  pendingStatus.value = null
  statusSaving[req.id] = true
  try {
    await adminApi.updateRequirementStatus(req.id, { status })
    req.status = status
    toast.success(`Status changed to ${status}.`)
  } catch {
    toast.error('Could not update status.')
  } finally {
    delete statusSaving[req.id]
  }
}

onMounted(() => load())
</script>
