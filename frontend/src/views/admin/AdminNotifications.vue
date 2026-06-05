<template>
  <div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
      <div>
        <p class="text-xs font-bold font-display text-gold-600 uppercase tracking-wide mb-1">Admin Inbox</p>
        <h1 class="font-display font-bold text-3xl text-navy-900">Notifications</h1>
        <p class="text-sm text-paper-500 font-body mt-1">
          {{ unread > 0 ? `${unread} unread notification${unread === 1 ? '' : 's'}` : 'All notifications are read' }}
        </p>
      </div>
      <button v-if="unread > 0" @click="markAll" :disabled="marking"
        class="inline-flex items-center justify-center gap-2 rounded-sm bg-navy-800 px-4 py-2.5 text-sm font-semibold font-display text-white shadow-sm hover:bg-navy-900 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
        </svg>
        {{ marking ? 'Marking...' : 'Mark All As Read' }}
      </button>
    </div>

    <!-- Filter bar -->
    <div v-if="!loading" class="mb-5 rounded-lg border border-paper-200 bg-white px-4 py-3 shadow-sm">
      <div class="flex flex-wrap items-end gap-3">
        <div class="w-44">
          <p class="text-xs text-paper-400 font-body mb-1">Type</p>
          <DropSelect
            :modelValue="typeFilter"
            :options="typeOptions"
            placeholder="All types"
            @update:modelValue="val => { typeFilter = val; load(1) }"
          />
        </div>
        <div class="w-40">
          <p class="text-xs text-paper-400 font-body mb-1">Sort</p>
          <DropSelect
            :modelValue="sortOrder"
            :options="sortDropOptions"
            @update:modelValue="val => { sortOrder = val; load(1) }"
          />
        </div>
        <div>
          <button v-if="typeFilter || sortOrder !== 'newest'" @click="clearFilters"
            class="rounded-sm bg-red-600 px-3 py-2 text-xs font-semibold font-display text-white hover:bg-red-700 transition-colors">
            Clear
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-24">
      <div class="w-8 h-8 border-2 border-navy-100 border-t-navy-700 rounded-full animate-spin"></div>
    </div>

    <div v-else-if="!notifications.length" class="card flex flex-col items-center justify-center py-20 text-center">
      <div class="w-14 h-14 rounded-2xl bg-navy-50 flex items-center justify-center mb-4">
        <svg class="w-7 h-7 text-navy-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
        </svg>
      </div>
      <p class="font-display font-semibold text-navy-700 text-lg">No notifications</p>
      <p class="text-paper-400 text-sm font-body mt-1 max-w-xs">
        {{ typeFilter ? 'No notifications match this filter.' : 'Admin activity and alerts will appear here.' }}
      </p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="n in notifications" :key="n.id"
        class="group relative overflow-hidden rounded-lg border bg-white p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md sm:p-5"
        :class="n.read_at ? 'border-paper-200' : 'border-navy-200 ring-1 ring-navy-100'">
        <div v-if="!n.read_at" class="absolute inset-y-0 left-0 w-1" :class="typeMeta(n.data.type).accent"></div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
          <!-- Icon -->
          <div class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0 ring-1 ring-inset"
            :class="typeMeta(n.data.type).iconBg">
            <svg v-if="n.data.type === 'tutor_shortlisted'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
            </svg>
            <svg v-else-if="n.data.type === 'connection_requested'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
            </svg>
            <svg v-else-if="n.data.type === 'pending_profile_change'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
            </svg>
            <svg v-else-if="n.data.type === 'pending_video'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
            </svg>
            <svg v-else-if="n.data.type === 'review_submitted'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
            </svg>
          </div>

          <!-- Body -->
          <div class="flex-1 min-w-0">
            <div class="mb-2 flex flex-wrap items-center gap-2">
              <span class="rounded-full px-2.5 py-1 text-[11px] font-bold font-display uppercase tracking-wide"
                :class="typeMeta(n.data.type).badge">
                {{ typeMeta(n.data.type).label }}
              </span>
              <span class="text-xs text-paper-400 font-body">{{ timeAgo(n.created_at) }}</span>
            </div>

            <p class="text-sm font-body leading-relaxed"
              :class="n.read_at ? 'text-navy-800' : 'font-semibold text-navy-900'">
              {{ n.data.message }}
            </p>

            <!-- Shortlist detail -->
            <div v-if="n.data.type === 'tutor_shortlisted'" class="mt-3 grid gap-2 sm:grid-cols-2">
              <div class="rounded-md bg-paper-50 px-3 py-2">
                <p class="text-[11px] font-bold font-display text-paper-400 uppercase tracking-wide">Guardian</p>
                <p class="mt-0.5 truncate text-sm font-semibold font-body text-navy-800">{{ n.data.guardian_name }}</p>
              </div>
              <div class="rounded-md bg-paper-50 px-3 py-2">
                <p class="text-[11px] font-bold font-display text-paper-400 uppercase tracking-wide">Tutor</p>
                <p class="mt-0.5 truncate text-sm font-semibold font-body text-navy-800">{{ n.data.tutor_name }}</p>
              </div>
            </div>

            <!-- Connection requested detail -->
            <div v-else-if="n.data.type === 'connection_requested'" class="mt-3">
              <div class="grid gap-2 sm:grid-cols-2">
                <div class="rounded-md bg-paper-50 px-3 py-2">
                  <p class="text-[11px] font-bold font-display text-paper-400 uppercase tracking-wide">Guardian</p>
                  <p class="mt-0.5 truncate text-sm font-semibold font-body text-navy-800">{{ n.data.guardian_name }}</p>
                </div>
                <div class="rounded-md bg-paper-50 px-3 py-2">
                  <p class="text-[11px] font-bold font-display text-paper-400 uppercase tracking-wide">Tutor</p>
                  <p class="mt-0.5 truncate text-sm font-semibold font-body text-navy-800">{{ n.data.tutor_name }}</p>
                </div>
              </div>
              <p v-if="n.data.guardian_message" class="mt-2 text-xs text-paper-500 font-body italic border-l-2 border-paper-300 pl-2">
                "{{ n.data.guardian_message }}"
              </p>
              <RouterLink to="/admin/connections"
                class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold font-display text-emerald-700 hover:text-emerald-900 transition-colors">
                Review connection
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
              </RouterLink>
            </div>

            <!-- Pending profile change action -->
            <div v-else-if="n.data.type === 'pending_profile_change'" class="mt-3">
              <RouterLink to="/admin/pending-changes"
                class="inline-flex items-center gap-1.5 text-xs font-semibold font-display text-amber-700 hover:text-amber-900 transition-colors">
                Review pending changes
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
              </RouterLink>
            </div>

            <!-- Pending video action -->
            <div v-else-if="n.data.type === 'pending_video'" class="mt-3">
              <RouterLink :to="{ name: 'admin-tutor-detail', params: { tutorId: n.data.tutor_id } }"
                class="inline-flex items-center gap-1.5 text-xs font-semibold font-display text-violet-700 hover:text-violet-900 transition-colors">
                Review video on tutor profile
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
              </RouterLink>
            </div>

            <!-- Review submitted detail -->
            <div v-else-if="n.data.type === 'review_submitted'" class="mt-3 flex flex-wrap gap-2 items-center">
              <span class="text-xs font-body text-paper-500">
                {{ n.data.guardian_name }} → {{ n.data.tutor_name }}
              </span>
              <span class="text-xs font-semibold font-display text-amber-600">
                {{ n.data.rating }} ★
              </span>
              <RouterLink to="/admin/reviews"
                class="inline-flex items-center gap-1.5 text-xs font-semibold font-display text-purple-700 hover:text-purple-900 transition-colors">
                Moderate review
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
              </RouterLink>
            </div>

            <p class="text-xs text-paper-400 font-body mt-3">{{ formatDate(n.created_at) }}</p>
          </div>

          <!-- Read/Unread + action -->
          <div class="shrink-0 flex flex-row items-center justify-between gap-3 sm:flex-col sm:items-end">
            <span v-if="n.read_at"
              class="text-[11px] font-semibold font-display px-2.5 py-1 rounded-pill bg-paper-100 text-paper-500">Read</span>
            <span v-else
              class="text-[11px] font-bold font-display px-2.5 py-1 rounded-pill bg-navy-800 text-white">New</span>
            <button v-if="!n.read_at" @click="markOne(n)"
              class="inline-flex items-center justify-center rounded-sm border border-paper-200 bg-white px-3 py-1.5 text-xs font-semibold font-display text-navy-700 hover:bg-navy-50 transition-colors">
              Mark Read
            </button>
          </div>
        </div>
      </div>
    </div>

    <AdminPagination :meta="meta" @page="load" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { useNotificationStore } from '@/stores/notification.js'
