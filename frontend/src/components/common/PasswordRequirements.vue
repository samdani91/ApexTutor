<template>
  <ul class="mt-2 space-y-1">
    <li v-for="r in rules" :key="r.label"
      class="flex items-center gap-1.5 text-xs font-body transition-colors"
      :class="r.met ? 'text-emerald-600' : 'text-paper-400'">
      <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path v-if="r.met" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
      </svg>
      {{ r.label }}
    </li>
  </ul>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ password: { type: String, default: '' } })

const rules = computed(() => [
  { label: 'At least 8 characters',      met: props.password.length >= 8 },
  { label: 'One uppercase letter (A–Z)',  met: /[A-Z]/.test(props.password) },
  { label: 'One lowercase letter (a–z)',  met: /[a-z]/.test(props.password) },
  { label: 'One number (0–9)',            met: /[0-9]/.test(props.password) },
  { label: 'One symbol (!@#$…)',          met: /[^A-Za-z0-9]/.test(props.password) },
])
</script>
