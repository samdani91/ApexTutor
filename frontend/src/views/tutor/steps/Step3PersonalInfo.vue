<template>
  <div class="space-y-5">
    <section class="card">
      <div class="mb-5">
        <h2 class="font-display font-semibold text-navy-700 text-lg">Personal information</h2>
      </div>

      <div class="space-y-5">
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">About me</label>
          <textarea v-model="bio" rows="4" class="input text-sm resize-none"
            placeholder="Write a short introduction — your teaching background, strengths, and what makes you a great tutor…" />
          <p class="text-xs text-paper-400 font-body mt-1">Shown on your public profile. Max 2000 characters.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Email address</label>
            <input :value="auth.user?.email" type="email" class="input text-sm bg-paper-100 text-paper-500 cursor-not-allowed" readonly />
            <p class="text-xs text-paper-400 font-body mt-1">Registered email — contact admin to change.</p>
          </div>
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Mobile number</label>
            <input :value="auth.user?.phone" type="text" class="input text-sm bg-paper-100 text-paper-500 cursor-not-allowed" readonly />
            <p class="text-xs text-paper-400 font-body mt-1">Registered phone — contact support to change.</p>
          </div>
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Additional phone number</label>
            <input v-model="form.additional_phone" type="text" class="input text-sm" placeholder="e.g. 01700000000" />
          </div>
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Gender</label>
            <DropSelect v-model="form.gender" :options="genderOptions" placeholder="Select" />
          </div>
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Date of birth</label>
            <input v-model="form.date_of_birth" type="date" class="input text-sm" />
          </div>
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Religion</label>
            <DropSelect v-model="form.religion" :options="religionOptions" placeholder="Prefer not to say" />
          </div>
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Nationality</label>
            <input v-model="form.nationality" type="text" class="input text-sm" placeholder="Bangladeshi" />
          </div>
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">National ID number</label>
            <input v-model="form.national_id" type="text" class="input text-sm" placeholder="NID number" />
          </div>
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Facebook profile URL</label>
            <input v-model="form.facebook_url" type="url" class="input text-sm" placeholder="https://facebook.com/yourprofile" />
          </div>
          <div>
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">LinkedIn profile URL</label>
            <input v-model="form.linkedin_url" type="url" class="input text-sm" placeholder="https://linkedin.com/in/yourprofile" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Present address</label>
            <textarea v-model="form.present_address" rows="2" class="input text-sm resize-none"
              placeholder="Your current address" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Permanent address</label>
            <textarea v-model="form.permanent_address" rows="2" class="input text-sm resize-none"
              placeholder="Your permanent / home address" />
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-start">
        <button @click="savePersonalSection" :disabled="!!savingSection" class="btn-primary text-sm py-2.5 px-6">
          {{ savingSection === 'personal' ? 'Saving…' : 'Save personal information' }}
        </button>
      </div>
    </section>

    <section class="card">
      <div class="mb-5">
        <h2 class="font-display font-semibold text-navy-700 text-lg">Guardian information</h2>
      </div>

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

      <div class="mt-6 flex justify-start">
        <button @click="saveGuardianSection" :disabled="!!savingSection" class="btn-primary text-sm py-2.5 px-6">
          {{ savingSection === 'guardian' ? 'Saving…' : 'Save guardian information' }}
        </button>
      </div>
    </section>

    <section class="card">
      <div class="mb-5">
        <h2 class="font-display font-semibold text-navy-700 text-lg">Emergency contact</h2>
      </div>

      <div class="grid sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Contact name</label>
          <input v-model="emergency.name" type="text" class="input text-sm" placeholder="Full name" />
        </div>
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Relation</label>
          <DropSelect v-model="emergency.relation" :options="relationOptions" placeholder="Select" />
        </div>
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Phone number</label>
          <input v-model="emergency.phone" type="text" class="input text-sm" placeholder="e.g. 01700000000" />
        </div>
        <div>
          <label class="block text-xs font-semibold font-display text-navy-700 mb-1">Address</label>
          <input v-model="emergency.address" type="text" class="input text-sm" placeholder="Optional address" />
        </div>
      </div>

      <div class="mt-6 flex justify-start">
        <button @click="saveEmergencySection" :disabled="!!savingSection" class="btn-primary text-sm py-2.5 px-6">
          {{ savingSection === 'emergency' ? 'Saving…' : 'Save emergency contact' }}
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { tutorApi } from '@/api/tutor.js'
import { useAuthStore } from '@/stores/auth.js'
import { toast } from 'vue-sonner'

const emit = defineEmits(['saved'])
const auth = useAuthStore()
const savingSection = ref(null)
const bio = ref('')
const initialBio = ref('')
const initialForm = ref(null)
const initialEmergency = ref(null)

