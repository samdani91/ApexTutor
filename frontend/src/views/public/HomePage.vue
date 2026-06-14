<template>
  <DefaultLayout>
    <main class="landing-page bg-white text-navy-900">
      <section class="hero-screen relative bg-white">
        <div class="content-wrap grid min-h-[calc(100svh-4rem)] items-center gap-10 pb-40 pt-3 md:grid-cols-2 md:pb-44 md:pt-4">
          <div>
            <h1 class="hero-title font-display text-4xl font-bold leading-tight text-navy-900 sm:text-5xl md:text-6xl">
              <template v-for="(wordObj, wi) in heroWordGroups" :key="wi">
                <span class="inline-block whitespace-nowrap">
                  <span
                    v-for="ch in wordObj.chars"
                    :key="ch.idx"
                    class="hero-char"
                    :style="{ animationDelay: `${ch.idx * 24}ms` }"
                  >{{ ch.c }}</span>
                </span><span
                  v-if="wordObj.spaceIdx >= 0"
                  class="hero-char hero-space"
                  :style="{ animationDelay: `${wordObj.spaceIdx * 24}ms` }"
                ></span>
              </template>
            </h1>
            <p class="reveal mt-5 max-w-xl font-body text-lg leading-relaxed text-paper-600">
              Compare verified tutor profiles by class, subject, location, salary and teaching style before you shortlist.
            </p>
            <div class="reveal mt-8 flex flex-wrap items-center gap-4">
              <RouterLink to="/search" class="btn-primary rounded-sm px-8 py-3.5 text-sm font-bold">
                Hire a Tutor
              </RouterLink>
              <p class="font-body text-sm text-paper-600">
                Want to become a tutor?
                <RouterLink to="/register" class="font-display font-bold text-navy-700 hover:underline">
                  Sign Up
                </RouterLink>
              </p>
            </div>

            <div class="reveal mt-7 max-w-xl rounded-md border border-paper-200 bg-white p-2 shadow-lg">
              <div class="flex flex-col gap-2 sm:flex-row">
                <label class="relative flex-1">
                  <span class="sr-only">Search tutors</span>
                  <component :is="IconSearch" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-paper-500" />
                  <input
                    v-model="query"
                    @keyup.enter="goSearch"
                    type="search"
                    placeholder="Search by subject, class, or area"
                    class="min-h-[48px] w-full rounded-sm border border-paper-200 bg-paper-50 py-2 pl-9 pr-3 font-body text-sm text-navy-900 outline-none transition-colors placeholder:text-paper-400 focus:border-gold-400 focus:bg-white"
                  />
                </label>
                <button @click="goSearch" class="btn-gold min-h-[48px] rounded-sm px-5 text-sm">
                  Search
                </button>
              </div>
            </div>

            <div class="reveal mt-4 flex max-w-xl flex-wrap gap-2">
              <button
                v-for="chip in quickChips"
                :key="chip.label"
                @click="quickSearch(chip)"
                class="rounded-pill border border-paper-200 bg-white px-3 py-1.5 font-display text-xs font-semibold text-navy-700 shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:border-gold-300 hover:text-navy-900"
              >
                {{ chip.label }}
              </button>
            </div>
          </div>

          <div class="hero-collage grid grid-cols-2 gap-3">
            <div
              v-for="(image, index) in heroImages"
              :key="image"
              class="hero-img overflow-hidden rounded-lg shadow-lg"
              :class="index % 2 === 1 ? 'translate-y-8' : ''"
            >
              <img :src="image" alt="Tutoring session" class="h-44 w-full object-cover sm:h-52" loading="lazy" />
            </div>
          </div>
        </div>

        <div ref="statsRef" class="hero-stats absolute inset-x-0 bottom-6 z-10 px-4 md:px-6">
        <div class="content-wrap rounded-lg bg-navy-700 p-6 shadow-xl md:p-8">
          <div class="grid grid-cols-2 gap-6 md:grid-cols-4 md:gap-0">
            <div
              v-for="(stat, index) in statsCards"
              :key="stat.label"
              class="text-center"
              :class="index < statsCards.length - 1 ? 'md:border-r md:border-white/20' : ''"
            >
              <component :is="stat.icon" class="mx-auto mb-2 h-10 w-10 text-white/80" />
              <div class="font-display text-3xl font-bold text-white md:text-4xl">
                {{ stat.display }}<span class="text-xl">{{ stat.suffix }}</span>
              </div>
              <p class="mt-1 font-body text-sm text-white/80">{{ stat.label }}</p>
            </div>
          </div>
        </div>
        </div>
      </section>

      <section class="section-pad relative bg-paper-50">
        <div class="content-wrap">
          <div class="mx-auto mb-12 max-w-xl text-center">
            <p class="font-display text-xs font-bold uppercase tracking-widest text-gold-600">What we offer</p>
            <h2 class="section-title mt-2 font-display text-3xl font-bold text-navy-900 md:text-4xl">Tuition Types</h2>
            <p class="mt-3 font-body text-paper-600">Choose the format that fits your child's learning style and schedule.</p>
          </div>

          <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Featured first card: spans 2 columns on desktop -->
            <button
              @click="quickSearch(tuitionTypes[0])"
              class="group type-card scroll-reveal flex flex-col gap-5 rounded-xl border border-paper-200 bg-white p-7 text-left shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-gold-200 hover:shadow-lg sm:flex-row sm:items-center lg:col-span-2"
              style="transition-delay: 0ms"
            >
              <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl transition-colors duration-200"
                :class="[tuitionTypes[0].iconBg, 'group-hover:opacity-90']">
                <component :is="tuitionTypes[0].icon" class="h-8 w-8" :class="tuitionTypes[0].iconColor" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-display text-[0.65rem] font-bold uppercase tracking-widest" :class="tuitionTypes[0].labelColor">Most popular</p>
                <h3 class="mt-1 font-display text-xl font-bold text-navy-900">{{ tuitionTypes[0].title }}</h3>
                <p class="mt-2 font-body text-sm leading-relaxed text-paper-600">{{ tuitionTypes[0].desc }}</p>
              </div>
              <svg class="h-5 w-5 shrink-0 text-paper-300 transition-all duration-200 group-hover:translate-x-1 group-hover:text-navy-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </button>

            <!-- Remaining cards -->
            <button
              v-for="(type, index) in tuitionTypes.slice(1)"
              :key="type.title"
              @click="quickSearch(type)"
              class="group type-card scroll-reveal flex flex-col rounded-xl border border-paper-200 bg-white p-6 text-left shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-gold-200 hover:shadow-lg"
              :style="{ transitionDelay: `${(index + 1) * 85}ms` }"
            >
              <div class="flex h-12 w-12 items-center justify-center rounded-xl transition-colors duration-200" :class="type.iconBg">
                <component :is="type.icon" class="h-6 w-6" :class="type.iconColor" />
              </div>
              <h3 class="mt-5 font-display text-lg font-bold text-navy-900">{{ type.title }}</h3>
              <p class="mt-2 flex-1 font-body text-sm leading-relaxed text-paper-600">{{ type.desc }}</p>
              <span class="mt-5 inline-flex items-center gap-1 font-display text-xs font-bold text-navy-700 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                Browse
                <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
              </span>
            </button>
          </div>
        </div>
      </section>

      <section class="section-pad guardian-flow-section bg-white">
        <div class="content-wrap">
          <div class="mx-auto mb-12 max-w-2xl text-center">
            <p class="font-display text-xs font-bold uppercase tracking-widest text-gold-600">For guardians</p>
            <h2 class="section-title mt-2 font-display text-3xl font-bold text-navy-900 md:text-4xl">How does it work for guardians?</h2>
            <p class="mt-3 font-body text-paper-600">Move from search to shortlist with clear tutor details at every step.</p>
          </div>

          <div class="guardian-flow-grid">
            <article
              v-for="(step, index) in guardianSteps"
              :key="step.title"
              class="guardian-flow-step scroll-reveal"
              :style="{ transitionDelay: `${index * 120}ms` }"
            >
              <div class="guardian-flow-number">{{ step.num }}</div>
              <div class="guardian-flow-icon" :class="step.iconBg">
                <component :is="step.icon" class="h-8 w-8" :class="step.iconColor" />
              </div>
              <h3 class="mt-5 font-display text-xl font-bold text-navy-900">{{ step.title }}</h3>
              <p class="mt-3 font-body text-sm leading-relaxed text-paper-600">{{ step.desc }}</p>
            </article>
          </div>

          <RouterLink to="/search" class="btn-primary mx-auto mt-10 flex w-fit rounded-sm px-7 py-3 text-sm font-bold">
            Start Searching
          </RouterLink>
        </div>
      </section>

      <section class="section-pad bg-paper-50">
        <div class="content-wrap">
          <h2 class="section-title text-center font-display text-3xl font-bold text-navy-900 md:text-4xl">Serving Categories</h2>
          <div class="mt-10 grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4">
            <button
              v-for="(category, index) in categories"
              :key="category.name"
              @click="quickSearch(category)"
              class="cat-card scroll-reveal overflow-hidden rounded-lg bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
              :style="{ transitionDelay: `${(index % 4) * 70}ms` }"
            >
              <div class="aspect-[16/10] overflow-hidden">
                <img :src="category.img" :alt="category.name" class="h-full w-full object-cover transition-transform duration-500 hover:scale-105" loading="lazy" />
              </div>
              <h3 class="px-2 py-4 text-center font-display text-sm font-bold text-navy-900">{{ category.name }}</h3>
            </button>
          </div>
        </div>
      </section>

      <TestimonialsSection
        v-if="guardianTestimonials.length"
        title="What guardians say about TutorKhujo"
        subtitle="Hire a tutor today and start learning with confidence."
        cta-label="Hire a Tutor"
        cta-to="/search"
        :items="guardianTestimonials"
      />

      <section class="section-pad tutor-flow-section bg-white">
        <div class="content-wrap">
          <div class="mx-auto mb-12 max-w-2xl text-center">
            <p class="font-display text-xs font-bold uppercase tracking-widest text-gold-600">For tutors</p>
            <h2 class="section-title mt-2 font-display text-3xl font-bold text-navy-900 md:text-4xl">How does it work for tutors?</h2>
            <p class="mt-3 font-body text-paper-600">Create a clear profile, complete verification and get discovered by suitable guardians.</p>
          </div>

          <div class="tutor-flow-grid">
            <article
              v-for="(step, index) in tutorSteps"
              :key="step.title"
              class="tutor-flow-step scroll-reveal"
              :style="{ transitionDelay: `${index * 95}ms` }"
            >
              <div class="tutor-flow-icon" :class="step.iconBg">
                <component :is="step.icon" class="h-7 w-7" :class="step.iconColor" />
              </div>
              <div class="tutor-flow-number">{{ step.num }}</div>
              <h3 class="mt-5 font-display text-lg font-bold text-navy-900">{{ step.title }}</h3>
              <p class="mt-3 font-body text-sm leading-relaxed text-paper-600">{{ step.desc }}</p>
            </article>
          </div>

          <RouterLink to="/register" class="btn-primary mx-auto mt-10 flex w-fit rounded-sm px-7 py-3 text-sm font-bold">
            Create Tutor Profile
          </RouterLink>
        </div>
      </section>

      <TestimonialsSection
        v-if="tutorTestimonials.length"
        title="What tutors say about TutorKhujo"
        subtitle="Build a verified profile and connect with suitable guardians."
        cta-label="Become a Tutor"
        cta-to="/register"
        outlined
        :items="tutorTestimonials"
      />
    </main>
  </DefaultLayout>
