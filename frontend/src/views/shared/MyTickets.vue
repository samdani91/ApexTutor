<template>
  <div class="dashboard-page space-y-6">
    <!-- Header -->
    <div class="dashboard-card reveal">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="font-display text-xs font-bold uppercase text-gold-600">Help & Support</p>
          <h1 class="mt-1 font-display font-bold text-2xl text-navy-900 md:text-3xl">My Support Tickets</h1>
          <p class="mt-2 font-body text-sm text-paper-600">
            Create a ticket and our support team will get back to you.
          </p>
        </div>
        <button @click="showCreate = true"
          class="inline-flex items-center gap-2 rounded-sm bg-navy-800 px-4 py-2.5 text-sm font-semibold font-display text-white shadow-sm hover:bg-navy-900 transition-colors self-start sm:self-auto">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          New Ticket
        </button>
      </div>
    </div>

    <!-- Status filter tabs -->
    <div class="flex gap-1 border-b border-paper-200">
      <button v-for="tab in tabs" :key="tab.value" @click="setTab(tab.value)"
        class="px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors"
        :class="activeTab === tab.value
          ? 'bg-white border border-b-white border-paper-200 -mb-px text-navy-900'
          : 'text-paper-500 hover:text-navy-700'">
        {{ tab.label }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="w-8 h-8 border-2 border-navy-100 border-t-navy-700 rounded-full animate-spin"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!tickets.length" class="dashboard-card reveal flex flex-col items-center py-16 text-center">
      <div class="w-14 h-14 rounded-2xl bg-navy-50 flex items-center justify-center mb-4">
        <svg class="w-7 h-7 text-navy-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
        </svg>
      </div>
      <p class="font-display font-semibold text-navy-700">No tickets yet</p>
      <p class="text-sm text-paper-400 font-body mt-1">
        {{ activeTab === 'all' ? 'You haven\'t opened any support tickets yet.' : `No ${activeTab.replace('_', ' ')} tickets.` }}
      </p>
      <button @click="showCreate = true"
        class="mt-4 rounded-sm bg-navy-800 px-4 py-2 text-sm font-semibold font-display text-white hover:bg-navy-900 transition-colors">
        Open a Ticket
      </button>
    </div>

    <!-- Ticket list -->
    <div v-else class="space-y-3">
      <RouterLink v-for="ticket in tickets" :key="ticket.id"
        :to="ticketDetailPath(ticket.id)"
        class="dashboard-card reveal flex flex-col sm:flex-row sm:items-center gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all duration-150 cursor-pointer no-underline block">
        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2 mb-1">
            <span class="text-xs font-bold font-display text-paper-400">{{ ticket.ticket_number }}</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full border" :class="statusClass(ticket.status)">
              {{ statusLabel(ticket.status) }}
            </span>
            <span class="text-xs px-2 py-0.5 rounded-full bg-paper-100 text-paper-500 font-display font-semibold border border-paper-200">
              {{ categoryLabel(ticket.category) }}
            </span>
          </div>
          <p class="font-display font-semibold text-navy-900 truncate">{{ ticket.subject }}</p>
          <p class="text-xs text-paper-400 font-body mt-0.5">
            {{ formatDate(ticket.created_at) }}
            <span v-if="ticket.replies?.length" class="ml-2 text-navy-600 font-semibold">
              · {{ ticket.replies.length }} repl{{ ticket.replies.length === 1 ? 'y' : 'ies' }}
            </span>
          </p>
        </div>
        <svg class="w-4 h-4 text-paper-300 shrink-0 hidden sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
      </RouterLink>
    </div>

    <!-- Create ticket modal -->
    <Teleport to="body">
      <div v-if="showCreate" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4 pb-4 sm:p-6">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-bold text-navy-900 text-lg">New Support Ticket</h2>
            <button @click="showCreate = false; resetForm()"
              class="text-paper-400 hover:text-paper-700 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Category</label>
              <DropSelect v-model="form.category" :options="categoryOptions" placeholder="Select a category" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Subject</label>
              <input v-model="form.subject" type="text" placeholder="Brief description of your issue"
                class="input w-full text-sm" maxlength="200" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Message</label>
              <textarea v-model="form.description" rows="5" placeholder="Describe your issue in detail…"
                class="input w-full resize-none text-sm" maxlength="5000" />
              <p class="text-xs text-paper-400 font-body mt-1 text-right">{{ form.description.length }}/5000</p>
            </div>
          </div>

          <div class="flex gap-3 mt-5 justify-end">
            <button @click="showCreate = false; resetForm()"
              class="px-4 py-2 text-sm font-semibold font-display border border-paper-300 text-paper-600 hover:bg-paper-50 rounded-sm transition-colors">
              Cancel
            </button>
            <button @click="submitTicket" :disabled="!isFormValid || creating"
              class="px-5 py-2 text-sm font-semibold font-display bg-navy-800 text-white hover:bg-navy-900 rounded-sm transition-colors disabled:opacity-50">
              {{ creating ? 'Submitting…' : 'Submit Ticket' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { ticketApi } from '@/api/tickets.js'
import { toast } from 'vue-sonner'
import DropSelect from '@/components/search/DropSelect.vue'

const auth    = useAuthStore()
const router  = useRouter()
const tickets = ref([])
const loading = ref(true)
const showCreate = ref(false)
const creating   = ref(false)
const activeTab  = ref('all')
const meta       = ref({})

const form = reactive({ subject: '', description: '', category: '' })

const tabs = [
  { value: 'all',         label: 'All'         },
  { value: 'open',        label: 'Open'        },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'resolved',    label: 'Resolved'    },
  { value: 'closed',      label: 'Closed'      },
]

const categoryOptions = [
  { value: 'account',   label: 'Account Issue'     },
  { value: 'technical', label: 'Technical Problem' },
  { value: 'tuition',   label: 'Tuition Related'   },
  { value: 'other',     label: 'Other'             },
]

const isFormValid = computed(() =>
  form.category && form.subject.trim().length >= 5 && form.description.trim().length >= 10
)

function ticketDetailPath(id) {
  return auth.isTutor ? `/tutor/support/${id}` : `/guardian/support/${id}`
}

function setTab(val) {
  activeTab.value = val
  loadTickets()
}

function statusLabel(s) {
  return { open: 'Open', in_progress: 'In Progress', resolved: 'Resolved', closed: 'Closed' }[s] ?? s
}
function statusClass(s) {
  return {
    open:        'bg-amber-50   text-amber-700   border-amber-200',
    in_progress: 'bg-blue-50    text-blue-700    border-blue-200',
    resolved:    'bg-emerald-50 text-emerald-700 border-emerald-200',
    closed:      'bg-red-50     text-red-600     border-red-200',
  }[s] ?? ''
}
function categoryLabel(c) {
  return { account: 'Account', technical: 'Technical', tuition: 'Tuition', other: 'Other' }[c] ?? c
}
function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : ''
}

function resetForm() {
  Object.assign(form, { subject: '', description: '', category: '' })
}

async function loadTickets(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (activeTab.value !== 'all') params.status = activeTab.value
    const { data } = await ticketApi.getAll(params)
    tickets.value = data.data?.data ?? []
    meta.value    = data.data ?? {}
  } finally {
    loading.value = false
  }
}

async function submitTicket() {
  if (!isFormValid.value) return
  creating.value = true
  try {
    await ticketApi.create({ ...form })
    toast.success('Ticket submitted! We\'ll respond shortly.')
    showCreate.value = false
    resetForm()
    loadTickets()
  } catch {
    toast.error('Failed to submit ticket. Please try again.')
  } finally {
    creating.value = false
  }
}

onMounted(loadTickets)
</script>

<style scoped>
.dashboard-card {
  @apply rounded-md border border-paper-200 bg-white p-5 shadow-lg md:p-6;
}
</style>
