<template>
  <div>
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="font-display font-bold text-2xl text-navy-900">Audit log</h1>
        <p class="mt-1 text-sm font-body text-paper-500">Track admin actions, targets and moderation changes.</p>
      </div>
      <p v-if="meta.total" class="text-xs font-semibold font-display text-paper-500">
        {{ meta.total }} entries
      </p>
    </div>

    <!-- Filters -->
    <div class="card mb-5">
      <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_180px_auto] lg:items-end">
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Search</span>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-paper-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input v-model="filters.search" @input="debouncedLoad" type="search" placeholder="Search description or admin..."
              class="input pl-9 text-sm" />
          </div>
        </div>
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Action</span>
          <DropSelect v-model="filters.action" :options="actionOptions" placeholder="All actions"
            @update:modelValue="() => load()" />
        </div>
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Target</span>
          <DropSelect v-model="filters.target_type" :options="targetOptions" placeholder="All targets"
            @update:modelValue="() => load()" />
        </div>
        <button v-if="filters.search || filters.action || filters.target_type" @click="clearFilters"
          class="min-h-[44px] rounded-sm bg-red-600 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-red-700">
          Clear
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>

    <div v-else-if="!logs.length" class="card text-center py-12 text-paper-500 font-body">
      No audit log entries found.
    </div>

    <div v-else class="space-y-2">
      <div v-for="log in logs" :key="log.id"
        class="card py-3 flex flex-col sm:flex-row sm:items-start gap-2">
        <!-- Left: action badge + description -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap mb-1">
            <span class="text-[10px] font-bold font-display uppercase tracking-wide bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill">
              {{ actionLabel(log.action ?? '') }}
            </span>
            <span class="text-[10px] font-semibold font-display text-paper-400 uppercase tracking-wide">
              {{ titleCase(log.target_type ?? '') }} #{{ log.target_id }}
            </span>
          </div>
          <p class="text-sm text-navy-800 font-body">{{ log.description }}</p>
          <p class="text-xs text-paper-400 font-body mt-0.5">IP: {{ log.ip_address }}</p>
        </div>
        <!-- Right: admin + time -->
        <div class="text-right shrink-0">
          <p class="text-sm font-semibold font-display text-navy-900">{{ log.admin?.name }}</p>
          <p class="text-xs text-paper-400 font-body capitalize">{{ log.admin?.role?.replace('_',' ') }}</p>
          <p class="text-xs text-paper-400 font-body mt-0.5">{{ formatDate(log.created_at) }}</p>
        </div>
      </div>
    </div>

    <AdminPagination :meta="meta" @page="load" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'
import AdminPagination from '@/components/admin/AdminPagination.vue'
import DropSelect from '@/components/search/DropSelect.vue'

const logs    = ref([])
const loading = ref(true)
const meta    = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const filters = reactive({ search: '', action: '', target_type: '' })
function titleCase(str) {
  return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const ACTION_LABELS = {
  'create_admin':             'Admin Creation',
  'update_admin':             'Admin Update',
  'update_tutor':             'Tutor Profile Edit',
  'update_tutor_status':      'Tutor Status Change',
  'upload_tutor_document':    'Tutor Document Upload',
  'delete_tutor_document':    'Tutor Document Deletion',
  'update_tutor_video':       'Tutor Video Update',
  'delete_tutor_video':       'Tutor Video Deletion',
  'review_tutor_video':       'Tutor Video Review',
  'update_guardian':          'Guardian Profile Edit',
  'update_guardian_status':   'Guardian Status Change',
  'upload_guardian_nid':      'Guardian NID Upload',
  'delete_guardian_nid':      'Guardian NID Deletion',
  'replace_user_avatar':      'Avatar Replacement',
  'remove_user_avatar':       'Avatar Removal',
  'approve_verification':     'Verification Approval',
  'reject_verification':      'Verification Rejection',
  'approve_pending_changes':  'Pending Changes Approval',
  'reject_pending_changes':   'Pending Changes Rejection',
  'update_connection_status': 'Connection Status Change',
  'add_connection_notes':     'Connection Note Added',
  'approve_review':           'Review Approval',
  'reject_review':            'Review Rejection',
  'create_subject':           'Subject Creation',
  'update_subject':           'Subject Update',
  'delete_subject':           'Subject Deletion',
  'create_district':          'District Creation',
  'update_district':          'District Update',
  'delete_district':          'District Deletion',
  'create_area':              'Area Creation',
  'update_area':              'Area Update',
  'delete_area':              'Area Deletion',
}

function actionLabel(slug) {
  return ACTION_LABELS[slug] ?? titleCase(slug)
}

const actionOptions = computed(() => [
  { value: '', label: 'All actions' },
  ...Object.entries(ACTION_LABELS).map(([value, label]) => ({ value, label })),
])
const targetOptions = [
  { value: '',                   label: 'All targets' },
  { value: 'user',               label: 'User' },
  { value: 'tutor_profile',      label: 'Tutor Profile' },
  { value: 'guardian_profile',   label: 'Guardian Profile' },
  { value: 'connection_request', label: 'Connection Request' },
  { value: 'review',             label: 'Review' },
  { value: 'subject',            label: 'Subject' },
  { value: 'district',           label: 'District' },
  { value: 'area',               label: 'Area' },
]

let searchTimer = null
function debouncedLoad() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => load(), 350)
}

async function load(page = 1) {
  loading.value = true
  try {
    const { data } = await adminApi.getAuditLog({ ...filters, page, per_page: 10 })
    logs.value = data.data.data ?? data.data
    meta.value = data.data.meta ?? data.data
  } finally {
    loading.value = false
  }
}

function clearFilters() {
  filters.search = ''
  filters.action = ''
  filters.target_type = ''
  load()
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(() => load())
</script>
