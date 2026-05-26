<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-6">Post a requirement</h1>
    <div class="card max-w-2xl">
      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Student name</label>
          <input v-model="form.student_name" type="text" class="input" required placeholder="Student's name" />
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Medium</label>
            <select v-model="form.medium" class="input" required>
              <option value="">Select medium</option>
              <option v-for="m in MEDIUMS" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Class</label>
            <select v-model="form.class_level" class="input" required>
              <option value="">Select class</option>
              <option v-for="c in CLASS_LEVELS" :key="c.value" :value="c.value">{{ c.label }}</option>
            </select>
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold font-display text-navy-700 mb-1">City / Area</label>
            <input v-model="form.city" type="text" class="input" placeholder="Mirpur, Dhaka" />
          </div>
          <div>
            <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Budget (BDT/mo)</label>
            <input v-model.number="form.salary_max" type="number" min="0" class="input" placeholder="5000" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold font-display text-navy-700 mb-1">Special notes</label>
          <textarea v-model="form.special_notes" rows="3" class="input" placeholder="Any specific requirements…"></textarea>
        </div>
        <p v-if="error" class="text-red-600 text-sm font-body">{{ error }}</p>
        <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? 'Posting…' : 'Post requirement' }}</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { guardianApi } from '@/api/guardian.js'
import { MEDIUMS, CLASS_LEVELS } from '@/utils/constants.js'
import { toast } from 'vue-sonner'

const router = useRouter()
const saving = ref(false)
const error = ref('')
const form = reactive({ student_name: '', medium: '', class_level: '', city: '', salary_max: '', special_notes: '' })

async function submit() {
  error.value = ''
  saving.value = true
  try {
    await guardianApi.postRequirement(form)
    toast.success('Requirement posted!')
    router.push('/guardian/dashboard')
  } catch (e) {
    error.value = e.response?.data?.message || 'Could not post requirement.'
  } finally {
    saving.value = false
  }
}
</script>
