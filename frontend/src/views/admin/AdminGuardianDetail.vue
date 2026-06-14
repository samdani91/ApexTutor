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

    <!-- Confirm dialogs -->
    <AdminConfirmDialog
      :show="!!avatarReplaceFile"
      title="Replace Profile Photo?"
      :message="`Replace ${guardian?.user?.name}'s current photo?`"
      confirm-label="Yes, Replace"
      @confirm="confirmAvatarReplace"
      @cancel="avatarReplaceFile = null"
    />
    <AdminConfirmDialog
      :show="avatarRemoveConfirm"
      title="Remove Profile Photo?"
      :message="`Remove ${guardian?.user?.name}'s profile photo?`"
      confirm-label="Remove Photo"
      :danger="true"
      @confirm="confirmAvatarRemove"
      @cancel="avatarRemoveConfirm = false"
    />
    <AdminConfirmDialog
      :show="nidRemoveConfirm"
      title="Remove NID Document?"
      :message="`Delete ${guardian?.user?.name}'s NID document? This cannot be undone.`"
      confirm-label="Delete Document"
      :danger="true"
      @confirm="confirmNidRemove"
      @cancel="nidRemoveConfirm = false"
    />
    <AdminConfirmDialog
      :show="!!nidReplaceFile"
      title="Replace NID Document?"
      :message="`Upload a new NID document for ${guardian?.user?.name}?`"
      confirm-label="Yes, Upload"
      @confirm="confirmNidReplace"
      @cancel="nidReplaceFile = null"
    />
    <AdminConfirmDialog
      :show="saveConfirmOpen"
      title="Save Guardian Profile?"
      message="Apply these changes to the guardian's account directly?"
      confirm-label="Save Changes"
      @confirm="saveConfirmOpen = false; saveEdit()"
      @cancel="saveConfirmOpen = false"
    />
    <AdminConfirmDialog
      :show="!!statusConfirmTarget"
      :title="statusConfirmTarget?.isActive ? 'Reactivate Account?' : 'Suspend Account?'"
      :message="statusConfirmTarget?.isActive
        ? `Reactivate ${guardian?.user?.name}'s account? They will regain full platform access.`
        : `Suspend ${guardian?.user?.name}'s account? They will lose access until reactivated.`"
      :confirm-label="statusConfirmTarget?.isActive ? 'Reactivate' : 'Suspend'"
      :danger="!statusConfirmTarget?.isActive"
      @confirm="doToggleStatus"
      @cancel="statusConfirmTarget = null"
    />

    <template v-if="!loading && guardian">

      <!-- Header card -->
      <div class="card mb-5">
        <div class="grid gap-4 lg:grid-cols-[auto_minmax(0,1fr)_auto] lg:items-start">

          <!-- Avatar -->
          <div class="flex flex-col items-center gap-2 mx-auto sm:mx-0">
            <div class="w-20 h-20 rounded-xl bg-navy-100 flex items-center justify-center shrink-0 overflow-hidden ring-2 ring-white shadow relative">
              <img v-if="guardian.user?.avatar_url" :src="guardian.user.avatar_url" class="w-full h-full object-cover" />
              <span v-else class="font-display font-bold text-2xl text-navy-700">{{ initials }}</span>
              <span v-if="guardian.user?.pending_avatar_url"
                class="absolute top-0.5 right-0.5 bg-amber-400 text-amber-900 text-[8px] font-bold font-display px-1 py-0.5 rounded leading-tight">
                Pending
              </span>
            </div>
            <div v-if="editing" class="flex gap-1">
              <label class="cursor-pointer inline-flex items-center gap-1 text-xs font-semibold font-display px-2 py-1 rounded-sm border border-paper-300 bg-white text-navy-700 hover:bg-navy-50 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4-4m0 0L8 12m4-4v12"/>
                </svg>
                Replace
                <input type="file" class="hidden" accept="image/jpeg,image/png,image/webp" @change="onAvatarSelected" />
              </label>
              <button v-if="guardian.user?.avatar_url" @click="avatarRemoveConfirm = true"
                class="inline-flex items-center gap-1 text-xs font-semibold font-display px-2 py-1 rounded-sm bg-red-600 text-white hover:bg-red-700 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Remove
              </button>
            </div>
          </div>

          <!-- Identity -->
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <h1 class="font-display font-bold text-xl text-navy-900 break-words">{{ guardian.user?.name }}</h1>
              <span v-if="guardian.guardian_id"
                class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill">
                {{ guardian.guardian_id }}
              </span>
              <span class="status-chip bg-blue-50 text-blue-700 capitalize">{{ guardian.account_type || 'guardian' }}</span>
              <span class="status-chip" :class="guardian.user?.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                {{ guardian.user?.is_active ? 'Active' : 'Suspended' }}
              </span>
            </div>
            <p class="text-sm text-paper-500 font-body break-all">{{ guardian.user?.email }}</p>
            <p class="text-sm text-paper-500 font-body">{{ guardian.user?.phone }}</p>
            <p v-if="guardian.user?.address" class="text-sm text-paper-500 font-body">{{ guardian.user.address }}</p>
            <p class="text-xs text-paper-400 font-body mt-1">Member since {{ formatDate(guardian.user?.created_at) }}</p>
          </div>

          <!-- Admin actions -->
          <div class="flex flex-col gap-2 w-full lg:w-36">
            <button @click="toggleEdit"
              class="text-sm font-semibold font-display px-4 py-1.5 rounded-md transition-colors text-center w-full"
              :class="editing ? 'bg-paper-100 border border-paper-300 text-paper-600' : 'bg-navy-700 text-white hover:bg-navy-900'">
              {{ editing ? 'Cancel Edit' : 'Edit Profile' }}
            </button>
            <button v-if="guardian.user?.is_active" @click="statusConfirmTarget = { isActive: false }"
              :disabled="statusSaving"
              class="text-sm font-semibold font-display px-4 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700 transition-colors disabled:opacity-50 w-full">
              {{ statusSaving ? 'Saving…' : 'Suspend' }}
            </button>
            <button v-else @click="statusConfirmTarget = { isActive: true }"
              :disabled="statusSaving"
              class="text-sm font-semibold font-display px-4 py-1.5 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 transition-colors disabled:opacity-50 w-full">
              {{ statusSaving ? 'Saving…' : 'Reactivate' }}
            </button>
          </div>
        </div>

        <!-- Inline edit form -->
        <div v-if="editing" class="mt-5 pt-5 border-t border-paper-200 space-y-4">
          <p class="text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Edit profile</p>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Full name</label>
              <input v-model="editForm.user.name" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Email</label>
              <input v-model="editForm.user.email" type="email" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Phone</label>
              <input v-model="editForm.user.phone" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Address</label>
              <input v-model="editForm.user.address" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Occupation</label>
              <input v-model="editForm.profile.occupation" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Relationship to student</label>
              <input v-model="editForm.profile.relationship_to_student" class="input text-sm w-full" placeholder="e.g. parent, sibling" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">NID number</label>
              <input v-model="editForm.profile.nid_number" class="input text-sm w-full" />
            </div>
            <div>
              <label class="block text-xs font-semibold font-display text-paper-500 mb-1">Account type</label>
              <select v-model="editForm.profile.account_type" class="input text-sm w-full">
                <option value="guardian">Guardian</option>
                <option value="student">Student</option>
              </select>
            </div>
          </div>
          <div class="flex gap-3 pt-1">
            <button @click="saveConfirmOpen = true" :disabled="editSaving"
              class="text-sm font-semibold font-display px-5 py-2 rounded-md bg-navy-700 text-white hover:bg-navy-900 transition-colors disabled:opacity-50">
              {{ editSaving ? 'Saving…' : 'Save Changes' }}
            </button>
            <button @click="editing = false"
              class="text-sm font-semibold font-display px-4 py-2 rounded-md border border-paper-300 text-paper-600 hover:bg-paper-100 transition-colors">
              Cancel
            </button>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-5">

        <!-- Account information -->
        <div class="card">
          <h2 class="section-title">Account information</h2>
          <div class="responsive-info-grid">
            <div class="info-row">
              <span class="info-label">Full name</span>
              <span class="info-value">{{ guardian.user?.name || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Email</span>
              <span class="info-value break-all">{{ guardian.user?.email || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Phone</span>
              <span class="info-value">{{ guardian.user?.phone || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Address</span>
              <span class="info-value">{{ guardian.user?.address || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Account type</span>
              <span class="info-value capitalize">{{ guardian.account_type || 'guardian' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Occupation</span>
              <span class="info-value">{{ guardian.occupation || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Relationship</span>
              <span class="info-value capitalize">{{ guardian.relationship_to_student || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">NID number</span>
              <span class="info-value">{{ guardian.nid_number || '—' }}</span>
            </div>
          </div>
        </div>

        <!-- NID document -->
        <div class="card">
          <h2 class="section-title">NID document</h2>

          <!-- Document exists -->
          <div v-if="guardian.nid_document_url" class="space-y-3">
            <div class="rounded-lg border border-paper-200 bg-paper-50 p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div class="flex items-center gap-2 min-w-0">
                <svg class="w-8 h-8 shrink-0 text-navy-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <span class="text-sm font-semibold font-display text-navy-800 truncate">NID Document</span>
              </div>
              <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <button @click="viewNid"
                  class="inline-flex items-center gap-1.5 border border-navy-200 bg-navy-50 px-3 py-1.5 rounded-sm text-xs font-semibold font-display text-navy-700 hover:bg-navy-100 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  View
                </button>
              </div>
            </div>
            <div class="flex gap-2">
              <label class="cursor-pointer inline-flex items-center gap-1.5 text-xs font-semibold font-display px-3 py-1.5 rounded-sm border border-paper-300 bg-white text-navy-700 hover:bg-navy-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4-4m0 0L8 12m4-4v12"/>
                </svg>
                Replace
                <input type="file" class="hidden" accept="image/jpeg,image/png,application/pdf" @change="onNidSelected" />
              </label>
              <button @click="nidRemoveConfirm = true" :disabled="nidSaving"
                class="inline-flex items-center gap-1.5 text-xs font-semibold font-display px-3 py-1.5 rounded-sm bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                </svg>
                Delete
              </button>
            </div>
          </div>

          <!-- No document -->
          <div v-else class="space-y-3">
            <p class="empty-state">No NID document uploaded.</p>
            <label class="cursor-pointer inline-flex items-center gap-1.5 text-xs font-semibold font-display px-3 py-1.5 rounded-sm border border-paper-300 bg-white text-navy-700 hover:bg-navy-50 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4-4m0 0L8 12m4-4v12"/>
              </svg>
              Upload NID document
              <input type="file" class="hidden" accept="image/jpeg,image/png,application/pdf" @change="onNidSelected" />
            </label>
          </div>
        </div>

        <!-- Connection requests -->
        <div class="card">
          <h2 class="section-title">
            Connection requests
            <span class="text-paper-400 font-body font-normal">({{ guardian.connection_requests?.length || 0 }})</span>
          </h2>
          <div v-if="guardian.connection_requests?.length" class="space-y-2">
            <div v-for="conn in guardian.connection_requests" :key="conn.id"
              class="rounded-lg border border-paper-200 bg-paper-50 p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-display font-semibold text-navy-900 truncate">
                  {{ conn.tutor_profile?.user?.name || 'Unknown tutor' }}
                </p>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span v-if="conn.tutor_profile?.tutor_id"
                    class="text-xs font-semibold font-display text-navy-500 bg-navy-50 border border-navy-200 px-1.5 py-0 rounded-pill">
                    {{ conn.tutor_profile.tutor_id }}
                  </span>
                  <RouterLink v-if="conn.tutor_profile?.tutor_id"
                    :to="{ name: 'admin-tutor-detail', params: { tutorId: conn.tutor_profile.tutor_id } }"
                    class="text-xs text-navy-700 hover:underline font-semibold font-display">View tutor</RouterLink>
                </div>
                <p class="text-xs text-paper-400 font-body mt-0.5">{{ formatDate(conn.created_at) }}</p>
              </div>
              <span class="status-chip capitalize shrink-0" :class="connStatusClass(conn.status)">
                {{ conn.status.replace(/_/g,' ') }}
              </span>
            </div>
          </div>
          <p v-else class="empty-state">No connection requests.</p>
        </div>

        <!-- Shortlisted tutors -->
        <div class="card">
          <h2 class="section-title">
            Shortlisted tutors
            <span class="text-paper-400 font-body font-normal">({{ guardian.shortlists?.length || 0 }})</span>
          </h2>
          <div v-if="guardian.shortlists?.length" class="grid gap-2">
            <div v-for="sl in guardian.shortlists" :key="sl.id"
              class="flex items-center justify-between gap-3 rounded-lg border border-paper-200 bg-paper-50 px-3 py-2.5">
              <div class="min-w-0">
                <p class="font-display font-semibold text-navy-900 text-sm truncate">
                  {{ sl.tutor_profile?.user?.name || 'Unknown' }}
                </p>
                <span v-if="sl.tutor_profile?.tutor_id"
                  class="text-xs font-semibold font-display text-navy-500">
                  {{ sl.tutor_profile.tutor_id }}
                </span>
              </div>
              <RouterLink v-if="sl.tutor_profile?.tutor_id"
                :to="{ name: 'admin-tutor-detail', params: { tutorId: sl.tutor_profile.tutor_id } }"
                class="text-xs font-semibold font-display text-navy-700 hover:text-navy-900 underline shrink-0">
                View
              </RouterLink>
            </div>
          </div>
          <p v-else class="empty-state">No tutors shortlisted.</p>
        </div>

        <!-- Platform Feedback — full width -->
        <div class="card lg:col-span-2">
          <h2 class="section-title">Platform Feedback</h2>
          <div v-if="feedback" class="flex flex-col sm:flex-row sm:items-start gap-5 rounded-lg border border-paper-200 bg-paper-50 p-5">
            <!-- Quote -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-body text-navy-800 italic leading-relaxed">"{{ feedback.quote }}"</p>
              <p class="mt-2 text-xs text-paper-400 font-body">Last updated {{ formatDate(feedback.updated_at) }}</p>
            </div>
            <!-- Meta -->
            <div class="flex flex-col gap-2 shrink-0 sm:items-end">
              <span class="text-xs font-semibold font-display px-2.5 py-1 rounded-pill border self-start sm:self-auto"
                :class="feedback.moderation_status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                      : feedback.moderation_status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200'
                      : 'bg-gold-50 text-gold-700 border-gold-200'">
                {{ feedback.moderation_status }}
              </span>
              <p class="text-xs text-paper-400 font-body">Showing as:</p>
              <p class="text-sm font-semibold font-display text-navy-700">{{ feedback.display_label }}</p>
            </div>
          </div>
          <p v-else class="empty-state">No platform feedback submitted.</p>
        </div>

      </div>
    </template>

    <!-- NID document preview modal -->
    <Teleport to="body">
      <Transition name="dialog">
        <div v-if="previewNid" class="fixed inset-0 z-[240] flex items-center justify-center px-4"
          @click.self="previewNid = null">
          <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="previewNid = null" />
          <div class="relative w-full max-w-2xl bg-white rounded-sm shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-paper-200">
              <div class="min-w-0">
                <p class="font-display font-semibold text-sm text-navy-900">NID Document</p>
                <p class="text-xs text-paper-400 font-body mt-0.5">{{ guardian?.user?.name }}</p>
              </div>
              <button @click="previewNid = null"
                class="ml-3 shrink-0 w-8 h-8 flex items-center justify-center rounded-md text-paper-400 hover:bg-paper-100 hover:text-navy-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-paper-50 min-h-[200px] max-h-[70vh] overflow-auto">
              <img :src="previewNid" alt="NID Document"
                class="max-w-full max-h-[60vh] object-contain rounded-sm shadow-sm" />
            </div>
            <div class="px-5 py-3 border-t border-paper-200 flex justify-end">
              <a :href="previewNid" target="_blank" rel="noopener"
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
import { ref, reactive, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { feedbackApi } from '@/api/feedback.js'
import { getInitials } from '@/utils/helpers.js'
import { toast } from 'vue-sonner'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'

const route               = useRoute()
const guardian            = ref(null)
const feedback            = ref(null)
const loading             = ref(true)
const editing             = ref(false)
const editSaving          = ref(false)
const saveConfirmOpen     = ref(false)
const statusSaving        = ref(false)
const statusConfirmTarget = ref(null)

const avatarReplaceFile   = ref(null)
const avatarRemoveConfirm = ref(false)
const avatarSaving        = ref(false)

const nidReplaceFile   = ref(null)
const nidRemoveConfirm = ref(false)
const nidSaving        = ref(false)
const previewNid       = ref(null)

const editForm = reactive({
  user:    { name: '', email: '', phone: '', address: '' },
  profile: { occupation: '', relationship_to_student: '', nid_number: '', account_type: '' },
})

const initials = computed(() => getInitials(guardian.value?.user?.name))

onMounted(async () => {
  try {
    const { data } = await adminApi.getGuardian(route.params.guardianId)
    guardian.value = data.data
    const fbRes = await feedbackApi.adminGetUserFeedback(guardian.value.user.id).catch(() => null)
    feedback.value = fbRes?.data?.data ?? null
  } finally {
    loading.value = false
  }
})

function toggleEdit() {
  if (editing.value) { editing.value = false; return }
  const g = guardian.value
  Object.assign(editForm.user, {
    name: g.user?.name ?? '',
    email: g.user?.email ?? '',
    phone: g.user?.phone ?? '',
    address: g.user?.address ?? '',
  })
  Object.assign(editForm.profile, {
    occupation: g.occupation ?? '',
    relationship_to_student: g.relationship_to_student ?? '',
    nid_number: g.nid_number ?? '',
    account_type: g.account_type ?? 'guardian',
  })
  editing.value = true
}

async function saveEdit() {
  editSaving.value = true
  try {
    await adminApi.updateGuardian(route.params.guardianId, { ...editForm })
    const { data } = await adminApi.getGuardian(route.params.guardianId)
    guardian.value = data.data
    editing.value = false
    toast.success('Guardian profile updated.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not save changes.')
  } finally {
    editSaving.value = false
  }
}

async function doToggleStatus() {
  const { isActive } = statusConfirmTarget.value
  statusConfirmTarget.value = null
  statusSaving.value = true
  try {
    await adminApi.updateGuardianStatus(route.params.guardianId, { is_active: isActive })
    guardian.value.user.is_active = isActive
    toast.success(isActive ? 'Guardian reactivated.' : 'Guardian suspended.')
  } catch {
    toast.error('Could not update status.')
  } finally {
    statusSaving.value = false
  }
}

// Avatar
function onAvatarSelected(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (file) avatarReplaceFile.value = file
}

async function confirmAvatarReplace() {
  const file = avatarReplaceFile.value
  avatarReplaceFile.value = null
  avatarSaving.value = true
  try {
    const fd = new FormData()
    fd.append('avatar', file)
    const { data } = await adminApi.replaceUserAvatar(guardian.value.user.id, fd)
    guardian.value.user.avatar_url = data.avatar_url
    guardian.value.user.pending_avatar_url = null
    toast.success('Profile photo updated.')
  } catch {
    toast.error('Failed to update photo.')
  } finally {
    avatarSaving.value = false
  }
}

async function confirmAvatarRemove() {
  avatarRemoveConfirm.value = false
  avatarSaving.value = true
  try {
    await adminApi.removeUserAvatar(guardian.value.user.id)
    guardian.value.user.avatar_url = null
    guardian.value.user.pending_avatar_url = null
    toast.success('Profile photo removed.')
  } catch {
    toast.error('Failed to remove photo.')
  } finally {
    avatarSaving.value = false
  }
}

// NID document
function viewNid() {
  const url = guardian.value?.nid_document_url
  if (!url) return
  if (url.toLowerCase().endsWith('.pdf')) {
    window.open(url, '_blank', 'noopener')
  } else {
    previewNid.value = url
  }
}

function onNidSelected(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (file) nidReplaceFile.value = file
}

async function confirmNidReplace() {
  const file = nidReplaceFile.value
  nidReplaceFile.value = null
  nidSaving.value = true
  try {
    const fd = new FormData()
    fd.append('nid_document', file)
    const { data } = await adminApi.uploadGuardianNid(route.params.guardianId, fd)
    guardian.value.nid_document_url = data.data.nid_document_url
    toast.success('NID document updated.')
  } catch {
    toast.error('Failed to upload NID document.')
  } finally {
    nidSaving.value = false
  }
}

async function confirmNidRemove() {
  nidRemoveConfirm.value = false
  nidSaving.value = true
  try {
    await adminApi.deleteGuardianNid(route.params.guardianId)
    guardian.value.nid_document_url = null
    toast.success('NID document removed.')
  } catch {
    toast.error('Failed to remove NID document.')
  } finally {
    nidSaving.value = false
  }
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function connStatusClass(status) {
  if (status === 'confirmed') return 'bg-emerald-50 text-emerald-700'
  if (status === 'pending')   return 'bg-amber-50 text-amber-700'
  if (status === 'declined')  return 'bg-red-50 text-red-700'
  return 'bg-blue-50 text-blue-700'
}
</script>

<style scoped>
.section-title {
  @apply font-display font-semibold text-navy-700 text-base mb-4;
}

.status-chip {
  @apply text-xs font-semibold px-2 py-0.5 rounded-pill font-display whitespace-nowrap;
}

.responsive-info-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 gap-2;
}

.info-row {
  @apply rounded-lg border border-paper-200 bg-paper-50 px-3 py-2.5;
}

.info-label {
  @apply block text-xs text-paper-400 font-display font-semibold uppercase tracking-wide mb-1;
}

.info-value {
  @apply block text-sm text-navy-800 font-body leading-relaxed break-words;
}

.chip {
  @apply text-xs bg-navy-50 text-navy-700 border border-navy-200 px-2 py-0.5 rounded-pill font-semibold font-display;
}

.empty-state {
  @apply text-paper-400 text-xs font-body italic rounded-lg border border-dashed border-paper-200 bg-paper-50 px-3 py-3;
}

.dialog-enter-active { transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.dialog-leave-active { transition: all 0.15s ease-in; }
.dialog-enter-from   { opacity: 0; transform: scale(0.88) translateY(8px); }
.dialog-leave-to     { opacity: 0; transform: scale(0.95) translateY(4px); }
</style>