import DropSelect from '@/components/search/DropSelect.vue'
import AdminPagination from '@/components/admin/AdminPagination.vue'

const notifStore = useNotificationStore()

const notifications = ref([])
const unread        = ref(0)
const loading       = ref(true)
const marking       = ref(false)
const sortOrder     = ref('newest')
const typeFilter    = ref('')
const meta          = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

const typeOptions = [
  { value: '',                       label: 'All' },
  { value: 'tutor_shortlisted',      label: 'Shortlist' },
  { value: 'connection_requested',   label: 'Connection' },
  { value: 'pending_profile_change', label: 'Profile Change' },
  { value: 'pending_video',          label: 'Video Review' },
  { value: 'review_submitted',       label: 'Review' },
]

const sortDropOptions = [
  { value: 'newest', label: 'Newest first' },
  { value: 'oldest', label: 'Oldest first' },
]

function clearFilters() {
  typeFilter.value = ''
  sortOrder.value  = 'newest'
  load(1)
}

onMounted(load)

async function load(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 10 }
    if (typeFilter.value) params.type = typeFilter.value
    if (sortOrder.value !== 'newest') params.sort = sortOrder.value
    const { data } = await adminApi.getNotifications(params)
    notifications.value = data.data
    meta.value = data.meta ?? { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 }
    unread.value = data.unread
    notifStore.setUnread(data.unread)
  } finally {
    loading.value = false
  }
}

