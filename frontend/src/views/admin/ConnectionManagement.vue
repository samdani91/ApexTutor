<template>
  <div>
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="font-display font-bold text-2xl text-navy-900">Connection management</h1>
        <p class="mt-1 text-sm font-body text-paper-500">Review guardian-tutor requests and update connection progress.</p>
      </div>
      <p v-if="meta.total" class="text-xs font-semibold font-display text-paper-500">
        {{ meta.total }} total
      </p>
    </div>

    <!-- Search + filter -->
    <div class="card mb-5 space-y-3">
      <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto] md:items-end">
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Search</span>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-paper-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input v-model="filters.search" @input="debouncedLoad" type="search"
              placeholder="Search guardian or tutor name..." class="input pl-9 text-sm" />
          </div>
        </div>
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Status</span>
          <DropSelect v-model="filters.status" :options="connectionStatusFilterOptions" placeholder="All statuses"
            @update:modelValue="() => load()" />
        </div>
        <button v-if="filters.search || filters.status" @click="clearFilters"
          class="min-h-[44px] rounded-sm bg-red-600 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-red-700">
          Clear
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>

    <template v-else-if="connections.length">
      <div class="space-y-3">
        <div v-for="conn in connections" :key="conn.id"
          class="card overflow-hidden transition-all">

          <!-- Summary row -->
          <div class="flex flex-col sm:flex-row sm:items-center gap-3 cursor-pointer"
            @click="toggleExpand(conn.id)">

            <!-- Guardian / Tutor names -->
            <div class="flex flex-1 min-w-0 gap-6">
              <div class="min-w-0">
                <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-0.5">Guardian</p>
                <p class="font-display font-semibold text-navy-900 text-sm truncate">
                  {{ conn.guardian_profile?.user?.name || '—' }}
                </p>
              </div>
              <div class="min-w-0">
                <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-0.5">Tutor</p>
                <p class="font-display font-semibold text-navy-900 text-sm truncate">
                  {{ conn.tutor_profile?.user?.name || '—' }}
                </p>
              </div>
            </div>

            <!-- Status badge -->
            <span class="px-2.5 py-0.5 rounded-pill text-xs font-semibold capitalize shrink-0"
              :class="statusBadgeClass(conn.status)">
              {{ conn.status.replace(/_/g,' ') }}
            </span>

            <!-- Expand chevron -->
            <svg class="w-4 h-4 text-paper-400 shrink-0 transition-transform"
              :class="expandedId === conn.id ? 'rotate-180' : ''"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </div>

          <!-- Expanded detail panel -->
          <div v-if="expandedId === conn.id" class="mt-4 pt-4 border-t border-paper-200 space-y-4">

            <div v-if="detailLoading" class="text-sm text-paper-500 font-body">Loading details…</div>
            <template v-else-if="detail">

              <!-- Contact info grid -->
              <div class="grid sm:grid-cols-2 gap-4">
                <!-- Guardian -->
                <div class="rounded-lg border border-paper-200 bg-paper-50 p-3 space-y-1">
                  <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-2">Guardian</p>
                  <p class="text-sm font-semibold font-display text-navy-900">{{ detail.guardian_profile?.user?.name }}</p>
                  <p v-if="detail.guardian_profile?.user?.email" class="text-xs text-paper-600 font-body flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-paper-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                    </svg>
                    {{ detail.guardian_profile?.user?.email }}
                  </p>
                  <p v-if="detail.guardian_profile?.user?.phone" class="text-xs text-paper-600 font-body flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-paper-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z"/>
                    </svg>
                    {{ detail.guardian_profile?.user?.phone }}
                  </p>
                </div>

                <!-- Tutor -->
                <div class="rounded-lg border border-paper-200 bg-paper-50 p-3 space-y-1">
                  <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-2">Tutor</p>
                  <p class="text-sm font-semibold font-display text-navy-900">{{ detail.tutor_profile?.user?.name }}</p>
                  <p v-if="detail.tutor_profile?.user?.email" class="text-xs text-paper-600 font-body flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-paper-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                    </svg>
                    {{ detail.tutor_profile?.user?.email }}
                  </p>
                  <p v-if="detail.tutor_profile?.user?.phone" class="text-xs text-paper-600 font-body flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-paper-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z"/>
                    </svg>
                    {{ detail.tutor_profile?.user?.phone }}
                  </p>
                </div>
              </div>

              <!-- Guardian message -->
              <div v-if="detail.guardian_message" class="rounded-lg border border-blue-100 bg-blue-50 p-3">
                <p class="text-xs font-semibold font-display text-blue-700 uppercase tracking-wide mb-1">Guardian's message</p>
                <p class="text-sm text-blue-900 font-body leading-relaxed">{{ detail.guardian_message }}</p>
              </div>

              <!-- Admin notes -->
              <div class="space-y-2">
                <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Admin notes</p>
                <textarea v-model="notesMap[conn.id]" rows="3"
                  placeholder="Add internal notes about this connection…"
                  class="input text-sm w-full resize-none" />
                <button @click="saveNotes(conn.id)"
                  :disabled="notesSaving[conn.id]"
                  class="text-xs font-semibold font-display bg-navy-700 text-white hover:bg-navy-800 px-3 py-1.5 rounded-md transition-colors disabled:opacity-50">
                  {{ notesSaving[conn.id] ? 'Saving…' : 'Save notes' }}
                </button>
              </div>

              <!-- Status change -->
              <div class="flex items-center gap-3 pt-2 border-t border-paper-100">
                <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide shrink-0">Update status</p>
                <div class="w-56">
                  <DropSelect :model-value="conn.status" :options="connectionStatusOptions" placeholder="Pending"
                    @update:modelValue="value => onStatusChange(conn, value)" />
                </div>
              </div>
            </template>
          </div>

        </div>
      </div>
    </template>

    <div v-else class="card text-center py-12 text-paper-500 font-body">
      No connections found.
    </div>

    <AdminPagination :meta="meta" @page="load" />

    <!-- Status change confirm -->
    <AdminConfirmDialog
      :show="!!pendingChange"
      title="Update Connection Status?"
      :message="pendingChange ? `Change status to &quot;${pendingChange.newStatus.replace(/_/g,' ')}&quot; for ${pendingChange.conn.guardian_profile?.user?.name}&apos;s request?` : ''"
      confirm-label="Yes, Update"
      @confirm="confirmStatusChange"
      @cancel="cancelStatusChange"
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

