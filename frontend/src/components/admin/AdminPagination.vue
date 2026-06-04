<template>
  <div v-if="lastPage > 1" class="mt-6 flex flex-col items-center justify-between gap-3 sm:flex-row">
    <p class="text-xs font-body text-paper-500">
      Showing {{ from }}-{{ to }} of {{ total }}
    </p>
    <div class="flex flex-wrap items-center justify-center gap-1">
      <button @click="emitPage(currentPage - 1)" :disabled="currentPage === 1"
        class="flex h-8 w-8 items-center justify-center rounded-md border border-paper-300 text-navy-700 transition-colors hover:bg-navy-50 disabled:cursor-not-allowed disabled:opacity-35"
        aria-label="Previous page">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
      </button>

      <template v-for="page in pageButtons" :key="page">
        <span v-if="page === '...'"
          class="flex h-8 w-8 select-none items-center justify-center text-sm font-body text-paper-400">
          ...
        </span>
        <button v-else @click="emitPage(page)"
          class="flex h-8 w-8 items-center justify-center rounded-md border text-sm font-semibold font-display transition-colors"
          :class="page === currentPage
            ? 'border-navy-700 bg-navy-700 text-white'
            : 'border-paper-300 text-navy-700 hover:bg-navy-50'">
          {{ page }}
        </button>
      </template>

      <button @click="emitPage(currentPage + 1)" :disabled="currentPage === lastPage"
        class="flex h-8 w-8 items-center justify-center rounded-md border border-paper-300 text-navy-700 transition-colors hover:bg-navy-50 disabled:cursor-not-allowed disabled:opacity-35"
        aria-label="Next page">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  meta: { type: Object, required: true },
})

const emit = defineEmits(['page'])

const currentPage = computed(() => Number(props.meta.current_page || 1))
const lastPage = computed(() => Number(props.meta.last_page || 1))
const total = computed(() => Number(props.meta.total || 0))
const from = computed(() => Number(props.meta.from || (total.value ? 1 : 0)))
const to = computed(() => Number(props.meta.to || 0))

const pageButtons = computed(() => {
  const totalPages = lastPage.value
  const current = currentPage.value
  if (totalPages <= 7) return Array.from({ length: totalPages }, (_, i) => i + 1)

  const pages = [1]
  if (current > 3) pages.push('...')
  for (let page = Math.max(2, current - 1); page <= Math.min(totalPages - 1, current + 1); page++) {
    pages.push(page)
  }
  if (current < totalPages - 2) pages.push('...')
  pages.push(totalPages)
  return pages
})

function emitPage(page) {
  if (page < 1 || page > lastPage.value || page === currentPage.value) return
  emit('page', page)
}
</script>
