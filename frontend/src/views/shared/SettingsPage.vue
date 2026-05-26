<template>
  <div class="max-w-lg">
    <h1 class="font-display font-bold text-navy-900 text-xl mb-6">Settings</h1>

    <div class="card">
      <h2 class="font-display font-semibold text-navy-700 text-base mb-5">Change password</h2>

      <!-- ── Step: OTP verification ── -->
      <template v-if="step === 'otp'">
        <div class="rounded-lg bg-navy-50 border border-navy-200 px-4 py-3 mb-5">
          <p class="text-sm font-body text-navy-700">
            A verification code was sent to <strong>{{ maskedEmail }}</strong>. Enter it below to confirm the password change.
          </p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Verification code</label>
            <input v-model="otpCode" type="text" inputmode="numeric" maxlength="6"
              class="input text-center text-xl font-display font-bold tracking-[0.4em]"
              placeholder="000000" autocomplete="one-time-code"
              @keydown.enter.prevent="submitWithOtp" />
          </div>

          <div class="flex flex-col sm:flex-row gap-3">
            <button @click="submitWithOtp" :disabled="saving || otpCode.length < 6"
              class="btn-primary text-sm py-2.5 px-6">
              {{ saving ? 'Updating…' : 'Confirm & update password' }}
            </button>
            <button @click="cancelOtp" class="btn-outline text-sm py-2.5 px-5">
              Cancel
            </button>
          </div>

          <p class="text-sm text-paper-400 font-body">
            Didn't receive it?
            <button @click="handleResend" :disabled="resending"
              class="text-navy-700 font-semibold hover:text-navy-900 transition-colors disabled:opacity-50">
              {{ resending ? 'Sending…' : 'Resend code' }}
            </button>
          </p>
        </div>
      </template>

      <!-- ── Step: Password form ── -->
      <template v-else>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Current password</label>
            <div class="relative">
              <input v-model="form.current_password" :type="show.current ? 'text' : 'password'"
                class="input text-sm pr-10" placeholder="Enter your current password" autocomplete="current-password" />
              <button type="button" @click="show.current = !show.current"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-paper-400 hover:text-navy-700 transition-colors">
                <svg v-if="show.current" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">New password</label>
            <div class="relative">
              <input v-model="form.password" :type="show.password ? 'text' : 'password'"
                class="input text-sm pr-10" placeholder="At least 8 characters" autocomplete="new-password" />
              <button type="button" @click="show.password = !show.password"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-paper-400 hover:text-navy-700 transition-colors">
                <svg v-if="show.password" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </button>
            </div>
          </div>

          <PasswordRequirements v-if="form.password" :password="form.password" />

          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Confirm new password</label>
            <div class="relative">
              <input v-model="form.password_confirmation" :type="show.confirm ? 'text' : 'password'"
                class="input text-sm pr-10" placeholder="Repeat new password" autocomplete="new-password" />
              <button type="button" @click="show.confirm = !show.confirm"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-paper-400 hover:text-navy-700 transition-colors">
                <svg v-if="show.confirm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <button @click="requestChange" :disabled="requesting" class="btn-primary mt-6 text-sm py-2.5 px-6">
          {{ requesting ? 'Sending code…' : 'Send verification code' }}
        </button>
      </template>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { authApi } from '@/api/auth.js'
import { toast } from 'vue-sonner'
import PasswordRequirements from '@/components/common/PasswordRequirements.vue'

const step        = ref('form')
const maskedEmail = ref('')
const otpCode     = ref('')
const requesting  = ref(false)
const saving      = ref(false)
const resending   = ref(false)

const form = reactive({
  current_password:      '',
  password:              '',
  password_confirmation: '',
})

const show = reactive({ current: false, password: false, confirm: false })

async function requestChange() {
  if (!form.current_password || !form.password || !form.password_confirmation) {
    toast.error('Please fill in all password fields.')
    return
  }
  if (form.password !== form.password_confirmation) {
    toast.error('New password and confirmation do not match.')
    return
  }
  requesting.value = true
  try {
    const { data } = await authApi.requestPasswordChange({ current_password: form.current_password })
    maskedEmail.value = data.data.email
    otpCode.value     = ''
    step.value        = 'otp'
    toast.success('Verification code sent to your email.')
  } catch (e) {
    const msg = e.response?.data?.message
      || Object.values(e.response?.data?.errors || {})[0]?.[0]
      || 'Failed to send code.'
    toast.error(msg)
  } finally {
    requesting.value = false
  }
}

async function submitWithOtp() {
  if (otpCode.value.length !== 6) return
  saving.value = true
  try {
    await authApi.changePassword({
      current_password:      form.current_password,
      password:              form.password,
      password_confirmation: form.password_confirmation,
      otp_code:              otpCode.value,
    })
    form.current_password      = ''
    form.password              = ''
    form.password_confirmation = ''
    otpCode.value = ''
    step.value    = 'form'
    toast.success('Password updated successfully.')
  } catch (e) {
    const msg = e.response?.data?.message
      || Object.values(e.response?.data?.errors || {})[0]?.[0]
      || 'Failed to update password.'
    toast.error(msg)
  } finally {
    saving.value = false
  }
}

async function handleResend() {
  resending.value = true
  try {
    await authApi.requestPasswordChange({ current_password: form.current_password })
    toast.success('New code sent.')
    otpCode.value = ''
  } catch {
    toast.error('Could not resend code.')
  } finally {
    resending.value = false
  }
}

function cancelOtp() {
  step.value    = 'form'
  otpCode.value = ''
}
</script>
