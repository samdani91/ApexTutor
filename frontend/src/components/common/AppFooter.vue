<template>
  <footer class="bg-navy-900 text-navy-100">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 md:py-14">

      <!-- Brand + links row -->
      <div class="grid gap-10 lg:grid-cols-[1.25fr_1fr] lg:gap-16">

        <!-- Brand -->
        <div>
          <RouterLink to="/" class="inline-flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-md bg-gold-400 font-display text-sm font-bold text-navy-900 shadow-md shadow-navy-950/20 shrink-0">
              TK
            </span>
            <span>
              <span class="block font-display text-lg font-bold leading-tight text-white">TutorKhujo</span>
              <span class="block font-body text-xs text-navy-300 mt-0.5">Verified tuition connections</span>
            </span>
          </RouterLink>

          <p class="mt-4 font-body text-sm leading-relaxed text-navy-300 max-w-sm">
            Search verified tutors, compare profiles, shortlist matches and move toward confirmed tuition with clearer information.
          </p>
        </div>

        <!-- Link groups — 2 cols on mobile, 3 on sm+ -->
        <div class="grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3">
          <nav v-for="group in linkGroups" :key="group.title" aria-label="Footer navigation">
            <p class="mb-3 font-display text-xs font-bold uppercase tracking-wider text-gold-300">{{ group.title }}</p>
            <div class="space-y-2.5">
              <RouterLink
                v-for="link in group.links"
                :key="link.label"
                :to="link.to"
                class="block font-body text-sm text-navy-300 transition-colors hover:text-white"
              >
                {{ link.label }}
              </RouterLink>
            </div>
          </nav>
        </div>
      </div>

      <!-- CTA box -->
      <div class="mt-8 md:mt-10 rounded-md border border-white/10 bg-white/[0.04] p-4 sm:p-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="font-display text-sm font-bold text-white">Ready to find a tutor?</p>
          <p class="mt-1 font-body text-sm text-navy-300">Start with search, then shortlist profiles that fit your needs.</p>
        </div>
        <RouterLink
          to="/search"
          class="flex items-center justify-center min-h-[42px] rounded-sm bg-gold-400 px-6 font-display text-sm font-bold text-navy-900 transition-colors hover:bg-gold-300 shrink-0 sm:w-auto w-full"
        >
          Find Tutors
        </RouterLink>
      </div>

      <!-- Bottom bar -->
      <div class="mt-7 border-t border-white/10 pt-5 font-body text-xs text-navy-400">
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
      { label: 'Find Tutors',    to: '/search'   },
      { label: 'Create Account', to: '/register' },
      { label: 'Log In',         to: '/login'    },
    ],
  },
  {
    title: 'Tutors',
    links: [
      { label: 'Become a Tutor', to: '/register' },
      { label: 'Tutor Log In',   to: '/login'    },
      { label: 'Browse Tutors',  to: '/search'   },
    ],
  },
  {
    title: 'Platform',
    links: [
      { label: 'Home',         to: '/'        },
      { label: 'Browse Tutors', to: '/search' },
      { label: 'Sign In',       to: '/login'  },
    ],
  },
]
</script>
