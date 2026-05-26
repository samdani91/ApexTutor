<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-6">Shortlist</h1>
    <div v-if="loading" class="text-paper-500 font-body">Loading…</div>
    <div v-else-if="!list.length" class="card text-center py-12">
      <p class="font-display font-semibold text-navy-700 text-lg mb-2">Your shortlist is empty</p>
      <p class="text-paper-500 text-sm font-body mb-4">Browse tutors and save the ones you like.</p>
      <RouterLink to="/search" class="btn-primary text-sm py-2 px-5">Find tutors</RouterLink>
    </div>
    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <TutorCard v-for="item in list" :key="item.id" :tutor="item.tutor_profile" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import TutorCard from '@/components/tutor/TutorCard.vue'
import { guardianApi } from '@/api/guardian.js'

const list = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await guardianApi.getShortlist()
    list.value = data.data || []
  } finally {
    loading.value = false
  }
})
</script>
