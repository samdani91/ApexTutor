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

    <!-- Lock banner -->
    <div v-if="isLocked"
      class="flex items-start gap-3 rounded-lg px-4 py-3 mb-4"
      :class="lockReason === 'review_pending' ? 'bg-blue-50 border border-blue-200' : 'bg-amber-50 border border-amber-200'">
      <svg class="w-5 h-5 shrink-0 mt-0.5"
        :class="lockReason === 'review_pending' ? 'text-blue-600' : 'text-amber-600'"
        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
      </svg>
      <div>
        <template v-if="lockReason === 'review_pending'">
          <p class="font-display font-semibold text-blue-800 text-sm">Profile submitted for re-review</p>
          <p class="text-xs text-blue-700 font-body mt-0.5">
            Your updated profile is awaiting admin approval. You cannot make further edits until it is approved.
            Go to your <RouterLink to="/tutor/dashboard" class="underline font-semibold">dashboard</RouterLink> to check the status.
          </p>
        </template>
        <template v-else>
          <p class="font-display font-semibold text-amber-800 text-sm">Profile is verified and locked</p>
          <p class="text-xs text-amber-700 font-body mt-0.5">
            You can view your information below but cannot make changes.
            Go to your <RouterLink to="/tutor/dashboard" class="underline font-semibold">dashboard</RouterLink> to submit a change request.
          </p>
        </template>
      </div>
    </div>

    <!-- Step content -->
    <div class="card relative">
      <div v-if="isLocked"
        class="absolute inset-0 z-10 rounded-lg cursor-not-allowed"
        style="background: rgba(255,255,255,0.55);"
        @click.prevent @keydown.prevent />
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
import { ref, computed, markRaw, onMounted, provide } from 'vue'
import { RouterLink } from 'vue-router'
import { tutorApi } from '@/api/tutor.js'
import { toast } from 'vue-sonner'
import Step1Education from './steps/Step1Education.vue'
import Step2TuitionPrefs from './steps/Step2TuitionPrefs.vue'
import Step3PersonalInfo from './steps/Step3PersonalInfo.vue'
import Step4Documents from './steps/Step4Documents.vue'
import Step5TeachingVideo from './steps/Step5TeachingVideo.vue'

const currentStep = ref(0)
const isLocked    = ref(false)
const lockReason  = ref('verified') // 'verified' | 'review_pending'

provide('profileLocked', isLocked)

const steps = [
  { key: 'education',   label: 'Education',            component: markRaw(Step1Education) },
  { key: 'preferences', label: 'Tuition Preferences',  component: markRaw(Step2TuitionPrefs) },
  { key: 'personal',    label: 'Personal Information',  component: markRaw(Step3PersonalInfo) },
  { key: 'documents',   label: 'Documents',             component: markRaw(Step4Documents) },
  { key: 'video',       label: 'Teaching Video',        component: markRaw(Step5TeachingVideo) },
]

onMounted(async () => {
  try {
    const [dashRes, crRes] = await Promise.all([
      tutorApi.getDashboard(),
      tutorApi.getChangeRequest(),
    ])
    const verified      = dashRes.data.data.is_verified === true
    const reviewPending = crRes.data.data?.status === 'review_pending'
    if (verified) {
      isLocked.value   = true
      lockReason.value = 'verified'
    } else if (reviewPending) {
      isLocked.value   = true
      lockReason.value = 'review_pending'
    }
  } catch {}
})

function onSaved() {
  toast.success('Saved!')
  if (currentStep.value < steps.length - 1) currentStep.value++
}
</script>
