<template>
  <div>
    <div class="flex items-center gap-3 mb-6">
      <h1 class="font-display font-bold text-2xl text-navy-900">Pending Profile Photos</h1>
      <span v-if="items.length"
        class="text-xs font-bold font-display bg-amber-100 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-pill">
        {{ items.length }}
      </span>
    </div>

    <div v-if="loading" class="text-paper-500 font-body text-sm">Loading…</div>

    <div v-else-if="!items.length"
      class="card text-center py-14">
      <svg class="w-10 h-10 text-paper-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="font-display font-semibold text-navy-700">All clear</p>
      <p class="text-paper-400 text-sm font-body mt-1">No profile photo changes are awaiting review.</p>
    </div>

    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="item in items" :key="item.id" class="card overflow-hidden">
        <!-- Header -->
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-lg bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden">
            <img v-if="item.avatar_url" :src="item.avatar_url" class="w-full h-full object-cover" alt="" />
            <span v-else class="font-display font-bold text-xs text-navy-700">{{ initials(item.name) }}</span>
          </div>
          <div class="min-w-0">
            <p class="font-display font-semibold text-sm text-navy-900 truncate">{{ item.name }}</p>
            <p class="text-xs text-paper-400 font-body truncate">{{ item.email }}</p>
          </div>
          <span class="ml-auto text-xs font-semibold font-display px-2 py-0.5 rounded-pill border shrink-0"
            :class="item.role === 'tutor' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'">
            {{ item.role === 'tutor' ? 'Tutor' : 'Guardian' }}
          </span>
        </div>

        <p v-if="item.profile_id" class="text-xs font-semibold font-display text-navy-500 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill inline-block mb-4">
          {{ item.profile_id }}
        </p>

        <!-- Photo comparison -->
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div>
            <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1.5">Current</p>
            <div class="aspect-square rounded-sm overflow-hidden bg-paper-100 border border-paper-200">
              <img v-if="item.avatar_url" :src="item.avatar_url" class="w-full h-full object-cover" alt="Current photo" />
              <div v-else class="w-full h-full flex items-center justify-center">
                <span class="font-display font-bold text-2xl text-paper-300">{{ initials(item.name) }}</span>
              </div>
            </div>
          </div>
          <div>
            <p class="text-xs font-semibold font-display text-amber-500 uppercase tracking-wide mb-1.5">Proposed</p>
            <div class="aspect-square rounded-sm overflow-hidden bg-amber-50 border border-amber-200 cursor-pointer hover:opacity-90 transition-opacity"
              @click="previewItem = item">
              <img :src="item.pending_avatar_url" class="w-full h-full object-cover" alt="Pending photo" />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
          <button @click="openApprove(item)"
            class="flex-1 inline-flex items-center justify-center gap-1.5 bg-emerald-600 text-white text-xs font-semibold font-display py-2 rounded-md hover:bg-emerald-700 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
            Approve
          </button>
          <button @click="openReject(item)"
            class="flex-1 inline-flex items-center justify-center gap-1.5 bg-red-600 text-white text-xs font-semibold font-display py-2 rounded-md hover:bg-red-700 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Reject
          </button>
        </div>
      </div>
    </div>

    <!-- Full-size preview -->
    <Teleport to="body">
      <Transition name="dialog">
        <div v-if="previewItem" class="fixed inset-0 z-[240] flex items-center justify-center px-4"
          @click.self="previewItem = null">
          <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="previewItem = null" />
          <div class="relative w-full max-w-md bg-white rounded-sm shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-paper-200">
              <div class="min-w-0">
                <p class="font-display font-semibold text-sm text-navy-900 truncate">{{ previewItem.name }}</p>
                <p class="text-xs text-amber-600 font-body mt-0.5">Pending profile photo</p>
              </div>
              <button @click="previewItem = null"
                class="ml-3 shrink-0 w-8 h-8 flex items-center justify-center rounded-md text-paper-400 hover:bg-paper-100 hover:text-navy-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-paper-50">
              <img :src="previewItem.pending_avatar_url" :alt="previewItem.name"
                class="max-w-full max-h-[60vh] object-contain rounded-sm shadow-sm" />
            </div>
            <div class="px-5 py-3 border-t border-paper-200 flex gap-2 justify-end">
              <button @click="openApprove(previewItem); previewItem = null"
                class="inline-flex items-center gap-1.5 bg-emerald-600 text-white text-xs font-semibold font-display px-4 py-1.5 rounded-md hover:bg-emerald-700 transition-colors">
                Approve
              </button>
              <button @click="openReject(previewItem); previewItem = null"
                class="inline-flex items-center gap-1.5 bg-red-600 text-white text-xs font-semibold font-display px-4 py-1.5 rounded-md hover:bg-red-700 transition-colors">
                Reject
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Approve confirm -->
    <Teleport to="body">
      <Transition name="dialog">
        <div v-if="approveTarget" class="fixed inset-0 z-[200] flex items-center justify-center px-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="approveTarget = null" />
          <div class="relative bg-white rounded-sm shadow-xl w-full max-w-xs overflow-hidden">
            <div class="bg-emerald-600 px-6 pt-8 pb-7 text-center">
              <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <h3 class="font-display font-bold text-white text-xl">Approve photo?</h3>
              <p class="text-emerald-200 text-xs font-body mt-1">This will replace their current profile picture.</p>
            </div>
            <div class="px-6 py-5">
              <p class="text-paper-600 font-body text-sm text-center mb-5">
                Approve the new photo for <strong>{{ approveTarget?.name }}</strong>?
              </p>
              <div class="flex gap-3">
                <button @click="approveTarget = null" class="btn-outline flex-1 py-2.5 text-sm">Cancel</button>
                <button @click="confirmApprove" :disabled="actionLoading"
                  class="flex-1 inline-flex items-center justify-center bg-emerald-600 text-white font-semibold font-display py-2.5 rounded-md text-sm hover:bg-emerald-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                  {{ actionLoading ? 'Approving…' : 'Yes, Approve' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Reject modal -->
    <Teleport to="body">
      <Transition name="dialog">
        <div v-if="rejectTarget" class="fixed inset-0 z-[200] flex items-center justify-center px-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="rejectTarget = null" />
          <div class="relative bg-white rounded-sm shadow-xl w-full max-w-sm overflow-hidden">
            <div class="bg-red-600 px-6 pt-8 pb-7 text-center">
              <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </div>
              <h3 class="font-display font-bold text-white text-xl">Reject photo?</h3>
              <p class="text-red-200 text-xs font-body mt-1">The pending photo will be discarded.</p>
            </div>
            <div class="px-6 py-5">
              <div class="flex gap-3">
                <button @click="rejectTarget = null" class="btn-outline flex-1 py-2.5 text-sm">Cancel</button>
                <button @click="confirmReject" :disabled="actionLoading"
                  class="flex-1 inline-flex items-center justify-center bg-red-600 text-white font-semibold font-display py-2.5 rounded-md text-sm hover:bg-red-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                  {{ actionLoading ? 'Rejecting…' : 'Reject' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'
