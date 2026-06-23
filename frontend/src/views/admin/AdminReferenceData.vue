<template>
  <div>
    <h1 class="font-display font-bold text-2xl text-navy-900 mb-6">Reference data</h1>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 border-b border-paper-200">
      <button v-for="t in tabs" :key="t.key" @click="activeTab = t.key"
        class="px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors"
        :class="activeTab === t.key ? 'bg-white border border-b-white border-paper-200 -mb-px text-navy-900' : 'text-paper-500 hover:text-navy-700'">
        {{ t.label }}
      </button>
    </div>

    <!-- ── Subjects tab ───────────────────────────────────────────── -->
    <div v-if="activeTab === 'subjects'">
      <!-- Add form -->
      <div class="card mb-5">
        <p class="font-display font-semibold text-navy-900 mb-3">Add subject</p>
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_160px_140px_auto] lg:items-end">
          <input v-model="newSubject.name" type="text" placeholder="Name (English)" class="input text-sm flex-1 min-w-[140px]" />
          <input v-model="newSubject.name_bn" type="text" placeholder="Name (Bangla)" class="input text-sm flex-1 min-w-[140px]" />
          <input v-model="newSubject.class_level" type="text" placeholder="Class level" class="input text-sm w-36" />
          <DropSelect v-model="newSubject.medium" :options="mediumOptions" placeholder="Medium" />
          <button @click="addSubject" :disabled="!newSubject.name || !newSubject.class_level || subjectAdding"
            class="btn-primary text-sm px-4 py-2 disabled:opacity-50">
            {{ subjectAdding ? 'Adding…' : 'Add' }}
          </button>
        </div>
      </div>

      <!-- Filter -->
      <div class="card mb-4">
        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_auto] md:items-end">
          <div>
            <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Search</span>
            <input v-model="subjectSearch" @input="loadSubjects" type="search" placeholder="Filter subjects..." class="input text-sm" />
          </div>
          <div>
            <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Class level</span>
            <input v-model="subjectClassFilter" @input="loadSubjects" type="text" placeholder="Class level" class="input text-sm" />
          </div>
          <button v-if="subjectSearch || subjectClassFilter" @click="clearSubjectFilters"
            class="min-h-[44px] rounded-sm bg-red-600 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-red-700">
            Clear
          </button>
        </div>
      </div>

      <div v-if="subjectsLoading" class="text-paper-500 font-body">Loading…</div>
      <div v-else-if="!subjects.length" class="card text-center py-8 text-paper-500 font-body">No subjects found.</div>
      <div v-else class="card overflow-hidden">
        <table class="w-full text-sm font-body">
          <thead>
            <tr class="border-b border-paper-200 bg-paper-50">
              <th class="text-left px-4 py-2.5 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Name</th>
              <th class="text-left px-4 py-2.5 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Name (BN)</th>
              <th class="text-left px-4 py-2.5 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Class</th>
              <th class="text-left px-4 py-2.5 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Medium</th>
              <th class="px-4 py-2.5"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in subjects" :key="s.id" class="border-b border-paper-100 last:border-0 hover:bg-paper-50">
              <td class="px-4 py-2.5">
                <input v-if="editingSubject === s.id" v-model="editBuf.name" class="input text-sm w-full" />
                <span v-else class="text-navy-900 font-semibold">{{ s.name }}</span>
              </td>
              <td class="px-4 py-2.5">
                <input v-if="editingSubject === s.id" v-model="editBuf.name_bn" class="input text-sm w-full" />
                <span v-else class="text-paper-600">{{ s.name_bn || '—' }}</span>
              </td>
              <td class="px-4 py-2.5">
                <input v-if="editingSubject === s.id" v-model="editBuf.class_level" class="input text-sm w-28" />
                <span v-else>{{ s.class_level }}</span>
              </td>
              <td class="px-4 py-2.5">
                <DropSelect v-if="editingSubject === s.id" v-model="editBuf.medium" :options="mediumOptions" placeholder="—" />
                <span v-else>{{ s.medium || '—' }}</span>
              </td>
              <td class="px-4 py-2.5 text-right">
                <template v-if="editingSubject === s.id">
                  <button @click="saveSubject(s)" class="rounded-sm bg-emerald-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-emerald-700 mr-2">Save</button>
                  <button @click="editingSubject = null" class="rounded-sm bg-paper-100 px-3 py-1.5 text-xs font-semibold font-display text-paper-600 hover:bg-paper-200">Cancel</button>
                </template>
                <template v-else>
                  <button @click="startEditSubject(s)" class="rounded-sm bg-navy-50 px-3 py-1.5 text-xs font-semibold font-display text-navy-700 border border-navy-100 hover:bg-navy-100 mr-2">Edit</button>
                  <button @click="openDelete('subject', s)" class="rounded-sm bg-red-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-red-700">Delete</button>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <AdminPagination :meta="subjectMeta" @page="fetchSubjects" />
    </div>

    <!-- ── Districts & Areas tab ─────────────────────────────────── -->
    <div v-else-if="activeTab === 'districts'">
      <!-- Add district -->
      <div class="card mb-5">
        <p class="font-display font-semibold text-navy-900 mb-3">Add district</p>
        <div class="flex flex-wrap gap-3">
          <input v-model="newDistrict.name" type="text" placeholder="District name" class="input text-sm flex-1 min-w-[160px]" />
          <input v-model="newDistrict.name_bn" type="text" placeholder="Bangla name" class="input text-sm flex-1 min-w-[160px]" />
          <input v-model="newDistrict.division" type="text" placeholder="Division" class="input text-sm w-36" />
          <button @click="addDistrict" :disabled="!newDistrict.name || districtAdding"
            class="btn-primary text-sm px-4 py-2 disabled:opacity-50">
            {{ districtAdding ? 'Adding…' : 'Add' }}
          </button>
        </div>
      </div>

      <div v-if="districtsLoading" class="text-paper-500 font-body">Loading…</div>
      <div v-else class="space-y-3">
        <div v-for="d in districts" :key="d.id" class="card">
          <!-- District header -->
          <div class="flex items-center justify-between gap-3 mb-2">
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <template v-if="editingDistrict === d.id">
                <input v-model="editBuf.name" class="input text-sm w-36" placeholder="Name" />
                <input v-model="editBuf.name_bn" class="input text-sm w-32" placeholder="Bangla" />
                <input v-model="editBuf.division" class="input text-sm w-28" placeholder="Division" />
                <button @click="saveDistrict(d)" class="rounded-sm bg-emerald-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-emerald-700">Save</button>
                <button @click="editingDistrict = null" class="rounded-sm bg-paper-100 px-3 py-1.5 text-xs font-semibold font-display text-paper-600 hover:bg-paper-200">Cancel</button>
              </template>
              <template v-else>
                <p class="font-display font-semibold text-navy-900">{{ d.name }}</p>
                <span v-if="d.name_bn" class="text-xs text-paper-500 font-body">{{ d.name_bn }}</span>
                <span v-if="d.division" class="text-xs text-paper-400 font-body">· {{ d.division }}</span>
              </template>
            </div>
            <div v-if="editingDistrict !== d.id" class="flex gap-2 shrink-0">
              <button @click="startEditDistrict(d)" class="rounded-sm bg-navy-50 px-3 py-1.5 text-xs font-semibold font-display text-navy-700 border border-navy-100 hover:bg-navy-100">Edit</button>
              <button @click="openDelete('district', d)" class="rounded-sm bg-red-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-red-700">Delete</button>
            </div>
          </div>

          <!-- Areas -->
          <div class="pl-3 border-l-2 border-paper-200 space-y-1.5">
            <div v-for="a in d.areas" :key="a.id" class="flex items-center gap-2">
              <template v-if="editingArea === a.id">
                <input v-model="editBuf.name" class="input text-sm flex-1" />
                <button @click="saveArea(a, d)" class="rounded-sm bg-emerald-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-emerald-700">Save</button>
                <button @click="editingArea = null" class="rounded-sm bg-paper-100 px-3 py-1.5 text-xs font-semibold font-display text-paper-600 hover:bg-paper-200">Cancel</button>
              </template>
              <template v-else>
                <span class="text-sm text-paper-600 font-body flex-1">{{ a.name }}</span>
                <button @click="startEditArea(a)" class="rounded-sm bg-navy-50 px-2.5 py-1 text-xs font-semibold font-display text-navy-700 border border-navy-100 hover:bg-navy-100">Edit</button>
                <button @click="openDelete('area', a, d)" class="rounded-sm bg-red-600 px-2.5 py-1 text-xs font-semibold font-display text-white hover:bg-red-700">Delete</button>
              </template>
            </div>
            <!-- Add area inline -->
            <div class="flex items-center gap-2 mt-2">
              <input v-model="newAreas[d.id]" type="text" :placeholder="`Add area in ${d.name}…`"
                class="input text-sm flex-1" @keyup.enter="addArea(d)" />
              <button @click="addArea(d)" :disabled="!newAreas[d.id]?.trim()"
                class="text-xs font-semibold text-navy-600 hover:text-navy-900 disabled:opacity-40 px-2 py-1 border border-paper-200 rounded-md">
                Add
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Universities tab ──────────────────────────────────────────── -->
    <div v-else-if="activeTab === 'universities'">
      <!-- Add form -->
      <div class="card mb-5">
        <p class="font-display font-semibold text-navy-900 mb-3">Add university</p>
        <div class="grid gap-3 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_140px_auto] lg:items-end">
          <input v-model="newUniversity.name" type="text" placeholder="University name" class="input text-sm" />
          <input v-model="newUniversity.district" type="text" placeholder="District" class="input text-sm" />
          <DropSelect v-model="newUniversity.type" :options="uniTypeOptions" placeholder="Type" />
          <button @click="addUniversity" :disabled="!newUniversity.name || !newUniversity.district || !newUniversity.type || uniAdding"
            class="btn-primary text-sm px-4 py-2 disabled:opacity-50">
            {{ uniAdding ? 'Adding…' : 'Add' }}
          </button>
        </div>
      </div>

      <!-- Filter -->
      <div class="card mb-4">
        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_140px_auto] md:items-end">
          <div>
            <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Search</span>
            <input v-model="uniSearch" @input="loadUniversities" type="search" placeholder="Filter universities…" class="input text-sm" />
          </div>
          <div>
            <span class="mb-1 block text-xs font-semibold font-display text-navy-700">District</span>
            <input v-model="uniDistrictFilter" @input="loadUniversities" type="text" placeholder="District" class="input text-sm" />
          </div>
          <div>
            <span class="mb-1 block text-xs font-semibold font-display text-navy-700">Type</span>
            <DropSelect v-model="uniTypeFilter" :options="uniTypeFilterOptions" @update:modelValue="loadUniversities" placeholder="All types" />
          </div>
          <button v-if="uniSearch || uniDistrictFilter || uniTypeFilter" @click="clearUniFilters"
            class="min-h-[44px] rounded-sm bg-red-600 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-red-700">
            Clear
          </button>
        </div>
      </div>

      <div v-if="uniLoading" class="text-paper-500 font-body">Loading…</div>
      <div v-else-if="!universities.length" class="card text-center py-8 text-paper-500 font-body">No universities found.</div>
      <div v-else class="card overflow-hidden">
        <table class="w-full text-sm font-body">
          <thead>
            <tr class="border-b border-paper-200 bg-paper-50">
              <th class="text-left px-4 py-2.5 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Name</th>
              <th class="text-left px-4 py-2.5 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">District</th>
              <th class="text-left px-4 py-2.5 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Type</th>
              <th class="text-left px-4 py-2.5 text-xs font-semibold font-display text-paper-400 uppercase tracking-wide">Logo</th>
              <th class="px-4 py-2.5"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in universities" :key="u.id" class="border-b border-paper-100 last:border-0 hover:bg-paper-50">
              <td class="px-4 py-2.5">
                <input v-if="editingUni === u.id" v-model="editBuf.name" class="input text-sm w-full" />
                <span v-else class="font-semibold text-navy-900">{{ u.name }}</span>
              </td>
              <td class="px-4 py-2.5">
                <input v-if="editingUni === u.id" v-model="editBuf.district" class="input text-sm w-28" />
                <span v-else>{{ u.district }}</span>
              </td>
              <td class="px-4 py-2.5">
                <DropSelect v-if="editingUni === u.id" v-model="editBuf.type" :options="uniTypeOptions" placeholder="Type" />
                <span v-else :class="u.type === 'public' ? 'text-emerald-700 font-semibold' : 'text-paper-500'">
                  {{ u.type === 'public' ? 'Public' : 'Private' }}
                </span>
              </td>
              <td class="px-4 py-2.5">
                <div class="flex items-center gap-2">
                  <img v-if="u.logo_url" :src="u.logo_url" :alt="u.name" class="h-8 w-8 rounded object-contain border border-paper-200 bg-white" />
                  <span v-else class="text-xs text-paper-400 font-body">None</span>
                  <label class="cursor-pointer rounded-sm bg-navy-50 px-2 py-1 text-xs font-semibold font-display text-navy-700 border border-navy-100 hover:bg-navy-100">
                    {{ u.logo_url ? 'Replace' : 'Upload' }}
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="(e) => handleLogoUpload(u, e)" />
                  </label>
                  <button v-if="u.logo_url" @click="handleLogoRemove(u)"
                    class="rounded-sm bg-red-50 px-2 py-1 text-xs font-semibold font-display text-red-600 border border-red-100 hover:bg-red-100">
                    Remove
                  </button>
                </div>
              </td>
              <td class="px-4 py-2.5 text-right">
                <template v-if="editingUni === u.id">
                  <button @click="saveUniversity(u)" class="rounded-sm bg-emerald-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-emerald-700 mr-2">Save</button>
                  <button @click="editingUni = null" class="rounded-sm bg-paper-100 px-3 py-1.5 text-xs font-semibold font-display text-paper-600 hover:bg-paper-200">Cancel</button>
                </template>
                <template v-else>
                  <button @click="startEditUni(u)" class="rounded-sm bg-navy-50 px-3 py-1.5 text-xs font-semibold font-display text-navy-700 border border-navy-100 hover:bg-navy-100 mr-2">Edit</button>
                  <button @click="openDelete('university', u)" class="rounded-sm bg-red-600 px-3 py-1.5 text-xs font-semibold font-display text-white hover:bg-red-700">Delete</button>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <AdminPagination :meta="uniMeta" @page="fetchUniversities" />
    </div>

    <AdminConfirmDialog
      :show="!!deleteTarget"
      :title="deleteDialogTitle"
      :message="deleteDialogMessage"
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="deleteTarget = null"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'
import { toast } from 'vue-sonner'
import AdminConfirmDialog from '@/components/admin/AdminConfirmDialog.vue'
import AdminPagination from '@/components/admin/AdminPagination.vue'
import DropSelect from '@/components/search/DropSelect.vue'

