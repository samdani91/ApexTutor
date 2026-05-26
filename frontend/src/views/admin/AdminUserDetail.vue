<template>
  <div>
    <RouterLink to="/admin/users" class="inline-flex items-center gap-1.5 text-sm font-semibold font-display text-navy-700 hover:text-navy-900 mb-5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
      </svg>
      Back to users
    </RouterLink>

    <div v-if="loading" class="text-paper-500 font-body py-12 text-center">Loading…</div>

    <template v-else-if="admin">
      <div class="card max-w-lg">
        <!-- Header -->
        <div class="flex items-start gap-5 mb-6 pb-5 border-b border-paper-100">
          <div class="w-16 h-16 rounded-xl bg-navy-700 flex items-center justify-center shrink-0 overflow-hidden ring-2 ring-white shadow">
            <img v-if="admin.avatar_url" :src="admin.avatar_url" class="w-full h-full object-cover" alt="" />
            <span v-else class="font-display font-bold text-xl text-white">{{ initials }}</span>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <h1 class="font-display font-bold text-xl text-navy-900">{{ admin.name }}</h1>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize"
                :class="admin.role === 'super_admin' ? 'bg-gold-50 text-gold-700 border border-gold-200' : 'bg-navy-50 text-navy-700 border border-navy-200'">
                {{ admin.role === 'super_admin' ? 'Super Admin' : 'Admin' }}
              </span>
            </div>
            <p class="text-sm text-paper-500 font-body">{{ admin.email }}</p>
            <span class="inline-block mt-1.5 text-xs font-semibold px-2 py-0.5 rounded-pill"
              :class="admin.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
              {{ admin.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>

        <!-- Details -->
        <dl class="space-y-3 text-sm">
          <div class="flex gap-3">
            <dt class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-28 shrink-0 pt-0.5">Mobile</dt>
            <dd class="text-navy-800 font-body">{{ admin.phone || '—' }}</dd>
          </div>
          <div class="flex gap-3">
            <dt class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-28 shrink-0 pt-0.5">Address</dt>
            <dd class="text-navy-800 font-body leading-relaxed">{{ admin.address || '—' }}</dd>
          </div>
          <div class="flex gap-3">
            <dt class="text-xs text-paper-400 font-display font-semibold uppercase tracking-wide w-28 shrink-0 pt-0.5">Joined</dt>
            <dd class="text-navy-800 font-body">{{ formatDate(admin.created_at) }}</dd>
          </div>
        </dl>
      </div>
    </template>

    <div v-else class="card text-center py-12 text-paper-500 font-body">Admin not found.</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { getInitials } from '@/utils/helpers.js'

const route   = useRoute()
const admin   = ref(null)
const loading = ref(true)

const initials = computed(() => getInitials(admin.value?.name))

onMounted(async () => {
  try {
    const { data } = await adminApi.getAdmin(route.params.id)
    admin.value = data.data
  } finally {
    loading.value = false
  }
})

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
