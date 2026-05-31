<template>
  <div class="min-h-screen flex">

    <!-- ── Left branding panel (desktop only) ── -->
    <div class="hidden lg:flex lg:w-[400px] xl:w-[460px] shrink-0 bg-navy-900 flex-col justify-between p-10 relative overflow-hidden">
      <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(255,255,255,0.08)_0,rgba(255,255,255,0)_42%),linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[length:100%_100%,28px_28px,28px_28px]" />
      <RouterLink to="/" class="relative z-10 inline-block">
        <span class="font-display font-bold text-2xl text-white">Tutor<span class="text-blue-400">Khujo</span></span>
      </RouterLink>
      <div class="relative z-10 space-y-5">
        <div class="w-12 h-12 rounded-sm bg-blue-500/20 border border-blue-400/30 flex items-center justify-center">
          <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3M13.5 19.5l-.197-2.96a1.5 1.5 0 01.01-.23l.287-2.587A4.5 4.5 0 0118 9.75 4.5 4.5 0 0118 1.5h-.75a4.5 4.5 0 00-4.5 4.5v1.5M10.5 19.5H6.75a2.25 2.25 0 01-2.25-2.25v-8.25A2.25 2.25 0 016.75 6.75H9M10.5 19.5a2.25 2.25 0 002.25-2.25v-1.5A2.25 2.25 0 0010.5 13.5H9a2.25 2.25 0 00-2.25 2.25v1.5A2.25 2.25 0 009 19.5h1.5z"/>
          </svg>
        </div>
        <h2 class="font-display font-bold text-3xl text-white leading-snug">
          Join thousands of tutors and guardians today.
        </h2>
        <p class="text-navy-300 font-body text-sm leading-relaxed">
          Create a free account as a tutor to showcase your skills, or as a guardian to find the best tutor for your child.
        </p>
        <ul class="space-y-2.5 pt-1">
          <li v-for="item in perks" :key="item" class="flex items-center gap-2.5 text-navy-300 text-sm font-body">
            <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
            {{ item }}
          </li>
        </ul>
      </div>
      <p class="relative z-10 text-navy-500 text-xs font-body">© 2025 TutorKhujo</p>
    </div>

    <!-- ── Right form panel ── -->
    <div class="flex-1 flex flex-col bg-white">
      <div class="flex items-center justify-between px-6 pt-6 pb-2">
        <button @click="step === 'otp' ? (step = 'form') : router.push('/')"
          class="inline-flex items-center gap-1.5 text-sm font-semibold font-display text-navy-500 hover:text-navy-900 transition-colors group">
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
          </svg>
          Back
        </button>
        <RouterLink to="/" class="lg:hidden font-display font-bold text-navy-700">
          Tutor<span class="text-blue-600">Khujo</span>
        </RouterLink>
        <div class="w-12 lg:hidden" />
      </div>

      <div class="flex-1 flex items-center justify-center px-6 py-8">
        <div class="w-full max-w-sm">

          <!-- ── Step: OTP verification ── -->
          <template v-if="step === 'otp'">
            <div class="mb-7">
              <div class="w-12 h-12 rounded-sm bg-navy-50 border border-navy-200 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-navy-700" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                </svg>
              </div>
              <h1 class="font-display font-bold text-2xl text-navy-900">Check your email</h1>
              <p class="text-paper-400 text-sm mt-1.5 font-body">
                We sent a 6-digit code to <strong class="text-navy-700">{{ maskedEmail }}</strong>. Enter it below to verify your account.
              </p>
            </div>

            <div class="space-y-4">
              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">Verification code</label>
                <input v-model="otpCode" type="text" inputmode="numeric" maxlength="6"
                  class="input text-center text-xl font-display font-bold tracking-[0.4em]"
                  placeholder="000000" autocomplete="one-time-code"
                  @keydown.enter.prevent="handleVerify" />
              </div>

              <button @click="handleVerify" :disabled="verifying || otpCode.length < 6"
                class="btn-primary w-full py-3 text-sm">
                <svg v-if="verifying" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ verifying ? 'Verifying…' : 'Verify email' }}
              </button>

              <p class="text-center text-sm text-paper-400 font-body">
                Didn't receive it?
                <button @click="handleResend" :disabled="resending"
                  class="text-navy-700 font-semibold hover:text-navy-900 transition-colors disabled:opacity-50">
                  {{ resending ? 'Sending…' : 'Resend code' }}
                </button>
              </p>
            </div>
          </template>

          <!-- ── Step: Registration form ── -->
          <template v-else>
            <div class="mb-7">
              <h1 class="font-display font-bold text-2xl text-navy-900">Create your account</h1>
              <p class="text-paper-400 text-sm mt-1.5 font-body">Free forever — no credit card required</p>
            </div>

            <form @submit.prevent="handleRegister" class="space-y-4">
              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">I am a</label>
                <div class="grid grid-cols-2 gap-2.5">
                  <button type="button" v-for="r in roles" :key="r.value"
                    @click="form.role = r.value"
                    class="flex items-center gap-2 px-3 py-2.5 rounded-sm text-sm font-semibold font-display border transition-colors focus:outline-none"
                    :class="form.role === r.value ? 'choice-btn-active shadow-sm' : 'choice-btn-idle'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <path v-if="r.value === 'tutor'" stroke-linecap="round" stroke-linejoin="round"
                        d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                      <path v-else stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                    {{ r.label }}
                  </button>
                </div>
              </div>

              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">Full name</label>
                <input v-model="form.name" type="text" autocomplete="name" placeholder="Your full name" class="input" required />
              </div>

              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">Email address</label>
                <input v-model="form.email" type="email" autocomplete="email" placeholder="you@example.com" class="input" required />
              </div>

              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">Phone number</label>
                <input v-model="form.phone" type="tel" autocomplete="tel" placeholder="017xxxxxxxx" class="input" required />
              </div>

              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">Password</label>
                <div class="relative">
                  <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                    autocomplete="new-password" placeholder="Min. 8 characters" class="input pr-10" required minlength="8" />
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

              <PasswordRequirements v-if="form.password" :password="form.password" />

              <div class="space-y-1.5">
                <label class="block text-sm font-semibold font-display text-navy-700">Confirm password</label>
                <div class="relative">
                  <input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'"
                    autocomplete="new-password" placeholder="Repeat password" class="input pr-10" required />
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
              </div>

              <button type="submit" :disabled="auth.loading" class="btn-primary w-full py-3 text-sm mt-1">
                <svg v-if="auth.loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ auth.loading ? 'Creating account…' : 'Create account' }}
              </button>
            </form>

            <div class="mt-7 pt-5 border-t border-paper-100 text-center">
              <p class="text-sm text-paper-400 font-body">
                Already have an account?
                <RouterLink to="/login" class="text-navy-700 font-semibold hover:text-navy-900 transition-colors">
                  Log in
                </RouterLink>
              </p>
            </div>
          </template>

        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { authApi } from '@/api/auth.js'
