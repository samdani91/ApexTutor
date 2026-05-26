<template>
  <DefaultLayout>
    <!-- Hero -->
    <section class="bg-navy-700 text-white py-16 md:py-24 px-4">
      <div class="max-w-3xl mx-auto text-center">
        <p class="text-xs font-display font-bold tracking-widest uppercase text-gold-400 mb-3 md:mb-4">TutorKhujo</p>
        <h1 class="font-display font-bold text-4xl sm:text-5xl md:text-6xl leading-tight mb-4 md:mb-6">Find your tutor.</h1>
        <p class="text-base md:text-xl text-navy-200 mb-8 md:mb-10 font-body px-4">Search verified tutors near you, by subject and budget.</p>
        <!-- Search bar -->
        <div class="bg-white rounded-xl p-1.5 sm:p-2 flex gap-2 max-w-xl mx-auto shadow-xl">
          <input v-model="query" @keyup.enter="goSearch" type="text"
            placeholder="Subject, area, or class…"
            class="flex-1 px-3 sm:px-4 py-2.5 text-navy-900 font-body text-sm focus:outline-none rounded-lg min-w-0" />
          <button @click="goSearch" class="btn-gold px-4 sm:px-6 py-2.5 text-sm rounded-lg shrink-0">Search</button>
        </div>
        <!-- Quick chips -->
        <div class="flex flex-wrap justify-center gap-2 mt-5">
          <button v-for="chip in quickChips" :key="chip" @click="quickSearch(chip)"
            class="text-xs font-semibold font-display text-navy-200 border border-navy-500 px-3 py-1.5 rounded-pill hover:bg-navy-600 transition-colors">
            {{ chip }}
          </button>
        </div>
      </div>
    </section>

    <!-- Stats band -->
    <section class="bg-white border-y border-paper-200 py-8 md:py-10">
      <div class="max-w-4xl mx-auto px-4 grid grid-cols-3 gap-4 md:gap-8 text-center">
        <div>
          <p class="font-display font-bold text-2xl md:text-3xl text-navy-700">500+</p>
          <p class="text-xs md:text-sm text-paper-500 mt-1 font-body">Verified tutors</p>
        </div>
        <div>
          <p class="font-display font-bold text-2xl md:text-3xl text-navy-700">64</p>
          <p class="text-xs md:text-sm text-paper-500 mt-1 font-body">Districts</p>
        </div>
        <div>
          <p class="font-display font-bold text-2xl md:text-3xl text-navy-700">1,200+</p>
          <p class="text-xs md:text-sm text-paper-500 mt-1 font-body">Happy students</p>
        </div>
      </div>
    </section>

    <!-- How it works -->
    <section class="py-14 md:py-20 px-4">
      <div class="max-w-4xl mx-auto text-center mb-10 md:mb-12">
        <p class="text-xs font-display font-bold tracking-widest uppercase text-gold-500 mb-3">How it works</p>
        <h2 class="font-display font-bold text-2xl md:text-3xl text-navy-900">Three steps to the right tutor</h2>
      </div>
      <div class="max-w-4xl mx-auto grid sm:grid-cols-3 gap-6 md:gap-8">
        <div v-for="(step, i) in steps" :key="i" class="text-center">
          <div class="w-12 h-12 bg-navy-700 text-white font-display font-bold text-xl rounded-xl flex items-center justify-center mx-auto mb-4">
            {{ i + 1 }}
          </div>
          <h3 class="font-display font-semibold text-navy-900 text-lg mb-2">{{ step.title }}</h3>
          <p class="text-paper-500 text-sm font-body">{{ step.desc }}</p>
        </div>
      </div>
    </section>

    <!-- Subject quick links -->
    <section class="bg-white border-y border-paper-200 py-10 md:py-14 px-4">
      <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
          <p class="text-xs font-display font-bold tracking-widest uppercase text-gold-500 mb-3">Browse by subject</p>
          <h2 class="font-display font-bold text-2xl text-navy-900">Popular subjects</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
          <RouterLink v-for="sub in popularSubjects" :key="sub.label"
            :to="{ name: 'search', query: { q: sub.label } }"
            class="card py-4 px-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-150 group">
            <span class="text-2xl mb-1 block">{{ sub.icon }}</span>
            <p class="font-display font-semibold text-sm text-navy-900 group-hover:text-navy-700">{{ sub.label }}</p>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-navy-700 py-14 md:py-16 px-4 text-center">
      <h2 class="font-display font-bold text-2xl md:text-3xl text-white mb-3 md:mb-4">Are you a tutor?</h2>
      <p class="text-navy-200 mb-7 md:mb-8 font-body text-sm md:text-base">Create a free profile and reach students across Bangladesh.</p>
      <RouterLink to="/register" class="btn-gold px-6 md:px-8 py-3 inline-block rounded-md font-display font-semibold">Get started</RouterLink>
    </section>
  </DefaultLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import DefaultLayout from '@/layouts/DefaultLayout.vue'

const router = useRouter()
const query = ref('')

const quickChips = ['HSC Math', 'English Medium', 'Physics', 'O Level', 'University']

const steps = [
  { title: 'Search', desc: 'Filter by subject, class, area, and budget to find the right match.' },
  { title: 'Connect', desc: "Send a connection request — we'll introduce you to the tutor." },
  { title: 'Learn', desc: 'Start your sessions and leave a review after you\'re done.' },
]

const popularSubjects = [
  { label: 'Mathematics', icon: '📐' },
  { label: 'Physics', icon: '⚛️' },
  { label: 'Chemistry', icon: '🧪' },
  { label: 'English', icon: '📖' },
  { label: 'Biology', icon: '🔬' },
  { label: 'ICT', icon: '💻' },
  { label: 'Accounting', icon: '📊' },
  { label: 'IELTS', icon: '🌐' },
]

function goSearch() {
  router.push({ name: 'search', query: query.value ? { q: query.value } : {} })
}
function quickSearch(chip) {
  router.push({ name: 'search', query: { q: chip } })
}
</script>
