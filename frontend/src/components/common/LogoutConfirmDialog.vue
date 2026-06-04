<template>
  <Teleport to="body">
    <Transition name="dialog">
      <div v-if="show" class="fixed inset-0 z-[200] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('cancel')" />
        <div class="relative bg-white rounded-sm shadow-xl w-full max-w-xs overflow-hidden">
          <!-- Red header band -->
          <div class="bg-red-600 px-6 pt-8 pb-7 text-center">
            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
              </svg>
            </div>
            <h3 class="font-display font-bold text-white text-xl leading-tight">Log Out?</h3>
            <p class="text-red-200 text-xs font-body mt-1 tracking-wide">TutorKhujo</p>
          </div>
          <!-- Body -->
          <div class="px-6 py-5">
            <p class="text-paper-600 font-body text-sm text-center leading-relaxed mb-5">
              Are you sure you want to log out of your account?
            </p>
            <div class="flex gap-3">
              <button @click="$emit('cancel')"
                class="btn-outline flex-1 py-2.5 text-sm">
                Cancel
              </button>
              <button @click="$emit('confirm')"
                class="flex-1 inline-flex items-center justify-center bg-red-600 text-white font-semibold font-display py-2.5 rounded-md text-sm hover:bg-red-700 active:bg-red-800 transition-colors">
                Log Out
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({ show: { type: Boolean, required: true } })
defineEmits(['confirm', 'cancel'])
</script>

<style scoped>
.dialog-enter-active { transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.dialog-leave-active { transition: all 0.15s ease-in; }
.dialog-enter-from   { opacity: 0; transform: scale(0.88) translateY(8px); }
.dialog-leave-to     { opacity: 0; transform: scale(0.95) translateY(4px); }
</style>
