<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-6">Verification queue</h1>
    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>
    <div v-else-if="!queue.length" class="card text-center py-12 text-paper-500 font-body">
      All caught up — no pending verifications.
    </div>
    <div v-else class="space-y-4">
      <div v-for="tutor in queue" :key="tutor.id" class="card">

        <!-- Top row: avatar + info -->
        <div class="flex items-start gap-3">
          <!-- Avatar -->
          <div class="w-11 h-11 rounded-xl bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden">
            <img v-if="tutor.user?.avatar_url" :src="tutor.user.avatar_url" class="w-full h-full object-cover" />
            <span v-else class="font-display font-bold text-navy-700">{{ initials(tutor.user?.name) }}</span>
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <p class="font-display font-semibold text-navy-900">{{ tutor.user?.name }}</p>
              <span v-if="tutor.tutor_id"
                class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-1.5 py-0 rounded-pill">
                {{ tutor.tutor_id }}
              </span>
            </div>
            <p class="text-xs text-paper-500 font-body mt-0.5 truncate">{{ tutor.user?.email }}</p>
            <div class="flex items-center gap-2 mt-1.5 flex-wrap text-xs font-body text-paper-500">
              <span>{{ tutor.documents?.length || 0 }} docs</span>
              <span>·</span>
              <span>{{ tutor.profile_completion_percent }}% complete</span>
            </div>
          </div>
        </div>

        <!-- Document chips -->
        <div v-if="tutor.documents?.length" class="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-paper-100">
          <span v-for="doc in tutor.documents" :key="doc.id"
            class="text-xs bg-paper-100 text-paper-600 px-2 py-0.5 rounded-pill font-body capitalize">
            {{ doc.type.replace(/_/g,' ') }}
          </span>
        </div>

        <!-- Preference details -->
        <template v-if="tutor.tuition_preference">
          <!-- Experience -->
          <div v-if="tutor.tuition_preference.experience_details || tutor.tuition_preference.total_experience_years"
            class="mt-3 pt-3 border-t border-paper-100">
            <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1">Experience</p>
            <p v-if="tutor.tuition_preference.total_experience_years" class="text-xs font-body text-paper-500 mb-0.5">
              {{ tutor.tuition_preference.total_experience_years >= 21 ? '20+' : tutor.tuition_preference.total_experience_years }} year{{ tutor.tuition_preference.total_experience_years === 1 ? '' : 's' }}
            </p>
            <p v-if="tutor.tuition_preference.experience_details" class="text-sm font-body text-navy-700 leading-relaxed text-justify">{{ tutor.tuition_preference.experience_details }}</p>
          </div>

          <!-- Available days -->
          <div v-if="tutor.tuition_preference.days?.length" class="mt-3 pt-3 border-t border-paper-100">
            <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1.5">Available days</p>
            <div class="flex flex-wrap gap-1.5">
              <span v-for="d in tutor.tuition_preference.days" :key="d.day"
                class="text-xs bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill font-display font-semibold capitalize">
                {{ d.day }}
              </span>
            </div>
          </div>

          <!-- Preferred subjects -->
          <div v-if="tutor.tuition_preference.subjects?.length" class="mt-3 pt-3 border-t border-paper-100">
            <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1.5">Subjects</p>
            <div class="flex flex-wrap gap-1.5">
              <span v-for="s in tutor.tuition_preference.subjects" :key="s.id"
                class="text-xs bg-paper-100 text-paper-600 px-2 py-0.5 rounded-pill font-body">
                {{ s.name }}
              </span>
            </div>
          </div>

          <!-- Preferred locations -->
          <div v-if="tutor.tuition_preference.locations?.length" class="mt-3 pt-3 border-t border-paper-100">
            <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1.5">Preferred locations</p>
            <div class="flex flex-wrap gap-1.5">
              <span v-for="loc in tutor.tuition_preference.locations" :key="loc.id"
                class="text-xs bg-paper-100 text-paper-600 px-2 py-0.5 rounded-pill font-body">
                {{ loc.area_name }}
              </span>
            </div>
          </div>

          <!-- Tutoring method -->
          <div v-if="tutor.tuition_preference.tutoring_method_description"
            class="mt-3 pt-3 border-t border-paper-100">
            <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1">Tutoring method</p>
            <p class="text-sm font-body text-navy-700 leading-relaxed text-justify">{{ tutor.tuition_preference.tutoring_method_description }}</p>
          </div>
        </template>

        <!-- Bottom row: view link + action buttons -->
        <div class="flex items-center justify-between gap-3 mt-3 pt-3 border-t border-paper-100 flex-wrap">
          <RouterLink :to="{ name: 'admin-tutor-detail', params: { id: tutor.id } }"
            class="text-xs font-semibold font-display text-navy-700 hover:underline">
            View full profile
          </RouterLink>
          <div class="flex gap-2 ml-auto">
            <button @click="openApprove(tutor)"
              class="btn-primary text-sm py-1.5 px-4">
              Approve
            </button>
            <button @click="openReject(tutor)"
              class="bg-red-600 text-white text-sm font-semibold font-display py-1.5 px-4 rounded-md hover:bg-red-700 transition-colors">
              Reject
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Approve confirm -->
    <AdminConfirmDialog
      :show="!!approveTarget"
      title="Approve tutor?"
      :message="`Approve ${approveTarget?.user?.name} and mark their profile as verified?`"
      confirm-label="Yes, approve"
      @confirm="confirmApprove"
      @cancel="approveTarget = null"
    />

    <!-- Reject confirm (with reason input) -->
    <AdminConfirmDialog
      :show="!!rejectTarget"
      title="Reject tutor?"
      :message="`Reject ${rejectTarget?.user?.name}'s verification request.`"
      confirm-label="Reject"
      danger
      with-input
      input-label="Rejection reason"
      input-placeholder="Explain why this profile is being rejected…"
      @confirm="confirmReject"
      @cancel="rejectTarget = null"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import { getInitials } from '@/utils/helpers.js'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'

const queue        = ref([])
const loading      = ref(true)
const approveTarget = ref(null)
const rejectTarget  = ref(null)

function initials(name) { return getInitials(name) }

onMounted(async () => {
  try {
    const { data } = await adminApi.getVerificationQueue()
    queue.value = data.data.data || data.data || []
  } finally {
    loading.value = false
  }
})

function openApprove(tutor) { approveTarget.value = tutor }
function openReject(tutor)  { rejectTarget.value  = tutor }

async function confirmApprove() {
  const id = approveTarget.value.id
  approveTarget.value = null
  await adminApi.approveTutor(id)
  queue.value = queue.value.filter(t => t.id !== id)
  toast.success('Tutor approved.')
}

async function confirmReject(reason) {
  const tutor = rejectTarget.value
  rejectTarget.value = null
  await adminApi.rejectTutor(tutor.id, { rejection_reason: reason })
  queue.value = queue.value.filter(t => t.id !== tutor.id)
  toast.success('Tutor rejected.')
}
</script>
