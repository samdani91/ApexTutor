<template>
  <RouterView />
  <Toaster
    position="top-right"
    closeButtonPosition="top-right"
    :duration="3000"
    :visibleToasts="4"
    :offset="{ top: 20, right: 20 }"
    :mobileOffset="{ top: 16, right: 16, left: 16 }"
    richColors
    closeButton
    expand
  />
</template>

<script setup>
import 'vue-sonner/style.css'
import { RouterView } from 'vue-router'
import { Toaster } from 'vue-sonner'
</script>
