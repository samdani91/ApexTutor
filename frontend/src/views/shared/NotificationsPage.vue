<template>
  <div @click="activeMenu = null">

    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
      <div>
        <p class="text-xs font-bold font-display text-gold-600 uppercase tracking-wide mb-1">Inbox</p>
        <h1 class="font-display font-bold text-3xl text-navy-900">Notifications</h1>
        <p class="text-sm text-paper-500 font-body mt-1">
          {{ notifStore.unreadCount > 0 ? `${notifStore.unreadCount} unread notification${notifStore.unreadCount === 1 ? '' : 's'}` : 'Everything is up to date' }}
        </p>
      </div>
      <button @click="markAll" :disabled="marking || notifStore.unreadCount === 0"
        class="inline-flex items-center justify-center gap-2 rounded-sm bg-navy-800 px-4 py-2.5 text-sm font-semibold font-display text-white shadow-sm hover:bg-navy-900 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
        </svg>
        {{ marking ? 'Marking...' : 'Mark All As Read' }}
      </button>
    </div>

    <!-- Filter / Sort bar -->
    <div class="mb-6 rounded-lg border border-paper-200 bg-white p-3 shadow-sm">
      <div class="grid gap-3 sm:grid-cols-[minmax(0,176px)_minmax(0,160px)_auto] sm:items-center">
      <div>
        <DropSelect v-model="activeType" :options="typeOpts" placeholder="All types"
          @update:modelValue="onFilterChange" />
      </div>
      <div>
        <DropSelect v-model="sortOrder" :options="sortOpts" placeholder="Newest first"
          @update:modelValue="onFilterChange" />
      </div>
      <button v-if="activeType !== '' || sortOrder !== 'newest'"
        @click="activeType = ''; sortOrder = 'newest'; onFilterChange()"
        class="inline-flex h-10 items-center justify-center rounded-sm bg-red-500 px-4 text-xs font-semibold font-display text-white hover:bg-red-600 transition-colors">
        Reset Filters
      </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-24">
      <div class="w-8 h-8 border-2 border-navy-100 border-t-navy-700 rounded-full animate-spin"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!items.length"
      class="card flex flex-col items-center justify-center py-20 text-center">
      <div class="w-14 h-14 rounded-2xl bg-navy-50 flex items-center justify-center mb-4">
        <svg class="w-7 h-7 text-navy-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
        </svg>
      </div>
      <p class="font-display font-semibold text-navy-700 text-lg">
        {{ activeType === '' ? 'All caught up' : 'Nothing here' }}
      </p>
      <p class="text-paper-400 text-sm font-body mt-1 max-w-xs">
        {{ activeType === ''
          ? 'You have no notifications yet.'
          : `No ${typeOpts.find(t => t.value === activeType)?.label.toLowerCase()} notifications.` }}
      </p>
    </div>

    <!-- List -->
    <template v-else>
      <div class="space-y-3">
        <div v-for="n in items" :key="n.id"
          class="group relative overflow-hidden rounded-lg border bg-white p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md sm:p-5"
          :class="n.read_at ? 'border-paper-200' : 'border-navy-200 ring-1 ring-navy-100'">
          <div v-if="!n.read_at" class="absolute inset-y-0 left-0 w-1 bg-gold-500"></div>
          <div class="flex flex-col gap-4 sm:flex-row sm:items-start">

          <!-- Icon -->
          <div class="shrink-0 w-11 h-11 rounded-lg flex items-center justify-center ring-1 ring-inset"
            :class="iconBg(n.data.type, n.data)">
            <svg v-if="n.data.type === 'pending_change_approved' || isConfirmedNotification(n)"
              class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg v-else-if="n.data.type === 'pending_change_rejected'"
              class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg v-else-if="n.data.type === 'tutor_shortlisted'"
              class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
            </svg>
            <svg v-else class="w-5 h-5 text-navy-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
            </svg>
          </div>

          <!-- Body -->
          <div class="flex-1 min-w-0">
            <div class="mb-2 flex flex-wrap items-center gap-2">
              <span class="rounded-full px-2.5 py-1 text-[11px] font-bold font-display uppercase tracking-wide"
                :class="typePill(n.data.type, n.data)">
                {{ typeLabel(n.data.type, n.data) }}
              </span>
              <span class="text-xs text-paper-400 font-body">{{ timeAgo(n.created_at) }}</span>
            </div>

            <!-- Approved -->
            <template v-if="n.data.type === 'pending_change_approved'">
              <p class="text-sm font-body leading-relaxed"
                :class="n.read_at ? 'text-navy-800' : 'font-semibold text-navy-900'">
                Your profile changes have been
                <span class="text-emerald-600 font-semibold">approved</span>
                and are now live.
              </p>
            </template>

            <!-- Rejected -->
            <template v-else-if="n.data.type === 'pending_change_rejected'">
              <p class="text-sm font-body leading-relaxed"
                :class="n.read_at ? 'text-navy-800' : 'font-semibold text-navy-900'">
                Your profile changes were
                <span class="text-red-600 font-semibold">not approved</span>
                by the admin.
              </p>
              <div v-if="n.data.note"
                class="mt-2 flex items-start gap-2 bg-red-50 border border-red-100 rounded-md px-3 py-2">
                <svg class="w-3.5 h-3.5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.05 3.378c.866-1.5 3.032-1.5 3.898 0L21.303 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <div>
                  <span class="text-[11px] font-bold font-display text-red-600 uppercase tracking-wide">Admin note</span>
                  <p class="text-xs text-red-700 font-body mt-0.5 leading-relaxed">{{ n.data.note }}</p>
                </div>
              </div>
              <div v-if="n.data.submitted?.length" class="mt-2 border border-paper-200 rounded-md overflow-hidden">
                <div class="px-3 py-1.5 bg-paper-50 border-b border-paper-200">
                  <span class="text-[11px] font-bold font-display text-paper-500 uppercase tracking-wide">Values you submitted</span>
                </div>
                <div v-for="(row, i) in n.data.submitted" :key="i"
                  class="flex items-center gap-3 px-3 py-1.5 text-xs"
                  :class="i % 2 === 0 ? 'bg-white' : 'bg-paper-50'">
                  <span class="text-paper-500 font-body w-32 shrink-0">{{ row.field }}</span>
                  <span class="text-navy-800 font-semibold font-body truncate">{{ row.value }}</span>
                </div>
              </div>
              <div v-if="n.data.sections?.length" class="mt-2 flex flex-wrap gap-1">
                <span class="text-[11px] text-paper-400 font-body self-center mr-1">Affected:</span>
                <span v-for="s in n.data.sections" :key="s"
                  class="text-[11px] font-semibold font-display bg-red-50 text-red-600 border border-red-100 px-2 py-0.5 rounded-pill">
                  {{ s }}
                </span>
              </div>
            </template>

            <!-- Shortlisted -->
            <template v-else-if="n.data.type === 'tutor_shortlisted'">
              <p class="text-sm font-body leading-relaxed"
                :class="n.read_at ? 'text-navy-800' : 'font-semibold text-navy-900'">
                <span class="text-violet-700 font-semibold">{{ n.data.guardian_name || 'A guardian' }}</span>
                has added you to their shortlist.
              </p>
            </template>

            <!-- Fallback -->
            <template v-else>
              <p class="text-sm font-body text-navy-800 leading-relaxed"
                :class="n.read_at ? '' : 'font-semibold'">
                {{ n.data.message }}
              </p>
            </template>

            <p class="text-xs text-paper-400 font-body mt-3">{{ formatDate(n.created_at) }}</p>
          </div>

          <!-- Right: badge + 3-dot -->
          <div class="shrink-0 flex flex-row items-center justify-between gap-3 sm:flex-col sm:items-end">
            <span v-if="!n.read_at"
              class="text-[11px] font-bold font-display bg-navy-800 text-white px-2.5 py-1 rounded-pill">
              New
            </span>
            <span v-else class="text-[11px] font-semibold font-display bg-paper-100 text-paper-500 px-2.5 py-1 rounded-pill">Read</span>

            <div class="relative" @click.stop>
              <button
                @click="activeMenu = activeMenu === n.id ? null : n.id"
                class="w-7 h-7 flex items-center justify-center rounded-md text-paper-400 hover:bg-paper-100 hover:text-navy-700 transition-colors"
                title="Options">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <circle cx="12" cy="5"  r="1.5"/>
                  <circle cx="12" cy="12" r="1.5"/>
                  <circle cx="12" cy="19" r="1.5"/>
                </svg>
              </button>
              <Transition name="drop">
                <div v-if="activeMenu === n.id"
                  class="absolute right-0 top-8 z-10 bg-white border border-paper-200 rounded-sm shadow-lg py-1 min-w-[150px]">
                  <button v-if="!n.read_at"
                    @click="markOne(n); activeMenu = null"
                    class="w-full text-left px-3 py-2 text-sm font-body text-navy-700 hover:bg-navy-50 transition-colors flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-navy-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                    Mark as read
                  </button>
                  <span v-else class="block px-3 py-2 text-sm font-body text-paper-400 italic">
                    Already read
                  </span>
                </div>
              </Transition>
            </div>
          </div>

          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1"
        class="flex items-center justify-center gap-1 mt-6 flex-wrap">
        <!-- Prev -->
        <button @click="goPage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="w-8 h-8 flex items-center justify-center rounded-md border border-paper-300 text-navy-700 hover:bg-navy-50 disabled:opacity-35 disabled:cursor-not-allowed transition-colors"
          aria-label="Previous page">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
          </svg>
        </button>

        <!-- Page numbers -->
        <template v-for="p in pageButtons" :key="p">
          <span v-if="p === '...'"
            class="w-8 h-8 flex items-center justify-center text-paper-400 text-sm font-body select-none">
            …
          </span>
          <button v-else @click="goPage(p)"
            class="w-8 h-8 flex items-center justify-center rounded-md text-sm font-semibold font-display border transition-colors"
            :class="p === pagination.current_page
              ? 'bg-navy-700 text-white border-navy-700'
              : 'border-paper-300 text-navy-700 hover:bg-navy-50'">
            {{ p }}
          </button>
        </template>

        <!-- Next -->
        <button @click="goPage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="w-8 h-8 flex items-center justify-center rounded-md border border-paper-300 text-navy-700 hover:bg-navy-50 disabled:opacity-35 disabled:cursor-not-allowed transition-colors"
          aria-label="Next page">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
          </svg>
        </button>
      </div>
    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { notificationApi } from '@/api/notifications.js'
