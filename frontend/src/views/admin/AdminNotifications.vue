<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="font-display font-bold text-2xl text-navy-900">Shortlist Requests</h1>
      <button v-if="unread > 0" @click="markAll" :disabled="marking"
        class="btn-outline text-sm py-2 px-4">
        {{ marking ? 'Marking…' : 'Mark all as read' }}
      </button>
    </div>

    <div v-if="loading" class="text-paper-500 font-body text-sm">Loading…</div>

    <div v-else-if="!notifications.length" class="card text-center py-12">
      <p class="text-paper-400 font-body text-sm">No shortlist requests yet.</p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="n in notifications" :key="n.id"
        class="card flex items-start gap-4 transition-colors"
        :class="n.read_at ? 'opacity-70' : 'border-l-4 border-l-navy-500'">
        <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center shrink-0 mt-0.5">
          <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-body text-paper-700 leading-relaxed">{{ n.data.message }}</p>
          <div class="flex flex-wrap gap-4 mt-2 text-xs font-body text-paper-400">
            <span>Guardian: <span class="font-semibold text-navy-700">{{ n.data.guardian_name }}</span> ({{ n.data.guardian_id }})</span>
            <span>Tutor: <span class="font-semibold text-navy-700">{{ n.data.tutor_name }}</span> ({{ n.data.tutor_id }})</span>
          </div>
          <p class="text-xs text-paper-400 font-body mt-1">{{ new Date(n.created_at).toLocaleString() }}</p>
        </div>
        <div class="shrink-0 flex flex-col items-end gap-2">
          <span v-if="n.read_at"
            class="text-xs font-semibold px-2 py-0.5 rounded-pill bg-paper-100 text-paper-400">Read</span>
          <span v-else
            class="text-xs font-semibold px-2 py-0.5 rounded-pill bg-navy-50 text-navy-700">New</span>
          <button v-if="!n.read_at" @click="markOne(n)"
            class="text-xs text-navy-700 font-semibold hover:underline">
            Mark read
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'

const notifications = ref([])
const unread        = ref(0)
const loading       = ref(true)
const marking       = ref(false)

onMounted(async () => {
  await load()
})

async function load() {
  loading.value = true
  try {
    const { data } = await adminApi.getNotifications()
    notifications.value = data.data
    unread.value = data.unread
  } finally {
    loading.value = false
  }
}

async function markOne(notification) {
  await adminApi.markNotificationRead(notification.id)
  notification.read_at = new Date().toISOString()
  unread.value = Math.max(0, unread.value - 1)
}

async function markAll() {
  marking.value = true
  try {
    await adminApi.markAllNotificationsRead()
    notifications.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
    unread.value = 0
  } finally {
    marking.value = false
  }
}
</script>