const activeTab = ref('subjects')
const tabs = [
  { key: 'subjects', label: 'Subjects' },
  { key: 'districts', label: 'Districts & Areas' },
  { key: 'universities', label: 'Universities' },
]
const deleteTarget = ref(null)
const deleteDialogTitle = computed(() => {
  if (!deleteTarget.value) return 'Delete item?'
  return `Delete ${deleteTarget.value.type}?`
})
const deleteDialogMessage = computed(() => {
  if (!deleteTarget.value) return ''
  const item = deleteTarget.value.item
  const name = item?.name || 'this item'
  if (deleteTarget.value.type === 'district') return `Delete district '${name}' and all its areas? This cannot be undone.`
  return `Delete ${deleteTarget.value.type} '${name}'? This cannot be undone.`
})
const mediumOptions = [
  { value: '', label: 'Medium' },
  { value: 'English', label: 'English' },
  { value: 'Bangla', label: 'Bangla' },
  { value: 'Both', label: 'Both' },
]

// ── Subjects ──────────────────────────────────────────────────────────────
const subjects        = ref([])
const subjectsLoading = ref(false)
const subjectSearch   = ref('')
const subjectClassFilter = ref('')
const subjectAdding   = ref(false)
const editingSubject  = ref(null)
const editBuf         = reactive({})
const newSubject      = reactive({ name: '', name_bn: '', class_level: '', medium: '' })
const subjectMeta     = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

