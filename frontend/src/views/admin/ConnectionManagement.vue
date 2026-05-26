<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-6">Connection management</h1>
    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>

    <template v-else-if="connections.length">

      <!-- Mobile cards -->
      <div class="md:hidden space-y-3">
        <div v-for="conn in connections" :key="conn.id" class="card">

          <!-- Guardian + Tutor -->
          <div class="flex items-start gap-3 mb-3">
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-0.5">Guardian</p>
              <p class="font-display font-semibold text-navy-900 text-sm truncate">
                {{ conn.guardian_profile?.user?.name || '—' }}
              </p>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-0.5">Tutor</p>
              <p class="font-display font-semibold text-navy-900 text-sm truncate">
                {{ conn.tutor_profile?.user?.name || '—' }}
              </p>
            </div>
          </div>

          <!-- Status badge + select -->
          <div class="flex items-center justify-between gap-3 pt-3 border-t border-paper-100">
            <span class="px-2 py-0.5 rounded-pill text-xs font-semibold capitalize shrink-0"
              :class="statusBadgeClass(conn.status)">
              {{ conn.status.replace(/_/g,' ') }}
            </span>
            <select @change="(e) => onStatusChange(conn, e)" :value="conn.status"
              class="status-select flex-1">
              <option value="pending">Pending</option>
              <option value="admin_reviewing">Admin reviewing</option>
              <option value="tutor_contacted">Tutor contacted</option>
              <option value="connected">Connected</option>
              <option value="declined">Declined</option>
            </select>
          </div>

        </div>
      </div>

      <!-- Desktop table -->
      <div class="hidden md:block card overflow-x-auto">
        <table class="w-full text-sm font-body">
          <thead>
            <tr class="border-b border-paper-200 text-left">
              <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Guardian</th>
              <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Tutor</th>
              <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Status</th>
              <th class="pb-3 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Change status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="conn in connections" :key="conn.id"
              class="border-b border-paper-100 last:border-0 hover:bg-navy-50/40 transition-colors">
              <td class="py-3 pr-6 font-body text-navy-900">{{ conn.guardian_profile?.user?.name }}</td>
              <td class="py-3 pr-6 font-body text-navy-900">{{ conn.tutor_profile?.user?.name }}</td>
              <td class="py-3 pr-6">
                <span class="px-2 py-0.5 rounded-pill text-xs font-semibold capitalize"
                  :class="statusBadgeClass(conn.status)">
                  {{ conn.status.replace(/_/g,' ') }}
                </span>
              </td>
              <td class="py-3">
                <select @change="(e) => onStatusChange(conn, e)" :value="conn.status"
                  class="status-select">
                  <option value="pending">Pending</option>
                  <option value="admin_reviewing">Admin reviewing</option>
                  <option value="tutor_contacted">Tutor contacted</option>
                  <option value="connected">Connected</option>
                  <option value="declined">Declined</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </template>

    <div v-else class="card text-center py-12 text-paper-500 font-body">
      No connections found.
    </div>

    <!-- Status change confirm -->
    <AdminConfirmDialog
      :show="!!pendingChange"
      title="Update connection status?"
      :message="pendingChange ? `Change ${pendingChange.conn.guardian_profile?.user?.name}'s connection status to &quot;${pendingChange.newStatus.replace(/_/g,' ')}&quot;?` : ''"
      confirm-label="Yes, update"
      @confirm="confirmStatusChange"
      @cancel="cancelStatusChange"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'

const connections   = ref([])
const loading       = ref(true)
const pendingChange = ref(null)

onMounted(async () => {
  try {
    const { data } = await adminApi.getConnections()
    connections.value = data.data.data || data.data || []
  } finally {
    loading.value = false
  }
})

function statusBadgeClass(status) {
  if (status === 'connected')       return 'bg-emerald-50 text-emerald-700'
  if (status === 'declined')        return 'bg-red-50 text-red-700'
  if (status === 'pending')         return 'bg-amber-50 text-amber-700'
  return 'bg-blue-50 text-blue-700'
}

function onStatusChange(conn, e) {
  const newStatus = e.target.value
  e.target.value = conn.status
  pendingChange.value = { conn, newStatus }
}

async function confirmStatusChange() {
  const { conn, newStatus } = pendingChange.value
  pendingChange.value = null
  await adminApi.updateConnectionStatus(conn.id, { status: newStatus })
  conn.status = newStatus
  toast.success('Status updated.')
}

function cancelStatusChange() {
  pendingChange.value = null
}
</script>