</template>

<script setup>
import { computed, defineComponent, h, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { useLandingStats } from '@/composables/useLandingStats.js'
import { searchApi } from '@/api/search.js'

import hero1 from '@/assets/landing-ref/hero-1.jpg'
import hero2 from '@/assets/landing-ref/hero-2.jpg'
import hero3 from '@/assets/landing-ref/hero-3.jpg'
import hero4 from '@/assets/landing-ref/hero-4.jpg'
import catLanguage from '@/assets/landing-ref/cat-language.jpg'
import catTestPrep from '@/assets/landing-ref/cat-test-prep.jpg'
import catUni from '@/assets/landing-ref/cat-uni.jpg'
import catBangla from '@/assets/landing-ref/cat-bangla.jpg'
import catEnglish from '@/assets/landing-ref/cat-english-med.jpg'

const router = useRouter()
const query = ref('')
const statsRef = ref(null)
const countersStarted = ref(false)
const heroText = 'Find the right tutor with less searching and more clarity'
const heroWordGroups = computed(() => {
  let idx = 0
  return heroText.split(' ').map((word, wi, arr) => {
    const chars = word.split('').map(c => ({ c, idx: idx++ }))
    const spaceIdx = wi < arr.length - 1 ? idx++ : -1
    return { chars, spaceIdx }
  })
})
const heroImages = [hero1, hero2, hero3, hero4]

function iconComponent(path) {
  return {
    render() {
      return h('svg', { fill: 'none', stroke: 'currentColor', strokeWidth: '1.9', viewBox: '0 0 24 24' }, [
        h('path', { d: path, strokeLinecap: 'round', strokeLinejoin: 'round' }),
      ])
    },
  }
}

const IconSearch = iconComponent('m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z')
const IconUsers = iconComponent('M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z')
const IconBook = iconComponent('M4.5 5.25A2.25 2.25 0 0 1 6.75 3H20v15.75H7.5A3 3 0 0 0 4.5 21V5.25Zm0 0A2.25 2.25 0 0 1 6.75 3m0 15.75V3')
const IconStar = iconComponent('M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z')
const IconHome = iconComponent('m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25')
const IconLaptop = iconComponent('M4 5.75A1.75 1.75 0 0 1 5.75 4h12.5A1.75 1.75 0 0 1 20 5.75V15H4V5.75ZM2.75 18h18.5M8 18h8')
const IconPerson = iconComponent('M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z')
const IconGroup = iconComponent('M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.724 6.724 0 0 1 6 18.75l.001-.03a3.75 3.75 0 0 1 7.5 0m-7.5 0a9.094 9.094 0 0 1-3.741-.479 3 3 0 0 1 4.682-2.72m11.117 0a5.971 5.971 0 0 0-3.059-.872m-8.001.872a5.971 5.971 0 0 1 3.059-.872m0 0a3 3 0 1 0 5.999 0m-5.999 0a3 3 0 1 1 5.999 0')
const IconShield = iconComponent('M12 3.75 19.5 6v5.7c0 4.65-3.15 8.85-7.5 10.05-4.35-1.2-7.5-5.4-7.5-10.05V6L12 3.75Zm-3 8.1 2.1 2.1L15.75 9.3')
const IconMap = iconComponent('M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z')
const IconList = iconComponent('M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.008v.008H3.75V6.75Zm0 5.25h.008v.008H3.75V12Zm0 5.25h.008v.008H3.75v-.008Z')
const IconCheck = iconComponent('M9 12.75 11.25 15 15.75 9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z')
const IconDocument = iconComponent('M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z')
const IconBadge = iconComponent('M9 12.75 11.25 15 15.75 9M12 2.25l2.1 2.1 2.97-.42.42 2.97 2.1 2.1-2.1 2.1-.42 2.97-2.97-.42L12 15.75l-2.1-2.1-2.97.42-.42-2.97-2.1-2.1 2.1-2.1.42-2.97 2.97.42L12 2.25Z')

const quickChips = [
  { label: 'Class 9', query: { class_level: 'class_9' } },
  { label: 'Class 10', query: { class_level: 'class_10' } },
  { label: 'HSC', query: { class_level: 'hsc' } },
  { label: 'English Medium', query: { medium: 'english_medium' } },
  { label: 'O Level', query: { class_level: 'o_level' } },
  { label: 'A Level', query: { class_level: 'a_level' } },
  { label: 'Online', query: { place_of_tutoring: 'online' } },
]

const counterValues = reactive({ tutors: 0, districts: 0, students: 0, rating: 0 })
const { statTargets, loadLandingStats: fetchLandingStats } = useLandingStats()

const statsCards = computed(() => [
  { icon: IconUsers, display: Math.round(counterValues.tutors).toLocaleString(),    suffix: '+',  label: 'Verified Tutors' },
  { icon: IconBook,  display: Math.round(counterValues.students).toLocaleString(),  suffix: '+',  label: 'Tuition Matches' },
  { icon: IconMap,   display: Math.round(counterValues.districts).toLocaleString(), suffix: '',   label: 'Districts Covered' },
  { icon: IconStar,  display: counterValues.rating.toFixed(1),                      suffix: '/5', label: 'Average Rating' },
])

const tuitionTypes = [
  { icon: IconHome,   title: 'Home Tutoring',      desc: 'A tutor visits your home for focused, personalised sessions at your schedule.', query: { place_of_tutoring: 'student_home' }, iconBg: 'bg-gold-50',    iconColor: 'text-gold-700',    labelColor: 'text-gold-600'    },
  { icon: IconLaptop, title: 'Online Tutoring',     desc: 'Remote classes through flexible digital learning tools.',                       query: { place_of_tutoring: 'online'       }, iconBg: 'bg-sky-50',     iconColor: 'text-sky-700',     labelColor: 'text-sky-600'     },
  { icon: IconGroup,  title: 'Group Tutoring',      desc: 'Small group sessions for affordable collaborative learning.',                   query: { tutoring_styles: 'group'          }, iconBg: 'bg-purple-50',  iconColor: 'text-purple-700',  labelColor: 'text-purple-600'  },
  { icon: IconBook,   title: 'Exam Preparation',    desc: 'Targeted support for SSC, HSC, O Level and A Level exams.',                    query: { class_level: 'ssc'                }, iconBg: 'bg-rose-50',    iconColor: 'text-rose-700',    labelColor: 'text-rose-600'    },
  { icon: IconPerson, title: 'One-to-One Tutoring', desc: 'Dedicated individual attention for rapid and steady progress.',                 query: { tutoring_styles: 'one_to_one'     }, iconBg: 'bg-emerald-50', iconColor: 'text-emerald-700', labelColor: 'text-emerald-600' },
]

const guardianSteps = [
  { num: 1, icon: IconSearch, title: 'Search Tutors', desc: 'Filter by class, subject, location, budget and teaching method.', iconBg: 'bg-sky-50', iconColor: 'text-sky-700' },
  { num: 2, icon: IconList, title: 'Compare Profiles', desc: 'Review education, salary range, preferred locations and profile status.', iconBg: 'bg-gold-50', iconColor: 'text-gold-700' },
  { num: 3, icon: IconCheck, title: 'Shortlist and Connect', desc: 'Save suitable tutors and continue with admin-assisted confirmation.', iconBg: 'bg-emerald-50', iconColor: 'text-emerald-700' },
]

const tutorSteps = [
  { num: 1, icon: IconDocument, title: 'Complete Your Profile', desc: 'Add education, subjects, salary range, documents and teaching details.', iconBg: 'bg-sky-50', iconColor: 'text-sky-700' },
  { num: 2, icon: IconBadge, title: 'Get Verified', desc: 'Submit profile information for admin review before public listing.', iconBg: 'bg-gold-50', iconColor: 'text-gold-700' },
  { num: 3, icon: IconUsers, title: 'Get Shortlisted', desc: 'Guardians compare your profile and request connections when you match.', iconBg: 'bg-purple-50', iconColor: 'text-purple-700' },
  { num: 4, icon: IconBook, title: 'Start Tutoring', desc: 'Confirm the tuition and begin teaching with clear expectations.', iconBg: 'bg-emerald-50', iconColor: 'text-emerald-700' },
]

const categories = [
  { name: 'Bangla Medium', img: catBangla, query: { medium: 'bangla_medium' } },
  { name: 'English Version', img: catEnglish, query: { medium: 'english_version' } },
  { name: 'English Medium', img: catLanguage, query: { medium: 'english_medium' } },
  { name: 'Primary Classes', img: catBangla, query: { class_level: 'class_5' } },
  { name: 'Class 6-8', img: catTestPrep, query: { class_level: 'class_8' } },
  { name: 'Class 9-10', img: catTestPrep, query: { class_level: 'class_10' } },
  { name: 'SSC', img: catTestPrep, query: { class_level: 'ssc' } },
  { name: 'HSC', img: catUni, query: { class_level: 'hsc' } },
  { name: 'O Level', img: catEnglish, query: { class_level: 'o_level' } },
  { name: 'A Level', img: catLanguage, query: { class_level: 'a_level' } },
  { name: 'Online Tutors', img: catLanguage, query: { place_of_tutoring: 'online' } },
  { name: 'Home Tutors', img: catBangla, query: { place_of_tutoring: 'student_home' } },
]

const guardianTestimonials = ref([])
const tutorTestimonials    = ref([])

const TestimonialsSection = defineComponent({
  name: 'TestimonialsSection',
  props: {
    title:    { type: String,  required: true },
    subtitle: { type: String,  required: true },
    ctaLabel: { type: String,  required: true },
    ctaTo:    { type: String,  required: true },
    outlined: { type: Boolean, default: false },
    items:    { type: Array,   required: true },
  },
  setup(props) {
    const avatar = (item) => item.avatar
      ? h('img', { src: item.avatar, alt: item.name, class: 'ts-avatar', loading: 'lazy' })
      : h('div', { class: 'ts-avatar ts-avatar-fallback' }, item.name.charAt(0).toUpperCase())

    // Scrolling marquee card (used when 4+ items)
    const marqueeCard = (item, i) => h('article', { key: `m-${i}`, class: 'marquee-card' }, [
      h('p', { class: 'ts-deco-quote' }, '“'),
      h('p', { class: 'font-body text-[15px] leading-relaxed text-paper-700' }, item.quote),
      h('div', { class: 'mt-5 flex items-center gap-3' }, [
        avatar(item),
        h('div', [
          h('p', { class: 'font-display text-sm font-bold text-navy-700' }, item.name),
          h('p', { class: 'font-body text-xs text-paper-500' }, item.role),
        ]),
      ]),
    ])

    // Static grid card (used when 2-3 items)
    const gridCard = (item, i) => h('article', { key: `g-${i}`, class: 'ts-grid-card' }, [
      h('p', { class: 'ts-deco-quote' }, '“'),
      h('p', { class: 'font-body text-[15px] leading-relaxed text-paper-700 flex-1' }, item.quote),
      h('div', { class: 'mt-5 flex items-center gap-3 pt-4 border-t border-paper-200' }, [
        avatar(item),
        h('div', [
          h('p', { class: 'font-display text-sm font-bold text-navy-700' }, item.name),
          h('p', { class: 'font-body text-xs text-paper-500' }, item.role),
        ]),
      ]),
    ])

    return () => {
      const n = props.items.length

      const header = h('div', { class: 'mx-auto max-w-[84rem] px-4 sm:px-6 mb-10 text-center' }, [
        h('h2', { class: 'font-display text-3xl font-bold text-navy-900 md:text-4xl' }, props.title),
        h('p', { class: 'mt-2 font-body text-paper-600' }, props.subtitle),
        h(RouterLink, { to: props.ctaTo, class: 'btn-primary mt-6 rounded-sm px-8 py-3 text-sm font-bold' }, () => props.ctaLabel),
      ])

      // ── 1 item: full-width spotlight card ────────────────────────────
      if (n === 1) {
        const item = props.items[0]
        return h('section', { class: 'overflow-hidden bg-white py-16 md:py-20' }, [
          header,
          h('div', { class: 'mx-auto max-w-[84rem] px-4 sm:px-6' }, [
            h('div', { class: 'ts-spotlight' }, [
              h('span', { class: 'ts-spotlight-mark' }, '“'),
              h('p', { class: 'ts-spotlight-quote' }, item.quote),
              h('div', { class: 'ts-spotlight-author' }, [
                item.avatar
                  ? h('img', { src: item.avatar, alt: item.name, class: 'ts-spotlight-avatar', loading: 'lazy' })
                  : h('div', { class: 'ts-spotlight-avatar ts-avatar-fallback ts-avatar-fallback--lg' }, item.name.charAt(0).toUpperCase()),
                h('div', [
                  h('p', { class: 'font-display font-bold text-white text-base' }, item.name),
                  h('p', { class: 'font-body text-sm text-white/60 mt-0.5' }, item.role),
                ]),
              ]),
            ]),
          ]),
        ])
      }

      // ── 2-3 items: static responsive grid ────────────────────────────
      if (n < 4) {
        const gridCols = n === 2 ? 'sm:grid-cols-2' : 'sm:grid-cols-2 lg:grid-cols-3'
        return h('section', { class: 'overflow-hidden bg-white py-16 md:py-20' }, [
          header,
          h('div', { class: `mx-auto max-w-[84rem] px-4 sm:px-6` }, [
            h('div', { class: `grid gap-6 ${gridCols}` }, props.items.map(gridCard)),
          ]),
        ])
      }

      // ── 4+ items: dual scrolling marquee ─────────────────────────────
      const forwardItems = [...props.items, ...props.items, ...props.items, ...props.items]
      const reverseItems = [...props.items].reverse().concat([...props.items].reverse(), [...props.items].reverse(), [...props.items].reverse())

      return h('section', { class: 'overflow-hidden bg-white py-16 md:py-20' }, [
        header,
        h('div', { class: 'mobile-testimonial-snap px-4' }, props.items.map(marqueeCard)),
        h('div', { class: 'desktop-testimonial-marquees' }, [
          h('div', { class: 'gallery-wrap mb-5' }, [
            h('div', { class: 'gallery-track animate-marquee' }, forwardItems.map(marqueeCard)),
          ]),
          h('div', { class: 'gallery-wrap' }, [
            h('div', { class: 'gallery-track animate-marquee-reverse' }, reverseItems.map(marqueeCard)),
          ]),
        ]),
      ])
    }
  },
})

let statsObserver
let revealObserver

onMounted(async () => {
  // Fetch real stats and testimonials in parallel before starting counters
  await Promise.all([
    fetchLandingStats(),
    searchApi.landingTestimonials().then(({ data }) => {
      if (data.data?.guardian?.length) guardianTestimonials.value = data.data.guardian
      if (data.data?.platform?.length) tutorTestimonials.value    = data.data.platform
    }).catch(() => { /* keep fallbacks */ }),
  ])

  statsObserver = new IntersectionObserver((entries) => {
    if (entries.some(entry => entry.isIntersecting)) startCounters()
  }, { threshold: 0.25 })
  if (statsRef.value) statsObserver.observe(statsRef.value)

  revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return
      entry.target.classList.add('is-visible')
      revealObserver.unobserve(entry.target)
    })
  }, { threshold: 0.18, rootMargin: '0px 0px -8% 0px' })

  document.querySelectorAll('.scroll-reveal').forEach((element) => {
    revealObserver.observe(element)
  })
})