async function markOne(notification) {
  try {
    await adminApi.markNotificationRead(notification.id)
    notification.read_at = new Date().toISOString()
    unread.value = Math.max(0, unread.value - 1)
    notifStore.decrement()
  } catch { /* silent */ }
}

async function markAll() {
  marking.value = true
  try {
    await adminApi.markAllNotificationsRead()
    notifications.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
    unread.value = 0
    notifStore.clearUnread()
  } finally {
    marking.value = false
  }
}

function typeMeta(type) {
  switch (type) {
    case 'tutor_shortlisted':
      return { label: 'Shortlist',      badge: 'bg-gold-50 text-gold-700',      iconBg: 'bg-gold-50 text-gold-700 ring-gold-100',       accent: 'bg-gold-400' }
    case 'connection_requested':
      return { label: 'Connection',     badge: 'bg-emerald-50 text-emerald-700', iconBg: 'bg-emerald-50 text-emerald-700 ring-emerald-100', accent: 'bg-emerald-500' }
    case 'pending_profile_change':
      return { label: 'Profile Change', badge: 'bg-amber-50 text-amber-700',    iconBg: 'bg-amber-50 text-amber-600 ring-amber-100',    accent: 'bg-amber-400' }
    case 'pending_video':
      return { label: 'Video Review',   badge: 'bg-violet-50 text-violet-700',  iconBg: 'bg-violet-50 text-violet-600 ring-violet-100', accent: 'bg-violet-500' }
    case 'review_submitted':
      return { label: 'Review',         badge: 'bg-purple-50 text-purple-700',  iconBg: 'bg-purple-50 text-purple-600 ring-purple-100', accent: 'bg-purple-500' }
    default:
      return { label: 'Notification',   badge: 'bg-navy-50 text-navy-700',      iconBg: 'bg-navy-50 text-navy-600 ring-navy-100',       accent: 'bg-navy-400' }
  }
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
    month: 'short', day: 'numeric', year: 'numeric',
    hour: 'numeric', minute: '2-digit',
  })
}
</script>
