<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-6">Admin overview</h1>
    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>
    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-5 gap-5">
      <div class="card text-center">
        <p class="font-display font-bold text-3xl text-navy-700">{{ stats.total_tutors }}</p>
        <p class="text-sm text-paper-500 font-body mt-1">Total tutors</p>
      </div>
      <div class="card text-center">
        <p class="font-display font-bold text-3xl text-gold-500">{{ stats.pending_verifications }}</p>
        <p class="text-sm text-paper-500 font-body mt-1">Pending verifications</p>
        <RouterLink to="/admin/verifications" class="text-xs text-navy-700 font-semibold hover:underline mt-1 block">Review</RouterLink>
      </div>
      <div class="card text-center">
        <p class="font-display font-bold text-3xl text-emerald-600">{{ stats.active_connections }}</p>
        <p class="text-sm text-paper-500 font-body mt-1">Active connections</p>
      </div>
      <div class="card text-center">
        <p class="font-display font-bold text-3xl text-navy-700">{{ stats.total_users }}</p>
        <p class="text-sm text-paper-500 font-body mt-1">Total users</p>
        <RouterLink to="/admin/users" class="text-xs text-navy-700 font-semibold hover:underline mt-1 block">Manage</RouterLink>
      </div>
      <div class="card text-center">
        <p class="font-display font-bold text-3xl text-amber-600">{{ stats.pending_change_requests ?? 0 }}</p>
        <p class="text-sm text-paper-500 font-body mt-1">Unlock requests</p>
        <RouterLink to="/admin/change-requests" class="text-xs text-navy-700 font-semibold hover:underline mt-1 block">Review</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { adminApi } from '@/api/admin.js'

const stats = ref({})
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await adminApi.getDashboard()
    stats.value = data.data
  } finally {
    loading.value = false
  }
})
</script>
