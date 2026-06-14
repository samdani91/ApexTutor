<template>
  <div>
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="font-display font-bold text-2xl text-navy-900">Platform Feedback</h1>
        <p class="mt-1 text-sm font-body text-paper-500">Approve feedback to show it on the landing page testimonials.</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-5 border-b border-paper-200">
      <button v-for="tab in tabs" :key="tab.value" @click="switchTab(tab.value)"
        class="px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors"
        :class="activeTab === tab.value
          ? 'bg-white border border-b-white border-paper-200 -mb-px text-navy-900'
          : 'text-paper-500 hover:text-navy-700'">
        {{ tab.label }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card py-16 text-center text-paper-400 font-body text-sm">Loading…</div>

    <!-- Empty -->
    <div v-else-if="!items.length" class="card py-16 text-center text-paper-400 font-body text-sm">
      No feedback in this category.
    </div>

    <!-- List -->
    <div v-else class="space-y-3">
      <div v-for="item in items" :key="item.id"
        class="card flex flex-col sm:flex-row sm:items-start gap-4">
        <div class="flex items-center gap-3 shrink-0">
          <div class="h-10 w-10 rounded-full bg-navy-100 flex items-center justify-center overflow-hidden shrink-0">
            <img v-if="item.user?.avatar_url" :src="item.user.avatar_url" class="h-full w-full object-cover" />
            <span v-else class="font-display font-bold text-sm text-navy-700">
              {{ (item.user?.name || '?').charAt(0).toUpperCase() }}
            </span>
          </div>
          <div>
            <p class="font-display font-semibold text-sm text-navy-900">{{ item.user?.name }}</p>
            <p class="text-xs text-paper-500 font-body capitalize">{{ item.user?.role }}</p>
          </div>
        </div>

        <div class="flex-1 min-w-0">
          <p class="text-xs text-paper-400 font-body mb-1">Will appear as: <span class="font-semibold text-navy-700">{{ item.display_label }}</span></p>
          <p class="text-sm font-body text-navy-800 italic">"{{ item.quote }}"</p>
          <p class="text-xs text-paper-400 font-body mt-1.5">{{ formatDate(item.created_at) }}</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border"
            :class="statusClass(item.moderation_status)">
            {{ item.moderation_status }}
          </span>
          <button v-if="item.moderation_status !== 'approved'" @click="approve(item)"
            :disabled="!!acting[item.id]"
            class="rounded-sm bg-emerald-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-emerald-700 transition-colors disabled:opacity-50">
            Approve
          </button>
          <button v-if="item.moderation_status !== 'rejected'" @click="reject(item)"
            :disabled="!!acting[item.id]"
            class="rounded-sm bg-red-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-red-700 transition-colors disabled:opacity-50">
            Reject
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { feedbackApi } from '@/api/feedback.js'
import { toast } from 'vue-sonner'

const tabs = [
  { value: 'pending',  label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
  { value: 'all',      label: 'All' },
]

const activeTab = ref('pending')
const items     = ref([])
const loading   = ref(false)
const acting    = ref({})

async function load() {
  loading.value = true
  try {
    const { data } = await feedbackApi.adminList(activeTab.value)
    items.value = data.data?.data || data.data || []
  } finally {
    loading.value = false
  }
}

function switchTab(tab) {
  activeTab.value = tab
  load()
}

async function approve(item) {
  acting.value[item.id] = true
  try {
    await feedbackApi.adminApprove(item.id)
    item.moderation_status = 'approved'
    toast.success('Feedback approved and added to landing page.')
    if (activeTab.value === 'pending' || activeTab.value === 'rejected') load()
  } catch { toast.error('Failed to approve.') }
  finally { delete acting.value[item.id] }
}

async function reject(item) {
  acting.value[item.id] = true
  try {
    await feedbackApi.adminReject(item.id)
    item.moderation_status = 'rejected'
    toast.success('Feedback rejected.')
    if (activeTab.value === 'pending' || activeTab.value === 'approved') load()
  } catch { toast.error('Failed to reject.') }
  finally { delete acting.value[item.id] }
}

function statusClass(status) {
  if (status === 'approved') return 'bg-emerald-50 text-emerald-700 border-emerald-200'
  if (status === 'rejected') return 'bg-red-50 text-red-700 border-red-200'
  return 'bg-gold-50 text-gold-700 border-gold-200'
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(load)
</script>
