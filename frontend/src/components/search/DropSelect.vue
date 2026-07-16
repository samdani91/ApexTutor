<template>
  <div ref="triggerRef" class="relative w-full">
    <!-- Trigger -->
    <button
      type="button"
      @click="toggle"
      class="input text-sm w-full flex items-center justify-between gap-2 text-left pr-8"
      :class="hasValue ? 'text-navy-900' : 'text-paper-400'"
    >
      <span class="truncate">{{ selectedLabel }}</span>
      <svg
        class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-paper-400 shrink-0 transition-transform"
        :class="open ? 'rotate-180' : ''"
        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <!-- Options panel — teleported so it's never clipped by overflow containers -->
    <Teleport to="body">
      <!-- Backdrop to catch outside clicks -->
      <div v-if="open" class="fixed inset-0 z-[298]" @click="open = false" />

      <Transition name="drop">
        <div
          v-if="open"
          :style="panelStyle"
          class="fixed z-[299] bg-white border border-paper-200 rounded-sm shadow-lg overflow-hidden"
        >
          <!-- Optional search input (shown when options > 8) -->
          <div v-if="options.length > 8" class="border-b border-paper-100 px-2 py-1.5">
            <input
              ref="searchRef"
              v-model="query"
              type="text"
              placeholder="Search…"
              class="w-full text-sm font-body text-navy-800 bg-transparent outline-none placeholder:text-paper-300 px-1"
              @click.stop
            />
          </div>
          <div class="overflow-y-auto" :style="`max-height: ${maxListHeight}px`">
            <button
              v-for="opt in filtered"
              :key="opt.value"
              type="button"
              @click="select(opt.value)"
              class="w-full text-left px-3 py-2 text-sm font-body transition-colors flex items-center gap-2"
              :class="isSelected(opt.value)
                ? 'bg-navy-700 text-white font-semibold'
                : 'text-navy-700 hover:bg-navy-50'"
            >
              <span v-if="multiple"
                class="h-4 w-4 rounded-sm border flex items-center justify-center text-[10px] leading-none"
                :class="isSelected(opt.value) ? 'border-white bg-white text-navy-700' : 'border-paper-300 bg-white text-transparent'">
                ✓
              </span>
              <span class="truncate">{{ opt.label }}</span>
            </button>
            <p v-if="!filtered.length" class="px-3 py-2 text-xs text-paper-400 font-body">No results</p>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: { default: '' },
  options:    { type: Array, required: true }, // [{ value, label }]
  placeholder:{ type: String, default: 'Select…' },
  multiple:   { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue'])

const triggerRef = ref(null)
const searchRef  = ref(null)
const open       = ref(false)
const query      = ref('')
const panelStyle = ref({})
const maxListHeight = ref(220)

const selectedLabel = computed(() => {
  if (props.multiple) {
    const selected = Array.isArray(props.modelValue) ? props.modelValue : []
    if (!selected.length) return props.placeholder
    const labels = props.options
      .filter(o => selected.includes(o.value))
      .map(o => o.label)
    if (!labels.length) return props.placeholder
    if (labels.length <= 2) return labels.join(', ')
    return `${labels.slice(0, 2).join(', ')} +${labels.length - 2}`
  }
  const opt = props.options.find(o => o.value === props.modelValue)
  return opt ? opt.label : props.placeholder
})

const hasValue = computed(() => {
  if (props.multiple) return Array.isArray(props.modelValue) && props.modelValue.length > 0
  return props.modelValue !== '' && props.modelValue !== null
})

const filtered = computed(() => {
  if (!query.value.trim()) return props.options
  const q = query.value.toLowerCase()
  return props.options.filter(o => o.label.toLowerCase().includes(q))
})

function computePosition() {
  const el = triggerRef.value
  if (!el) return
  const rect = el.getBoundingClientRect()
  const vp   = window.innerHeight
  const searchH    = props.options.length > 8 ? 44 : 0
  const spaceBelow = vp - rect.bottom - 12
  const spaceAbove = rect.top - 12

  // Open downward by default; flip upward when the list would be cut off
  // below and there's more room above (e.g. triggers near the screen bottom).
  const desired = Math.min(220, props.options.length * 38 + 8) + searchH
  const openUp  = spaceBelow < desired && spaceAbove > spaceBelow

  maxListHeight.value = Math.min(220, Math.max(80, (openUp ? spaceAbove : spaceBelow) - searchH))

  panelStyle.value = {
    left:     `${rect.left}px`,
    width:    `${rect.width}px`,
    minWidth: '160px',
    ...(openUp
      ? { bottom: `${vp - rect.top + 4}px` }   // anchored above the trigger, grows upward
      : { top: `${rect.bottom + 4}px` }),
  }
}

async function toggle() {
  if (open.value) { open.value = false; return }
  computePosition()
  open.value = true
  query.value = ''
  if (props.options.length > 8) {
    await nextTick()
    searchRef.value?.focus()
  }
}

function isSelected(val) {
  if (props.multiple) return Array.isArray(props.modelValue) && props.modelValue.includes(val)
  return val === props.modelValue
}

function select(val) {
  if (props.multiple) {
    const selected = Array.isArray(props.modelValue) ? [...props.modelValue] : []
    const index = selected.indexOf(val)
    if (index === -1) selected.push(val)
    else selected.splice(index, 1)
    emit('update:modelValue', selected)
    return
  }
  emit('update:modelValue', val)
  open.value = false
  query.value = ''
}

// Recompute on scroll/resize so panel follows the trigger
function onScroll() { if (open.value) computePosition() }
window.addEventListener('scroll', onScroll, true)
window.addEventListener('resize', onScroll)
onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll, true)
  window.removeEventListener('resize', onScroll)
})
</script>

<style scoped>
.drop-enter-active { transition: opacity 0.12s ease, transform 0.12s ease; }
.drop-leave-active { transition: opacity 0.08s ease, transform 0.08s ease; }
.drop-enter-from  { opacity: 0; transform: translateY(-4px); }
.drop-leave-to    { opacity: 0; transform: translateY(-4px); }
</style>