let subjectTimer = null
function loadSubjects() {
  clearTimeout(subjectTimer)
  subjectTimer = setTimeout(() => fetchSubjects(), 300)
}

async function fetchSubjects(page = 1) {
  subjectsLoading.value = true
  try {
    const { data } = await adminApi.getSubjects({ search: subjectSearch.value, class_level: subjectClassFilter.value, page, per_page: 10 })
    subjects.value = data.data.data ?? data.data
    subjectMeta.value = data.data
  } finally {
    subjectsLoading.value = false
  }
}

function clearSubjectFilters() {
  subjectSearch.value = ''
  subjectClassFilter.value = ''
  fetchSubjects()
}

async function addSubject() {
  subjectAdding.value = true
  try {
    await adminApi.createSubject({ ...newSubject })
    Object.assign(newSubject, { name: '', name_bn: '', class_level: '', medium: '' })
    await fetchSubjects()
    toast.success('Subject added.')
  } catch { toast.error('Could not add subject.') }
  finally { subjectAdding.value = false }
}

function startEditSubject(s) { editingSubject.value = s.id; Object.assign(editBuf, { name: s.name, name_bn: s.name_bn, class_level: s.class_level, medium: s.medium }) }

async function saveSubject(s) {
  try {
    await adminApi.updateSubject(s.id, { ...editBuf })
    Object.assign(s, { ...editBuf })
    editingSubject.value = null
    toast.success('Subject updated.')
  } catch { toast.error('Could not update.') }
}

