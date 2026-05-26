<template>
  <Teleport to="body">
    <div class="fixed bottom-6 right-6 flex flex-col gap-2 z-[9999]">
      <TransitionGroup name="toast">
        <div
          v-for="toast in notifications.toasts"
          :key="toast.id"
          class="px-5 py-3 rounded-md shadow-lg font-body text-sm flex items-center gap-3 min-w-[240px]"
          :class="toast.type === 'success' ? 'bg-emerald-700 text-white' : toast.type === 'error' ? 'bg-red-600 text-white' : 'bg-navy-700 text-white'"
        >
          {{ toast.message }}
          <button @click="notifications.dismiss(toast.id)" class="ml-auto opacity-70 hover:opacity-100 text-lg leading-none">&times;</button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useNotificationStore } from '@/stores/notification.js'
const notifications = useNotificationStore()
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.2s ease; }
.toast-enter-from { opacity: 0; transform: translateX(20px); }
.toast-leave-to { opacity: 0; transform: translateX(20px); }
</style>