import { getInitials } from '@/utils/helpers.js'
import { toast } from 'vue-sonner'

const items         = ref([])
const loading       = ref(true)
const approveTarget = ref(null)
const rejectTarget  = ref(null)
const previewItem   = ref(null)
const actionLoading = ref(false)

onMounted(async () => {
  try {
    const { data } = await adminApi.getPendingAvatars()
    items.value = data.data || []
  } finally {
    loading.value = false
  }
})

function initials(name) {
  return getInitials(name)
}

function openApprove(item) { approveTarget.value = item }
function openReject(item)  { rejectTarget.value = item }

async function confirmApprove() {
  const item = approveTarget.value
  approveTarget.value = null
  actionLoading.value = true
  const toastId = `approve-avatar-${item.id}`
  toast.loading('Approving photo…', { id: toastId })
  try {
    await adminApi.approvePendingAvatar(item.id)
    items.value = items.value.filter(i => i.id !== item.id)
    toast.success('Photo approved.', {
      id: toastId,
      description: `${item.name}'s new photo is now live.`,
    })
  } catch {
    toast.error('Failed to approve photo.', { id: toastId, description: 'Please try again.' })
  } finally {
    actionLoading.value = false
  }
}

async function confirmReject() {
  const item = rejectTarget.value
  rejectTarget.value = null
  actionLoading.value = true
  const toastId = `reject-avatar-${item.id}`
  toast.loading('Rejecting photo…', { id: toastId })
  try {
    await adminApi.rejectPendingAvatar(item.id)
    items.value = items.value.filter(i => i.id !== item.id)
    toast.success('Photo rejected.', {
      id: toastId,
      description: `${item.name}'s pending photo has been discarded.`,
    })
  } catch {
    toast.error('Failed to reject photo.', { id: toastId, description: 'Please try again.' })
  } finally {
    actionLoading.value = false
  }
}
</script>

<style scoped>
.dialog-enter-active { transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.dialog-leave-active { transition: all 0.15s ease-in; }
.dialog-enter-from   { opacity: 0; transform: scale(0.88) translateY(8px); }
.dialog-leave-to     { opacity: 0; transform: scale(0.95) translateY(4px); }
</style>