function openDelete(type, item, parent = null) {
  deleteTarget.value = { type, item, parent }
}

async function removeSubject(s) {
  try {
    await adminApi.deleteSubject(s.id)
    subjects.value = subjects.value.filter(x => x.id !== s.id)
    toast.success('Subject deleted.')
  } catch { toast.error('Could not delete.') }
}

// ── Districts ─────────────────────────────────────────────────────────────
const districts        = ref([])
const districtsLoading = ref(false)
const districtAdding   = ref(false)
const editingDistrict  = ref(null)
const editingArea      = ref(null)
const newDistrict      = reactive({ name: '', name_bn: '', division: '' })
const newAreas         = reactive({})

async function fetchDistricts() {
  districtsLoading.value = true
  try {
    const { data } = await adminApi.getDistricts()
    districts.value = data.data
  } finally {
    districtsLoading.value = false }
}

async function addDistrict() {
  districtAdding.value = true
  try {
    await adminApi.createDistrict({ ...newDistrict })
    Object.assign(newDistrict, { name: '', name_bn: '', division: '' })
    await fetchDistricts()
    toast.success('District added.')
  } catch { toast.error('Could not add district.') }
  finally { districtAdding.value = false }
}

function startEditDistrict(d) { editingDistrict.value = d.id; Object.assign(editBuf, { name: d.name, name_bn: d.name_bn, division: d.division }) }

