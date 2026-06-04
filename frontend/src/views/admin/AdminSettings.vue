<template>
  <div class="admin-settings space-y-6">
    <div class="settings-card reveal">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="font-display text-xs font-bold uppercase text-gold-600">Admin Settings</p>
          <h1 class="mt-1 font-display font-bold text-2xl text-navy-900 md:text-3xl">Account Settings</h1>
          <p class="mt-2 max-w-2xl font-body text-sm leading-relaxed text-paper-600">
            Update your admin profile details and secure your account password.
          </p>
        </div>
      </div>
    </div>

    <!-- Sub-tabs -->
    <div class="reveal flex w-full gap-1 rounded-md border border-paper-200 bg-white p-1 shadow-sm sm:w-fit">
      <button
        v-for="tab in tabs" :key="tab.key"
        @click="activeTab = tab.key"
        class="flex items-center gap-2 px-4 py-1.5 rounded-md text-sm font-semibold font-display transition-colors"
        :class="activeTab === tab.key ? 'bg-navy-700 text-white shadow-sm' : 'text-paper-500 hover:bg-navy-50 hover:text-navy-700'"
      >
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
          <path v-if="tab.key === 'profile'" stroke-linecap="round" stroke-linejoin="round"
            d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path v-else stroke-linecap="round" stroke-linejoin="round"
            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
        </svg>
        {{ tab.label }}
      </button>
    </div>

    <!-- ── Profile tab ── -->
    <div v-if="activeTab === 'profile'" class="settings-card reveal">
      <div class="grid gap-6 lg:grid-cols-[18rem_minmax(0,1fr)]">
        <aside class="rounded-md border border-paper-200 bg-paper-50 p-4">
          <h2 class="font-display font-bold text-navy-900 text-lg">Profile Information</h2>
          <p class="mt-2 font-body text-sm leading-relaxed text-paper-600">
            Keep your admin identity and contact details up to date.
          </p>

          <!-- Avatar -->
          <div class="mt-5 flex items-center gap-4">
            <div class="relative shrink-0">
              <div class="w-16 h-16 rounded-lg bg-navy-100 flex items-center justify-center overflow-hidden ring-4 ring-white shadow">
                <img v-if="auth.user?.avatar_url" :src="auth.user.avatar_url" class="w-full h-full object-cover" alt="" />
                <span v-else class="font-display font-bold text-xl text-navy-700">{{ initials }}</span>
              </div>
              <label class="absolute bottom-0 right-0 w-6 h-6 bg-gold-400 rounded-full flex items-center justify-center cursor-pointer hover:bg-gold-500 shadow transition-colors">
                <svg class="w-3 h-3 text-navy-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                </svg>
                <input type="file" class="hidden" accept="image/jpeg,image/png,image/webp"
                  @change="uploadAvatar" :disabled="uploadingAvatar" />
              </label>
            </div>
            <div class="min-w-0">
              <p class="font-display font-semibold text-navy-900 truncate">{{ auth.user?.name }}</p>
              <p class="text-xs text-paper-400 font-body capitalize mt-0.5">{{ auth.user?.role?.replace('_', ' ') }}</p>
              <p v-if="uploadingAvatar" class="text-xs text-navy-500 font-body mt-1">Uploading…</p>
            </div>
          </div>
        </aside>

      <form @submit.prevent="saveProfile" class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-1">
          <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Full name</label>
          <input v-model="profileForm.name" type="text" name="name" id="name" autocomplete="name"
            class="input" required />
        </div>
        <div class="md:col-span-1">
          <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Email</label>
          <input v-model="profileForm.email" type="email" name="email" id="email" autocomplete="email"
            class="input" required />
        </div>
        <div class="md:col-span-1">
          <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Mobile number</label>
          <input v-model="profileForm.phone" type="tel" name="phone" id="phone" autocomplete="tel"
            class="input" placeholder="e.g. 01700000000" maxlength="20" />
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Address</label>
          <textarea v-model="profileForm.address" name="address" id="address" autocomplete="street-address"
            class="input resize-none" rows="4" placeholder="Your address…" maxlength="500" />
        </div>
        <p v-if="profileError" class="md:col-span-2 text-red-600 text-sm font-body">{{ profileError }}</p>
        <div class="md:col-span-2">
        <button type="submit" :disabled="savingProfile" class="btn-primary text-sm py-2.5 px-5">
          {{ savingProfile ? 'Saving…' : 'Save Changes' }}
        </button>
        </div>
      </form>
      </div>
    </div>

    <!-- ── Security tab ── -->
    <div v-if="activeTab === 'security'" class="settings-card reveal">
      <div class="grid gap-6 lg:grid-cols-[18rem_minmax(0,1fr)]">
        <aside class="rounded-md border border-paper-200 bg-paper-50 p-4">
          <h2 class="font-display font-bold text-navy-900 text-lg">Change Password</h2>
          <p class="mt-2 font-body text-sm leading-relaxed text-paper-600">
            Use a strong password and avoid reusing credentials from other services.
          </p>
        </aside>

      <form @submit.prevent="savePassword" class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Current password</label>
          <div class="relative">
            <input v-model="passwordForm.current_password" :type="showCurrent ? 'text' : 'password'"
              name="current-password" id="current-password" autocomplete="current-password"
              class="input pr-10" required placeholder="••••••••" />
            <button type="button" @click="showCurrent = !showCurrent"
              class="absolute inset-y-0 right-0 flex items-center px-3 text-paper-400 hover:text-navy-700 transition-colors">
              <EyeIcon :open="showCurrent" />
            </button>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold font-display text-navy-700 mb-1">New password</label>
          <div class="relative">
            <input v-model="passwordForm.password" :type="showNew ? 'text' : 'password'"
              name="new-password" id="new-password" autocomplete="new-password"
              class="input pr-10" required placeholder="Min. 8 characters" minlength="8" />
            <button type="button" @click="showNew = !showNew"
              class="absolute inset-y-0 right-0 flex items-center px-3 text-paper-400 hover:text-navy-700 transition-colors">
              <EyeIcon :open="showNew" />
            </button>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Confirm new password</label>
          <div class="relative">
            <input v-model="passwordForm.password_confirmation" :type="showConfirm ? 'text' : 'password'"
              name="confirm-password" id="confirm-password" autocomplete="new-password"
              class="input pr-10" required placeholder="Repeat new password" />
            <button type="button" @click="showConfirm = !showConfirm"
              class="absolute inset-y-0 right-0 flex items-center px-3 text-paper-400 hover:text-navy-700 transition-colors">
              <EyeIcon :open="showConfirm" />
            </button>
          </div>
        </div>
        <p v-if="passwordError" class="md:col-span-2 text-red-600 text-sm font-body">{{ passwordError }}</p>
        <div class="md:col-span-2">
        <button type="submit" :disabled="savingPassword" class="btn-primary text-sm py-2.5 px-5">
          {{ savingPassword ? 'Saving…' : 'Change Password' }}
        </button>
        </div>
      </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import { authApi } from '@/api/auth.js'
