<template>
  <div class="w-full">
    <RouterLink :to="{ name: 'admin-tutor-detail', params: { id: route.params.id } }"
      class="inline-flex items-center gap-1.5 text-sm font-semibold font-display text-navy-700 hover:text-navy-900 mb-5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
      </svg>
      Back to profile
    </RouterLink>

    <div v-if="loading" class="text-paper-500 font-body py-12 text-center">Loading…</div>

    <template v-else>
      <div class="flex flex-col gap-3 mb-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h1 class="font-display font-bold text-2xl text-navy-900">Edit tutor profile</h1>
          <p class="text-sm text-paper-400 font-body mt-0.5">{{ tutor?.user?.name }} · {{ tutor?.tutor_id }}</p>
        </div>
        <button @click="saveConfirmOpen = true" :disabled="saving"
          class="btn-primary text-sm px-5 py-2.5 disabled:opacity-50">
          {{ saving ? 'Saving…' : 'Save all changes' }}
        </button>
      </div>

      <!-- ── Account info ─────────────────────────────────────── -->
      <div class="grid gap-4 xl:grid-cols-2">
      <section class="card">
        <h2 class="section-title">Account info</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <field label="Full name"><input v-model="form.user.name" class="input text-sm w-full" /></field>
          <field label="Email"><input v-model="form.user.email" type="email" class="input text-sm w-full" /></field>
          <field label="Phone"><input v-model="form.user.phone" class="input text-sm w-full" /></field>
          <field label="Address"><input v-model="form.user.address" class="input text-sm w-full" /></field>
        </div>
      </section>

      <!-- ── Bio ─────────────────────────────────────────────── -->
      <section class="card">
        <h2 class="section-title">Bio</h2>
        <textarea v-model="form.profile.bio" rows="4" class="input text-sm w-full resize-none"
          placeholder="Tutor bio…" maxlength="2000" />
        <p class="text-xs text-paper-400 font-body mt-1 text-right">{{ (form.profile.bio || '').length }}/2000</p>
      </section>
      </div>

      <!-- ── Personal info ────────────────────────────────────── -->
      <section class="card mt-4">
        <h2 class="section-title">Personal information</h2>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
          <field label="Gender">
            <select v-model="form.personal_info.gender" class="input text-sm w-full">
              <option value="">Not specified</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </field>
          <field label="Date of birth"><input v-model="form.personal_info.date_of_birth" type="date" class="input text-sm w-full" /></field>
          <field label="Nationality"><input v-model="form.personal_info.nationality" class="input text-sm w-full" /></field>
          <field label="Religion"><input v-model="form.personal_info.religion" class="input text-sm w-full" /></field>
          <field label="National ID"><input v-model="form.personal_info.national_id" class="input text-sm w-full" /></field>
          <field label="Additional phone"><input v-model="form.personal_info.additional_phone" class="input text-sm w-full" /></field>
          <field label="Present address" class="sm:col-span-2"><input v-model="form.personal_info.present_address" class="input text-sm w-full" /></field>
          <field label="Permanent address" class="sm:col-span-2"><input v-model="form.personal_info.permanent_address" class="input text-sm w-full" /></field>
          <field label="Facebook URL"><input v-model="form.personal_info.facebook_url" class="input text-sm w-full" /></field>
          <field label="LinkedIn URL"><input v-model="form.personal_info.linkedin_url" class="input text-sm w-full" /></field>
          <field label="Father's name"><input v-model="form.personal_info.fathers_name" class="input text-sm w-full" /></field>
          <field label="Father's phone"><input v-model="form.personal_info.fathers_phone" class="input text-sm w-full" /></field>
          <field label="Mother's name"><input v-model="form.personal_info.mothers_name" class="input text-sm w-full" /></field>
          <field label="Mother's phone"><input v-model="form.personal_info.mothers_phone" class="input text-sm w-full" /></field>
        </div>
      </section>

      <div class="grid gap-4 mt-4 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
      <!-- ── Emergency contact ────────────────────────────────── -->
      <section class="card">
        <h2 class="section-title">Emergency contact</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <field label="Name"><input v-model="form.emergency_contact.name" class="input text-sm w-full" /></field>
          <field label="Relation"><input v-model="form.emergency_contact.relation" class="input text-sm w-full" /></field>
          <field label="Phone"><input v-model="form.emergency_contact.phone" class="input text-sm w-full" /></field>
          <field label="Address"><input v-model="form.emergency_contact.address" class="input text-sm w-full" /></field>
        </div>
      </section>

      <!-- ── Tuition preferences ──────────────────────────────── -->
      <section class="card">
        <h2 class="section-title">Tuition preferences</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <field label="Min salary (৳/mo)"><input v-model.number="form.preference.expected_salary_min" type="number" min="0" class="input text-sm w-full" /></field>
          <field label="Max salary (৳/mo)"><input v-model.number="form.preference.expected_salary_max" type="number" min="0" class="input text-sm w-full" /></field>
          <field label="Experience (years)"><input v-model.number="form.preference.total_experience_years" type="number" min="0" class="input text-sm w-full" /></field>
          <field label="Days per week"><input v-model.number="form.preference.days_per_week" type="number" min="1" max="7" class="input text-sm w-full" /></field>
          <field label="Hours per session"><input v-model.number="form.preference.hours_per_day" type="number" min="0.5" max="12" step="0.5" class="input text-sm w-full" /></field>
          <field label="Experience details" class="sm:col-span-2">
            <textarea v-model="form.preference.experience_details" rows="3" class="input text-sm w-full resize-none" maxlength="2000" />
          </field>
          <field label="Teaching method description" class="sm:col-span-2">
            <textarea v-model="form.preference.tutoring_method_description" rows="3" class="input text-sm w-full resize-none" maxlength="1000" />
          </field>
        </div>
      </section>
      </div>

      <!-- Save button (bottom) -->
      <div class="flex flex-col-reverse gap-3 mt-4 sm:flex-row sm:items-center sm:justify-end">
        <button @click="saveConfirmOpen = true" :disabled="saving"
          class="btn-primary text-sm px-6 py-2.5 disabled:opacity-50">
          {{ saving ? 'Saving…' : 'Save all changes' }}
        </button>
        <button @click="cancelConfirmOpen = true"
          class="text-sm font-semibold font-display px-4 py-2.5 rounded-sm border border-paper-300 bg-paper-100 text-paper-700 hover:bg-paper-200 transition-colors">
          Cancel
        </button>
      </div>

      <AdminConfirmDialog
        :show="saveConfirmOpen"
        title="Save Tutor Profile Changes?"
        message="Apply these edits directly to the tutor profile?"
        confirm-label="Save Changes"
        @confirm="save"
        @cancel="saveConfirmOpen = false"
      />

      <AdminConfirmDialog
        :show="cancelConfirmOpen"
        title="Cancel Tutor Edits?"
        message="Leave this page and discard unsaved tutor profile edits?"
        confirm-label="Leave Page"
        danger
        @confirm="confirmCancel"
        @cancel="cancelConfirmOpen = false"
      />
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, defineComponent, h } from 'vue'