async function saveDistrict(d) {
  try {
    await adminApi.updateDistrict(d.id, { ...editBuf })
    Object.assign(d, { ...editBuf })
    editingDistrict.value = null
    toast.success('District updated.')
  } catch { toast.error('Could not update.') }
}

async function removeDistrict(d) {
  try {
    await adminApi.deleteDistrict(d.id)
    districts.value = districts.value.filter(x => x.id !== d.id)
    toast.success('District deleted.')
  } catch { toast.error('Could not delete.') }
}

async function addArea(d) {
  const name = newAreas[d.id]?.trim()
  if (!name) return
  try {
    const { data } = await adminApi.createArea({ district_id: d.id, name })
    if (!d.areas) d.areas = []
    d.areas.push(data.data)
    newAreas[d.id] = ''
    toast.success('Area added.')
  } catch { toast.error('Could not add area.') }
}

function startEditArea(a) { editingArea.value = a.id; Object.assign(editBuf, { name: a.name }) }

async function saveArea(a, d) {
  try {
    await adminApi.updateArea(a.id, { name: editBuf.name })
    a.name = editBuf.name
    editingArea.value = null
    toast.success('Area updated.')
  } catch { toast.error('Could not update.') }
}

async function removeArea(a, d) {
  try {
    await adminApi.deleteArea(a.id)
    d.areas = d.areas.filter(x => x.id !== a.id)
    toast.success('Area deleted.')
  } catch { toast.error('Could not delete.') }
}

