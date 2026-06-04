<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-2">Build your profile</h1>
    <p class="text-paper-500 font-body text-sm mb-6">Complete all steps to get verified and appear in search results.</p>

    <!-- Mobile: step progress indicator -->
    <div class="sm:hidden mb-6">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-semibold font-display text-paper-400">Step {{ currentStep + 1 }} of {{ steps.length }}</span>
        <span class="text-sm font-semibold font-display text-navy-700">{{ steps[currentStep].label }}</span>
      </div>
      <div class="flex gap-1">
        <div v-for="(step, i) in steps" :key="step.key"
          class="h-1.5 flex-1 rounded-full transition-colors"
          :class="i <= currentStep ? 'bg-navy-700' : 'bg-paper-200'" />
      </div>
    </div>

    <!-- Desktop: full tab bar -->
    <div class="hidden sm:flex gap-1 mb-8 overflow-x-auto pb-1">
      <button v-for="(step, i) in steps" :key="step.key"
        @click="currentStep = i"
        class="px-4 py-2 rounded-md text-sm font-semibold font-display whitespace-nowrap transition-colors"
        :class="currentStep === i ? 'bg-navy-700 text-white' : 'bg-white border border-paper-200 text-navy-700 hover:bg-navy-50'">
        {{ i + 1 }}. {{ step.label }}
      </button>
    </div>

    <!-- Pending changes notice (verified profiles) -->
    <div v-if="hasPendingChanges"
      class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-5">
      <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <p class="font-display font-semibold text-blue-800 text-sm">Changes pending admin review</p>
        <p class="text-xs text-blue-700 font-body mt-0.5">
          Your latest edits are staged for review. The form shows your currently live profile. New saves will update your pending submission.
        </p>
      </div>
    </div>

    <!-- Step content -->
    <component
      v-if="steps[currentStep].key === 'personal'"
      :is="steps[currentStep].component"
      @saved="onSaved"
    />
    <div v-else class="card">
      <component :is="steps[currentStep].component" @saved="onSaved" />
    </div>

    <!-- Navigation buttons -->
    <div class="flex gap-3 mt-6">
      <button v-if="currentStep > 0" @click="currentStep--"
        class="btn-outline text-sm py-2 px-5 flex-1 sm:flex-none">
        Previous
      </button>
      <button v-if="currentStep < steps.length - 1" @click="currentStep++"
        class="btn-primary text-sm py-2 px-5 flex-1 sm:flex-none sm:ml-auto">
        Next step
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, markRaw, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'
import { toast } from 'vue-sonner'
import Step1Education from './steps/Step1Education.vue'
import Step2TuitionPrefs from './steps/Step2TuitionPrefs.vue'
import Step3PersonalInfo from './steps/Step3PersonalInfo.vue'
import Step4Documents from './steps/Step4Documents.vue'
import Step5TeachingVideo from './steps/Step5TeachingVideo.vue'

const currentStep     = ref(0)
const hasPendingChanges = ref(false)

const steps = [
  { key: 'education',   label: 'Education',           component: markRaw(Step1Education) },
  { key: 'preferences', label: 'Tuition Preferences', component: markRaw(Step2TuitionPrefs) },
  { key: 'personal',    label: 'Personal Information', component: markRaw(Step3PersonalInfo) },
  { key: 'documents',   label: 'Documents',            component: markRaw(Step4Documents) },
  { key: 'video',       label: 'Teaching Video',       component: markRaw(Step5TeachingVideo) },
]

onMounted(async () => {
  try {
    const { data } = await tutorApi.getDashboard()
    hasPendingChanges.value = !!data.data?.has_pending_changes
  } catch {}
})

function onSaved(isPending, shouldAdvance = true) {
  if (isPending) {
    toast.success('Uploaded successfully — pending admin review.')
  } else {
    toast.success('Saved!')
    if (shouldAdvance && currentStep.value < steps.length - 1) currentStep.value++
  }
}
</script>
