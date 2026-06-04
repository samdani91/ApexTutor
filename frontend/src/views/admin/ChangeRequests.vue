<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-6">Profile change requests</h1>

    <div v-if="loading" class="text-paper-500 font-body text-sm">Loading…</div>

    <div v-else-if="!requests.length"
      class="card text-center py-12 text-paper-500 font-body">
      No pending change requests.
    </div>

    <div v-else class="space-y-4">
      <div v-for="req in requests" :key="req.id" class="card">

        <!-- Info -->
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap mb-0.5">
            <p class="font-display font-semibold text-navy-900">{{ req.tutor_profile?.user?.name }}</p>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-pill"
              :class="req.status === 'review_pending'
                ? 'bg-blue-50 text-blue-700 border border-blue-200'
                : 'bg-amber-50 text-amber-700 border border-amber-200'">
              {{ req.status === 'review_pending' ? 'Re-review' : 'Unlock request' }}
            </span>
          </div>
          <p class="text-xs text-paper-500 font-body mt-0.5 truncate">{{ req.tutor_profile?.user?.email }}</p>
          <p class="text-sm font-body text-navy-700 mt-2 leading-relaxed">
            <span class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mr-1">Reason:</span>
            {{ req.reason }}
          </p>
          <p class="text-xs text-paper-400 font-body mt-1">
            {{ req.status === 'review_pending' ? 'Submitted for re-review' : 'Submitted' }} {{ formatDate(req.created_at) }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 mt-3 pt-3 border-t border-paper-100">
          <button @click="openApprove(req)"
            class="btn-primary text-sm py-1.5 flex-1 sm:flex-none sm:px-4">
            Approve
          </button>
          <button @click="openReject(req)"
            class="bg-red-600 text-white text-sm font-semibold font-display py-1.5 flex-1 sm:flex-none sm:px-4 rounded-md hover:bg-red-700 transition-colors">
            Reject
          </button>
        </div>

      </div>
    </div>

    <!-- Approve confirm -->
    <AdminConfirmDialog
      :show="!!approveTarget"
      :title="approveTarget?.status === 'review_pending' ? 'Re-verify Tutor Profile?' : 'Approve Change Request?'"
      :message="approveTarget?.status === 'review_pending'
        ? `Re-verify ${approveTarget?.tutor_profile?.user?.name}'s updated profile and mark them as verified?`
        : `Allow ${approveTarget?.tutor_profile?.user?.name} to edit their profile?`"
      confirm-label="Yes, Approve"
      @confirm="confirmApprove"
      @cancel="approveTarget = null"
    />

    <!-- Reject confirm -->
    <AdminConfirmDialog
      :show="!!rejectTarget"
      title="Reject Change Request?"
      :message="`Reject ${rejectTarget?.tutor_profile?.user?.name}'s request to edit their profile.`"
      confirm-label="Reject"
      danger
      with-input
      input-label="Rejection note"
      input-placeholder="Explain why this request is being rejected…"
      @confirm="confirmReject"
      @cancel="rejectTarget = null"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'

const requests     = ref([])
const loading      = ref(true)
const approveTarget = ref(null)
const rejectTarget  = ref(null)

onMounted(async () => {
  try {
    const { data } = await adminApi.getChangeRequests()
    requests.value = data.data.data || data.data || []
  } finally {
    loading.value = false
  }
})

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function openApprove(req) { approveTarget.value = req }
function openReject(req)  { rejectTarget.value  = req }

async function confirmApprove() {
  const id = approveTarget.value.id
  approveTarget.value = null
  await adminApi.approveChangeRequest(id)
  requests.value = requests.value.filter(r => r.id !== id)
  toast.success('Request approved — profile unlocked.')
}

async function confirmReject(note) {
  const req = rejectTarget.value
  rejectTarget.value = null
  await adminApi.rejectChangeRequest(req.id, { admin_note: note })
  requests.value = requests.value.filter(r => r.id !== req.id)
  toast.success('Request rejected.')
}
</script>
