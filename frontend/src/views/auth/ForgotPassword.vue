<template>
  <div class="min-h-screen flex bg-white">

    <!-- Left branding panel (desktop only) -->
    <div class="hidden lg:flex lg:w-[420px] xl:w-[500px] shrink-0 bg-navy-900 flex-col justify-between p-10 relative overflow-hidden">
      <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.055)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.055)_1px,transparent_1px)] bg-[length:34px_34px]" />
      <RouterLink to="/" class="relative z-10 inline-block">
        <span class="font-display font-bold text-2xl text-white">Apex Tutor</span>
      </RouterLink>
      <div class="relative z-10 space-y-5">
        <div class="w-12 h-12 rounded-sm bg-gold-400/15 border border-gold-300/30 flex items-center justify-center">
          <svg class="w-6 h-6 text-gold-300" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
          </svg>
        </div>
        <h2 class="font-display font-bold text-3xl text-white leading-snug">
          Regain access to your account in a few steps.
        </h2>
        <p class="text-navy-300 font-body text-sm leading-relaxed">
          Enter your email, verify with a one-time code, and set a new password securely.
        </p>
        <ul class="space-y-2.5 pt-1">
          <li v-for="step in steps" :key="step.n"
            class="flex items-center gap-3 text-sm font-body transition-colors"
            :class="currentStep === step.n ? 'text-white' : currentStep > step.n ? 'text-gold-300' : 'text-navy-500'">
            <span class="w-6 h-6 rounded-full border flex items-center justify-center shrink-0 text-xs font-bold transition-colors"
              :class="currentStep > step.n
                ? 'bg-gold-400 border-gold-400 text-navy-900'
                : currentStep === step.n
                  ? 'bg-white/10 border-white/40 text-white'
                  : 'bg-transparent border-navy-600 text-navy-500'">
              <svg v-if="currentStep > step.n" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
              </svg>
              <span v-else>{{ step.n }}</span>
            </span>
            {{ step.label }}
          </li>
        </ul>
      </div>
      <p class="relative z-10 text-navy-500 text-xs font-body">© 2026 Apex Tutor</p>
    </div>

    <!-- Right form panel -->
    <div class="relative flex-1 flex flex-col bg-white">
      <div class="pointer-events-none absolute inset-0 landing-grid"></div>
      <div class="flex items-center justify-between px-6 pt-6 pb-2">
        <button @click="handleBack"
          class="inline-flex items-center gap-1.5 text-sm font-semibold font-display text-navy-500 hover:text-navy-900 transition-colors group">
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
          </svg>
          Back
        </button>
        <RouterLink to="/" class="lg:hidden font-display font-bold text-navy-700">
          Apex Tutor
        </RouterLink>
        <div class="w-12 lg:hidden" />
      </div>

      <div class="relative flex-1 flex items-center justify-center px-6 py-10">
        <div class="w-full max-w-md rounded-lg border border-paper-200 bg-white p-6 shadow-xl sm:p-8">

          <!-- Step 1: Email -->
          <template v-if="currentStep === 1">
            <div class="mb-7">
              <div class="w-12 h-12 rounded-sm bg-gold-50 border border-gold-200 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gold-700" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                </svg>
              </div>
              <p class="font-display text-xs font-bold uppercase text-gold-600">Password Reset</p>
              <h1 class="mt-1 font-display font-bold text-2xl text-navy-900">Forgot your password?</h1>
              <p class="text-paper-400 text-sm mt-1.5 font-body">
                Enter the email address linked to your account and we'll send you a reset code.
              </p>
            </div>

            <form @submit.prevent="handleSendOtp" class="space-y-5">
              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">Email address</label>
                <input v-model="email" type="email" autocomplete="email" placeholder="you@example.com"
                  class="input" required />
              </div>

              <button type="submit" :disabled="loading" class="btn-primary w-full rounded-sm py-3 text-sm">
                <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ loading ? 'Sending code…' : 'Send reset code' }}
              </button>
            </form>

            <div class="mt-7 pt-5 border-t border-paper-100 text-center">
              <p class="text-sm text-paper-400 font-body">
                Remembered it?
                <RouterLink to="/login" class="text-navy-700 font-semibold hover:text-navy-900 transition-colors">
                  Log In
                </RouterLink>
              </p>
            </div>
          </template>

          <!-- Step 2: OTP verification -->
          <template v-else-if="currentStep === 2">
            <div class="mb-7">
              <div class="w-12 h-12 rounded-sm bg-gold-50 border border-gold-200 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gold-700" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
              </div>
              <p class="font-display text-xs font-bold uppercase text-gold-600">Password Reset</p>
              <h1 class="mt-1 font-display font-bold text-2xl text-navy-900">Check your email</h1>
              <p class="text-paper-400 text-sm mt-1.5 font-body">
                We sent a 6-digit code to <strong class="text-navy-700">{{ maskedEmail }}</strong>. Enter it below to continue.
              </p>
            </div>

            <div class="space-y-4">
              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">Reset code</label>
                <input v-model="otpCode" type="text" inputmode="numeric" maxlength="6"
                  class="input text-center text-xl font-display font-bold tracking-[0.4em]"
                  placeholder="000000" autocomplete="one-time-code"
                  @input="otpCode = otpCode.replace(/\D/g, '').slice(0, 6)"
                  @keydown.enter.prevent="handleVerifyOtp" />
              </div>

              <button @click="handleVerifyOtp" :disabled="loading || otpCode.length < 6"
                class="btn-primary w-full rounded-sm py-3 text-sm">
                <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ loading ? 'Verifying…' : 'Verify code' }}
              </button>

              <p class="text-center text-sm text-paper-400 font-body">
                Didn't receive it?
                <button @click="handleResend" :disabled="resending || resendCooldown > 0"
                  class="text-navy-700 font-semibold hover:text-navy-900 transition-colors disabled:opacity-50">
                  {{ resending ? 'Sending…' : resendCooldown > 0 ? `Resend in ${resendCooldown}s` : 'Resend code' }}
                </button>
              </p>
            </div>
          </template>

          <!-- Step 3: New password -->
          <template v-else>
            <div class="mb-7">
              <div class="w-12 h-12 rounded-sm bg-gold-50 border border-gold-200 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gold-700" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
              </div>
              <p class="font-display text-xs font-bold uppercase text-gold-600">Password Reset</p>
              <h1 class="mt-1 font-display font-bold text-2xl text-navy-900">Set new password</h1>
              <p class="text-paper-400 text-sm mt-1.5 font-body">
                Choose a strong password for your account.
              </p>
            </div>

            <form @submit.prevent="handleReset" class="space-y-4">
              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">New password</label>
                <div class="relative">
                  <input v-model="newPassword" :type="showPassword ? 'text' : 'password'"
                    autocomplete="new-password" placeholder="Min. 6 characters" class="input pr-10" required minlength="6" />
                  <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-paper-400 hover:text-navy-700 transition-colors">
                    <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                  </button>
                </div>
              </div>

              <PasswordRequirements v-if="newPassword" :password="newPassword" />

              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">Confirm new password</label>
                <div class="relative">
                  <input v-model="confirmPassword" :type="showConfirmPassword ? 'text' : 'password'"
                    autocomplete="new-password" placeholder="Repeat new password" class="input pr-10" required />
                  <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-paper-400 hover:text-navy-700 transition-colors">
                    <svg v-if="showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                  </button>
                </div>
                <p v-if="confirmPassword && newPassword !== confirmPassword"
                  class="text-xs text-red-500 font-body mt-1">
                  Passwords do not match.
                </p>
              </div>

              <button type="submit" :disabled="loading || !passwordsMatch"
                class="btn-primary w-full rounded-sm py-3 text-sm mt-1">
                <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ loading ? 'Resetting…' : 'Reset password' }}
              </button>
            </form>
          </template>

        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed, ref, onUnmounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { authApi } from '@/api/auth.js'
