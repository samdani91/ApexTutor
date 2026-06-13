<template>
  <svg
    :class="sizeClass"
    viewBox="0 0 24 24"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-label="Verified"
    role="img"
  >
    <circle cx="12" cy="12" r="12" fill="#0F2E5C" />
    <path
      d="M7.5 12.5l3 3 6-6"
      stroke="#F4B942"
      stroke-width="2.2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>

<script setup>
const props = defineProps({
  size: { type: String, default: 'md' },
})
const sizeClass = {
  sm: 'h-4 w-4 shrink-0',
  md: 'h-5 w-5 shrink-0',
  lg: 'h-6 w-6 shrink-0',
}[props.size] ?? 'h-5 w-5 shrink-0'
</script>