async function confirmDelete() {
  const target = deleteTarget.value
  deleteTarget.value = null
  if (!target) return
  if (target.type === 'subject') await removeSubject(target.item)
  if (target.type === 'district') await removeDistrict(target.item)
  if (target.type === 'area') await removeArea(target.item, target.parent)
  if (target.type === 'university') await removeUniversity(target.item)
}

// ── Universities ──────────────────────────────────────────────────────────
const universities    = ref([])
const uniLoading      = ref(false)
const uniSearch       = ref('')
const uniDistrictFilter = ref('')
const uniTypeFilter   = ref('')
const uniAdding       = ref(false)
const editingUni      = ref(null)
const newUniversity   = reactive({ name: '', district: '', type: '' })
const uniMeta         = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const uniTypeOptions  = [
  { value: 'public', label: 'Public' },
  { value: 'private', label: 'Private' },
]
const uniTypeFilterOptions = [
  { value: '', label: 'All types' },
  { value: 'public', label: 'Public' },
  { value: 'private', label: 'Private' },
]

let uniTimer = null
function loadUniversities() {
  clearTimeout(uniTimer)
  uniTimer = setTimeout(() => fetchUniversities(), 300)
}

async function fetchUniversities(page = 1) {
  uniLoading.value = true
  try {
    const { data } = await adminApi.getUniversities({ search: uniSearch.value, district: uniDistrictFilter.value, type: uniTypeFilter.value, page, per_page: 20 })
    universities.value = data.data.data ?? data.data
    uniMeta.value = data.data
  } finally { uniLoading.value = false }
}

function clearUniFilters() {
  uniSearch.value = ''
  uniDistrictFilter.value = ''
  uniTypeFilter.value = ''
  fetchUniversities()
}

async function addUniversity() {
  uniAdding.value = true
  try {
    await adminApi.createUniversity({ ...newUniversity })
    Object.assign(newUniversity, { name: '', district: '', type: '' })
    await fetchUniversities()
    toast.success('University added.')
  } catch { toast.error('Could not add university.') }
  finally { uniAdding.value = false }
}

function startEditUni(u) { editingUni.value = u.id; Object.assign(editBuf, { name: u.name, district: u.district, type: u.type }) }

async function saveUniversity(u) {
  try {
    await adminApi.updateUniversity(u.id, { ...editBuf })
    Object.assign(u, { ...editBuf })
    editingUni.value = null
    toast.success('University updated.')
  } catch { toast.error('Could not update.') }
}

async function removeUniversity(u) {
  try {
    await adminApi.deleteUniversity(u.id)
    universities.value = universities.value.filter(x => x.id !== u.id)
    toast.success('University deleted.')
  } catch { toast.error('Could not delete.') }
}

async function handleLogoUpload(u, event) {
  const file = event.target.files?.[0]
  if (!file) return
  if (file.size > 512 * 1024) {
    toast.error('Logo must be under 512 KB. Please resize the image and try again.')
    event.target.value = ''
    return
  }
  const formData = new FormData()
  formData.append('logo', file)
  try {
    const { data } = await adminApi.uploadUniversityLogo(u.id, formData)
    u.logo_url = data.logo_url
    toast.success('Logo uploaded.')
  } catch { toast.error('Could not upload logo.') }
  event.target.value = ''
}

async function handleLogoRemove(u) {
  try {
    await adminApi.removeUniversityLogo(u.id)
    u.logo_url = null
    toast.success('Logo removed.')
  } catch { toast.error('Could not remove logo.') }
}

onMounted(async () => {
  await fetchSubjects()
  await fetchDistricts()
  await fetchUniversities()
})
</script>