import { toast } from 'vue-sonner'
import PasswordRequirements from '@/components/common/PasswordRequirements.vue'

const router = useRouter()

const currentStep      = ref(1)
const email            = ref('')
const maskedEmail      = ref('')
const otpCode          = ref('')
const newPassword      = ref('')
const confirmPassword  = ref('')
const showPassword     = ref(false)
const showConfirmPassword = ref(false)
const loading          = ref(false)
const resending        = ref(false)
const resendCooldown   = ref(0)

let cooldownTimer = null

const steps = [
  { n: 1, label: 'Enter your email' },
  { n: 2, label: 'Verify reset code' },
  { n: 3, label: 'Set new password' },
]

const passwordsMatch = computed(() =>
  newPassword.value.length >= 6 && newPassword.value === confirmPassword.value
)

function handleBack() {
  if (currentStep.value > 1) {
    currentStep.value--
    otpCode.value = ''
  } else {
    router.push('/login')
  }
}

function startResendCooldown() {
  resendCooldown.value = 60
  cooldownTimer = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) {
      clearInterval(cooldownTimer)
      cooldownTimer = null
    }
  }, 1000)
}

onUnmounted(() => {
  if (cooldownTimer) clearInterval(cooldownTimer)
})

async function handleSendOtp() {
  loading.value = true
  try {
    const { data } = await authApi.forgotPassword({ email: email.value })
    maskedEmail.value = data.data?.email || email.value
    currentStep.value = 2
    startResendCooldown()
    toast.success('Reset code sent — check your inbox.')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Something went wrong. Please try again.')
  } finally {
    loading.value = false
  }
}

async function handleVerifyOtp() {
  if (otpCode.value.length < 6) return
  loading.value = true
  try {
    await authApi.verifyResetOtp({ email: email.value, code: otpCode.value })
    currentStep.value = 3
  } catch (e) {
    toast.error(e.response?.data?.message || 'Invalid or expired code. Try again.')
  } finally {
    loading.value = false
  }
}

async function handleResend() {
  resending.value = true
  try {
    const { data } = await authApi.forgotPassword({ email: email.value })
    maskedEmail.value = data.data?.email || maskedEmail.value
    otpCode.value = ''
    startResendCooldown()
    toast.success('New code sent — check your inbox.')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Could not resend code.')
  } finally {
    resending.value = false
  }
}

async function handleReset() {
  if (!passwordsMatch.value) return
  loading.value = true
  try {
    await authApi.resetPassword({
      email:                 email.value,
      code:                  otpCode.value,
      password:              newPassword.value,
      password_confirmation: confirmPassword.value,
    })
    toast.success('Password reset successfully! Please log in.')
    router.push('/login')
  } catch (e) {
    const errors = e.response?.data?.errors
    const msg = errors
      ? Object.values(errors).flat().join(' ')
      : (e.response?.data?.message || 'Failed to reset password.')
    toast.error(msg)
    if (e.response?.status === 422 && e.response?.data?.message?.toLowerCase().includes('code')) {
      currentStep.value = 2
      otpCode.value = ''
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.landing-grid {
  background-image:
    linear-gradient(rgba(15, 46, 92, 0.038) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 46, 92, 0.038) 1px, transparent 1px);
  background-size: 34px 34px;
}
</style>
