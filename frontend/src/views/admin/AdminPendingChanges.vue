<template>
  <div>
    <div class="flex items-center gap-3 mb-6">
      <h1 class="font-display font-bold text-2xl text-navy-900">Pending Profile Changes</h1>
      <span v-if="allItems.length"
        class="text-xs font-bold font-display bg-amber-100 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-pill">
        {{ allItems.length }}
      </span>
    </div>

    <div v-if="loading" class="text-paper-500 font-body text-sm">Loading…</div>

    <template v-else>
      <div v-if="allItems.length" class="space-y-4">
        <div v-for="item in pagedItems" :key="item._key" class="card overflow-hidden">

          <!-- Card header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-10 h-10 rounded-xl bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden">
                <img v-if="item.live?.avatar_url" :src="item.live.avatar_url" class="w-full h-full object-cover" alt="" />
                <span v-else class="font-display font-bold text-sm text-navy-700">{{ initials(item.user?.name) }}</span>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="font-display font-semibold text-navy-900">{{ item.user?.name }}</p>
                  <span v-if="item.profile_id"
                    class="text-xs font-semibold font-display text-navy-500 bg-navy-50 border border-navy-200 px-2 py-0 rounded-pill">
                    {{ item.profile_id }}
                  </span>
                  <span class="text-xs font-semibold font-display px-2 py-0 rounded-pill border"
                    :class="item._type === 'avatar'
                      ? (item.user?.role === 'tutor' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200')
                      : 'bg-amber-50 text-amber-700 border-amber-200'">
                    {{ item._type === 'avatar' ? (item.user?.role === 'tutor' ? 'Tutor · Photo' : 'Guardian · Photo') : 'Profile changes' }}
                  </span>
                </div>
                <p class="text-xs text-paper-400 font-body mt-0.5">
                  {{ item.user?.email }}<template v-if="item.submitted_at"> · Submitted {{ formatDate(item.submitted_at) }}</template>
                </p>
              </div>
            </div>

            <div class="flex gap-2 shrink-0">
              <button @click="toggleExpand(item._key)"
                class="btn-outline text-sm py-1.5 px-3">
                {{ expanded.has(item._key) ? 'Hide Diff' : 'Review' }}
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
            <div v-if="expanded.has(item._key)" class="mt-4 pt-4 border-t border-paper-100">
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
                      <!-- Avatar diff: same card style as documents -->
                      <template v-if="row.oldAvatarUrl !== undefined">
                        <td class="py-2.5 pr-4 font-body text-paper-500 align-top text-xs leading-relaxed max-w-0">
                          <DocumentChangeList
                            :documents="row.oldAvatarUrl ? [{ label: 'Profile Photo', file_name: avatarFilename(row.oldAvatarUrl), file_url: row.oldAvatarUrl, mime_type: 'image/jpeg' }] : []"
                            muted :view-doc="viewDoc" />
                        </td>
                        <td class="py-2.5 font-body align-top text-xs leading-relaxed max-w-0">
                          <DocumentChangeList
                            :documents="[{ label: 'Profile Photo', file_name: avatarFilename(row.newAvatarUrl), file_url: row.newAvatarUrl, mime_type: 'image/jpeg' }]"
                            highlight :view-doc="viewDoc" />
                        </td>
                      </template>
                      <!-- Document diff: show file cards -->
                      <template v-else-if="row.oldDocuments || row.newDocuments">
                        <td class="py-2.5 pr-4 font-body text-paper-500 align-top text-xs leading-relaxed max-w-0">
                          <DocumentChangeList :documents="row.oldDocuments || []" muted :view-doc="viewDoc" />
                        </td>
                        <td class="py-2.5 font-body align-top text-xs leading-relaxed max-w-0">
                          <DocumentChangeList :documents="row.newDocuments || []" highlight :view-doc="viewDoc" />
                        </td>
                      </template>
                      <!-- Default text diff -->
                      <template v-else>
                        <td class="py-2.5 pr-4 font-body text-paper-500 align-top text-xs leading-relaxed max-w-0">
                          <span class="line-clamp-3 block">{{ row.old }}</span>
                        </td>
                        <td class="py-2.5 font-body align-top text-xs leading-relaxed max-w-0">
                          <span class="line-clamp-3 block font-semibold text-navy-800 bg-emerald-50 border border-emerald-200 rounded px-1.5 py-0.5 inline-block">
                            {{ row.new }}
                          </span>
                        </td>
                      </template>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p v-else class="text-xs text-paper-400 font-body italic">No comparable field changes detected.</p>
            </div>
          </Transition>
        </div>
        <AdminPagination :meta="paginationMeta" @page="p => currentPage = p" />
      </div>

      <!-- Empty state -->
      <div v-else class="card text-center py-14">
        <svg class="w-10 h-10 text-paper-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="font-display font-semibold text-navy-700">All clear</p>
        <p class="text-paper-400 text-sm font-body mt-1">No profile changes are awaiting review.</p>
      </div>
    </template>

    <!-- Approve confirm -->
    <AdminConfirmDialog
      :show="!!approveTarget"
      title="Approve changes?"
      :message="`Apply all pending changes for ${approveTarget?.user?.name}?`"
      confirm-label="Yes, Approve"
      @confirm="confirmApprove"
      @cancel="approveTarget = null"
    />

    <!-- Reject confirm -->
    <AdminConfirmDialog
      :show="!!rejectTarget"
      :title="rejectTarget?._type === 'avatar' ? 'Reject photo?' : 'Reject changes?'"
      :message="rejectTarget?._type === 'avatar'
        ? `The pending photo for ${rejectTarget?.user?.name} will be discarded.`
        : `${rejectTarget?.user?.name}'s pending changes will be discarded.`"
      confirm-label="Reject"
      :danger="true"
      :with-input="true"
      :input-required="false"
      input-label="Rejection note (optional)"
      :input-placeholder="rejectTarget?._type === 'avatar' ? 'Explain why the photo was not approved…' : 'Explain what needs to be corrected…'"
      @confirm="confirmReject"
      @cancel="rejectTarget = null"
    />

    <!-- Document preview modal -->
    <Teleport to="body">
      <Transition name="dialog">
        <div v-if="previewDoc" class="fixed inset-0 z-[240] flex items-center justify-center px-4"
          @click.self="previewDoc = null">
          <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="previewDoc = null" />
          <div class="relative w-full max-w-2xl bg-white rounded-sm shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-paper-200">
              <div class="min-w-0">
                <p class="font-display font-semibold text-sm text-navy-900 truncate">{{ previewDoc.file_name }}</p>
                <p class="text-xs text-paper-400 font-body mt-0.5 capitalize">{{ previewDoc.label || previewDoc.type?.replace(/_/g, ' ') }}</p>
              </div>
              <button @click="previewDoc = null"
                class="ml-3 shrink-0 w-8 h-8 flex items-center justify-center rounded-md text-paper-400 hover:bg-paper-100 hover:text-navy-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-paper-50 min-h-[200px] max-h-[70vh] overflow-auto">
              <img :src="previewDoc.file_url" :alt="previewDoc.file_name"
                class="max-w-full max-h-[60vh] object-contain rounded-sm shadow-sm" />
            </div>
            <div class="px-5 py-3 border-t border-paper-200 flex justify-end">
              <a :href="previewDoc.file_url" target="_blank" rel="noopener"
                class="inline-flex items-center gap-1.5 text-xs font-semibold font-display text-navy-700 border border-paper-300 bg-white px-3 py-1.5 rounded-sm hover:bg-navy-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
                Open in new tab
              </a>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { h, ref, computed, onMounted, watch } from 'vue'
import { adminApi } from '@/api/admin.js'
import { getInitials } from '@/utils/helpers.js'
import { toast } from 'vue-sonner'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'
import AdminPagination from '@/components/admin/AdminPagination.vue'
import {
  buildDiff,
  changedLiveDocuments, changedPendingDocuments,
  applyEducationPreview, educationSummary,
  uniqueSubjectNames, norm,
  PREF_FIELDS, PERSONAL_FIELDS, EMERGENCY_FIELDS,
} from '@/composables/usePendingDiff.js'

const PAGE_SIZE      = 10
const profileItems   = ref([])
const avatarItems    = ref([])
const loading        = ref(true)
const expanded       = ref(new Set())
const approveTarget  = ref(null)
const rejectTarget   = ref(null)
const actionLoading  = ref(false)
const previewDoc     = ref(null)
const currentPage    = ref(1)

// Merge profile changes and standalone avatar items into one unified list.
// Avatar items are normalised into the same shape buildDiff() expects.
const allItems = computed(() => [
  ...profileItems.value.map(i => ({ ...i, _type: 'profile', _key: `p-${i.id}` })),
  ...avatarItems.value.map(av => ({
    _type:        'avatar',
    _key:         `a-${av.id}`,
    id:           av.id,
    profile_id:   av.profile_id || null,
    submitted_at: null,
    user:         { name: av.name, email: av.email, role: av.role },
    live:         { avatar_url: av.avatar_url },
    pending:      { avatar: { url: av.pending_avatar_url } },
  })),
])

const paginationMeta = computed(() => {
  const total     = allItems.value.length
  const lastPage  = Math.max(1, Math.ceil(total / PAGE_SIZE))
  const page      = Math.min(currentPage.value, lastPage)
  const from      = total ? (page - 1) * PAGE_SIZE + 1 : 0
  const to        = Math.min(page * PAGE_SIZE, total)
  return { current_page: page, last_page: lastPage, total, from, to }
})

const pagedItems = computed(() => {
  const page = paginationMeta.value.current_page
  return allItems.value.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE)
})

