<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="font-display font-bold text-2xl text-navy-900">Referrals</h1>
    </div>

    <!-- Search -->
    <div class="card mb-5">
      <div class="relative">
        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-paper-400"
          fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
        <input v-model="searchInput" @input="onSearch" type="search"
          placeholder="Search by name, email, or referral code…"
          class="input pl-9 w-full" />
      </div>
    </div>

    <div v-if="loading" class="text-paper-500 font-body text-sm py-8 text-center">Loading…</div>

    <div v-else-if="!rows.length" class="card text-center py-12 text-paper-500 font-body">
      No referral activity found.
    </div>

    <template v-else>
      <!-- Mobile cards -->
      <div class="md:hidden space-y-3">
        <div v-for="row in rows" :key="row.id" class="card">
          <div class="flex items-center justify-between mb-2">
            <p class="font-display font-semibold text-navy-900 text-sm truncate">{{ row.name }}</p>
            <span class="text-xs font-semibold px-1.5 py-0.5 rounded-pill bg-emerald-50 text-emerald-700 whitespace-nowrap">
              {{ row.referral_points }} pts
            </span>
          </div>
          <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-1.5 py-0.5 rounded-pill">
              {{ row.referral_code || '—' }}
            </span>
            <span class="text-xs font-semibold px-1.5 py-0.5 rounded-pill capitalize bg-blue-50 text-blue-700">
              {{ row.role }}
            </span>
            <span class="text-xs text-paper-500">{{ row.referrals_count }} referred</span>
          </div>
        </div>
      </div>

      <!-- Desktop table -->
      <div class="hidden md:block card overflow-x-auto">
        <table class="w-full text-sm font-body">
          <thead>
            <tr class="border-b border-paper-200 text-left">
              <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">User</th>
              <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Role</th>
              <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Referral Code</th>
              <th class="pb-3 pr-6 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Points</th>
              <th class="pb-3 font-semibold font-display text-navy-700 text-xs uppercase tracking-wider">Referred</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in rows" :key="row.id" class="border-b border-paper-100 last:border-0 hover:bg-navy-50/40 transition-colors">
              <td class="py-3 pr-6">
                <p class="font-display font-semibold text-navy-900 truncate">{{ row.name }}</p>
              </td>
              <td class="py-3 pr-6">
                <span class="text-xs font-semibold px-2 py-0.5 rounded-pill capitalize bg-blue-50 text-blue-700 whitespace-nowrap">
                  {{ row.role }}
                </span>
              </td>
              <td class="py-3 pr-6">
                <span class="text-xs font-semibold font-display text-navy-600 bg-navy-50 border border-navy-200 px-2 py-0.5 rounded-pill whitespace-nowrap">
                  {{ row.referral_code || '—' }}
                </span>
              </td>
              <td class="py-3 pr-6 font-display font-semibold text-navy-900">{{ row.referral_points }}</td>
              <td class="py-3 text-paper-500">{{ row.referrals_count }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <AdminPagination :meta="pagination" @page="goPage" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'
import AdminPagination from '@/components/admin/AdminPagination.vue'

const searchInput = ref('')
const rows       = ref([])
const loading    = ref(true)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
let searchTimeout = null

async function load(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 15, q: searchInput.value || undefined }
    const res = await adminApi.getReferrals(params)
    rows.value       = res.data.data.data || []
    pagination.value = res.data.data
  } finally {
    loading.value = false
  }
}

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => load(), 350)
}

function goPage(page) {
  if (page < 1 || page > pagination.value.last_page) return
  load(page)
}

onMounted(() => load())
</script>
