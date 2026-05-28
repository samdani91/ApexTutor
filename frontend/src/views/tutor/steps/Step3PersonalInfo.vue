<template>
  <div>
    <h2 class="font-display font-semibold text-navy-700 text-lg mb-5">Personal Information</h2>

    <!-- About me -->
    <div class="mb-5">
      <label class="block text-xs font-semibold font-display text-navy-700 mb-1">About me</label>
      <textarea v-model="bio" rows="4" class="input text-sm resize-none"
        placeholder="Write a short introduction — your teaching background, strengths, and what makes you a great tutor…" />
      <p class="text-xs text-paper-400 font-body mt-1">Shown on your public profile. Max 2000 characters.</p>
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
      <!-- Phone from registration (read-only) -->
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Mobile number</label>
        <input :value="auth.user?.phone" type="text" class="input text-sm bg-paper-100 text-paper-500 cursor-not-allowed" readonly />
        <p class="text-xs text-paper-400 font-body mt-1">Registered phone — contact support to change.</p>
      </div>

      <!-- Additional phone -->
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Additional phone number</label>
        <input v-model="form.additional_phone" type="text" class="input text-sm" placeholder="e.g. 01700000000" />
      </div>

      <!-- Gender -->
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Gender</label>
        <select v-model="form.gender" class="input text-sm">
          <option value="">Select</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>
      </div>

      <!-- Date of birth -->
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Date of birth</label>
        <input v-model="form.date_of_birth" type="date" class="input text-sm" />
      </div>

      <!-- Religion -->
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Religion</label>
        <select v-model="form.religion" class="input text-sm">
          <option value="">Prefer not to say</option>
          <option value="islam">Islam</option>
          <option value="hinduism">Hinduism</option>
          <option value="christianity">Christianity</option>
          <option value="buddhism">Buddhism</option>
          <option value="other">Other</option>
        </select>
      </div>

      <!-- Nationality -->
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Nationality</label>
        <input v-model="form.nationality" type="text" class="input text-sm" placeholder="Bangladeshi" />
      </div>

      <!-- National ID -->
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">National ID number</label>
        <input v-model="form.national_id" type="text" class="input text-sm" placeholder="NID number" />
      </div>

      <!-- Present address -->
      <div class="sm:col-span-2">
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Present address</label>
        <textarea v-model="form.present_address" rows="2" class="input text-sm resize-none"
          placeholder="Your current address" />
      </div>

      <!-- Permanent address -->
      <div class="sm:col-span-2">
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Permanent address</label>
        <textarea v-model="form.permanent_address" rows="2" class="input text-sm resize-none"
          placeholder="Your permanent / home address" />
      </div>
    </div>

    <!-- Social links -->
    <div class="grid sm:grid-cols-2 gap-5 mt-5">
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Facebook profile URL</label>
        <input v-model="form.facebook_url" type="url" class="input text-sm" placeholder="https://facebook.com/yourprofile" />
      </div>
      <div>
        <label class="block text-xs font-semibold font-display text-navy-700 mb-1">LinkedIn profile URL</label>
        <input v-model="form.linkedin_url" type="url" class="input text-sm" placeholder="https://linkedin.com/in/yourprofile" />
      </div>
    </div>

    <!-- Parents information -->
    <div class="mt-5">
      <h3 class="font-display font-semibold text-navy-700 text-sm mb-3">Parents / Guardian information</h3>
      <div class="grid sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Father's name</label>
          <input v-model="form.fathers_name" type="text" class="input text-sm" placeholder="Father's full name" />
        </div>
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Father's phone</label>
          <input v-model="form.fathers_phone" type="text" class="input text-sm" placeholder="Father's phone number" />
        </div>
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Mother's name</label>
          <input v-model="form.mothers_name" type="text" class="input text-sm" placeholder="Mother's full name" />
        </div>
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Mother's phone</label>
          <input v-model="form.mothers_phone" type="text" class="input text-sm" placeholder="Mother's phone number" />
        </div>
      </div>
    </div>

    <button @click="save" :disabled="saving" class="btn-primary mt-6 text-sm py-2.5 px-6">
      {{ saving ? 'Saving…' : 'Save personal information' }}
    </button>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'
import { useAuthStore } from '@/stores/auth.js'
import { toast } from 'vue-sonner'

const emit = defineEmits(['saved'])
const auth   = useAuthStore()
const saving = ref(false)
const bio    = ref('')

const form = reactive({
  gender: '',
  date_of_birth: '',
  religion: '',
  nationality: 'Bangladeshi',
  present_address: '',
  permanent_address: '',
  additional_phone: '',
  national_id: '',
  facebook_url: '',
  linkedin_url: '',
  fathers_name: '',
  fathers_phone: '',
  mothers_name: '',
  mothers_phone: '',
})

onMounted(async () => {
  try {
    const [profileRes, infoRes] = await Promise.all([
      tutorApi.getProfile(),
      tutorApi.getPersonalInfo(),
    ])
    bio.value = profileRes.data.data?.bio || ''
    const info = infoRes.data.data
    if (info) {
      Object.assign(form, {
        gender:            info.gender || '',
        date_of_birth:     info.date_of_birth ? info.date_of_birth.substring(0, 10) : '',
        religion:          info.religion || '',
        nationality:       info.nationality || 'Bangladeshi',
        present_address:   info.present_address || '',
        permanent_address: info.permanent_address || '',
        additional_phone:  info.additional_phone || '',
        national_id:       info.national_id || '',
        facebook_url:      info.facebook_url || '',
        linkedin_url:      info.linkedin_url || '',
        fathers_name:      info.fathers_name || '',
        fathers_phone:     info.fathers_phone || '',
        mothers_name:      info.mothers_name || '',
        mothers_phone:     info.mothers_phone || '',
      })
    }
  } catch {}
})

async function save() {
  saving.value = true
  try {
    const [bioRes, infoRes] = await Promise.all([
      tutorApi.updateProfile({ bio: bio.value }),
      tutorApi.savePersonalInfo(form),
    ])
    const isPending = !!(bioRes.data?.pending || infoRes.data?.pending)
    emit('saved', isPending)
  } catch (e) {
    const msg = e.response?.data?.message
      || Object.values(e.response?.data?.errors || {})[0]?.[0]
      || 'Failed to save personal information.'
    toast.error(msg)
  } finally {
    saving.value = false
  }
}
</script>