const personalKeys = [
  'gender',
  'date_of_birth',
  'religion',
  'nationality',
  'present_address',
  'permanent_address',
  'additional_phone',
  'national_id',
  'facebook_url',
  'linkedin_url',
]
const genderOptions = [
  { value: '', label: 'Select' },
  { value: 'male', label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other', label: 'Other' },
]
const religionOptions = [
  { value: '', label: 'Prefer not to say' },
  { value: 'islam', label: 'Islam' },
  { value: 'hinduism', label: 'Hinduism' },
  { value: 'christianity', label: 'Christianity' },
  { value: 'buddhism', label: 'Buddhism' },
  { value: 'other', label: 'Other' },
]
const relationOptions = [
  { value: '', label: 'Select' },
  { value: 'father', label: 'Father' },
  { value: 'mother', label: 'Mother' },
  { value: 'sibling', label: 'Sibling' },
  { value: 'spouse', label: 'Spouse' },
  { value: 'friend', label: 'Friend' },
  { value: 'relative', label: 'Relative' },
  { value: 'other', label: 'Other' },
]

const guardianKeys = [
  'fathers_name',
  'fathers_phone',
  'mothers_name',
  'mothers_phone',
]

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

const emergency = reactive({
  name: '',
  relation: '',
  phone: '',
  address: '',
})

onMounted(async () => {
  try {
    const [profileRes, infoRes, emergencyRes] = await Promise.all([
      tutorApi.getProfile(),
      tutorApi.getPersonalInfo(),
      tutorApi.getEmergencyContact(),
    ])
    bio.value = profileRes.data.data?.bio || ''
    initialBio.value = bio.value
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
    const emergencyContact = emergencyRes.data.data || profileRes.data.data?.emergency_contact
    if (emergencyContact) {
      Object.assign(emergency, {
        name:     emergencyContact.name || '',
        relation: emergencyContact.relation || '',
        phone:    emergencyContact.phone || '',
        address:  emergencyContact.address || '',
      })
    }
    initialForm.value = snapshot(form)
    initialEmergency.value = snapshot(emergency)
  } catch {}
})

async function savePersonalSection() {
  const payload = changedPayload(form, personalKeys, initialForm.value)
  const requests = []

  if (bio.value !== initialBio.value) {
    requests.push(tutorApi.updateProfile({ bio: bio.value }))
  }
  if (Object.keys(payload).length) {
    requests.push(tutorApi.savePersonalInfo(payload))
  }

  await runSectionSave('personal', requests, () => {
    initialBio.value = bio.value
    updateInitialFormKeys(personalKeys)
  })
}

async function saveGuardianSection() {
  const payload = changedPayload(form, guardianKeys, initialForm.value)
  const requests = Object.keys(payload).length ? [tutorApi.savePersonalInfo(payload)] : []

  await runSectionSave('guardian', requests, () => {
    updateInitialFormKeys(guardianKeys)
  })
}

async function saveEmergencySection() {
  const values = Object.values(emergency).map(value => String(value || '').trim())
  const hasEmergencyInput = values.some(Boolean)
  const hasRequiredEmergency = emergency.name && emergency.relation && emergency.phone

  if (hasEmergencyInput && !hasRequiredEmergency) {
    toast.error('Emergency contact name, relation, and phone are required.')
    return
  }

  const requests = hasRequiredEmergency && hasChanged(emergency, initialEmergency.value)
    ? [tutorApi.saveEmergencyContact(normalize(emergency))]
    : []

  await runSectionSave('emergency', requests, () => {
    initialEmergency.value = snapshot(emergency)
  })
}

async function runSectionSave(section, requests, onSuccess) {
  if (!requests.length) {
    toast.info('No changes to save.')
    return
  }

  savingSection.value = section
  try {
    const responses = await Promise.all(requests)
    onSuccess()
    const isPending = responses.some(res => !!res.data?.pending)
    emit('saved', isPending, false)
  } catch (e) {
    const msg = e.response?.data?.message
      || Object.values(e.response?.data?.errors || {})[0]?.[0]
      || 'Failed to save information.'
    toast.error(msg)
  } finally {
    savingSection.value = null
  }
}

function changedPayload(source, keys, initial) {
  const current = normalize(source)
  const original = initial ? JSON.parse(initial) : {}

  return keys.reduce((payload, key) => {
    if (current[key] !== original[key]) {
      payload[key] = current[key]
    }
    return payload
  }, {})
}

function updateInitialFormKeys(keys) {
  const current = normalize(form)
  const original = initialForm.value ? JSON.parse(initialForm.value) : {}

  for (const key of keys) {
    original[key] = current[key]
  }

  initialForm.value = JSON.stringify(original)
}

function snapshot(source) {
  return JSON.stringify(normalize(source))
}

function hasChanged(source, initial) {
  return snapshot(source) !== initial
}

function normalize(source) {
  return Object.fromEntries(
    Object.entries(source).map(([key, value]) => [key, value === '' ? null : value])
  )
}
</script>
