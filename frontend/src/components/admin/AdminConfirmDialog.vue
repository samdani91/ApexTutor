<template>
  <Teleport to="body">
    <Transition name="confirm-fade">
      <div v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
        @click.self="$emit('cancel')">
        <div class="bg-white rounded-sm shadow-xl w-full max-w-sm p-6">

          <!-- Icon + title -->
          <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-0.5"
              :class="danger ? 'bg-red-50' : 'bg-navy-50'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"
                :class="danger ? 'text-red-600' : 'text-navy-700'">
                <path v-if="danger" stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="min-w-0">
              <h3 class="font-display font-bold text-navy-900 text-base leading-snug">{{ title }}</h3>
              <p v-if="message" class="text-sm text-paper-500 font-body mt-0.5 leading-relaxed">{{ message }}</p>
            </div>
          </div>

          <!-- Optional text input (e.g. rejection reason) -->
          <div v-if="withInput" class="mb-4">
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">
              {{ inputLabel || 'Note' }}
              <span v-if="inputRequired" class="text-red-500">*</span>
              <span v-else class="text-paper-400 font-normal">(optional)</span>
            </label>
            <textarea v-model="inputValue" rows="3"
              class="input w-full resize-none"
              :placeholder="inputPlaceholder || 'Enter a note…'" />
          </div>

          <!-- Actions -->
          <div class="flex gap-3 mt-2">
            <button @click="$emit('cancel')"
              class="btn-outline flex-1 text-sm py-2">
              Cancel
            </button>
            <button @click="handleConfirm"
              :disabled="withInput && inputRequired && !inputValue.trim()"
              class="flex-1 text-sm font-semibold font-display py-2 rounded-md transition-colors disabled:opacity-50"
              :class="danger
                ? 'bg-red-600 text-white hover:bg-red-700'
                : 'bg-navy-700 text-white hover:bg-navy-800'">
              {{ confirmLabel || 'Confirm' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show:             { type: Boolean, default: false },
  title:            { type: String,  required: true },
  message:          { type: String,  default: '' },
  confirmLabel:     { type: String,  default: 'Confirm' },
  danger:           { type: Boolean, default: false },
  withInput:        { type: Boolean, default: false },
  inputRequired:    { type: Boolean, default: true },
  inputLabel:       { type: String,  default: '' },
  inputPlaceholder: { type: String,  default: '' },
})

const emit = defineEmits(['confirm', 'cancel'])

const inputValue = ref('')

// Reset input each time dialog opens
watch(() => props.show, (val) => { if (val) inputValue.value = '' })

function handleConfirm() {
  emit('confirm', props.withInput ? inputValue.value : undefined)
}
</script>

<style scoped>
.confirm-fade-enter-active, .confirm-fade-leave-active { transition: opacity 0.15s ease; }
.confirm-fade-enter-from, .confirm-fade-leave-to { opacity: 0; }
</style>
