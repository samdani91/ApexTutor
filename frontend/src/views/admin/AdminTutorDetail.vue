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

    <template v-else-if="tutor">
      <!-- Header card -->
      <div class="card mb-5">
        <div class="flex gap-5 items-start flex-wrap">
          <!-- Avatar -->
          <div class="w-20 h-20 rounded-xl bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden ring-2 ring-white shadow">
            <img v-if="tutor.user?.avatar_url" :src="tutor.user.avatar_url" class="w-full h-full object-cover" />
            <span v-else class="font-display font-bold text-2xl text-navy-700">{{ initials }}</span>
          </div>
          <!-- Basic info -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <h1 class="font-display font-bold text-xl text-navy-900">{{ tutor.user?.name }}</h1>
              <span v-if="tutor.tutor_id"
                class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill">
                {{ tutor.tutor_id }}
              </span>
              <span v-if="tutor.is_verified" class="badge-verified text-xs">✓ Verified</span>
              <span v-if="tutor.is_verified"
                class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-pill bg-amber-50 text-amber-700 border border-amber-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
                Locked
              </span>
            </div>
            <p class="text-sm text-paper-500 font-body">{{ tutor.user?.email }}</p>
            <p class="text-sm text-paper-500 font-body">{{ tutor.user?.phone }}</p>
            <div class="flex flex-wrap items-center gap-2 mt-2">
              <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize"
                :class="verificationClass(tutor.verification_status)">
                {{ tutor.verification_status }}
              </span>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize"
                :class="tutor.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                {{ tutor.status }}
              </span>
              <span class="text-xs text-paper-400 font-body">{{ tutor.profile_completion_percent }}% complete</span>
            </div>
          </div>
          <!-- Admin actions -->
          <div class="flex flex-col gap-2 shrink-0">
            <template v-if="tutor.verification_status === 'pending'">
              <button @click="openApprove" :disabled="acting"
                class="btn-primary text-sm py-1.5 px-4">Approve</button>
              <button @click="openReject"
                class="bg-red-600 text-white text-sm font-semibold font-display py-1.5 px-4 rounded-md hover:bg-red-700 transition-colors">
                Reject
              </button>
            </template>
            <select @change="onStatusChange" :value="statusValue"
              class="status-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
        </div>

        <!-- Rejection reason -->
        <div v-if="tutor.rejection_reason" class="mt-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 font-body">
          <span class="font-semibold font-display">Rejection reason:</span> {{ tutor.rejection_reason }}
        </div>
      </div>

      <!-- Two-column layout for sections -->
      <div class="grid lg:grid-cols-2 gap-5">

        <!-- Bio / Personal info -->
        <div class="card">
          <h2 class="section-title">Personal information</h2>
          <dl class="space-y-2 text-sm">
            <div v-if="tutor.bio">
              <dt class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide">About</dt>
              <dd class="mt-0.5 text-navy-800 font-body leading-relaxed text-justify">{{ tutor.bio }}</dd>
            </div>
            <template v-if="tutor.personal_info">
              <div v-if="tutor.personal_info.gender" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Gender</span>
                <span class="text-navy-800 font-body capitalize">{{ tutor.personal_info.gender }}</span>
              </div>
              <div v-if="tutor.personal_info.date_of_birth" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Date of birth</span>
                <span class="text-navy-800 font-body">{{ formatDate(tutor.personal_info.date_of_birth) }}</span>
              </div>
              <div v-if="tutor.personal_info.religion" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Religion</span>
                <span class="text-navy-800 font-body capitalize">{{ tutor.personal_info.religion }}</span>
              </div>
              <div v-if="tutor.personal_info.nationality" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Nationality</span>
                <span class="text-navy-800 font-body">{{ tutor.personal_info.nationality }}</span>
              </div>
              <div v-if="tutor.personal_info.additional_phone" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Alt. phone</span>
                <span class="text-navy-800 font-body">{{ tutor.personal_info.additional_phone }}</span>
              </div>
              <div v-if="tutor.personal_info.present_address" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Present address</span>
                <span class="text-navy-800 font-body">{{ tutor.personal_info.present_address }}</span>
              </div>
              <div v-if="tutor.personal_info.permanent_address" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Perm. address</span>
                <span class="text-navy-800 font-body">{{ tutor.personal_info.permanent_address }}</span>
              </div>
              <div v-if="tutor.personal_info.national_id" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">National ID</span>
                <span class="text-navy-800 font-body">{{ tutor.personal_info.national_id }}</span>
              </div>
              <div v-if="tutor.personal_info.facebook_url || tutor.personal_info.linkedin_url" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Social</span>
                <span class="text-navy-800 font-body space-x-2">
                  <a v-if="tutor.personal_info.facebook_url" :href="tutor.personal_info.facebook_url" target="_blank" class="text-blue-600 hover:underline text-xs">Facebook</a>
                  <a v-if="tutor.personal_info.linkedin_url" :href="tutor.personal_info.linkedin_url" target="_blank" class="text-blue-600 hover:underline text-xs">LinkedIn</a>
                </span>
              </div>
              <!-- Father -->
              <div class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Father's name</span>
                <span class="text-navy-800 font-body">{{ tutor.personal_info.fathers_name || 'Not given' }}</span>
              </div>
              <div class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Father's phone</span>
                <span class="text-navy-800 font-body">{{ tutor.personal_info.fathers_phone || 'Not given' }}</span>
              </div>
              <!-- Mother -->
              <div class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Mother's name</span>
                <span class="text-navy-800 font-body">{{ tutor.personal_info.mothers_name || 'Not given' }}</span>
              </div>
              <div class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Mother's phone</span>
                <span class="text-navy-800 font-body">{{ tutor.personal_info.mothers_phone || 'Not given' }}</span>
              </div>
            </template>
            <p v-else class="text-paper-400 text-xs font-body italic">No personal info submitted.</p>
          </dl>
        </div>

        <!-- Education -->
        <div class="card">
          <h2 class="section-title">Education</h2>
          <div v-if="tutor.education_entries?.length" class="space-y-3">
            <div v-for="edu in tutor.education_entries" :key="edu.id"
              class="border-l-2 border-gold-400 pl-3">
              <p class="font-display font-semibold text-navy-900 text-sm">{{ edu.degree_title }}</p>
              <p class="text-xs text-paper-500 font-body">{{ edu.institute_name }}</p>
              <p v-if="edu.year_of_passing" class="text-xs text-paper-400 font-body">{{ edu.year_of_passing }}</p>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No education entries.</p>
        </div>

        <!-- Tuition Preferences -->
        <div class="card">
          <h2 class="section-title">Tuition preferences</h2>
          <template v-if="tutor.tuition_preference">
            <!-- Key–value rows -->
            <div class="space-y-2 text-sm">
              <div v-if="tutor.tuition_preference.city" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">City</span>
                <span class="text-navy-800 font-body">{{ tutor.tuition_preference.city }}</span>
              </div>
              <div v-if="tutor.tuition_preference.total_experience_years != null" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Experience</span>
                <span class="text-navy-800 font-body">
                  {{ tutor.tuition_preference.total_experience_years >= 21 ? '20+' : tutor.tuition_preference.total_experience_years }}
                  year{{ tutor.tuition_preference.total_experience_years === 1 ? '' : 's' }}
                </span>
              </div>
              <div v-if="tutor.tuition_preference.days_per_week" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Days/week</span>
                <span class="text-navy-800 font-body">{{ tutor.tuition_preference.days_per_week }}</span>
              </div>
              <div v-if="tutor.tuition_preference.hours_per_day" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Hours/session</span>
                <span class="text-navy-800 font-body">{{ tutor.tuition_preference.hours_per_day }} hr</span>
              </div>
              <div v-if="tutor.tuition_preference.preferred_time?.length" class="flex gap-2 items-start">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0 mt-0.5">Preferred time</span>
                <div class="flex flex-wrap gap-1.5">
                  <span v-for="t in tutor.tuition_preference.preferred_time" :key="t"
                    class="text-xs bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill font-semibold font-display">
                    {{ TIME_MAP[t] || t }}
                  </span>
                </div>
              </div>
              <div v-if="formatArray(tutor.tuition_preference.tutoring_styles)" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Style</span>
                <span class="text-navy-800 font-body capitalize">{{ formatLabels(tutor.tuition_preference.tutoring_styles) }}</span>
              </div>
              <div v-if="formatArray(tutor.tuition_preference.place_of_tutoring)" class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Place</span>
                <span class="text-navy-800 font-body capitalize">{{ formatLabels(tutor.tuition_preference.place_of_tutoring) }}</span>
              </div>
              <div class="flex gap-2">
                <span class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-32 shrink-0">Salary range</span>
                <span class="text-navy-800 font-body">৳{{ tutor.tuition_preference.expected_salary_min || 0 }} – ৳{{ tutor.tuition_preference.expected_salary_max || 0 }}</span>
              </div>
            </div>

            <!-- Available days chips -->
            <div v-if="tutor.tuition_preference.days?.length" class="mt-3">
              <p class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1.5">Available days</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="d in tutor.tuition_preference.days" :key="d.day"
                  class="text-xs bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill font-semibold uppercase">
                  {{ d.day }}
                </span>
              </div>
            </div>

            <!-- Preferred subjects chips -->
            <div v-if="tutor.tuition_preference.subjects?.length" class="mt-3">
              <p class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1.5">Subjects</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="s in tutor.tuition_preference.subjects" :key="s.id"
                  class="text-xs bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill font-semibold">{{ s.name }}</span>
              </div>
            </div>

            <!-- Preferred locations chips -->
            <div v-if="tutor.tuition_preference.locations?.length" class="mt-3">
              <p class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1.5">Preferred locations</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="loc in tutor.tuition_preference.locations" :key="loc.id"
                  class="text-xs bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill font-semibold font-display">
                  {{ loc.area_name }}
                </span>
              </div>
            </div>

            <!-- Preferred curricula chips -->
            <div v-if="tutor.tuition_preference.preferred_curricula?.length" class="mt-3">
              <p class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1.5">Curriculum</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="c in tutor.tuition_preference.preferred_curricula" :key="c"
                  class="text-xs bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill font-semibold font-display capitalize">
                  {{ c.replace(/_/g,' ') }}
                </span>
              </div>
            </div>

            <!-- Preferred classes chips -->
            <div v-if="tutor.tuition_preference.preferred_classes?.length" class="mt-3">
              <p class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1.5">Preferred classes</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="cls in tutor.tuition_preference.preferred_classes" :key="cls"
                  class="text-xs bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill font-semibold font-display">
                  {{ cls.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                </span>
              </div>
            </div>

            <!-- Tutoring method description -->
            <div v-if="tutor.tuition_preference.tutoring_method_description" class="mt-3">
              <p class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1">Tutoring method</p>
              <p class="text-sm font-body text-navy-800 leading-relaxed text-justify">{{ tutor.tuition_preference.tutoring_method_description }}</p>
            </div>

            <!-- Experience details -->
            <div v-if="tutor.tuition_preference.experience_details" class="mt-3">
              <p class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1">Experience details</p>
              <p class="text-sm font-body text-navy-800 leading-relaxed text-justify">{{ tutor.tuition_preference.experience_details }}</p>
            </div>
          </template>
          <p v-else class="text-paper-400 text-xs font-body italic">No preferences saved.</p>
        </div>

        <!-- Documents -->
        <div class="card">
          <h2 class="section-title">Documents</h2>
          <div v-if="tutor.documents?.length" class="space-y-2">
            <div v-for="doc in tutor.documents" :key="doc.id"
              class="flex items-center justify-between gap-3 p-2.5 rounded-lg border border-paper-200 bg-paper-50">
              <div class="min-w-0">
                <p class="text-xs font-semibold font-display text-navy-800 capitalize">
                  {{ doc.type.replace(/_/g, ' ') }}
                </p>
                <p class="text-xs text-paper-400 font-body">{{ formatSize(doc.file_size) }}</p>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <span v-if="doc.review_status === 'approved'"
                  class="text-xs font-semibold px-2 py-0.5 rounded-pill bg-emerald-50 text-emerald-700">
                  Approved
                </span>
                <span v-else-if="doc.review_status === 'rejected'"
                  class="text-xs font-semibold px-2 py-0.5 rounded-pill bg-red-50 text-red-700">
                  Rejected
                </span>
                <a :href="doc.file_url" target="_blank"
                  class="text-xs font-semibold font-display text-navy-700 hover:text-navy-900 underline">
                  View
                </a>
              </div>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No documents uploaded.</p>
        </div>

        <!-- Teaching Videos -->
        <div class="card lg:col-span-2">
          <h2 class="section-title">
            Teaching videos
            <span v-if="tutor.teaching_videos?.length" class="text-paper-400 font-body font-normal">({{ tutor.teaching_videos.length }})</span>
          </h2>
          <div v-if="tutor.teaching_videos?.length" class="space-y-5">
            <div v-for="vid in tutor.teaching_videos" :key="vid.id">
              <video :src="vid.file_url" controls
                class="w-full rounded-lg bg-black max-h-60" preload="metadata" />
              <div class="mt-2">
                <p v-if="vid.title" class="font-display font-semibold text-sm text-navy-900 mb-1.5">{{ vid.title }}</p>
                <div class="flex items-center flex-wrap gap-2">
                  <span v-if="vid.subject" class="text-xs bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill font-body">{{ vid.subject }}</span>
                  <span v-if="vid.class_level" class="text-xs bg-navy-50 text-navy-700 border border-navy-100 px-2 py-0.5 rounded-pill font-body">Class {{ vid.class_level }}</span>
                  <span v-if="vid.medium" class="text-xs bg-paper-100 text-paper-600 border border-paper-200 px-2 py-0.5 rounded-pill font-body capitalize">{{ vid.medium.replace('_', ' & ') }}</span>
                  <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize"
                    :class="vid.review_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                    {{ vid.review_status }}
                  </span>
                  <span class="text-xs text-paper-400 font-body">{{ formatSize(vid.file_size) }}</span>
                </div>
              </div>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No teaching videos uploaded.</p>
        </div>

        <!-- Connection Requests -->
        <div class="card">
          <h2 class="section-title">Connection requests <span class="text-paper-400 font-body font-normal">({{ tutor.connection_requests?.length || 0 }})</span></h2>
          <div v-if="tutor.connection_requests?.length" class="divide-y divide-paper-100">
            <div v-for="conn in tutor.connection_requests" :key="conn.id" class="py-2.5 flex items-center justify-between">
              <div>
                <p class="text-sm font-display font-semibold text-navy-900">{{ conn.guardian_profile?.user?.name }}</p>
                <p class="text-xs text-paper-400 font-body">{{ conn.guardian_profile?.user?.email }}</p>
              </div>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize"
                :class="connStatusClass(conn.status)">{{ conn.status.replace(/_/g,' ') }}</span>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No connection requests.</p>
        </div>

        <!-- Reviews -->
        <div class="card">
          <h2 class="section-title">Reviews <span class="text-paper-400 font-body font-normal">({{ tutor.reviews?.length || 0 }})</span></h2>
          <div v-if="tutor.reviews?.length" class="divide-y divide-paper-100">
            <div v-for="rev in tutor.reviews" :key="rev.id" class="py-2.5">
              <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-display font-semibold text-navy-900">{{ rev.guardian_profile?.user?.name }}</p>
                <span class="text-xs text-gold-500 font-semibold">★ {{ rev.rating }}</span>
              </div>
              <p class="text-xs text-paper-600 font-body">{{ rev.review_text }}</p>
            </div>
          </div>
          <p v-else class="text-paper-400 text-xs font-body italic">No reviews yet.</p>
        </div>

      </div>
    </template>

    <!-- Approve confirm -->
    <AdminConfirmDialog
      :show="showApproveDialog"
      title="Approve tutor?"
      :message="tutor ? `Approve ${tutor.user?.name} and mark their profile as verified?` : ''"
      confirm-label="Yes, approve"
      @confirm="confirmApprove"
      @cancel="showApproveDialog = false"
    />

    <!-- Reject confirm -->
    <AdminConfirmDialog
      :show="showRejectDialog"
      title="Reject tutor?"
      :message="tutor ? `Reject ${tutor.user?.name}'s verification request.` : ''"
      confirm-label="Reject"
      danger
      with-input
      input-label="Rejection reason"
      input-placeholder="Explain why this profile is being rejected…"
      @confirm="confirmReject"
      @cancel="showRejectDialog = false"
    />

    <!-- Status change confirm -->
    <AdminConfirmDialog
      :show="!!pendingStatus"
      title="Update account status?"
      :message="pendingStatus ? `Change ${tutor?.user?.name}'s status to &quot;${pendingStatus}&quot;?` : ''"
      confirm-label="Yes, update"
      @confirm="confirmStatusChange"
      @cancel="cancelStatusChange"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import { getInitials } from '@/utils/helpers.js'
import { PREFERRED_TIMES } from '@/utils/constants.js'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'

const TIME_MAP = Object.fromEntries(PREFERRED_TIMES.map(t => [t.value, `${t.label} (${t.hint})`]))

const route  = useRoute()
const tutor  = ref(null)
const loading = ref(true)
const acting  = ref(false)
const statusValue      = ref('active')
const showApproveDialog = ref(false)
const showRejectDialog  = ref(false)
const pendingStatus     = ref(null)

const initials = computed(() => getInitials(tutor.value?.user?.name))

onMounted(async () => {
  try {
    const { data } = await adminApi.getTutor(route.params.id)
    tutor.value = data.data
    statusValue.value = tutor.value.status || 'active'
  } finally {
    loading.value = false
  }
})

function verificationClass(status) {
  if (status === 'approved') return 'bg-emerald-50 text-emerald-700'
  if (status === 'pending')  return 'bg-amber-50 text-amber-700'
  if (status === 'rejected') return 'bg-red-50 text-red-700'
  return 'bg-blue-50 text-blue-700'
}

function connStatusClass(status) {
  if (status === 'connected') return 'bg-emerald-50 text-emerald-700'
  if (status === 'pending')   return 'bg-amber-50 text-amber-700'
  if (status === 'declined')  return 'bg-red-50 text-red-700'
  return 'bg-blue-50 text-blue-700'
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatSize(bytes) {
  if (!bytes) return ''
  return bytes < 1048576 ? `${(bytes / 1024).toFixed(1)} KB` : `${(bytes / 1048576).toFixed(1)} MB`
}

function formatArray(arr) {
  if (!arr?.length) return null
  return Array.isArray(arr) ? arr.join(', ') : arr
}

function formatLabels(arr) {
  if (!arr) return ''
  const items = Array.isArray(arr) ? arr : [arr]
  return items.map(v => v.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())).join(', ')
}

function openApprove() { showApproveDialog.value = true }
function openReject()  { showRejectDialog.value  = true }

async function confirmApprove() {
  showApproveDialog.value = false
  acting.value = true
  try {
    await adminApi.approveTutor(tutor.value.id)
    tutor.value.verification_status = 'approved'
    tutor.value.is_verified = true
    toast.success('Tutor approved and verified.')
  } catch {
    toast.error('Failed to approve.')
  } finally {
    acting.value = false
  }
}

async function confirmReject(reason) {
  showRejectDialog.value = false
  acting.value = true
  try {
    await adminApi.rejectTutor(tutor.value.id, { rejection_reason: reason })
    tutor.value.verification_status = 'rejected'
    tutor.value.rejection_reason = reason
    toast.success('Tutor rejected.')
  } catch {
    toast.error('Failed to reject.')
  } finally {
    acting.value = false
  }
}

function onStatusChange(e) {
  pendingStatus.value = e.target.value
  e.target.value = statusValue.value  // reset select visually until confirmed
}

async function confirmStatusChange() {
  const newStatus = pendingStatus.value
  pendingStatus.value = null
  try {
    await adminApi.updateTutorStatus(tutor.value.id, { status: newStatus })
    statusValue.value = newStatus
    tutor.value.status = newStatus
    toast.success('Status updated.')
  } catch {
    toast.error('Failed to update status.')
  }
}

function cancelStatusChange() {
  pendingStatus.value = null
}
</script>

<style scoped>
.section-title {
  @apply font-display font-semibold text-navy-700 text-base mb-3 pb-2 border-b border-paper-100;
}
</style>
