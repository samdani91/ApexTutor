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
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
          </svg>
        </div>
        <h2 class="font-display font-bold text-3xl text-white leading-snug">
          Connect with verified tutors across Bangladesh.
        </h2>
        <p class="text-navy-300 font-body text-sm leading-relaxed">
          Thousands of guardians and tutors trust TutorKhujo to find the right match — quickly and safely.
        </p>
        <div class="flex gap-6 pt-2">
          <div>
            <p class="font-display font-bold text-white text-xl">500+</p>
            <p class="text-navy-400 text-xs font-body mt-0.5">Verified tutors</p>
          </div>
          <div>
            <p class="font-display font-bold text-white text-xl">1 200+</p>
            <p class="text-navy-400 text-xs font-body mt-0.5">Connections made</p>
          </div>
        </div>
      </div>
      <p class="relative z-10 text-navy-500 text-xs font-body">© 2025 TutorKhujo</p>
    </div>

    <!-- ── Right form panel ── -->
    <div class="flex-1 flex flex-col bg-white">
      <div class="flex items-center justify-between px-6 pt-6 pb-2">
        <button @click="router.push('/')"
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

      <div class="flex-1 flex items-center justify-center px-6 py-10">
        <div class="w-full max-w-sm">

          <div class="mb-8">
            <h1 class="font-display font-bold text-2xl text-navy-900">Welcome back</h1>
            <p class="text-paper-400 text-sm mt-1.5 font-body">Sign in to your account to continue</p>
          </div>

          <form @submit.prevent="handleLogin" class="space-y-5">
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold font-display text-navy-700">Email address</label>
              <input v-model="form.email" type="email" id="email" name="email" autocomplete="username email" placeholder="you@example.com" class="input" required />
            </div>

            <div class="space-y-1.5">
              <label class="block text-sm font-semibold font-display text-navy-700">Password</label>
              <div class="relative">
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                  id="password" name="password" autocomplete="current-password" placeholder="••••••••" class="input pr-10" required />
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

            <button type="submit" :disabled="auth.loading" class="btn-primary w-full py-3 text-sm mt-2">
              <svg v-if="auth.loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ auth.loading ? 'Signing in…' : 'Log in' }}
            </button>
          </form>

          <div class="mt-8 pt-6 border-t border-paper-100 text-center">
            <p class="text-sm text-paper-400 font-body">
              Don't have an account?
              <RouterLink to="/register" class="text-navy-700 font-semibold hover:text-navy-900 transition-colors">
                Create one free
              </RouterLink>
            </p>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { toast } from 'vue-sonner'

const auth   = useAuthStore()
const router = useRouter()
const route  = useRoute()

const form         = reactive({ email: '', password: '' })
const showPassword = ref(false)

const RECAPTCHA_SITE_KEY = import.meta.env.VITE_RECAPTCHA_SITE_KEY

async function getCaptchaToken() {
  if (!RECAPTCHA_SITE_KEY) return 'dev-bypass'
  return new Promise((resolve, reject) => {
    window.grecaptcha.ready(() => {
      window.grecaptcha
        .execute(RECAPTCHA_SITE_KEY, { action: 'login' })
        .then(resolve)
        .catch(reject)
    })
  })
}

async function handleLogin() {
  try {
    const captcha_token = await getCaptchaToken()
    const data = await auth.login({ ...form, captcha_token })
    if (data.data?.token) {
      toast.success('Welcome back!')
      const redirect = route.query.redirect
      if (redirect) return router.push(redirect)
      if (auth.isTutor) router.push('/tutor/dashboard')
      else if (auth.isAdmin) router.push('/admin/dashboard')
      else router.push('/guardian/dashboard')
    }
  } catch (e) {
    if (!e._toasted) {
      toast.error(e.response?.data?.message || 'Invalid credentials. Please try again.')
    }
  }
}
</script>