// If an item is removed and the current page becomes empty, go back one page.
watch(allItems, () => {
  const lastPage = Math.max(1, Math.ceil(allItems.value.length / PAGE_SIZE))
  if (currentPage.value > lastPage) currentPage.value = lastPage
})

function viewDoc(doc) {
  if (!doc?.file_url) return
  if (doc.mime_type === 'application/pdf') {
    window.open(doc.file_url, '_blank', 'noopener')
  } else {
    previewDoc.value = doc
  }
}

const DocumentChangeList = {
  props: {
    documents: { type: Array, default: () => [] },
    muted:     { type: Boolean, default: false },
    highlight: { type: Boolean, default: false },
    viewDoc:   { type: Function, default: null },
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
            ? h('button', {
                class: 'mt-1.5 inline-flex items-center gap-1 border border-navy-200 bg-navy-50 px-2 py-1 rounded-sm font-display text-xs font-semibold text-navy-700 hover:bg-navy-100 transition-colors',
                onClick: () => props.viewDoc ? props.viewDoc(doc) : window.open(doc.file_url, '_blank', 'noopener'),
              }, [
                h('svg', { class: 'w-3 h-3', fill: 'none', stroke: 'currentColor', 'stroke-width': '2', viewBox: '0 0 24 24' }, [
                  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z' }),
                  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' }),
                ]),
                'View',
              ])
            : null,
        ]))
      : [h('span', { class: 'text-xs text-paper-400' }, '—')])
  },
}