onBeforeUnmount(() => {
  statsObserver?.disconnect()
  revealObserver?.disconnect()
})

function startCounters() {
  if (countersStarted.value) return
  countersStarted.value = true
  animateCounter('tutors', statTargets.tutors, 900)
  animateCounter('districts', statTargets.districts, 760)
  animateCounter('students', statTargets.students, 1050)
  animateCounter('rating', statTargets.rating, 1200)
}

function animateCounter(key, target, duration) {
  const started = performance.now()
  const from = Number(counterValues[key] || 0)
  const tick = (now) => {
    const progress = Math.min((now - started) / duration, 1)
    const eased = 1 - Math.pow(1 - progress, 3)
    counterValues[key] = from + ((target - from) * eased)
    if (progress < 1) requestAnimationFrame(tick)
    else counterValues[key] = target
  }
  requestAnimationFrame(tick)
}

async function loadLandingStats() {
  const loaded = await fetchLandingStats()
  if (loaded && countersStarted.value) {
    animateCounter('tutors', statTargets.tutors, 500)
    animateCounter('districts', statTargets.districts, 500)
    animateCounter('students', statTargets.students, 500)
    animateCounter('rating', statTargets.rating, 500)
  }
}

function goSearch() {
  const q = query.value.trim()
  router.push({ name: 'search', query: q ? { q } : {} })
}

