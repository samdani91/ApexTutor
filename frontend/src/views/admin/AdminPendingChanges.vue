<template>
  <div>
    <div class="flex items-center gap-3 mb-6">
      <h1 class="font-display font-bold text-2xl text-navy-900">Pending Profile Changes</h1>
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
      <p class="text-paper-400 text-sm font-body mt-1">No profile changes are awaiting review.</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="item in items" :key="item.id" class="card overflow-hidden">

        <!-- Card header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-navy-100 flex items-center justify-center shrink-0">
              <span class="font-display font-bold text-sm text-navy-700">{{ initials(item.user?.name) }}</span>
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <p class="font-display font-semibold text-navy-900">{{ item.user?.name }}</p>
                <span class="text-xs font-semibold font-display text-navy-500 bg-navy-50 border border-navy-200 px-2 py-0 rounded-pill">
                  {{ item.tutor_id }}
                </span>
              </div>
              <p class="text-xs text-paper-400 font-body mt-0.5">
                {{ item.user?.email }} · Submitted {{ formatDate(item.submitted_at) }}
              </p>
            </div>
          </div>

          <div class="flex gap-2 shrink-0">
            <button @click="toggleExpand(item.id)"
              class="btn-outline text-sm py-1.5 px-3">
              {{ expanded.has(item.id) ? 'Hide Diff' : 'Review' }}
            </button>
            <button @click="openApprove(item)"
              class="btn-primary text-sm py-1.5 px-3">
              Approve
            </button>
            <button @click="openReject(item)"
              class="bg-red-600 text-white text-sm font-semibold font-display py-1.5 px-3 rounded-md hover:bg-red-700 transition-colors">
              Reject
            </button>
          </div>
        </div>

        <!-- Diff table (expandable) -->
        <Transition name="expand">
          <div v-if="expanded.has(item.id)" class="mt-4 pt-4 border-t border-paper-100">
            <div v-if="buildDiff(item).length">
              <table class="w-full text-sm">
                <thead>
                  <tr class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">
                    <th class="text-left pb-2 pr-4 w-1/4">Field</th>
                    <th class="text-left pb-2 pr-4 w-[37.5%]">Current value</th>
                    <th class="text-left pb-2 w-[37.5%]">Proposed change</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-paper-100">
                  <tr v-for="row in buildDiff(item)" :key="row.key">
                    <td class="py-2.5 pr-4 font-display font-semibold text-navy-700 text-xs align-top">
                      {{ row.label }}
                    </td>
                    <td class="py-2.5 pr-4 font-body text-paper-500 align-top text-xs leading-relaxed max-w-0">
                      <template v-if="row.oldDocuments">
                        <DocumentChangeList :documents="row.oldDocuments" muted />
                      </template>
                      <span v-else class="line-clamp-3 block">{{ row.old }}</span>
                    </td>
                    <td class="py-2.5 font-body align-top text-xs leading-relaxed max-w-0">
                      <template v-if="row.newDocuments">
                        <DocumentChangeList :documents="row.newDocuments" highlight />
                      </template>
                      <span v-else class="line-clamp-3 block font-semibold text-navy-800 bg-emerald-50 border border-emerald-200 rounded px-1.5 py-0.5 inline-block">
                        {{ row.new }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="text-xs text-paper-400 font-body italic">No comparable field changes detected.</p>
          </div>
        </Transition>
      </div>
    </div>

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
              <h3 class="font-display font-bold text-white text-xl">Approve changes?</h3>
              <p class="text-emerald-200 text-xs font-body mt-1">This will apply all pending edits to the live profile.</p>
            </div>
            <div class="px-6 py-5">
              <p class="text-paper-600 font-body text-sm text-center mb-5">
                Approve all pending changes for <strong>{{ approveTarget?.user?.name }}</strong>?
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
              <h3 class="font-display font-bold text-white text-xl">Reject changes?</h3>
              <p class="text-red-200 text-xs font-body mt-1">The tutor will be notified with your note.</p>
            </div>
            <div class="px-6 py-5">
              <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Rejection note <span class="text-paper-400 font-normal">(optional)</span></label>
              <textarea v-model="rejectNote" rows="3" placeholder="Explain what needs to be corrected…"
                class="input text-sm resize-none w-full mb-4" />
              <div class="flex gap-3">
                <button @click="rejectTarget = null; rejectNote = ''" class="btn-outline flex-1 py-2.5 text-sm">Cancel</button>
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
import { h, ref, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'
import { getInitials } from '@/utils/helpers.js'
import { toast } from 'vue-sonner'
import {
  buildDiff, display, documentLabel,
  changedLiveDocuments, changedPendingDocuments,
  applyEducationPreview, educationSummary,
  uniqueSubjectNames, norm,
  PREF_FIELDS, PERSONAL_FIELDS, EMERGENCY_FIELDS,
} from '@/composables/usePendingDiff.js'

const items         = ref([])
const loading       = ref(true)
const expanded      = ref(new Set())
const approveTarget = ref(null)
const rejectTarget  = ref(null)
const rejectNote    = ref('')
const actionLoading = ref(false)

const DocumentChangeList = {
  props: {
    documents: { type: Array, default: () => [] },
    muted: { type: Boolean, default: false },
    highlight: { type: Boolean, default: false },
  },
  setup(props) {
    return () => h('div', { class: 'space-y-1.5' }, props.documents.length
      ? props.documents.map(doc => h('div', {
          class: props.highlight
            ? 'rounded border border-emerald-200 bg-emerald-50 px-2 py-1.5'
            : 'rounded border border-paper-200 bg-paper-50 px-2 py-1.5',
        }, [
          h('p', { class: props.muted ? 'font-display text-xs font-semibold text-paper-600' : 'font-display text-xs font-semibold text-navy-800' }, doc.label),
          h('p', { class: 'mt-0.5 truncate font-body text-xs text-paper-500' }, doc.file_name || 'Uploaded document'),
          doc.file_url
            ? h('a', {
                href: doc.file_url,
                target: '_blank',
                class: 'mt-1 inline-flex font-display text-xs font-semibold text-navy-700 underline underline-offset-2',
              }, 'View document')
            : null,
        ]))
      : [h('span', { class: 'text-xs text-paper-400' }, '—')])
  },
}

onMounted(async () => {
  try {
    const { data } = await adminApi.getPendingChanges()
    items.value = data.data || []
  } finally {
    loading.value = false
  }
})

function initials(name) {
  return getInitials(name)
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function toggleExpand(id) {
  if (expanded.value.has(id)) {
    expanded.value.delete(id)
  } else {
    expanded.value.add(id)
  }
  expanded.value = new Set(expanded.value)
}

function openApprove(item) { approveTarget.value = item }
function openReject(item)  { rejectTarget.value = item; rejectNote.value = '' }

async function confirmApprove() {
  const id   = approveTarget.value.id
  const name = approveTarget.value.user?.name || 'this tutor'
  approveTarget.value = null
  actionLoading.value = true
  const toastId = `approve-pending-${id}`
  toast.loading('Approving profile changes…', {
    id: toastId,
    description: `Applying pending changes for ${name}.`,
  })
  try {
    await adminApi.approvePendingChange(id)
    items.value = items.value.filter(i => i.id !== id)
    toast.success('Changes approved and applied to live profile.', {
      id: toastId,
      description: `${name}'s profile is now updated.`,
    })
  } catch {
    toast.error('Failed to approve changes.', {
      id: toastId,
      description: 'Please try again.',
    })
  } finally {
    actionLoading.value = false
  }
}

async function confirmReject() {
  const item = rejectTarget.value
  const note = rejectNote.value
  rejectTarget.value = null
  rejectNote.value   = ''
  actionLoading.value = true
  const toastId = `reject-pending-${item.id}`
  toast.loading('Rejecting profile changes…', {
    id: toastId,
    description: `Saving rejection for ${item.user?.name || 'this tutor'}.`,
  })
  try {
    await adminApi.rejectPendingChange(item.id, { note })
    items.value = items.value.filter(i => i.id !== item.id)
    toast.success('Changes rejected.', {
      id: toastId,
      description: 'The tutor can review your note and submit again.',
    })
  } catch {
    toast.error('Failed to reject changes.', {
      id: toastId,
      description: 'Please try again.',
    })
  } finally {
    actionLoading.value = false
  }
}

// ─── All diff/display logic imported from @/composables/usePendingDiff.js ────
</script>

<style scoped>
.expand-enter-active { transition: all 0.2s ease-out; }
.expand-leave-active { transition: all 0.15s ease-in; }
.expand-enter-from   { opacity: 0; transform: translateY(-6px); }
.expand-leave-to     { opacity: 0; transform: translateY(-4px); }

.dialog-enter-active { transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.dialog-leave-active { transition: all 0.15s ease-in; }
.dialog-enter-from   { opacity: 0; transform: scale(0.88) translateY(8px); }
.dialog-leave-to     { opacity: 0; transform: scale(0.95) translateY(4px); }
</style>