import { toast } from 'vue-sonner'
import { getInitials } from '@/utils/helpers.js'

const EyeIcon = {
  props: ['open'],
  template: `
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <template v-if="open">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
      </template>
      <template v-else>
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
      </template>
    </svg>
  `,
}

const auth = useAuthStore()

const tabs = [
  { key: 'profile',  label: 'Profile'  },
  { key: 'security', label: 'Security' },
]
const activeTab = ref('profile')

const initials       = computed(() => getInitials(auth.user?.name))
const uploadingAvatar = ref(false)

const profileForm   = reactive({
  name:    auth.user?.name    ?? '',
  email:   auth.user?.email   ?? '',
  phone:   auth.user?.phone   ?? '',
  address: auth.user?.address ?? '',
})
const savingProfile = ref(false)
const profileError  = ref('')

const passwordForm   = reactive({ current_password: '', password: '', password_confirmation: '' })
const savingPassword = ref(false)
const passwordError  = ref('')
const showCurrent    = ref(false)
const showNew        = ref(false)
const showConfirm    = ref(false)

async function uploadAvatar(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (!file) return
  uploadingAvatar.value = true
  try {
    const fd = new FormData()
    fd.append('avatar', file)
    await auth.uploadAvatar(fd)
    toast.success('Avatar updated.')
  } catch {
    toast.error('Upload failed. Max 2 MB, JPG/PNG/WebP.')
  } finally {
    uploadingAvatar.value = false
  }
}

async function saveProfile() {
  profileError.value = ''
  savingProfile.value = true
  try {
    const { data } = await authApi.updateProfile({
      name:    profileForm.name,
      email:   profileForm.email,
      phone:   profileForm.phone   || null,
      address: profileForm.address || null,
    })
    auth.user = data.data
    toast.success('Profile updated.')
  } catch (e) {
    const errs = e.response?.data?.errors
    profileError.value = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'Update failed.')
  } finally {
    savingProfile.value = false
  }
}

async function savePassword() {
  passwordError.value = ''
  if (passwordForm.password !== passwordForm.password_confirmation) {
    passwordError.value = 'New passwords do not match.'
    return
  }
  savingPassword.value = true
  try {
    await authApi.changePassword(passwordForm)
    Object.assign(passwordForm, { current_password: '', password: '', password_confirmation: '' })
    toast.success('Password changed successfully.')
  } catch (e) {
    const errs = e.response?.data?.errors
    passwordError.value = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'Failed to change password.')
  } finally {
    savingPassword.value = false
  }
}
</script>

<style scoped>
.settings-card {
  @apply rounded-md border border-paper-200 bg-white p-5 shadow-lg md:p-6;
}

.reveal {
  animation: reveal-up 0.54s ease both;
}

@keyframes reveal-up {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .reveal {
    animation: none;
  }
}
</style>