function quickSearch(item) {
  router.push({ name: 'search', query: item.query || {} })
}

</script>

<style scoped>
.section-pad {
  padding-top: 4rem;
  padding-bottom: 4rem;
}

@media (min-width: 768px) {
  .section-pad {
    padding-top: 5rem;
    padding-bottom: 5rem;
  }
}

.content-wrap {
  max-width: 84rem;
  margin: 0 auto;
  padding-inline: 1rem;
}

@media (min-width: 768px) {
  .content-wrap {
    padding-inline: 1.5rem;
  }
}

.hero-screen {
  min-height: calc(100svh - 4rem);
}

.section-screen {
  min-height: calc(100svh - 4rem);
  display: flex;
  align-items: center;
  padding-top: 4rem;
  padding-bottom: 4rem;
}

.hero-char {
  display: inline-block;
  opacity: 0;
  transform: translateX(-160%) rotateY(80deg);
  transform-origin: 50% 100%;
  animation: hero-char-in 0.85s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.hero-space {
  width: 0.28em;
}

.reveal,
.hero-img {
  animation: reveal-up 0.65s ease both;
}

:deep(.scroll-reveal) {
  opacity: 0;
  transform: translateY(34px) scale(0.985);
  transition:
    opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1),
    transform 0.7s cubic-bezier(0.22, 1, 0.36, 1),
    border-color 0.25s ease,
    box-shadow 0.25s ease;
  will-change: opacity, transform;
}

