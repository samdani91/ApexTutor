<template>
  <DefaultLayout>
    <main class="landing-page relative isolate overflow-hidden bg-white text-navy-900">
      <div class="pointer-events-none absolute inset-0 -z-10 landing-grid"></div>

      <section>

        <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-7xl gap-8 px-4 pb-10 pt-10 sm:px-6 md:pb-12 lg:grid-cols-[minmax(0,1fr)_28rem] lg:items-center lg:gap-12">
          <div class="reveal max-w-3xl">
            <div class="inline-flex items-center gap-2 rounded-pill border border-gold-200 bg-white px-3 py-1.5 shadow-xs">
              <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
              <span class="font-display text-xs font-bold uppercase text-navy-700">Verified tutor marketplace</span>
            </div>

            <h1 class="mt-5 font-display text-4xl font-bold leading-tight text-navy-900 sm:text-5xl lg:text-6xl">
              Find the right tutor with less searching and more clarity.
            </h1>
            <p class="mt-5 max-w-2xl font-body text-base leading-relaxed text-paper-700 md:text-lg">
              TutorKhujo helps guardians compare verified tutors by class, subject, area, salary, teaching style and profile strength before making a shortlist.
            </p>

            <div class="mt-7 max-w-2xl rounded-md border border-paper-200 bg-white p-2 shadow-lg">
              <div class="flex flex-col gap-2 sm:flex-row">
                <label class="relative flex-1">
                  <span class="sr-only">Search Tutors</span>
                  <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-paper-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z" />
                  </svg>
                  <input
                    v-model="query"
                    @keyup.enter="goSearch"
                    type="search"
                    placeholder="Search by subject, class, or area"
                    class="min-h-[48px] w-full rounded-sm border border-paper-200 bg-paper-50 py-2 pl-9 pr-3 font-body text-sm text-navy-900 outline-none transition-colors placeholder:text-paper-400 focus:border-gold-400 focus:bg-white"
                  />
                </label>
                <button @click="goSearch" class="btn-primary min-h-[48px] rounded-sm px-6 text-sm">
                  Search Tutors
                </button>
              </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
              <button
                v-for="chip in quickChips"
                :key="chip"
                @click="quickSearch(chip)"
                class="rounded-pill border border-paper-200 bg-white px-3 py-1.5 font-display text-xs font-semibold text-navy-700 shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:border-gold-300 hover:text-navy-900"
              >
                {{ chip }}
              </button>
            </div>
          </div>

          <div class="reveal reveal-delay">
            <div class="relative mx-auto max-w-md">
              <div class="absolute -left-4 top-8 hidden rounded-md border border-paper-200 bg-white px-4 py-3 shadow-md sm:block float-card">
                <p class="font-display text-2xl font-bold text-navy-900">{{ stats[0].display }}{{ stats[0].suffix }}</p>
                <p class="font-body text-xs text-paper-600">verified tutors</p>
              </div>

              <div class="rounded-lg border border-paper-200 bg-white p-4 shadow-xl">
                <div class="flex items-center justify-between gap-3 border-b border-paper-100 pb-4">
                  <div>
                    <p class="font-display text-sm font-bold text-navy-900">Tutor match preview</p>
                    <p class="mt-1 font-body text-xs text-paper-500">Based on class, subject, location and budget</p>
                  </div>
                  <span class="rounded-pill bg-emerald-50 px-2.5 py-1 font-display text-xs font-bold text-emerald-700">Verified</span>
                </div>

                <div class="mt-4 flex items-start gap-3">
                  <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-md bg-navy-700 font-display text-lg font-bold text-white">
                    TK
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                      <h2 class="font-display text-lg font-bold text-navy-900">Top Mathematics Tutor</h2>
                      <span class="rounded-pill bg-gold-50 px-2 py-0.5 font-display text-xs font-bold text-gold-700">98% match</span>
                    </div>
                    <p class="mt-1 font-body text-sm text-paper-600">Class 9-10, SSC, HSC</p>
                  </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                  <div v-for="metric in previewMetrics" :key="metric.label" class="rounded-sm border border-paper-200 bg-paper-50 p-3">
                    <p class="font-display text-sm font-bold text-navy-900">{{ metric.value }}</p>
                    <p class="mt-1 font-body text-xs text-paper-500">{{ metric.label }}</p>
                  </div>
                </div>

                <div class="mt-5 space-y-3">
                  <div v-for="item in heroChecks" :key="item" class="flex items-center gap-2 text-sm text-paper-700">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                      <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                      </svg>
                    </span>
                    <span>{{ item }}</span>
                  </div>
                </div>

                <div class="mt-5 rounded-md bg-navy-700 p-4 text-white">
                  <div class="flex items-center justify-between gap-3">
                    <div>
                      <p class="font-display text-sm font-bold">Admin-assisted connection</p>
                      <p class="mt-1 font-body text-xs text-navy-100">Shortlist first, confirm with support.</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-sm bg-white/12">
                      <svg class="h-5 w-5 text-gold-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12.5 11 15l5-6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                      </svg>
                    </div>
                  </div>
                </div>
              </div>

              <div class="absolute -bottom-5 right-3 rounded-md border border-paper-200 bg-white px-4 py-3 shadow-md float-card-delayed">
                <p class="font-display text-sm font-bold text-navy-900">Fast shortlist</p>
                <p class="mt-1 font-body text-xs text-paper-600">Compare profiles side by side</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section ref="statsRef" class="bg-white/82 px-4 py-8 backdrop-blur-[1px] sm:px-6 md:py-10">
        <div class="mx-auto grid max-w-6xl gap-3 sm:grid-cols-3">
          <div v-for="stat in stats" :key="stat.label" class="reveal rounded-md border border-paper-200 bg-paper-50 px-5 py-5 shadow-xs">
            <p class="font-display text-3xl font-bold text-navy-900 md:text-4xl">
              {{ stat.display }}<span v-if="stat.suffix">{{ stat.suffix }}</span>
            </p>
            <p class="mt-1 font-body text-sm text-paper-600">{{ stat.label }}</p>
          </div>
        </div>
      </section>

      <section class="px-4 py-14 sm:px-6 md:py-18">
        <div class="mx-auto max-w-6xl">
          <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
              <p class="font-display text-xs font-bold uppercase text-gold-600">How it works</p>
              <h2 class="mt-2 font-display text-2xl font-bold text-navy-900 md:text-3xl">From search to confirmed tuition</h2>
            </div>
            <RouterLink to="/search" class="btn-outline w-fit rounded-sm px-4 py-2 text-sm">
              Browse Tutors
            </RouterLink>
          </div>

          <div class="grid gap-4 md:grid-cols-3">
            <article
              v-for="(step, index) in steps"
              :key="step.title"
              class="reveal rounded-md border border-paper-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md"
              :style="{ animationDelay: `${index * 80}ms` }"
            >
              <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-sm bg-navy-700 font-display text-sm font-bold text-white">
                {{ index + 1 }}
              </div>
              <h3 class="font-display text-lg font-bold text-navy-900">{{ step.title }}</h3>
              <p class="mt-2 font-body text-sm leading-relaxed text-paper-600">{{ step.desc }}</p>
            </article>
          </div>
        </div>
      </section>

      <section class="px-4 py-14 sm:px-6 md:py-16">
        <div class="mx-auto grid max-w-6xl gap-4 md:grid-cols-[1fr_1.25fr] md:items-stretch">
          <div class="reveal rounded-md bg-navy-700 p-6 text-white shadow-lg md:p-7">
            <p class="font-display text-xs font-bold uppercase text-gold-300">For guardians</p>
            <h2 class="mt-3 font-display text-2xl font-bold md:text-3xl">Shortlist with confidence.</h2>
            <p class="mt-3 font-body text-sm leading-relaxed text-navy-100 md:text-base">
              Review profile completion, preferred areas, teaching approach, documents and subject fit before requesting a connection.
            </p>
            <RouterLink to="/search" class="mt-6 inline-flex min-h-[44px] items-center justify-center rounded-sm bg-white px-5 font-display text-sm font-bold text-navy-800 transition-colors hover:bg-gold-50">
              Find Tutors
            </RouterLink>
          </div>

          <div class="reveal reveal-delay rounded-md border border-paper-200 bg-white p-6 shadow-sm md:p-7">
            <p class="font-display text-xs font-bold uppercase text-gold-600">For tutors</p>
            <h2 class="mt-3 font-display text-2xl font-bold text-navy-900 md:text-3xl">Build a strong tutor profile.</h2>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
              <div v-for="item in tutorBenefits" :key="item" class="rounded-sm border border-paper-200 bg-paper-50 p-3 font-body text-sm text-paper-700">
                {{ item }}
              </div>
            </div>
            <RouterLink to="/register" class="btn-gold mt-6 rounded-sm px-5 py-2.5 text-sm">
              Create Tutor Profile
            </RouterLink>
          </div>
        </div>
      </section>
    </main>
  </DefaultLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import DefaultLayout from '@/layouts/DefaultLayout.vue'

