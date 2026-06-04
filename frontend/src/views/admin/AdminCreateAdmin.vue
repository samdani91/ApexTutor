<template>
  <div class="w-full">
    <RouterLink to="/admin/users" class="inline-flex items-center gap-1.5 text-sm font-semibold font-display text-navy-700 hover:text-navy-900 mb-5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
      </svg>
      Back to users
    </RouterLink>

    <div class="mb-6">
      <h1 class="font-display font-bold text-2xl text-navy-900">Create admin account</h1>
      <p class="mt-1 text-sm font-body text-paper-500">Add a platform administrator with dashboard access.</p>
    </div>

    <form @submit.prevent="trySubmit" novalidate class="space-y-5">
      <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="card space-y-5">
          <div>
            <p class="font-display text-base font-semibold text-navy-900">Account details</p>
            <p class="mt-1 text-sm font-body text-paper-500">These details identify the admin in user lists and audit records.</p>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <!-- Full name -->
            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Full name <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text" required autocomplete="name"
                placeholder="Admin full name" class="input text-sm w-full"
                :class="fieldErr.name ? 'border-red-400 focus:border-red-500' : ''"
                @input="enforceName" @blur="validateName" />
              <p v-if="fieldErr.name" class="mt-1 text-xs text-red-600 font-body">{{ fieldErr.name }}</p>
              <p v-else class="mt-1 text-xs text-paper-400 font-body">Letters, spaces and dots only — no numbers or symbols.</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Email <span class="text-red-500">*</span></label>
              <input v-model="form.email" type="email" required autocomplete="email"
                placeholder="admin@example.com" class="input text-sm w-full"
                :class="fieldErr.email ? 'border-red-400 focus:border-red-500' : ''"
                @blur="validateEmail" />
              <p v-if="fieldErr.email" class="mt-1 text-xs text-red-600 font-body">{{ fieldErr.email }}</p>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Mobile number <span class="text-red-500">*</span></label>
              <input v-model="form.phone" type="tel" required autocomplete="tel"
                placeholder="017xxxxxxxx" class="input text-sm w-full"
                :class="fieldErr.phone ? 'border-red-400 focus:border-red-500' : ''"
                @input="enforcePhone" @blur="validatePhone"
                maxlength="11" inputmode="numeric" />
              <p v-if="fieldErr.phone" class="mt-1 text-xs text-red-600 font-body">{{ fieldErr.phone }}</p>
              <p v-else class="mt-1 text-xs text-paper-400 font-body">11 digits, numbers only — e.g. 017xxxxxxxx</p>
            </div>
          </div>
        </div>

        <div class="card space-y-4">
          <div>
            <p class="font-display text-base font-semibold text-navy-900">Access</p>
            <p class="mt-1 text-sm font-body text-paper-500">New admin accounts receive platform admin access.</p>
          </div>
          <div class="rounded-sm border border-navy-100 bg-navy-50 px-3 py-3">
            <p class="text-xs font-semibold font-display uppercase tracking-wide text-paper-500">Role</p>
            <p class="mt-1 font-display text-sm font-semibold text-navy-800">Super admin</p>
          </div>
        </div>
      </div>

      <!-- Password -->
      <div class="card space-y-5">
        <div>
          <p class="font-display text-base font-semibold text-navy-900">Password</p>
          <p class="mt-1 text-sm font-body text-paper-500">Must be at least 8 characters with uppercase, lowercase, number and symbol.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Password <span class="text-red-500">*</span></label>
            <div class="relative">
              <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required
                autocomplete="new-password" placeholder="Min. 8 characters"
                class="input text-sm w-full pr-10"
                :class="fieldErr.password ? 'border-red-400 focus:border-red-500' : ''" />
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
            <PasswordRequirements v-if="form.password" :password="form.password" />
            <p v-if="fieldErr.password" class="mt-1 text-xs text-red-600 font-body">{{ fieldErr.password }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Confirm password <span class="text-red-500">*</span></label>
            <div class="relative">
              <input v-model="form.password_confirmation" :type="showConfirm ? 'text' : 'password'" required
                autocomplete="new-password" placeholder="Repeat password"
                class="input text-sm w-full pr-10"
                :class="fieldErr.password_confirmation ? 'border-red-400 focus:border-red-500' : ''"
                @blur="validateConfirm" />
              <button type="button" @click="showConfirm = !showConfirm"
                class="absolute inset-y-0 right-0 flex items-center px-3 text-paper-400 hover:text-navy-700 transition-colors">
                <svg v-if="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </button>
            </div>
            <p v-if="fieldErr.password_confirmation" class="mt-1 text-xs text-red-600 font-body">{{ fieldErr.password_confirmation }}</p>
            <p v-else-if="form.password_confirmation && form.password === form.password_confirmation"
              class="mt-1 text-xs text-emerald-600 font-body flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
              </svg>
              Passwords match
            </p>
          </div>
        </div>
      </div>

      <div v-if="error" class="rounded-sm bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 font-body">
        {{ error }}
      </div>

      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
        <button type="button" @click="cancelConfirmOpen = true"
          class="inline-flex min-h-[42px] items-center justify-center rounded-sm border border-paper-300 bg-paper-100 px-4 text-sm font-semibold font-display text-paper-700 transition-colors hover:bg-paper-200">
          Cancel
        </button>
        <button type="submit" :disabled="saving || !passwordValid"
          class="inline-flex min-h-[42px] items-center justify-center rounded-sm bg-navy-700 px-6 text-sm font-semibold font-display text-white transition-colors hover:bg-navy-900 disabled:opacity-50">
          {{ saving ? 'Creating…' : 'Create Account' }}
        </button>
      </div>
    </form>

    <AdminConfirmDialog
      :show="createConfirmOpen"
      title="Create Admin Account?"
      message="Create this admin account with the details entered?"
      confirm-label="Create Account"
      @confirm="submit"
      @cancel="createConfirmOpen = false"
    />

    <AdminConfirmDialog
      :show="cancelConfirmOpen"
      title="Cancel Admin Creation?"
      message="Leave this page and discard the entered admin account details?"
      confirm-label="Leave Page"
      danger
      @confirm="confirmCancel"
      @cancel="cancelConfirmOpen = false"
    />
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'
import PasswordRequirements from '@/components/common/PasswordRequirements.vue'

const router = useRouter()
const saving = ref(false)
const error  = ref('')
const createConfirmOpen = ref(false)
const cancelConfirmOpen = ref(false)
const showPassword = ref(false)
const showConfirm  = ref(false)

const form = reactive({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
const fieldErr = reactive({ name: '', email: '', phone: '', password: '', password_confirmation: '' })

const PHONE_RE = /^01[3-9]\d{8}$/          // exactly 11 digits, Bangladeshi mobile
const NAME_RE  = /^[^\d!@#$%^&*()+=[\]{}|;:'",<>?/\\~`]+$/  // no digits or symbols except dot/space
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const passwordValid = computed(() => {
  const p = form.password
  return p.length >= 8 && /[A-Z]/.test(p) && /[a-z]/.test(p) && /[0-9]/.test(p) && /[^A-Za-z0-9]/.test(p)
})

function validateEmail() {
  fieldErr.email = !form.email ? 'Email is required.' : !EMAIL_RE.test(form.email) ? 'Enter a valid email address.' : ''
}

function validateName() {
  if (!form.name.trim()) {
    fieldErr.name = 'Full name is required.'
  } else if (/\d/.test(form.name)) {
    fieldErr.name = 'Name must not contain numbers.'
  } else if (/[!@#$%^&*()+={}\[\]|;:'"<>?\/\\~`]/.test(form.name)) {
    fieldErr.name = 'Name may only contain letters, spaces, and dots.'
  } else {
    fieldErr.name = ''
  }
}

function validatePhone() {
  if (!form.phone) {
    fieldErr.phone = 'Mobile number is required.'
  } else if (!/^\d+$/.test(form.phone)) {
    fieldErr.phone = 'Phone number must contain digits only.'
  } else if (form.phone.length !== 11) {
    fieldErr.phone = `Phone number must be exactly 11 digits (${form.phone.length} entered).`
  } else if (!PHONE_RE.test(form.phone)) {
    fieldErr.phone = 'Enter a valid Bangladeshi number (e.g. 017xxxxxxxx).'
  } else {
    fieldErr.phone = ''
  }
}

function enforceName(e) {
  const cleaned = e.target.value.replace(/[\d!@#$%^&*()+={}\[\]|;:'"<>?\/\\~`]/g, '')
  if (cleaned !== e.target.value) { form.name = cleaned; e.target.value = cleaned }
}

function enforcePhone(e) {
  const cleaned = e.target.value.replace(/\D/g, '').slice(0, 11)
  if (cleaned !== e.target.value) { form.phone = cleaned; e.target.value = cleaned }
}

function validateConfirm() {
  fieldErr.password_confirmation = form.password_confirmation && form.password !== form.password_confirmation ? 'Passwords do not match.' : ''
}

function validateAll() {
  validateName()
  validateEmail()
  validatePhone()
  fieldErr.password = !passwordValid.value ? 'Password must meet all requirements.' : ''
  validateConfirm()
  if (!form.password_confirmation) fieldErr.password_confirmation = 'Please confirm your password.'
  return !Object.values(fieldErr).some(Boolean)
}

function trySubmit() {
  if (!validateAll()) return
  createConfirmOpen.value = true
}

async function submit() {
  createConfirmOpen.value = false
  error.value = ''
  saving.value = true
  try {
    await adminApi.createAdmin({ ...form })
    toast.success('Admin account created.')
    router.push('/admin/users')
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) {
      Object.entries(errors).forEach(([key, msgs]) => { if (key in fieldErr) fieldErr[key] = msgs[0] })
    }
    error.value = e.response?.data?.message ?? 'Could not create account.'
  } finally {
    saving.value = false
  }
}

function confirmCancel() {
  cancelConfirmOpen.value = false
  router.push('/admin/users')
}
</script>
