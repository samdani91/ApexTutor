<template>
  <div>
    <!-- Back -->
    <RouterLink to="/admin/users" class="inline-flex items-center gap-1.5 text-sm font-semibold font-display text-navy-700 hover:text-navy-900 mb-5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
      </svg>
      Back to users
    </RouterLink>

    <div v-if="loading" class="text-paper-500 font-body py-12 text-center">Loading…</div>

    <template v-else-if="guardian">
      <!-- Header card -->
      <div class="card mb-5">
        <div class="flex gap-5 items-start flex-wrap">
          <!-- Avatar -->
          <div class="w-20 h-20 rounded-xl bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden ring-2 ring-white shadow">
            <img v-if="guardian.user?.avatar_url" :src="guardian.user.avatar_url" class="w-full h-full object-cover" />
            <span v-else class="font-display font-bold text-2xl text-navy-700">{{ initials }}</span>
          </div>
          <!-- Basic info -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <h1 class="font-display font-bold text-xl text-navy-900">{{ guardian.user?.name }}</h1>
              <span v-if="guardian.guardian_id"
                class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill">
                {{ guardian.guardian_id }}
              </span>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize bg-blue-50 text-blue-700">
                {{ guardian.account_type || 'guardian' }}
              </span>
            </div>
            <p class="text-sm text-paper-500 font-body">{{ guardian.user?.email }}</p>
            <p class="text-sm text-paper-500 font-body">{{ guardian.user?.phone }}</p>
            <p v-if="guardian.user?.address" class="text-sm text-paper-500 font-body">{{ guardian.user.address }}</p>
            <p class="text-xs text-paper-400 font-body mt-1">
              Member since {{ formatDate(guardian.user?.created_at) }}
            </p>
          </div>
          <!-- Extra details -->
          <div class="w-full mt-4 pt-4 border-t border-paper-100 grid sm:grid-cols-3 gap-x-6 gap-y-3">
            <div v-if="guardian.occupation">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Occupation</p>
              <p class="text-sm font-body text-navy-800 mt-0.5">{{ guardian.occupation }}</p>
            </div>
            <div v-if="guardian.relationship_to_student">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Relationship</p>
              <p class="text-sm font-body text-navy-800 mt-0.5 capitalize">{{ guardian.relationship_to_student }}</p>
            </div>
            <div v-if="guardian.nid_number">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">NID number</p>
              <p class="text-sm font-body text-navy-800 mt-0.5">{{ guardian.nid_number }}</p>
            </div>
            <div v-if="guardian.nid_document_url" class="sm:col-span-3">
              <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide mb-1">NID document</p>
              <a :href="guardian.nid_document_url" target="_blank"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-navy-700 hover:text-navy-900 underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                View NID document
              </a>
            </div>
            <div v-if="!guardian.nid_document_url" class="sm:col-span-3">
              <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-pill bg-amber-50 text-amber-700 border border-amber-200">
                NID not uploaded
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-5">

        <!-- Connection Requests -->
        <div class="card">
          <h2 class="section-title">
            Connection requests
            <span class="text-paper-400 font-body font-normal">({{ guardian.connection_requests?.length || 0 }})</span>
          </h2>
          <div v-if="guardian.connection_requests?.length" class="divide-y divide-paper-100">
            <div v-for="conn in guardian.connection_requests" :key="conn.id" class="py-2.5 flex items-center justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-display font-semibold text-navy-900 truncate">
                  {{ conn.tutor_profile?.user?.name || 'Unknown tutor' }}
                </p>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span v-if="conn.tutor_profile?.tutor_id"
                    class="text-xs font-semibold font-display text-navy-500 bg-navy-50 border border-navy-200 px-1.5 py-0 rounded-pill">
                    {{ conn.tutor_profile.tutor_id }}
                  </span>
                  <RouterLink v-if="conn.tutor_profile?.id"
                    :to="{ name: 'admin-tutor-detail', params: { id: conn.tutor_profile.id } }"
                    class="text-xs text-navy-700 hover:underline font-semibold">View tutor</RouterLink>
                </div>
                <p class="text-xs text-paper-400 font-body mt-0.5">{{ formatDate(conn.created_at) }}</p>
              </div>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize shrink-0"
                :class="connStatusClass(conn.status)">{{ conn.status.replace(/_/g,' ') }}</span>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No connection requests.</p>
        </div>

        <!-- Tuition Requirements -->
        <div class="card">
          <h2 class="section-title">
            Tuition requirements
            <span class="text-paper-400 font-body font-normal">({{ guardian.tuition_requirements?.length || 0 }})</span>
          </h2>
          <div v-if="guardian.tuition_requirements?.length" class="space-y-4">
            <div v-for="req in guardian.tuition_requirements" :key="req.id"
              class="border border-paper-200 rounded-lg p-3">
              <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                <p class="font-display font-semibold text-navy-900 text-sm">
                  {{ req.student_name || 'Unnamed student' }}
                </p>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize"
                  :class="req.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-paper-100 text-paper-500'">
                  {{ req.status }}
                </span>
              </div>
              <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs">
                <div v-if="req.medium">
                  <dt class="text-paper-400">Medium</dt>
                  <dd class="text-navy-800 capitalize">{{ req.medium.replace(/_/g,' ') }}</dd>
                </div>
                <div v-if="req.class_level">
                  <dt class="text-paper-400">Class</dt>
                  <dd class="text-navy-800">{{ req.class_level }}</dd>
                </div>
                <div v-if="req.city">
                  <dt class="text-paper-400">Location</dt>
                  <dd class="text-navy-800">{{ req.city }}</dd>
                </div>
                <div v-if="req.days_per_week">
                  <dt class="text-paper-400">Days/week</dt>
                  <dd class="text-navy-800">{{ req.days_per_week }}</dd>
                </div>
                <div v-if="req.salary_min || req.salary_max">
                  <dt class="text-paper-400">Budget</dt>
                  <dd class="text-navy-800">৳{{ req.salary_min || 0 }} – ৳{{ req.salary_max || 0 }}</dd>
                </div>
                <div v-if="req.preferred_tutor_gender">
                  <dt class="text-paper-400">Tutor gender</dt>
                  <dd class="text-navy-800 capitalize">{{ req.preferred_tutor_gender }}</dd>
                </div>
              </dl>
              <div v-if="req.subjects?.length" class="mt-2 flex flex-wrap gap-1">
                <span v-for="s in req.subjects" :key="s.id"
                  class="text-xs bg-navy-50 text-navy-700 px-1.5 py-0.5 rounded-pill font-semibold">{{ s.name }}</span>
              </div>
              <p v-if="req.special_notes" class="mt-2 text-xs text-paper-500 font-body italic">{{ req.special_notes }}</p>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No requirements posted.</p>
        </div>

        <!-- Shortlisted Tutors -->
        <div class="card lg:col-span-2">
          <h2 class="section-title">
            Shortlisted tutors
            <span class="text-paper-400 font-body font-normal">({{ guardian.shortlists?.length || 0 }})</span>
          </h2>
          <div v-if="guardian.shortlists?.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div v-for="sl in guardian.shortlists" :key="sl.id"
              class="flex items-center justify-between gap-3 border border-paper-200 rounded-lg px-3 py-2.5">
              <div class="min-w-0">
                <p class="font-display font-semibold text-navy-900 text-sm truncate">
                  {{ sl.tutor_profile?.user?.name || 'Unknown' }}
                </p>
                <span v-if="sl.tutor_profile?.tutor_id"
                  class="text-xs font-semibold font-display text-navy-500">
                  {{ sl.tutor_profile.tutor_id }}
                </span>
              </div>
              <RouterLink v-if="sl.tutor_profile?.id"
                :to="{ name: 'admin-tutor-detail', params: { id: sl.tutor_profile.id } }"
                class="text-xs font-semibold font-display text-navy-700 hover:text-navy-900 underline shrink-0">
                View
              </RouterLink>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No tutors shortlisted.</p>
        </div>

      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { getInitials } from '@/utils/helpers.js'

const route    = useRoute()
const guardian = ref(null)
const loading  = ref(true)

const initials = computed(() => getInitials(guardian.value?.user?.name))

onMounted(async () => {
  try {
    const { data } = await adminApi.getGuardian(route.params.id)
    guardian.value = data.data
  } finally {
    loading.value = false
  }
})

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function connStatusClass(status) {
  if (status === 'connected') return 'bg-emerald-50 text-emerald-700'
  if (status === 'pending')   return 'bg-amber-50 text-amber-700'
  if (status === 'declined')  return 'bg-red-50 text-red-700'
  return 'bg-blue-50 text-blue-700'
}
</script>

<style scoped>
.section-title {
  @apply font-display font-semibold text-navy-700 text-base mb-3 pb-2 border-b border-paper-100;
}
</style>
