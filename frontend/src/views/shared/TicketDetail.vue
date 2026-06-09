<template>
  <div class="dashboard-page space-y-5">
    <!-- Back -->
    <RouterLink :to="backPath"
      class="inline-flex items-center gap-1.5 text-sm font-semibold font-display text-paper-500 hover:text-navy-700 transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
      </svg>
      My Tickets
    </RouterLink>

    <div v-if="loading" class="flex items-center justify-center py-24">
      <div class="w-8 h-8 border-2 border-navy-100 border-t-navy-700 rounded-full animate-spin"></div>
    </div>

    <div v-else-if="!ticket" class="dashboard-card text-center py-14 text-paper-500 font-body">
      Ticket not found.
    </div>

    <template v-else>
      <!-- Ticket info card -->
      <div class="dashboard-card reveal">
        <div class="flex flex-wrap items-center gap-2 mb-2">
          <span class="text-xs font-bold font-display text-paper-400 tracking-wide">{{ ticket.ticket_number }}</span>
          <span class="text-xs font-semibold px-2 py-0.5 rounded-full border" :class="statusClass(ticket.status)">
            {{ statusLabel(ticket.status) }}
          </span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-paper-100 text-paper-500 font-display font-semibold border border-paper-200">
            {{ categoryLabel(ticket.category) }}
          </span>
        </div>
        <h1 class="font-display font-bold text-xl text-navy-900">{{ ticket.subject }}</h1>
        <p class="text-xs text-paper-400 font-body mt-1">Opened {{ formatDate(ticket.created_at) }}</p>

        <div class="mt-4 pt-4 border-t border-paper-100">
          <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-2">Your message</p>
          <p class="text-sm text-paper-700 font-body leading-relaxed whitespace-pre-wrap">{{ ticket.description }}</p>
        </div>
      </div>

      <!-- Conversation -->
      <div>
        <h2 class="font-display font-semibold text-navy-800 text-base mb-3">
          Conversation
          <span v-if="publicReplies.length" class="text-xs font-normal text-paper-400 ml-1">({{ publicReplies.length }})</span>
        </h2>

        <div v-if="!publicReplies.length" class="dashboard-card text-center py-8 text-paper-400 font-body text-sm">
          No replies yet. Our support team will respond soon.
        </div>

        <div v-else class="space-y-3">
          <div v-for="reply in publicReplies" :key="reply.id"
            class="dashboard-card"
            :class="isAdminReply(reply) ? 'border-l-4 border-l-navy-600' : 'border-l-4 border-l-gold-400'">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold font-display"
                  :class="isAdminReply(reply) ? 'bg-navy-800 text-white' : 'bg-gold-100 text-gold-700'">
                  {{ reply.user?.name?.charAt(0)?.toUpperCase() }}
                </div>
                <span class="text-sm font-semibold font-display text-navy-800">
                  {{ isAdminReply(reply) ? 'Support Team' : 'You' }}
                </span>
              </div>
              <span class="text-xs text-paper-400 font-body">{{ formatDate(reply.created_at) }}</span>
            </div>
            <p class="text-sm text-paper-700 font-body leading-relaxed whitespace-pre-wrap">{{ reply.body }}</p>
          </div>
        </div>
      </div>

      <!-- Reply box (disabled if closed) -->
      <div class="dashboard-card reveal">
        <template v-if="ticket.status === 'closed'">
          <div class="flex items-center gap-3 py-2 text-paper-500">
            <svg class="w-5 h-5 text-paper-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            <p class="text-sm font-body">This ticket is closed. If you need further help, please open a new ticket.</p>
          </div>
        </template>
        <template v-else>
          <h2 class="font-display font-semibold text-navy-800 text-base mb-3">Add a Reply</h2>
          <textarea v-model="replyBody" rows="4" placeholder="Describe your question or update…"
            class="input w-full resize-none text-sm mb-3" />
          <div class="flex justify-end">
            <button @click="submitReply" :disabled="!replyBody.trim() || replyLoading"
              class="rounded-sm bg-navy-800 px-5 py-2.5 text-sm font-semibold font-display text-white hover:bg-navy-900 transition-colors disabled:opacity-50">
              {{ replyLoading ? 'Sending…' : 'Send Reply' }}
            </button>
          </div>
        </template>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { ticketApi } from '@/api/tickets.js'
import { toast } from 'vue-sonner'

const route  = useRoute()
const auth   = useAuthStore()
const ticket = ref(null)
const loading     = ref(true)
const replyLoading = ref(false)
const replyBody   = ref('')

const backPath = computed(() => auth.isTutor ? '/tutor/support' : '/guardian/support')

const publicReplies = computed(() =>
  (ticket.value?.replies ?? []).filter(r => !r.is_internal)
)

function isAdminReply(reply) {
  return reply.user?.role === 'super_admin'
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
  return d ? new Date(d).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''
}

async function loadTicket() {
  loading.value = true
  try {
    const { data } = await ticketApi.getOne(route.params.id)
    ticket.value = data.data
  } finally {
    loading.value = false
  }
}

async function submitReply() {
  if (!replyBody.value.trim()) return
  replyLoading.value = true
  try {
    const { data } = await ticketApi.reply(ticket.value.id, { body: replyBody.value })
    if (!ticket.value.replies) ticket.value.replies = []
    ticket.value.replies.push(data.data)
    replyBody.value = ''
    toast.success('Reply sent.')
  } catch (err) {
    const msg = err?.response?.data?.message
    toast.error(msg ?? 'Failed to send reply.')
  } finally {
    replyLoading.value = false
  }
}

onMounted(loadTicket)
</script>

<style scoped>
.dashboard-card {
  @apply rounded-md border border-paper-200 bg-white p-5 shadow-lg md:p-6;
}
</style>