const connections   = ref([])
const loading       = ref(true)
const pendingChange = ref(null)
const expandedId    = ref(null)
const detail        = ref(null)
const detailLoading = ref(false)
const notesMap      = reactive({})
const notesSaving   = reactive({})
const filters       = reactive({ search: '', status: '' })
const meta          = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

const connectionStatusOptions = [
  { value: 'pending',          label: 'Pending' },
  { value: 'admin_reviewing',  label: 'Admin reviewing' },
  { value: 'tutor_contacted',  label: 'Tutor contacted' },
  { value: 'confirmed',        label: 'Confirmed' },
  { value: 'declined',         label: 'Declined' },
  { value: 'closed',           label: 'Closed' },
]
const connectionStatusFilterOptions = [
  { value: '', label: 'All statuses' },
  ...connectionStatusOptions,
]

let searchTimer = null
function debouncedLoad() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => load(), 350)
}

async function load(page = 1) {
  loading.value = true
  try {
    const { data } = await adminApi.getConnections({ ...filters, page, per_page: 10 })
    connections.value = data.data.data || data.data || []
    meta.value = data.data
  } finally {
    loading.value = false
  }
}

function clearFilters() {
  filters.search = ''
  filters.status = ''
  load()
}

onMounted(load)

async function toggleExpand(id) {
  if (expandedId.value === id) {
    expandedId.value = null
    detail.value = null
    return
  }
  expandedId.value  = id
  detail.value      = null
  detailLoading.value = true
  try {
    const { data } = await adminApi.getConnection(id)
    detail.value = data.data
    if (!(id in notesMap)) {
      notesMap[id] = data.data.admin_notes ?? ''
    }
  } finally {
    detailLoading.value = false
  }
}

async function saveNotes(id) {
  notesSaving[id] = true
  try {
    await adminApi.addConnectionNotes(id, { admin_notes: notesMap[id] })
    toast.success('Notes saved.')
  } catch {
    toast.error('Could not save notes.')
  } finally {
    notesSaving[id] = false
  }
}

function statusBadgeClass(status) {
  if (status === 'confirmed')  return 'bg-emerald-50 text-emerald-700'
  if (status === 'declined')   return 'bg-red-50 text-red-700'
  if (status === 'closed')     return 'bg-paper-100 text-paper-600'
  if (status === 'pending')    return 'bg-amber-50 text-amber-700'
  return 'bg-blue-50 text-blue-700'
}

function onStatusChange(conn, newStatus) {
  pendingChange.value = { conn, newStatus }
}

async function confirmStatusChange() {
  const { conn, newStatus } = pendingChange.value
  pendingChange.value = null
  try {
    await adminApi.updateConnectionStatus(conn.id, { status: newStatus })
    conn.status = newStatus
    if (detail.value?.id === conn.id) detail.value.status = newStatus
    toast.success('Status updated.')
  } catch {
    toast.error('Could not update status.')
  }
}

function cancelStatusChange() {
  pendingChange.value = null
}
</script>
