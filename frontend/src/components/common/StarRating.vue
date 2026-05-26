<template>
  <span class="flex items-center gap-0.5">
    <span v-for="i in 5" :key="i" class="text-lg leading-none" :class="i <= Math.round(Number(rating)) ? 'text-gold-400' : 'text-paper-300'">★</span>
    <span v-if="showCount && count !== undefined" class="text-sm text-paper-500 ml-1">({{ count }})</span>
  </span>
</template>
<script setup>
defineProps({ rating: { type: [Number, String], default: 0 }, count: Number, showCount: { type: Boolean, default: true } })
</script>