const router = useRouter()
const query = ref('')
const statsRef = ref(null)
const countersStarted = ref(false)

const quickChips = ['Class 9 Math', 'HSC Physics', 'English Medium', 'O Level', 'A Level']

const heroChecks = [
  'Verified education and document details',
  'Area, class, subject and salary filters',
  'Profile review before connection',
]

const previewMetrics = [
  { label: 'Experience', value: '5 years' },
  { label: 'Expected salary', value: '৳8k-12k' },
  { label: 'Preferred area', value: 'Dhanmondi' },
  { label: 'Teaching mode', value: 'Offline' },
]

const counterValues = reactive({
  tutors: 0,
  districts: 0,
  students: 0,
})

const stats = computed(() => [
  { label: 'Verified tutor profiles', display: Math.round(counterValues.tutors).toLocaleString(), suffix: '+' },
  { label: 'Districts covered', display: Math.round(counterValues.districts).toLocaleString(), suffix: '' },
  { label: 'Student matches supported', display: Math.round(counterValues.students).toLocaleString(), suffix: '+' },
])

const steps = [
  { title: 'Search with filters', desc: 'Use class, subject, area, budget, medium and gender preferences to narrow the list.' },
  { title: 'Compare tutor profiles', desc: 'Review education, preferred locations, teaching approach, documents and profile status.' },
  { title: 'Shortlist and connect', desc: 'Save suitable tutors and continue with admin-assisted confirmation when ready.' },
]

