<template>
  <div>
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="font-display font-bold text-2xl text-navy-900">Support Tickets</h1>
        <p class="mt-1 text-sm font-body text-paper-500">Manage and respond to user support requests.</p>
      </div>
      <div v-if="counts" class="flex gap-3 text-sm font-display">
        <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 font-semibold">
          {{ counts.open }} open
        </span>
        <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-semibold">
          {{ counts.in_progress }} in progress
        </span>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-5">
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_160px_160px_160px_auto] lg:items-end">
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Search</span>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-paper-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input v-model="filters.search" @input="debouncedLoad" type="search"
              placeholder="Search ticket, subject, name…" class="input pl-9 text-sm" />
          </div>
        </div>
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Status</span>
          <DropSelect v-model="filters.status" :options="statusOptions" placeholder="All statuses"
            @update:modelValue="loadTickets()" />
        </div>
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Category</span>
          <DropSelect v-model="filters.category" :options="categoryOptions" placeholder="All categories"
            @update:modelValue="loadTickets()" />
        </div>
        <div>
          <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Priority</span>
          <DropSelect v-model="filters.priority" :options="priorityOptions" placeholder="All priorities"
            @update:modelValue="loadTickets()" />
        </div>
        <button v-if="hasFilters" @click="clearFilters"
          class="min-h-[44px] rounded-sm bg-red-600 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-red-700">
          Clear
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="w-8 h-8 border-2 border-navy-100 border-t-navy-700 rounded-full animate-spin"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!tickets.length" class="card text-center py-14">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-navy-50 flex items-center justify-center mb-4">
        <svg class="w-7 h-7 text-navy-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
        </svg>
      </div>
      <p class="font-display font-semibold text-navy-700">No tickets found</p>
      <p class="text-sm text-paper-400 font-body mt-1">No support tickets match the current filters.</p>
    </div>

    <!-- Ticket List -->
    <div v-else class="space-y-3">
      <RouterLink v-for="ticket in tickets" :key="ticket.id"
        :to="`/admin/tickets/${ticket.id}`"
        class="card flex flex-col sm:flex-row sm:items-center gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all duration-150 cursor-pointer no-underline">

        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2 mb-1">
            <span class="text-xs font-bold font-display text-paper-400">{{ ticket.ticket_number }}</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full border" :class="statusClass(ticket.status)">
              {{ statusLabel(ticket.status) }}
            </span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full border" :class="priorityClass(ticket.priority)">
              {{ ticket.priority }}
            </span>
            <span class="text-xs px-2 py-0.5 rounded-full bg-paper-100 text-paper-500 font-display font-semibold border border-paper-200">
              {{ categoryLabel(ticket.category) }}
            </span>
          </div>
          <p class="font-display font-semibold text-navy-900 truncate">{{ ticket.subject }}</p>
          <p class="text-xs text-paper-400 font-body mt-0.5">
            {{ ticket.user?.name }} · {{ ticket.user?.email }}
          </p>
        </div>

        <div class="text-xs text-paper-400 font-body shrink-0 text-right sm:text-left">
          <p>{{ formatDate(ticket.created_at) }}</p>
        </div>

        <svg class="w-4 h-4 text-paper-300 shrink-0 hidden sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
      </RouterLink>
    </div>

    <AdminPagination :meta="meta" @page="loadTickets" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { adminTicketApi } from '@/api/tickets.js'
import AdminPagination from '@/components/admin/AdminPagination.vue'
import DropSelect from '@/components/search/DropSelect.vue'

const tickets = ref([])
const counts  = ref(null)
const loading = ref(true)
const meta    = ref({ current_page: 1, last_page: 1, total: 0 })
const filters = reactive({ search: '', status: '', category: '', priority: '' })

const statusOptions   = [
  { value: 'open',        label: 'Open'        },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'resolved',    label: 'Resolved'    },
  { value: 'closed',      label: 'Closed'      },
]
const categoryOptions = [
  { value: 'account',   label: 'Account'   },
  { value: 'technical', label: 'Technical' },
  { value: 'tuition',   label: 'Tuition'   },
  { value: 'other',     label: 'Other'     },
]
const priorityOptions = [
  { value: 'low',    label: 'Low'    },
  { value: 'medium', label: 'Medium' },
  { value: 'high',   label: 'High'   },
]

const hasFilters = computed(() =>
  filters.search || filters.status || filters.category || filters.priority
)

function statusLabel(s) {
  return { open: 'Open', in_progress: 'In Progress', resolved: 'Resolved', closed: 'Closed' }[s] ?? s
}
function statusClass(s) {
  return {
    open:        'bg-amber-50  text-amber-700  border-amber-200',
    in_progress: 'bg-blue-50   text-blue-700   border-blue-200',
    resolved:    'bg-emerald-50 text-emerald-700 border-emerald-200',
    closed:      'bg-paper-100 text-paper-500  border-paper-200',
  }[s] ?? 'bg-paper-100 text-paper-500 border-paper-200'
}
function priorityClass(p) {
  return {
    high:   'bg-red-50   text-red-700   border-red-200',
    medium: 'bg-orange-50 text-orange-700 border-orange-200',
    low:    'bg-paper-50 text-paper-500  border-paper-200',
  }[p] ?? 'bg-paper-50 text-paper-400 border-paper-200'
}
function categoryLabel(c) {
  return { account: 'Account', technical: 'Technical', tuition: 'Tuition', other: 'Other' }[c] ?? c
}
function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : ''
}

let timer = null
function debouncedLoad() {
  clearTimeout(timer)
  timer = setTimeout(() => loadTickets(), 350)
}

function clearFilters() {
  Object.assign(filters, { search: '', status: '', category: '', priority: '' })
  loadTickets()
}

async function loadTickets(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 15, ...Object.fromEntries(Object.entries(filters).filter(([,v]) => v)) }
    const { data } = await adminTicketApi.getAll(params)
    tickets.value = data.data?.data ?? []
    meta.value    = data.data ?? {}
  } finally {
    loading.value = false
  }
}

async function loadCounts() {
  try {
    const { data } = await adminTicketApi.getCounts()
    counts.value = data.data
  } catch {}
}

onMounted(() => {
  loadTickets()
  loadCounts()
})
</script>
