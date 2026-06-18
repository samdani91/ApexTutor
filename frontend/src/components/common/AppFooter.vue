<template>
  <footer class="bg-navy-900 text-navy-100">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 md:py-14">

      <!-- Brand + links — stacked on mobile, side-by-side on md+ -->
      <div class="grid gap-10 md:grid-cols-[1.1fr_1fr] md:gap-12 lg:gap-20">

        <!-- Brand -->
        <div>
          <RouterLink to="/" class="inline-flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-gold-400 font-display text-sm font-bold text-navy-900 shadow-md">
              TK
            </span>
            <span>
              <span class="block font-display text-xl font-bold leading-tight text-white">Apex Tutor</span>
              <span class="block font-body text-xs text-navy-300 mt-0.5">Verified tuition connections</span>
            </span>
          </RouterLink>

          <p class="mt-4 font-body text-sm leading-relaxed text-navy-300 max-w-sm">
            Search verified tutors, compare profiles, shortlist matches and move toward confirmed tuition with clearer information.
          </p>
        </div>

        <!-- Nav groups — always 3 columns so no orphan row -->
        <div class="grid grid-cols-3 gap-x-3 sm:gap-x-8">
          <nav v-for="group in linkGroups" :key="group.title" aria-label="Footer navigation">
            <p class="mb-3 font-display text-[10px] font-bold uppercase tracking-wider text-gold-300 sm:text-xs">
              {{ group.title }}
            </p>
            <div class="space-y-2.5">
              <RouterLink
                v-for="link in group.links"
                :key="link.label"
                :to="link.to"
                class="block font-body text-xs text-navy-300 transition-colors hover:text-white sm:text-sm"
              >
                {{ link.label }}
              </RouterLink>
            </div>
          </nav>
        </div>
      </div>

      <!-- CTA box -->
      <div class="mt-8 md:mt-10 flex flex-col gap-4 rounded-md border border-white/10 bg-white/[0.04] p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
        <div>
          <p class="font-display text-sm font-bold text-white">Ready to find a tutor?</p>
          <p class="mt-1 font-body text-sm text-navy-300">Start with search, then shortlist profiles that fit your needs.</p>
        </div>
        <RouterLink
          to="/search"
          class="flex w-full items-center justify-center rounded-sm bg-gold-400 px-6 py-2.5 font-display text-sm font-bold text-navy-900 transition-colors hover:bg-gold-300 sm:w-auto sm:shrink-0"
        >
          Find Tutors
        </RouterLink>
      </div>

      <!-- Bottom bar -->
      <div class="mt-7 border-t border-white/10 pt-5 font-body text-xs text-navy-400">
        <span>© {{ currentYear }} Apex Tutor. All rights reserved.</span>
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
      { label: 'Home',          to: '/'        },
      { label: 'Browse Tutors', to: '/search'  },
      { label: 'Sign In',       to: '/login'   },
    ],
  },
]
</script>