const tutorBenefits = [
  'Add education, subjects, salary and preferred locations',
  'Upload documents for admin review',
  'Show teaching approach clearly',
  'Track profile status and pending changes',
]

let observer

onMounted(() => {
  observer = new IntersectionObserver((entries) => {
    if (entries.some(entry => entry.isIntersecting)) startCounters()
  }, { threshold: 0.25 })

  if (statsRef.value) observer.observe(statsRef.value)
})

onBeforeUnmount(() => {
  observer?.disconnect()
})

function startCounters() {
  if (countersStarted.value) return
  countersStarted.value = true
  animateCounter('tutors', 500, 900)
  animateCounter('districts', 64, 760)
  animateCounter('students', 1200, 1050)
}

function animateCounter(key, target, duration) {
  const started = performance.now()
  const tick = (now) => {
    const progress = Math.min((now - started) / duration, 1)
    const eased = 1 - Math.pow(1 - progress, 3)
    counterValues[key] = target * eased
    if (progress < 1) requestAnimationFrame(tick)
    else counterValues[key] = target
  }
  requestAnimationFrame(tick)
}

function goSearch() {
  router.push({ name: 'search', query: query.value ? { q: query.value } : {} })
}

function quickSearch(chip) {
  router.push({ name: 'search', query: { q: chip } })
}
</script>

<style scoped>
.landing-grid {
  background-image:
    linear-gradient(rgba(15, 46, 92, 0.038) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 46, 92, 0.038) 1px, transparent 1px);
  background-size: 34px 34px;
}

.reveal {
  animation: reveal-up 0.58s ease both;
}

.reveal-delay {
  animation-delay: 110ms;
}

.float-card {
  animation: float-soft 4.5s ease-in-out infinite;
}

.float-card-delayed {
  animation: float-soft 4.5s ease-in-out 0.8s infinite;
}

@keyframes reveal-up {
  from {
    opacity: 0;
    transform: translateY(14px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes float-soft {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}

@media (prefers-reduced-motion: reduce) {
  .reveal,
  .float-card,
  .float-card-delayed {
    animation: none;
  }
}
</style>
