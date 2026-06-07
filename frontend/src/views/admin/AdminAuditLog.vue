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

    <div v-else class="space-y-3">
      <article v-for="log in logs" :key="log.id" class="audit-card">
        <div class="audit-icon" :class="actionTone(log).icon">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" :d="actionTone(log).path" />
          </svg>
        </div>

        <div class="min-w-0 flex-1">
          <div class="mb-2 flex flex-wrap items-center gap-2">
            <span class="audit-action" :class="actionTone(log).badge">
              {{ actionLabel(log.action ?? '') }}
            </span>
            <span v-if="log.target_type" class="audit-target">
              {{ titleCase(log.target_type) }}<span v-if="log.target_id"> #{{ log.target_id }}</span>
            </span>
          </div>

          <p class="font-body text-sm leading-relaxed text-navy-800">
            {{ log.description || 'No description provided.' }}
          </p>

          <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs font-body text-paper-500">
            <span class="inline-flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-paper-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3.5 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
              {{ formatDate(log.created_at) }}
            </span>
            <span v-if="log.ip_address" class="inline-flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-paper-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 0 0 9-9H3a9 9 0 0 0 9 9Zm0 0c2.25-2.25 3.375-5.25 3.375-9S14.25 5.25 12 3m0 18c-2.25-2.25-3.375-5.25-3.375-9S9.75 5.25 12 3m0 0a9 9 0 0 1 9 9M12 3a9 9 0 0 0-9 9" />
              </svg>
              {{ log.ip_address }}
            </span>
          </div>
        </div>

        <div class="audit-admin">
          <div class="audit-avatar">
            {{ initials(log.admin?.name) }}
          </div>
          <div class="min-w-0 sm:text-right">
            <p class="truncate font-display text-sm font-bold text-navy-900">
              {{ log.admin?.name || 'System' }}
            </p>
            <p class="mt-0.5 text-xs font-body capitalize text-paper-500">
              {{ roleLabel(log.admin?.role) }}
            </p>
          </div>
        </div>
      </article>
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

const ACTION_TONES = {
  create: {
    icon: 'bg-emerald-700 text-white',
    badge: 'bg-emerald-700 text-white border-emerald-800',
    path: 'M12 4.5v15m7.5-7.5h-15',
  },
  update: {
    icon: 'bg-blue-700 text-white',
    badge: 'bg-blue-700 text-white border-blue-800',
    path: 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z',
  },
  approve: {
    icon: 'bg-green-700 text-white',
    badge: 'bg-green-700 text-white border-green-800',
    path: 'M9 12.75 11.25 15 15.75 9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
  },
  reject: {
    icon: 'bg-red-100 text-red-700',
    badge: 'bg-red-100 text-red-800 border-red-300',
    path: 'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
  },
  delete: {
    icon: 'bg-red-600 text-white',
    badge: 'bg-red-600 text-white border-red-700',
    path: 'm14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M18 6l-.867 12.142A2.25 2.25 0 0 1 14.889 20.25H9.11a2.25 2.25 0 0 1-2.244-2.108L6 6m3 0V4.875c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V6',
  },
  danger: {
    icon: 'bg-red-600 text-white',
    badge: 'bg-red-600 text-white border-red-700',
    path: 'M12 9v3.75m0 3.75h.008v.008H12V16.5Zm8.25-4.5a8.25 8.25 0 1 1-16.5 0 8.25 8.25 0 0 1 16.5 0Z',
  },
  upload: {
    icon: 'bg-amber-400 text-navy-900',
    badge: 'bg-amber-400 text-navy-900 border-amber-500',
    path: 'M12 16.5V3.75m0 0L7.5 8.25M12 3.75l4.5 4.5M4.5 19.5h15',
  },
  default: {
    icon: 'bg-sky-700 text-white',
    badge: 'bg-sky-700 text-white border-sky-800',
    path: 'M9 12h6m-6 4.5h6M7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v12A2.25 2.25 0 0 1 16.5 20.25h-9A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z',
  },
}

const DANGER_WORDS = ['suspend', 'suspended', 'delete', 'deleted', 'remove', 'removed', 'ban', 'banned', 'disable', 'disabled', 'block', 'blocked', 'deactivate', 'deactivated']

function actionTone(log = {}) {
  const action = String(log.action || '')
  const haystack = `${action} ${log.description || ''}`.toLowerCase()

  if (DANGER_WORDS.some(word => haystack.includes(word))) {
    return ACTION_TONES.danger
  }

  const key = action.split('_')[0]
  return ACTION_TONES[key] || ACTION_TONES.default
}

function initials(name = '') {
  const parts = String(name || 'System').trim().split(/\s+/).filter(Boolean)
  return parts.slice(0, 2).map(part => part[0]?.toUpperCase()).join('') || 'S'
}

function roleLabel(role = '') {
  return role ? String(role).replace(/_/g, ' ') : 'system'
}

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

<style scoped>
.audit-card {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  gap: 1rem;
  align-items: start;
  border: 1px solid #e7e1d5;
  border-radius: 0.75rem;
  background: #ffffff;
  padding: 1rem;
  box-shadow: 0 12px 28px rgba(15, 46, 92, 0.055);
  transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
}

.audit-card:hover {
  border-color: #d8cfbe;
  box-shadow: 0 16px 36px rgba(15, 46, 92, 0.09);
  transform: translateY(-1px);
}

.audit-icon {
  display: flex;
  height: 2.75rem;
  width: 2.75rem;
  align-items: center;
  justify-content: center;
  border-radius: 0.65rem;
}

.audit-action {
  border-width: 1px;
  border-style: solid;
  border-radius: 999px;
  padding: 0.25rem 0.65rem;
  font-family: var(--font-display, inherit);
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.035em;
  text-transform: uppercase;
}

.audit-target {
  border-radius: 999px;
  border: 1px solid #e7e1d5;
  background: #fbf9f3;
  padding: 0.25rem 0.65rem;
  font-family: var(--font-display, inherit);
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.035em;
  color: #6d6254;
  text-transform: uppercase;
}

.audit-admin {
  display: flex;
  max-width: 14rem;
  align-items: center;
  gap: 0.65rem;
}

.audit-avatar {
  display: flex;
  height: 2.35rem;
  width: 2.35rem;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: #0f2e5c;
  color: #ffffff;
  font-family: var(--font-display, inherit);
  font-size: 0.75rem;
  font-weight: 800;
}

@media (max-width: 640px) {
  .audit-card {
    grid-template-columns: auto minmax(0, 1fr);
  }

  .audit-admin {
    grid-column: 2;
    max-width: none;
    padding-top: 0.5rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .audit-card {
    transition: none;
  }

  .audit-card:hover {
    transform: none;
  }
}
</style>