:deep(.scroll-reveal.is-visible) {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.hero-img:nth-child(2) {
  animation-delay: 120ms;
}

.hero-img:nth-child(3) {
  animation-delay: 220ms;
}

.hero-img:nth-child(4) {
  animation-delay: 340ms;
}

.section-title {
  position: relative;
}

.section-title::after {
  content: "";
  display: block;
  width: 4rem;
  height: 3px;
  margin: 0.85rem auto 0;
  border-radius: 999px;
  background: #f4b942;
}

.guardian-flow-grid {
  position: relative;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.25rem;
}

.guardian-flow-step {
  position: relative;
  min-height: 18rem;
  border: 1px solid #e7e1d5;
  border-radius: 0.9rem;
  background: #ffffff;
  padding: 2rem;
  text-align: center;
  box-shadow: 0 18px 45px rgba(15, 46, 92, 0.08);
}

.guardian-flow-number {
  position: absolute;
  top: 1rem;
  left: 1rem;
  display: flex;
  width: 2.15rem;
  height: 2.15rem;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: #0f2e5c;
  color: #ffffff;
  font-family: var(--font-display, inherit);
  font-size: 0.8rem;
  font-weight: 800;
}

.guardian-flow-icon {
  position: relative;
  z-index: 1;
  display: flex;
  width: 5.5rem;
  height: 5.5rem;
  margin: 0 auto;
  align-items: center;
  justify-content: center;
  border: 6px solid #ffffff;
  border-radius: 1.1rem;
  box-shadow: 0 16px 34px rgba(15, 46, 92, 0.1);
}

.tutor-flow-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
}