import { toast } from 'vue-sonner'
import PasswordRequirements from '@/components/common/PasswordRequirements.vue'

const auth   = useAuthStore()
const router = useRouter()

const showPassword        = ref(false)
const showConfirmPassword = ref(false)
const step        = ref('form')
const maskedEmail = ref('')
const rawEmail    = ref('')
const otpCode     = ref('')
const verifying   = ref(false)
const resending   = ref(false)

const roles = [
  { value: 'tutor',    label: 'Tutor'    },
  { value: 'guardian', label: 'Guardian' },
]
const perks = [
  'Free account, no credit card needed',
  'Browse verified tutor profiles',
  'Post tuition requirements instantly',
]
const form = reactive({
  name: '', email: '', phone: '', role: 'guardian', password: '', password_confirmation: '',
})

const RECAPTCHA_SITE_KEY = import.meta.env.VITE_RECAPTCHA_SITE_KEY

async function getCaptchaToken() {
  // If no site key configured (local dev without keys), return a placeholder
  if (!RECAPTCHA_SITE_KEY) return 'dev-bypass'
  return new Promise((resolve, reject) => {
    window.grecaptcha.ready(() => {
      window.grecaptcha
        .execute(RECAPTCHA_SITE_KEY, { action: 'register' })
        .then(resolve)
        .catch(reject)
    })
  })
}

async function handleRegister() {
  try {
    const captcha_token = await getCaptchaToken()
    const data = await auth.register({ ...form, captcha_token })
    if (data.data?.pending_verification) {
      rawEmail.value    = form.email
      maskedEmail.value = data.data.email
      step.value        = 'otp'
      otpCode.value     = ''
      toast.success('Account created! Check your email for the verification code.')
    }
  } catch (e) {
    if (!e._toasted) {
      const errs = e.response?.data?.errors
      const msg  = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'Registration failed.')
      toast.error(msg)
    }
  }
}

async function handleVerify() {
  if (otpCode.value.length !== 6) return
  verifying.value = true
  try {
    const { data } = await authApi.verifyEmail({ email: rawEmail.value, code: otpCode.value })
    auth.setAuth(data.data.user, data.data.token)
    toast.success('Email verified! Welcome to TutorKhujo.')
    if (auth.isTutor) router.push('/tutor/profile')
    else router.push('/guardian/dashboard')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Invalid or expired code. Try again.')
  } finally {
    verifying.value = false
  }
}

async function handleResend() {
  resending.value = true
  try {
    await authApi.resendVerification({ email: rawEmail.value })
    toast.success('New code sent — check your inbox.')
    otpCode.value = ''
  } catch (e) {
    toast.error(e.response?.data?.message || 'Could not resend code.')
  } finally {
    resending.value = false
  }
}
</script>
