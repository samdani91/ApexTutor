<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="font-display font-bold text-2xl text-navy-900">Users</h1>
      <RouterLink to="/admin/admins/create"
        class="text-sm font-semibold font-display px-4 py-2 rounded-md bg-navy-700 text-white hover:bg-navy-900 transition-colors">
        + Create admin
      </RouterLink>
    </div>

    <!-- Search + tabs + filters -->
    <div class="card mb-5 space-y-3">
      <!-- Row 1: search + tabs -->
      <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
        <div class="relative flex-1 w-full">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-paper-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
          </svg>
          <input v-model="searchInput" @input="onSearch" type="search" placeholder="Search by name or email…"
            class="input pl-9 w-full" />
        </div>
        <div class="flex bg-paper-100 rounded-lg p-1 gap-1 shrink-0 w-full sm:w-auto">
          <button v-for="tab in tabs" :key="tab.value" @click="switchTab(tab.value)"
            class="flex-1 sm:flex-none px-3 sm:px-4 py-1.5 rounded-md text-sm font-semibold font-display transition-colors"
            :class="activeTab === tab.value ? 'bg-white text-navy-900 shadow-sm' : 'text-paper-500 hover:text-navy-700'">
            {{ tab.label }}
          </button>
        </div>
      </div>

      <!-- Row 2: filters -->
      <div class="pt-2 border-t border-paper-100">
        <!-- Dropdowns row — grid on mobile, flex on desktop with clear button pushed to the right -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap sm:items-end gap-2">
          <!-- Status filter (tutors) -->
          <div v-if="activeTab === 'tutors'" class="flex flex-col gap-1">
            <span class="text-xs font-semibold font-display text-navy-700">Status</span>
            <DropSelect v-model="statusFilter" :options="tutorStatusOptions" placeholder="All"
              @update:modelValue="load()" />
          </div>

          <!-- Sort by ID -->
          <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold font-display text-navy-700">Sort by</span>
            <DropSelect v-model="sortOrder" :options="sortOptions" placeholder="Newest first"
              @update:modelValue="load()" />
          </div>

          <!-- Clear filters — inline on desktop (ml-auto pushes to far right), hidden here on mobile -->
          <button v-if="statusFilter || sortOrder !== 'id_desc'"
            @click="clearFilters"
            class="hidden sm:inline-flex sm:ml-auto text-xs font-semibold font-display text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 px-4 py-1.5 rounded-sm transition-colors self-end">
            Clear Filters
          </button>
        </div>

        <!-- Clear filters — mobile only, full width below the grid -->
        <button v-if="statusFilter || sortOrder !== 'id_desc'"
          @click="clearFilters"
          class="sm:hidden mt-3 w-full text-xs font-semibold font-display text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 px-4 py-1.5 rounded-sm transition-colors">
          Clear Filters
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-paper-500 font-body text-sm py-8 text-center">Loading…</div>

    <div v-else-if="!rows.length" class="card text-center py-12 text-paper-500 font-body">
      No users found.
    </div>

    <template v-else>

      <!-- ═══ TUTORS / GUARDIANS ═══ -->
      <template v-if="activeTab !== 'admins'">

        <!-- Mobile cards -->
        <div class="md:hidden space-y-3">
          <div v-for="row in rows" :key="row.id"
            class="card flex items-center gap-3">
            <!-- Avatar -->
            <div class="w-10 h-10 rounded-lg bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden">
              <img v-if="userOf(row)?.avatar_url" :src="userOf(row).avatar_url" class="w-full h-full object-cover" />
              <span v-else class="font-display font-bold text-navy-700 text-sm">{{ initials(userOf(row)?.name) }}</span>
            </div>
            <!-- Info -->
            <div class="flex-1 min-w-0">
              <p class="font-display font-semibold text-navy-900 text-sm truncate">{{ userOf(row)?.name }}</p>
              <p class="text-xs text-paper-400 truncate">{{ userOf(row)?.email }}</p>
              <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                <span v-if="row.tutor_id || row.guardian_id"
                  class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-1.5 py-0.5 rounded-pill">
                  {{ row.tutor_id || row.guardian_id }}
                </span>
                <template v-if="activeTab === 'tutors'">
                  <span class="text-xs font-semibold px-1.5 py-0.5 rounded-pill capitalize"
                    :class="verificationClass(row.verification_status)">
                    {{ row.verification_status }}
                  </span>
                </template>
                <template v-else>
                  <span class="text-xs font-semibold px-1.5 py-0.5 rounded-pill capitalize bg-blue-50 text-blue-700">
                    {{ row.account_type || 'guardian' }}
                  </span>
                </template>
              </div>
            </div>
            <!-- Action -->
            <RouterLink :to="detailRoute(row)"
              class="shrink-0 text-xs font-semibold font-display bg-navy-700 text-white px-3 py-1.5 rounded-md hover:bg-navy-800 transition-colors">
              View
            </RouterLink>
          </div>
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block card overflow-x-auto">
          <table class="w-full text-sm font-body">
            <thead>
              <tr class="border-b border-paper-200 text-left">
                <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">User</th>
                <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">ID</th>
                <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Phone</th>
                <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Status</th>
                <th class="pb-3 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in rows" :key="row.id" class="border-b border-paper-100 last:border-0 hover:bg-navy-50/40 transition-colors">
                <td class="py-3 pr-6">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden">
                      <img v-if="userOf(row)?.avatar_url" :src="userOf(row).avatar_url" class="w-full h-full object-cover" />
                      <span v-else class="font-display font-bold text-navy-700 text-sm">{{ initials(userOf(row)?.name) }}</span>
                    </div>
                    <div class="min-w-0">
                      <p class="font-display font-semibold text-navy-900 truncate">{{ userOf(row)?.name }}</p>
                      <p class="text-xs text-paper-400 truncate">{{ userOf(row)?.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-3 pr-6">
                  <span class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill whitespace-nowrap">
                    {{ row.tutor_id || row.guardian_id || '—' }}
                  </span>
                </td>
                <td class="py-3 pr-6 text-paper-500 whitespace-nowrap">{{ userOf(row)?.phone || '—' }}</td>
                <td class="py-3 pr-6">
                  <template v-if="activeTab === 'tutors'">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize whitespace-nowrap"
                      :class="verificationClass(row.verification_status)">
                      {{ row.verification_status }}
                    </span>
                  </template>
                  <template v-else>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize bg-blue-50 text-blue-700 whitespace-nowrap">
                      {{ row.account_type || 'guardian' }}
                    </span>
                  </template>
                </td>
                <td class="py-3 text-right">
                  <RouterLink :to="detailRoute(row)"
                    class="inline-block text-xs font-semibold font-display bg-navy-700 text-white px-3 py-1.5 rounded-md hover:bg-navy-800 transition-colors whitespace-nowrap">
                    View
                  </RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

      <!-- ═══ ADMINS ═══ -->
      <template v-else>

        <!-- Mobile cards -->
        <div class="md:hidden space-y-3">
          <div v-for="row in rows" :key="row.id"
            class="card flex items-center gap-3">
            <!-- Avatar -->
            <div class="w-10 h-10 rounded-lg bg-navy-700 flex items-center justify-center shrink-0 overflow-hidden">
              <img v-if="row.avatar_url" :src="row.avatar_url" class="w-full h-full object-cover" />
              <span v-else class="font-display font-bold text-white text-sm">{{ initials(row.name) }}</span>
            </div>
            <!-- Info -->
            <div class="flex-1 min-w-0">
              <p class="font-display font-semibold text-navy-900 text-sm truncate">{{ row.name }}</p>
              <p class="text-xs text-paper-400 truncate">{{ row.email }}</p>
              <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                <span class="text-xs font-semibold px-1.5 py-0.5 rounded-pill capitalize whitespace-nowrap bg-navy-50 text-navy-700 border border-navy-200">
                  Admin
                </span>
                <span class="text-xs font-semibold px-1.5 py-0.5 rounded-pill whitespace-nowrap"
                  :class="row.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                  {{ row.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
            <!-- Action -->
            <RouterLink :to="{ name: 'admin-user-detail', params: { id: row.id } }"
              class="shrink-0 text-xs font-semibold font-display bg-navy-700 text-white px-3 py-1.5 rounded-md hover:bg-navy-800 transition-colors">
              View
            </RouterLink>
          </div>
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block card overflow-x-auto">
          <table class="w-full text-sm font-body">
            <thead>
              <tr class="border-b border-paper-200 text-left">
                <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Admin</th>
                <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Phone</th>
                <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Role</th>
                <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Status</th>
                <th class="pb-3 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in rows" :key="row.id" class="border-b border-paper-100 last:border-0 hover:bg-navy-50/40 transition-colors">
                <td class="py-3 pr-6">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-navy-700 flex items-center justify-center shrink-0 overflow-hidden">
                      <img v-if="row.avatar_url" :src="row.avatar_url" class="w-full h-full object-cover" />
                      <span v-else class="font-display font-bold text-white text-sm">{{ initials(row.name) }}</span>
                    </div>
                    <div class="min-w-0">
                      <p class="font-display font-semibold text-navy-900 truncate">{{ row.name }}</p>
                      <p class="text-xs text-paper-400 truncate">{{ row.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-3 pr-6 text-paper-500 whitespace-nowrap">{{ row.phone || '—' }}</td>
                <td class="py-3 pr-6">
                  <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize whitespace-nowrap bg-navy-50 text-navy-700 border border-navy-200">
                    Admin
                  </span>
                </td>
                <td class="py-3 pr-6">
                  <span class="text-xs font-semibold px-2 py-0.5 rounded-pill whitespace-nowrap"
                    :class="row.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                    {{ row.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="py-3 text-right">
                  <RouterLink :to="{ name: 'admin-user-detail', params: { id: row.id } }"
                    class="inline-block text-xs font-semibold font-display bg-navy-700 text-white px-3 py-1.5 rounded-md hover:bg-navy-800 transition-colors whitespace-nowrap">
                    View
                  </RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

    </template>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-1 mt-6 flex-wrap">
      <!-- Prev arrow -->
      <button @click="goPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
        class="w-8 h-8 flex items-center justify-center rounded-md border border-paper-300 text-navy-700 hover:bg-navy-50 disabled:opacity-35 disabled:cursor-not-allowed transition-colors"
        aria-label="Previous page">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
      </button>

      <!-- Page number buttons -->
      <template v-for="page in pageButtons" :key="page">
        <span v-if="page === '...'"
          class="w-8 h-8 flex items-center justify-center text-paper-400 text-sm font-body select-none">
          …
        </span>
        <button v-else @click="goPage(page)"
          class="w-8 h-8 flex items-center justify-center rounded-md text-sm font-semibold font-display border transition-colors"
          :class="page === pagination.current_page
            ? 'bg-navy-700 text-white border-navy-700'
            : 'border-paper-300 text-navy-700 hover:bg-navy-50'">
          {{ page }}
        </button>
      </template>

      <!-- Next arrow -->
      <button @click="goPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
        class="w-8 h-8 flex items-center justify-center rounded-md border border-paper-300 text-navy-700 hover:bg-navy-50 disabled:opacity-35 disabled:cursor-not-allowed transition-colors"
        aria-label="Next page">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { getInitials } from '@/utils/helpers.js'

const tabs = [
  { value: 'tutors',    label: 'Tutors'    },
  { value: 'guardians', label: 'Guardians' },
  { value: 'admins',    label: 'Admins'    },
]
const tutorStatusOptions = [
  { value: '', label: 'All' },
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
]
const sortOptions = [
  { value: 'id_desc', label: 'Newest first' },
  { value: 'id_asc', label: 'Oldest first' },
]

const activeTab   = ref('tutors')
const searchInput = ref('')
const statusFilter = ref('')
const sortOrder    = ref('id_desc')
const rows        = ref([])
const loading     = ref(true)
const pagination  = ref({ current_page: 1, last_page: 1 })
let searchTimeout = null

// Builds page number list with ellipsis: [1, 2, '...', 8, 9, 10]
const pageButtons = computed(() => {
  const total   = pagination.value.last_page
  const current = pagination.value.current_page
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = []
  const addRange = (from, to) => { for (let i = from; i <= to; i++) pages.push(i) }
  pages.push(1)
  if (current > 3) pages.push('...')
  addRange(Math.max(2, current - 1), Math.min(total - 1, current + 1))
  if (current < total - 2) pages.push('...')
  pages.push(total)
  return pages
})

function userOf(row) { return row.user ?? null }
function initials(name) { return getInitials(name) }

function verificationClass(status) {
  if (status === 'approved') return 'bg-emerald-50 text-emerald-700'
  if (status === 'pending')  return 'bg-amber-50 text-amber-700'
  if (status === 'rejected') return 'bg-red-50 text-red-700'
  return 'bg-blue-50 text-blue-700'
}

function detailRoute(row) {
  if (activeTab.value === 'tutors')  return { name: 'admin-tutor-detail',    params: { id: row.id } }
  if (activeTab.value === 'admins')  return { name: 'admin-user-detail',     params: { id: row.id } }
  return { name: 'admin-guardian-detail', params: { id: row.id } }
}

function switchTab(tab) {
  activeTab.value = tab
  statusFilter.value = ''
  load()
}

function clearFilters() {
  statusFilter.value = ''
  sortOrder.value    = 'id_desc'
  load()
}

async function load(page = 1) {
  loading.value = true
  try {
    const params = {
      page,
      per_page: 10,
      search: searchInput.value || undefined,
      sort:   sortOrder.value !== 'id_desc' ? sortOrder.value : undefined,
    }
    if (activeTab.value === 'tutors' && statusFilter.value)
      params.verification_status = statusFilter.value
    if (activeTab.value === 'admins' && statusFilter.value)
      params.role = statusFilter.value

    let res
    if (activeTab.value === 'tutors')         res = await adminApi.getTutors(params)
    else if (activeTab.value === 'guardians') res = await adminApi.getGuardians(params)
    else                                      res = await adminApi.getAdmins(params)
    rows.value       = res.data.data.data || res.data.data || []
    pagination.value = res.data.data
  } finally {
    loading.value = false
  }
}

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => load(), 350)
}

function goPage(page) {
  if (page < 1 || page > pagination.value.last_page) return
  load(page)
}

onMounted(() => load())
</script>
