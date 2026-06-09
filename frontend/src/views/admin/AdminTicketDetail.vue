<template>
  <div>
    <!-- Back -->
    <RouterLink to="/admin/tickets"
      class="inline-flex items-center gap-1.5 text-sm font-semibold font-display text-paper-500 hover:text-navy-700 transition-colors mb-5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
      </svg>
      All Tickets
    </RouterLink>

    <div v-if="loading" class="flex items-center justify-center py-24">
      <div class="w-8 h-8 border-2 border-navy-100 border-t-navy-700 rounded-full animate-spin"></div>
    </div>

    <div v-else-if="!ticket" class="card text-center py-14 text-paper-500 font-body">
      Ticket not found.
    </div>

    <template v-else>
      <!-- Header -->
      <div class="card mb-5">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <span class="text-xs font-bold font-display text-paper-400 tracking-wide">{{ ticket.ticket_number }}</span>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-full border" :class="statusClass(ticket.status)">
                {{ statusLabel(ticket.status) }}
              </span>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-full border" :class="priorityClass(ticket.priority)">
                {{ ticket.priority }} priority
              </span>
              <span class="text-xs px-2 py-0.5 rounded-full bg-paper-100 text-paper-500 font-display font-semibold border border-paper-200">
                {{ categoryLabel(ticket.category) }}
              </span>
            </div>
            <h1 class="font-display font-bold text-xl text-navy-900">{{ ticket.subject }}</h1>
            <p class="text-sm text-paper-400 font-body mt-1">
              By <strong class="text-navy-700">{{ ticket.user?.name }}</strong>
              ({{ ticket.user?.email }}) ·
              {{ formatDate(ticket.created_at) }}
            </p>
          </div>

          <!-- Status actions -->
          <div class="flex flex-col gap-2 shrink-0">
            <div>
              <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Change Status</span>
              <DropSelect v-model="newStatus" :options="statusOptions" class="w-44" />
            </div>
            <div>
              <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Priority</span>
              <DropSelect v-model="newPriority" :options="priorityOptions" class="w-44" />
            </div>
            <button @click="updateStatus" :disabled="statusLoading"
              class="rounded-sm bg-navy-800 px-4 py-2 text-sm font-semibold font-display text-white hover:bg-navy-900 transition-colors disabled:opacity-50">
              {{ statusLoading ? 'Saving…' : 'Update' }}
            </button>
          </div>
        </div>

        <!-- Original description -->
        <div class="mt-4 pt-4 border-t border-paper-100">
          <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-2">Original Message</p>
          <p class="text-sm text-paper-700 font-body leading-relaxed whitespace-pre-wrap">{{ ticket.description }}</p>
        </div>
      </div>

      <!-- Conversation -->
      <div class="mb-5">
        <h2 class="font-display font-semibold text-navy-800 mb-3">Conversation</h2>

        <div v-if="!ticket.replies?.length" class="card text-center py-8 text-paper-400 font-body text-sm">
          No replies yet. Be the first to respond.
        </div>

        <div v-else class="space-y-3">
          <div v-for="reply in ticket.replies" :key="reply.id"
            class="card"
            :class="reply.is_internal ? 'border-amber-200 bg-amber-50' : ''">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold font-display"
                  :class="isAdmin(reply.user) ? 'bg-navy-800 text-white' : 'bg-gold-100 text-gold-700'">
                  {{ reply.user?.name?.charAt(0)?.toUpperCase() }}
                </div>
                <span class="text-sm font-semibold font-display text-navy-800">{{ reply.user?.name }}</span>
                <span v-if="isAdmin(reply.user)" class="text-xs px-1.5 py-0.5 rounded bg-navy-100 text-navy-700 font-display font-semibold">Support</span>
                <span v-if="reply.is_internal" class="text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-display font-semibold">Internal note</span>
              </div>
              <span class="text-xs text-paper-400 font-body">{{ formatDate(reply.created_at) }}</span>
            </div>
            <p class="text-sm text-paper-700 font-body leading-relaxed whitespace-pre-wrap">{{ reply.body }}</p>
          </div>
        </div>
      </div>

      <!-- Reply form -->
      <div class="card">
        <h2 class="font-display font-semibold text-navy-800 mb-3">Add Reply</h2>
        <textarea v-model="replyBody" rows="4" placeholder="Write your response…"
          class="input w-full resize-none text-sm mb-3" />
        <div class="flex flex-wrap items-center justify-between gap-3">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <div @click="isInternal = !isInternal"
              class="relative w-9 h-5 rounded-full transition-colors duration-200 cursor-pointer"
              :class="isInternal ? 'bg-amber-500' : 'bg-paper-300'">
              <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform duration-200"
                :class="isInternal ? 'translate-x-4' : ''"></div>
            </div>
            <span class="text-sm font-semibold font-display text-navy-700">Internal note</span>
            <span class="text-xs text-paper-400 font-body">(not visible to user)</span>
          </label>
          <button @click="submitReply" :disabled="!replyBody.trim() || replyLoading"
            class="rounded-sm px-5 py-2.5 text-sm font-semibold font-display text-white transition-colors disabled:opacity-50"
            :class="isInternal ? 'bg-amber-600 hover:bg-amber-700' : 'bg-navy-800 hover:bg-navy-900'">
            {{ replyLoading ? 'Sending…' : isInternal ? 'Add Note' : 'Send Reply' }}
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { adminTicketApi } from '@/api/tickets.js'
import { toast } from 'vue-sonner'
import DropSelect from '@/components/search/DropSelect.vue'

