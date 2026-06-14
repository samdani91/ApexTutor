<template>
  <footer class="bg-navy-900 text-navy-100">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 md:py-12">
      <div class="grid gap-8 lg:grid-cols-[1.25fr_1fr] lg:gap-12">
        <div>
          <RouterLink to="/" class="inline-flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-md bg-gold-400 font-display text-base font-bold text-navy-900 shadow-md shadow-navy-950/20">
              TK
            </span>
            <span>
              <span class="block font-display text-xl font-bold leading-tight text-white">TutorKhujo</span>
              <span class="block font-body text-xs text-navy-300">Verified tuition connections</span>
            </span>
          </RouterLink>

          <p class="mt-4 max-w-md font-body text-sm leading-relaxed text-navy-200">
            Search verified tutors, compare profiles, shortlist matches and move toward confirmed tuition with clearer information.
          </p>

        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
          <nav v-for="group in linkGroups" :key="group.title" aria-label="Footer navigation">
            <p class="mb-3 font-display text-xs font-bold uppercase text-gold-300">{{ group.title }}</p>
            <div class="space-y-2.5">
              <RouterLink
                v-for="link in group.links"
                :key="link.label"
                :to="link.to"
                class="block font-body text-sm text-navy-200 transition-colors hover:text-white"
              >
                {{ link.label }}
              </RouterLink>
            </div>
          </nav>
        </div>
      </div>

      <div class="mt-8 grid gap-4 rounded-md border border-white/10 bg-white/[0.04] p-4 sm:grid-cols-[1fr_auto] sm:items-center md:mt-10">
        <div>
          <p class="font-display text-sm font-bold text-white">Ready to find a tutor?</p>
          <p class="mt-1 font-body text-sm text-navy-300">Start with search, then shortlist the profiles that fit your needs.</p>
        </div>
        <RouterLink to="/search" class="inline-flex min-h-[42px] items-center justify-center rounded-sm bg-gold-400 px-5 font-display text-sm font-bold text-navy-900 transition-colors hover:bg-gold-300">
          Find Tutors
        </RouterLink>
      </div>

      <div class="mt-7 border-t border-white/10 pt-5 font-body text-xs text-navy-300">
        <span>© {{ currentYear }} TutorKhujo. All rights reserved.</span>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { RouterLink } from 'vue-router'

const currentYear = new Date().getFullYear()

const linkGroups = [
  {
    title: 'Guardians',
    links: [
      { label: 'Find Tutors', to: '/search' },
      { label: 'Create Account', to: '/register' },
      { label: 'Log In', to: '/login' },
    ],
  },
  {
    title: 'Tutors',
    links: [
      { label: 'Become A Tutor', to: '/register' },
      { label: 'Tutor Log In', to: '/login' },
      { label: 'Search page', to: '/search' },
    ],
  },
  {
    title: 'Platform',
    links: [
      { label: 'Home', to: '/' },
      { label: 'Browse Tutors', to: '/search' },
      { label: 'Sign In', to: '/login' },
    ],
  },
]
</script>