const field = defineComponent({
  props: ['label'],
  setup(props, { slots, attrs }) {
    return () => h('div', { class: attrs.class }, [
      h('label', { class: 'block text-xs font-semibold font-display text-paper-500 mb-1' }, props.label),
      slots.default?.(),
    ])
  },
})
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'

const route  = useRoute()
const router = useRouter()
const tutor  = ref(null)
const loading = ref(true)
const saving  = ref(false)
const saveConfirmOpen = ref(false)
const cancelConfirmOpen = ref(false)

const form = reactive({
  user:              { name: '', email: '', phone: '', address: '' },
  profile:           { bio: '' },
  personal_info:     { gender: '', date_of_birth: '', nationality: '', religion: '', national_id: '', additional_phone: '', present_address: '', permanent_address: '', facebook_url: '', linkedin_url: '', fathers_name: '', fathers_phone: '', mothers_name: '', mothers_phone: '' },
  emergency_contact: { name: '', relation: '', phone: '', address: '' },
  preference:        { expected_salary_min: '', expected_salary_max: '', total_experience_years: '', days_per_week: '', hours_per_day: '', experience_details: '', tutoring_method_description: '' },
})

onMounted(async () => {
  try {
    const { data } = await adminApi.getTutor(route.params.id)
    tutor.value = data.data
    populate(data.data)
  } finally {
    loading.value = false
  }
})

function populate(t) {
  Object.assign(form.user, { name: t.user?.name ?? '', email: t.user?.email ?? '', phone: t.user?.phone ?? '', address: t.user?.address ?? '' })
  Object.assign(form.profile, { bio: t.bio ?? '' })

  const pi = t.personal_info ?? {}
  Object.assign(form.personal_info, { gender: pi.gender ?? '', date_of_birth: pi.date_of_birth ? pi.date_of_birth.split('T')[0] : '', nationality: pi.nationality ?? '', religion: pi.religion ?? '', national_id: pi.national_id ?? '', additional_phone: pi.additional_phone ?? '', present_address: pi.present_address ?? '', permanent_address: pi.permanent_address ?? '', facebook_url: pi.facebook_url ?? '', linkedin_url: pi.linkedin_url ?? '', fathers_name: pi.fathers_name ?? '', fathers_phone: pi.fathers_phone ?? '', mothers_name: pi.mothers_name ?? '', mothers_phone: pi.mothers_phone ?? '' })

  const ec = t.emergency_contact ?? {}
  Object.assign(form.emergency_contact, { name: ec.name ?? '', relation: ec.relation ?? '', phone: ec.phone ?? '', address: ec.address ?? '' })

  const pref = t.tuition_preference ?? {}
  Object.assign(form.preference, { expected_salary_min: pref.expected_salary_min ?? '', expected_salary_max: pref.expected_salary_max ?? '', total_experience_years: pref.total_experience_years ?? '', days_per_week: pref.days_per_week ?? '', hours_per_day: pref.hours_per_day ?? '', experience_details: pref.experience_details ?? '', tutoring_method_description: pref.tutoring_method_description ?? '' })
}

async function save() {
  saveConfirmOpen.value = false
  saving.value = true
  try {
    await adminApi.updateTutor(route.params.id, { ...form })
    toast.success('Profile updated successfully.')
    router.push({ name: 'admin-tutor-detail', params: { id: route.params.id } })
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not save changes.')
  } finally {
    saving.value = false
  }
}

function confirmCancel() {
  cancelConfirmOpen.value = false
  router.push({ name: 'admin-tutor-detail', params: { id: route.params.id } })
}
</script>

<style scoped>
.section-title { @apply font-display font-semibold text-navy-800 text-base mb-4 pb-2 border-b border-paper-100; }
</style>
