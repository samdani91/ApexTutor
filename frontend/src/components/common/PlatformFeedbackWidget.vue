<template>
  <div class="dashboard-card">
    <h3 class="font-display font-semibold text-navy-800 text-base mb-1">Share Your Experience</h3>
    <p class="text-xs text-paper-500 font-body mb-4">
      Your feedback may appear on our landing page after review.
    </p>

    <!-- Approved / pending state -->
    <div v-if="existing && existing.moderation_status !== 'rejected'" class="mb-4 rounded-md border p-3"
      :class="existing.moderation_status === 'approved'
        ? 'border-emerald-200 bg-emerald-50'
        : 'border-gold-200 bg-gold-50'">
      <div class="flex items-center gap-2 mb-1.5">
        <span class="text-xs font-semibold font-display px-2 py-0.5 rounded-pill border"
          :class="existing.moderation_status === 'approved'
            ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
            : 'bg-gold-100 text-gold-700 border-gold-200'">
          {{ existing.moderation_status === 'approved' ? 'Approved' : 'Pending review' }}
        </span>
        <span class="text-xs text-paper-500 font-body">Showing as: {{ existing.display_label }}</span>
      </div>
      <p class="text-sm font-body text-navy-800 italic">"{{ existing.quote }}"</p>
    </div>

    <!-- Form -->
    <div>
      <label class="block text-xs font-semibold font-display text-navy-700 mb-1.5">
        {{ existing ? 'Update your feedback' : 'Your feedback' }}
      </label>
      <textarea v-model="quote" rows="4" maxlength="500"
        class="input text-sm resize-none"
        placeholder="How has TutorKhujo helped you? Share your honest experience…" />
      <div class="flex items-center justify-between mt-1.5">
        <p class="text-xs text-paper-400 font-body">{{ quote.length }}/500</p>
        <p v-if="existing" class="text-xs text-paper-400 font-body">Editing will reset to pending review</p>
      </div>
      <button @click="submit" :disabled="submitting || quote.trim().length < 20"
        class="mt-3 btn-primary text-sm py-2 px-5 disabled:opacity-50">
        {{ submitting ? 'Submitting…' : existing ? 'Update Feedback' : 'Submit Feedback' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { feedbackApi } from '@/api/feedback.js'
import { toast } from 'vue-sonner'

const existing  = ref(null)
const quote     = ref('')
const submitting = ref(false)

onMounted(async () => {
  try {
    const { data } = await feedbackApi.getMyFeedback()
    if (data.data) {
      existing.value = data.data
      quote.value    = data.data.quote
    }
  } catch { /* not critical */ }
})

async function submit() {
  if (quote.value.trim().length < 20) return
  submitting.value = true
  try {
    const { data } = await feedbackApi.submitFeedback(quote.value.trim())
    existing.value = data.data
    toast.success(existing.value ? 'Feedback updated.' : 'Thank you! Your feedback is pending review.')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Could not submit feedback.')
  } finally {
    submitting.value = false
  }
}
</script>