.tutor-flow-step {
  position: relative;
  min-height: 17rem;
  overflow: hidden;
  border: 1px solid #e7e1d5;
  border-radius: 0.9rem;
  background: #ffffff;
  padding: 1.5rem;
  box-shadow: 0 16px 38px rgba(15, 46, 92, 0.075);
}

.tutor-flow-step::before {
  content: "";
  position: absolute;
  inset-inline: 0;
  top: 0;
  height: 4px;
  background: #f4b942;
}

.tutor-flow-icon {
  display: flex;
  width: 4.5rem;
  height: 4.5rem;
  align-items: center;
  justify-content: center;
  border-radius: 1rem;
}

.tutor-flow-number {
  position: absolute;
  top: 1.4rem;
  right: 1.4rem;
  font-family: var(--font-display, inherit);
  font-size: 2.5rem;
  font-weight: 800;
  line-height: 1;
  color: rgba(15, 46, 92, 0.1);
}

/* ── Shared avatar ── */
:deep(.ts-avatar) {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}
:deep(.ts-avatar-fallback) {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #1a4a8a;
  color: #fff;
  font-family: var(--font-display, inherit);
  font-weight: 700;
  font-size: 1rem;
}
:deep(.ts-deco-quote) {
  font-size: 2.5rem;
  line-height: 1;
  color: rgba(15, 46, 92, 0.15);
  font-family: Georgia, serif;
  margin-bottom: 0.5rem;
}