import { useNotificationStore } from '@/stores/notification.js'
import DropSelect from '@/components/search/DropSelect.vue'

const notifStore = useNotificationStore()

const typeOpts = [
  { value: '',           label: 'All types'   },
  { value: 'approved',   label: 'Approved'    },
  { value: 'rejected',   label: 'Rejected'    },
  { value: 'shortlisted',label: 'Shortlisted' },
]
const sortOpts = [
  { value: 'newest', label: 'Newest first' },
  { value: 'oldest', label: 'Oldest first' },
]

const items      = ref([])
const pagination = ref({ current_page: 1, last_page: 1 })
const loading    = ref(true)
const marking    = ref(false)
const activeType = ref('')
const sortOrder  = ref('newest')
const activeMenu = ref(null)

// Same ellipsis logic as AdminUsersList
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

onMounted(() => load(1))

async function load(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (activeType.value) params.type = activeType.value
    if (sortOrder.value !== 'newest') params.sort = sortOrder.value

    const { data } = await notificationApi.getAll(params)
    items.value      = data.data
    pagination.value = data.meta
    notifStore.setUnread(data.unread)
  } finally {
    loading.value = false
  }
}

function onFilterChange() {
  load(1)
}

function goPage(page) {
  if (page < 1 || page > pagination.value.last_page) return
  load(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function markOne(n) {
  if (n.read_at) return
  await notificationApi.markRead(n.id)
  n.read_at = new Date().toISOString()
  notifStore.decrement()
}

async function markAll() {
  if (notifStore.unreadCount === 0) return
  marking.value = true
  try {
    await notificationApi.markAllRead()
    items.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
    notifStore.clearUnread()
  } finally {
    marking.value = false
  }
}

function iconBg(type, data = null) {
  if (type === 'pending_change_approved' || type === 'tuition_confirmed' || (type === 'connection_status_changed' && data?.status === 'confirmed')) return 'bg-emerald-50 text-emerald-600 ring-emerald-100'
  if (type === 'pending_change_rejected') return 'bg-red-50 text-red-600 ring-red-100'
  if (type === 'tutor_shortlisted')       return 'bg-gold-50 text-gold-700 ring-gold-100'
  return 'bg-navy-50 text-navy-600 ring-navy-100'
}

function typeLabel(type, data = null) {
  if (type === 'pending_change_approved') return 'Approved'
  if (type === 'tuition_confirmed' || (type === 'connection_status_changed' && data?.status === 'confirmed')) return 'Confirmed'
  if (type === 'pending_change_rejected') return 'Rejected'
  if (type === 'tutor_shortlisted') return 'Shortlisted'
  return 'Notification'
}

function typePill(type, data = null) {
  if (type === 'pending_change_approved' || type === 'tuition_confirmed' || (type === 'connection_status_changed' && data?.status === 'confirmed')) return 'bg-emerald-50 text-emerald-700'
  if (type === 'pending_change_rejected') return 'bg-red-50 text-red-700'
  if (type === 'tutor_shortlisted') return 'bg-gold-50 text-gold-700'
  return 'bg-navy-50 text-navy-700'
}

function isConfirmedNotification(n) {
  return n?.data?.type === 'tuition_confirmed'
    || (n?.data?.type === 'connection_status_changed' && n?.data?.status === 'confirmed')
}

function timeAgo(iso) {
  const diff = Math.floor((Date.now() - new Date(iso)) / 1000)
  if (diff < 60)    return 'just now'
  if (diff < 3600)  return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return `${Math.floor(diff / 86400)}d ago`
}

function formatDate(iso) {
  return new Date(iso).toLocaleString([], {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  })
}
</script>

<style scoped>
.drop-enter-active { transition: opacity 0.1s ease, transform 0.1s ease; }
.drop-leave-active { transition: opacity 0.08s ease, transform 0.08s ease; }
.drop-enter-from  { opacity: 0; transform: translateY(-4px); }
.drop-leave-to    { opacity: 0; transform: translateY(-4px); }
</style>
