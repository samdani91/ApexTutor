<template>
  <div>
    <p class="text-xs font-semibold font-display text-navy-700 uppercase tracking-wider mb-2">{{ label }}</p>
    <slot />
  </div>
</template>

<script setup>
defineProps({ label: { type: String, required: true } })
</script>