/* ── Spotlight (1 item) ── */
:deep(.ts-spotlight) {
  position: relative;
  max-width: 680px;
  margin: 0 auto;
  background: linear-gradient(135deg, #0F2E5C 0%, #1B4FA0 100%);
  border-radius: 1.5rem;
  padding: 3rem 3rem 2.5rem;
  text-align: center;
  overflow: hidden;
}
:deep(.ts-spotlight::before) {
  content: '';
  position: absolute;
  top: -60px;
  right: -60px;
  width: 240px;
  height: 240px;
  background: radial-gradient(circle, rgba(212,175,55,0.12) 0%, transparent 70%);
  pointer-events: none;
}
:deep(.ts-spotlight::after) {
  content: '';
  position: absolute;
  bottom: -40px;
  left: -40px;
  width: 160px;
  height: 160px;
  background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
  pointer-events: none;
}
:deep(.ts-spotlight-mark) {
  display: block;
  font-family: Georgia, serif;
  font-size: 6rem;
  line-height: 0.8;
  color: rgba(212, 175, 55, 0.45);
  margin-bottom: 0.75rem;
  user-select: none;
}
:deep(.ts-spotlight-quote) {
  font-family: var(--font-body, inherit);
  font-size: 1.125rem;
  line-height: 1.85;
  color: rgba(255, 255, 255, 0.92);
  font-style: italic;
  position: relative;
  z-index: 1;
}
:deep(.ts-spotlight-author) {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.875rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  position: relative;
  z-index: 1;
}
:deep(.ts-spotlight-avatar) {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.5);
}
:deep(.ts-avatar-fallback--lg) {
  width: 3.5rem;
  height: 3.5rem;
  font-size: 1.25rem;
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.5);
}

