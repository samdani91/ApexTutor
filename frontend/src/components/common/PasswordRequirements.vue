<template>
  <ul class="mt-2 space-y-1">
    <li class="flex items-center gap-1.5 text-xs font-body transition-colors"
      :class="password.length >= 6 ? 'text-emerald-600' : 'text-paper-400'">
      <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path v-if="password.length >= 6" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
      </svg>
      At least 6 characters
    </li>
  </ul>
</template>

<script setup>
defineProps({ password: { type: String, default: '' } })
</script>
