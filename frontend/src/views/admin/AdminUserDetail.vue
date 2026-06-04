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
      <div class="card">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
          <div class="w-16 h-16 rounded-sm bg-navy-700 flex items-center justify-center shrink-0 overflow-hidden ring-2 ring-white shadow">
            <img v-if="admin.avatar_url" :src="admin.avatar_url" class="w-full h-full object-cover" alt="" />
            <span v-else class="font-display font-bold text-xl text-white">{{ initials }}</span>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <h1 class="font-display font-bold text-2xl text-navy-900">{{ admin.name }}</h1>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-pill bg-navy-50 text-navy-700 border border-navy-200">Admin</span>
              <span class="text-xs font-semibold px-2 py-0.5 rounded-pill"
                :class="admin.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                {{ admin.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <p class="text-sm text-paper-500 font-body break-words">{{ admin.email }}</p>
            <p class="text-xs font-body text-paper-400 mt-0.5">{{ admin.phone || 'No phone' }}</p>
            <p class="mt-2 text-xs font-body text-paper-400">Joined {{ formatDate(admin.created_at) }}</p>
          </div>
        </div>
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