const route       = useRoute()
const ticket      = ref(null)
const loading     = ref(true)
const statusLoading = ref(false)
const replyLoading  = ref(false)
const replyBody   = ref('')
const isInternal  = ref(false)
const newStatus   = ref('')
const newPriority = ref('')

const statusOptions = [
  { value: 'open',        label: 'Open'        },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'resolved',    label: 'Resolved'    },
  { value: 'closed',      label: 'Closed'      },
]
const priorityOptions = [
  { value: 'low',    label: 'Low'    },
  { value: 'medium', label: 'Medium' },
  { value: 'high',   label: 'High'   },
]

function isAdmin(user) {
  return user?.role === 'super_admin'
}
function statusLabel(s) {
  return { open: 'Open', in_progress: 'In Progress', resolved: 'Resolved', closed: 'Closed' }[s] ?? s
}
function statusClass(s) {
  return {
    open:        'bg-amber-50   text-amber-700   border-amber-200',
    in_progress: 'bg-blue-50    text-blue-700    border-blue-200',
    resolved:    'bg-emerald-50 text-emerald-700 border-emerald-200',
    closed:      'bg-paper-100  text-paper-500   border-paper-200',
  }[s] ?? 'bg-paper-100 text-paper-500 border-paper-200'
}
function priorityClass(p) {
  return {
    high:   'bg-red-50    text-red-700    border-red-200',
    medium: 'bg-orange-50 text-orange-700 border-orange-200',
    low:    'bg-paper-50  text-paper-500  border-paper-200',
  }[p] ?? ''
}
function categoryLabel(c) {
  return { account: 'Account', technical: 'Technical', tuition: 'Tuition', other: 'Other' }[c] ?? c
}
function formatDate(d) {
  return d ? new Date(d).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''
}

async function loadTicket() {
  loading.value = true
  try {
    const { data } = await adminTicketApi.getOne(route.params.id)
    ticket.value    = data.data
    newStatus.value   = data.data.status
    newPriority.value = data.data.priority
  } finally {
    loading.value = false
  }
}

async function updateStatus() {
  statusLoading.value = true
  try {
    const { data } = await adminTicketApi.updateStatus(ticket.value.id, {
      status: newStatus.value, priority: newPriority.value,
    })
    ticket.value.status   = data.data.status
    ticket.value.priority = data.data.priority
    toast.success('Ticket updated.')
  } catch {
    toast.error('Failed to update ticket.')
  } finally {
    statusLoading.value = false
  }
}

async function submitReply() {
  if (!replyBody.value.trim()) return
  replyLoading.value = true
  try {
    const { data } = await adminTicketApi.reply(ticket.value.id, {
      body: replyBody.value, is_internal: isInternal.value,
    })
    if (!ticket.value.replies) ticket.value.replies = []
    ticket.value.replies.push(data.data)
    replyBody.value = ''
    if (!isInternal.value && ticket.value.status === 'open') {
      ticket.value.status = 'in_progress'
      newStatus.value = 'in_progress'
    }
    toast.success(isInternal.value ? 'Internal note added.' : 'Reply sent.')
  } catch {
    toast.error('Failed to send reply.')
  } finally {
    replyLoading.value = false
  }
}

onMounted(loadTicket)
</script>
