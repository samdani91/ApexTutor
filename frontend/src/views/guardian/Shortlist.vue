<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-6">Shortlist</h1>

    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>

    <div v-else-if="!enrichedList.length" class="card text-center py-12">
      <p class="font-display font-semibold text-navy-700 text-lg mb-2">Your shortlist is empty</p>
      <p v-if="confirmedIds.size" class="text-paper-500 text-sm font-body mb-4">
        Your shortlisted tutors have been confirmed. View them in
        <RouterLink to="/guardian/confirmed-tuitions" class="font-semibold text-emerald-700 hover:underline">Confirmed Tuitions</RouterLink>.
      </p>
      <p v-else class="text-paper-500 text-sm font-body mb-4">Browse tutors and save the ones you like.</p>
      <RouterLink to="/search" class="btn-primary text-sm py-2 px-5">Find Tutors</RouterLink>
    </div>

    <div v-else class="grid gap-4">
      <div v-for="item in enrichedList" :key="item.id"
        class="group overflow-hidden rounded-sm border border-paper-200 bg-white p-4 shadow-sm transition-all duration-150 hover:-translate-y-0.5 hover:border-navy-200 hover:shadow-md md:p-5">

        <!-- Avatar + info -->
        <RouterLink :to="`/tutors/${item.tutor_profile.public_id}`"
          class="flex min-w-0 items-start gap-3 transition-opacity hover:opacity-85">
          <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-navy-100 bg-navy-50 md:h-[72px] md:w-[72px]">
            <img v-if="item.tutor_profile.user?.avatar_url" :src="item.tutor_profile.user.avatar_url"
              class="w-full h-full object-cover" />
            <span v-else class="font-display font-bold text-xl text-navy-700 md:text-2xl">
              {{ initials(item.tutor_profile.user?.name) }}
            </span>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-start justify-between gap-2">
              <div class="min-w-0">
                <p class="truncate font-display text-base font-semibold leading-snug text-navy-900 md:text-lg">
                  {{ item.tutor_profile.user?.name }}
                </p>
                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                  <span v-if="item.tutor_profile.tutor_id"
                    class="rounded-pill border border-paper-200 bg-paper-50 px-2 py-0.5 font-display text-[11px] font-semibold text-paper-600">
                    {{ item.tutor_profile.tutor_id }}
                  </span>
                  <span class="rounded-pill border border-gold-100 bg-gold-50 px-2 py-0.5 font-display text-[11px] font-semibold text-gold-700">
                    Shortlisted
                  </span>
                </div>
              </div>
            </div>
            <div class="mt-2 flex items-center gap-0.5">
              <span v-for="i in 5" :key="i" class="text-sm leading-none"
                :class="i <= Math.round(Number(item.tutor_profile.rating)) ? 'text-gold-400' : 'text-paper-300'">★</span>
              <span class="ml-1 text-xs text-paper-500">({{ item.tutor_profile.review_count ?? 0 }})</span>
            </div>
          </div>
        </RouterLink>

        <div class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
          <!-- Salary -->
          <div class="rounded-sm border border-navy-100 bg-navy-50 px-3 py-2.5">
            <p class="font-display text-[11px] font-semibold uppercase tracking-wide text-paper-500">Expected salary</p>
            <p class="mt-0.5 font-display text-sm font-bold leading-snug text-navy-800">
              {{ formatSalaryRange(item.tutor_profile.tuition_preference?.expected_salary_min, item.tutor_profile.tuition_preference?.expected_salary_max) }}
              <span class="font-body text-xs font-normal text-paper-500">/mo</span>
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 sm:justify-end">
            <button @click="removeTarget = item.tutor_profile_id"
              :disabled="removing[item.tutor_profile_id]"
              class="inline-flex min-h-[40px] items-center justify-center rounded-sm border border-paper-200 px-3 text-xs font-semibold font-display text-paper-600 hover:border-red-200 hover:bg-red-50 hover:text-red-600 transition-colors disabled:opacity-50">
              {{ removing[item.tutor_profile_id] ? 'Removing…' : 'Remove' }}
            </button>

            <!-- In-progress connection badge -->
            <span v-if="item.connection"
              class="inline-flex min-h-[40px] items-center gap-1.5 rounded-sm border px-3 py-2 text-xs font-semibold font-display"
              :class="connectionBadgeClass(item.connection.status)">
              <span class="w-1.5 h-1.5 rounded-full inline-block" :class="connectionDotClass(item.connection.status)"></span>
              {{ connectionLabel(item.connection.status) }}
            </span>

            <!-- No active connection — show request button -->
            <button v-else
              @click="requestConnection(item)"
              :disabled="requesting[item.tutor_profile_id]"
              class="inline-flex min-h-[40px] items-center justify-center rounded-sm bg-navy-700 px-4 py-2 text-sm font-semibold font-display text-white transition-colors hover:bg-navy-800 disabled:opacity-50 whitespace-nowrap">
              {{ requesting[item.tutor_profile_id] ? 'Requesting…' : 'Request connection' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <ConfirmDialog
      :show="removeTarget !== null"
      title="Remove From Shortlist?"
      message="Remove this tutor from your shortlist? You can always add them back later."
      confirm-label="Remove"
      danger
      @confirm="confirmRemove"
      @cancel="removeTarget = null"
    />

    <!-- Connection request modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="activeRequest" class="fixed inset-0 z-50 bg-navy-900/50 flex items-center justify-center p-4"
          @click.self="activeRequest = null">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 space-y-4">
            <h3 class="font-display font-semibold text-navy-900 text-lg">Request connection</h3>
            <p class="text-sm text-paper-600 font-body">
              You're requesting a connection with <strong>{{ activeRequest.tutor_profile.user?.name }}</strong>.
              Optionally add a message for the admin.
            </p>
            <textarea v-model="guardianMessage" rows="3"
              placeholder="E.g. I need a tutor for my child studying in class 8, Bangla medium…"
              class="input text-sm w-full resize-none" maxlength="1000" />
            <div class="flex gap-2 justify-end">
              <button @click="activeRequest = null"
                class="text-sm font-semibold font-display border border-paper-300 text-paper-600 hover:bg-paper-100 px-4 py-2 rounded-md transition-colors">
                Cancel
              </button>
              <button @click="confirmRequest"
                :disabled="submitting"
                class="text-sm font-semibold font-display bg-navy-700 text-white hover:bg-navy-800 px-4 py-2 rounded-md transition-colors disabled:opacity-50">
                {{ submitting ? 'Sending…' : 'Send request' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { guardianApi } from '@/api/guardian.js'
import { toast } from 'vue-sonner'
import { getInitials, formatSalaryRange } from '@/utils/helpers.js'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const list        = ref([])
const connections = ref([])
const loading     = ref(true)

const activeRequest   = ref(null)
const guardianMessage = ref('')
const submitting      = ref(false)
const removeTarget    = ref(null) // tutor_profile_id pending removal

const initials = (name) => getInitials(name)

const requesting = reactive({})
const removing   = reactive({})

// Map active (non-confirmed) connections by tutor_profile_id
const connMap = computed(() => {
  const map = {}
  for (const c of connections.value) {
    if (c.status !== 'confirmed') map[c.tutor_profile_id] = c
  }
  return map
})

const confirmedIds = computed(() => new Set(
  connections.value.filter(c => c.status === 'confirmed').map(c => c.tutor_profile_id)
))

// Only show shortlisted tutors that are NOT yet confirmed (confirmed ones live in Confirmed Tuitions)
const enrichedList = computed(() =>
  list.value
    .filter(item => !confirmedIds.value.has(item.tutor_profile_id))
    .map(item => ({
      ...item,
      connection: connMap.value[item.tutor_profile_id] ?? null,
    }))
)

onMounted(async () => {
  try {
    const [shortlistRes, connRes] = await Promise.all([
      guardianApi.getShortlist(),
      guardianApi.getConnections(),
    ])
    list.value        = shortlistRes.data.data || []
    const connData    = connRes.data.data
    connections.value = Array.isArray(connData) ? connData : (connData?.data ?? [])
  } finally {
    loading.value = false
  }
})

async function confirmRemove() {
  const tutorProfileId = removeTarget.value
  removeTarget.value = null
  if (!tutorProfileId) return
  removing[tutorProfileId] = true
  try {
    await guardianApi.removeShortlist(tutorProfileId)
    list.value = list.value.filter(i => i.tutor_profile_id !== tutorProfileId)
    toast.success('Removed from shortlist.')
  } catch {
    toast.error('Could not remove. Please try again.')
  } finally {
    removing[tutorProfileId] = false
  }
}

function requestConnection(item) {
  guardianMessage.value = ''
  activeRequest.value   = item
}

async function confirmRequest() {
  const item = activeRequest.value
  if (!item) return
  submitting.value = true
  requesting[item.tutor_profile_id] = true
  try {
    const { data } = await guardianApi.requestConnection({
      tutor_profile_id: item.tutor_profile_id,
      guardian_message: guardianMessage.value || null,
    })
    connections.value.push({ ...data.data, tutor_profile_id: item.tutor_profile_id })
    toast.success('Connection request sent. Admin will review it shortly.')
    activeRequest.value = null
  } catch (err) {
    toast.error(err.response?.data?.message || 'Could not send request. Please try again.')
  } finally {
    submitting.value = false
    requesting[item.tutor_profile_id] = false
  }
}

function connectionLabel(status) {
  const labels = {
    pending:         'Pending review',
    admin_reviewing: 'Under review',
    tutor_contacted: 'Tutor contacted',
    declined:        'Declined',
    closed:          'Closed',
  }
  return labels[status] ?? status
}

function connectionBadgeClass(status) {
  if (status === 'declined') return 'bg-red-50 text-red-700 border-red-200'
  if (status === 'closed')   return 'bg-paper-100 text-paper-600 border-paper-200'
  return 'bg-blue-50 text-blue-700 border-blue-200'
}

function connectionDotClass(status) {
  if (status === 'declined') return 'bg-red-500'
  if (status === 'closed')   return 'bg-paper-400'
  return 'bg-blue-500'
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