onMounted(async () => {
  try {
    const { data } = await adminApi.getPendingChanges()
    profileItems.value = data.data           || []
    avatarItems.value  = data.pending_avatars || []
  } finally {
    loading.value = false
  }
})

function initials(name) { return getInitials(name) }

function avatarFilename(url) {
  if (!url) return 'photo'
  return decodeURIComponent(url.split('/').pop().split('?')[0]) || 'profile-photo.jpg'
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function toggleExpand(key) {
  if (expanded.value.has(key)) {
    expanded.value.delete(key)
  } else {
    expanded.value.add(key)
  }
  expanded.value = new Set(expanded.value)
}

function openApprove(item) { approveTarget.value = item }
function openReject(item)  { rejectTarget.value = item }

async function confirmApprove() {
  const item = approveTarget.value
  approveTarget.value = null
  actionLoading.value = true
  const toastId = `approve-${item._key}`
  toast.loading('Approving…', { id: toastId, description: `Applying changes for ${item.user?.name}.` })
  try {
    if (item._type === 'avatar') {
      await adminApi.approvePendingAvatar(item.id)
      avatarItems.value = avatarItems.value.filter(i => i.id !== item.id)
    } else {
      await adminApi.approvePendingChange(item.id)
      profileItems.value = profileItems.value.filter(i => i.id !== item.id)
    }
    toast.success('Approved.', { id: toastId, description: `${item.user?.name}'s profile is now updated.` })
  } catch {
    toast.error('Failed to approve.', { id: toastId, description: 'Please try again.' })
  } finally {
    actionLoading.value = false
  }
}

async function confirmReject(note) {
  const item = rejectTarget.value
  rejectTarget.value = null
  actionLoading.value = true
  const toastId = `reject-${item._key}`
  toast.loading('Rejecting…', { id: toastId })
  try {
    if (item._type === 'avatar') {
      await adminApi.rejectPendingAvatar(item.id, { note: note || null })
      avatarItems.value = avatarItems.value.filter(i => i.id !== item.id)
    } else {
      await adminApi.rejectPendingChange(item.id, { note })
      profileItems.value = profileItems.value.filter(i => i.id !== item.id)
    }
    toast.success('Rejected.', { id: toastId })
  } catch {
    toast.error('Failed to reject.', { id: toastId, description: 'Please try again.' })
  } finally {
    actionLoading.value = false
  }
}
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