/* ── Static grid cards (2-3 items) ── */
:deep(.ts-grid-card) {
  display: flex;
  flex-direction: column;
  background: #fbf9f3;
  border: 1px solid #e8e3d8;
  border-radius: 1.25rem;
  padding: 2rem;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}
:deep(.ts-grid-card:hover) {
  box-shadow: 0 8px 28px rgba(15, 46, 92, 0.1);
  transform: translateY(-3px);
}

/* ── Gallery / marquee (4+ items) ── */
:deep(.gallery-wrap) {
  overflow: hidden;
  width: 100%;
}

:deep(.desktop-testimonial-marquees) {
  display: block;
}

:deep(.gallery-track) {
  display: flex;
  width: max-content;
  gap: 1.25rem;
  will-change: transform;
}

:deep(.gallery-track:hover) {
  animation-play-state: paused;
}

:deep(.marquee-card) {
  width: 380px;
  min-width: 380px;
  flex-shrink: 0;
  border-radius: 1rem;
  background: #fbf9f3;
  padding: 2rem;
}

:deep(.mobile-testimonial-snap) {
  display: none;
  gap: 1rem;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scroll-padding-inline: 1rem;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

:deep(.mobile-testimonial-snap::-webkit-scrollbar) {
  display: none;
}

:deep(.mobile-testimonial-snap .marquee-card) {
  width: min(82vw, 340px);
  min-width: min(82vw, 340px);
  scroll-snap-align: center;
}

:deep(.animate-marquee) {
  animation: marquee-left 34s linear infinite;
}

:deep(.animate-marquee-reverse) {
  animation: marquee-right 38s linear infinite;
}

@keyframes hero-char-in {
  to {
    opacity: 1;
    transform: translateX(0) rotateY(0);
  }
}

@keyframes reveal-up {
  from {
    opacity: 0;
    transform: translateY(24px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes marquee-left {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-25%);
  }
}

@keyframes marquee-right {
  from {
    transform: translateX(-25%);
  }
  to {
    transform: translateX(0);
  }
}

@media (max-width: 768px) {
  :deep(.desktop-testimonial-marquees) {
    display: none;
  }

  :deep(.mobile-testimonial-snap) {
    display: flex;
  }

  .hero-screen {
    min-height: auto;
  }

  .hero-screen > .content-wrap {
    min-height: auto;
    padding-top: 1.5rem;
    padding-bottom: 2rem;
  }

  .hero-title {
    font-size: clamp(2.25rem, 11vw, 3.35rem);
  }

  .hero-collage img {
    height: 9.5rem;
  }

  .hero-stats {
    position: static;
    padding: 0 1rem 2rem;
  }

  .hero-stats > .content-wrap {
    padding: 1.25rem;
  }

  .section-screen {
    min-height: auto;
    padding-top: 3.5rem;
    padding-bottom: 3.5rem;
  }

  .hero-screen,
  .section-screen {
    min-height: auto;
  }

  :deep(.marquee-card) {
    width: 300px;
    min-width: 300px;
    padding: 1.5rem;
  }

  .guardian-flow-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .guardian-flow-step {
    min-height: auto;
    padding: 1.35rem 1.25rem 1.35rem 6rem;
    text-align: left;
  }

  .guardian-flow-number {
    top: 1rem;
    left: 1rem;
  }

  .guardian-flow-icon {
    position: absolute;
    left: 1.45rem;
    top: 3.7rem;
    width: 3.8rem;
    height: 3.8rem;
    border-width: 4px;
    border-radius: 0.85rem;
  }

  .guardian-flow-icon svg {
    width: 1.45rem;
    height: 1.45rem;
  }

  .tutor-flow-grid {
    grid-template-columns: 1fr;
  }

  .tutor-flow-step {
    min-height: auto;
    padding: 1.35rem;
  }

  .tutor-flow-icon {
    width: 3.8rem;
    height: 3.8rem;
  }

  .tutor-flow-icon svg {
    width: 1.45rem;
    height: 1.45rem;
  }

  /* ── Testimonial spotlight / grid cards ── */
  :deep(.ts-spotlight) {
    padding: 1.5rem 1.25rem;
    border-radius: 1rem;
    text-align: left;
  }
  :deep(.ts-spotlight-mark) {
    font-size: 3.5rem;
    margin-bottom: 0.25rem;
  }
  :deep(.ts-spotlight-quote) {
    font-size: 0.9375rem;
    line-height: 1.75;
  }
  :deep(.ts-spotlight-author) {
    margin-top: 1.25rem;
    padding-top: 1rem;
    justify-content: flex-start;
  }
  :deep(.ts-grid-card) {
    padding: 1.35rem;
    border-radius: 1rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .hero-char,
  .reveal,
  .hero-img,
  :deep(.scroll-reveal),
  :deep(.animate-marquee),
  :deep(.animate-marquee-reverse) {
    animation: none;
    transition: none;
    opacity: 1;
    transform: none;
  }
}
</style>
