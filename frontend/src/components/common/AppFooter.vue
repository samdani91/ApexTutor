<template>
  <footer class="bg-navy-900 text-navy-100">
    <div class="mx-auto max-w-7xl px-4 py-9 sm:px-6 md:py-14">

      <!-- Brand + links — stacked on mobile, side-by-side on md+ -->
      <div class="grid gap-8 md:grid-cols-[1.1fr_1fr] md:gap-12 lg:gap-20">

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

        <!-- Nav groups — 2 columns on phones (3 was too cramped to tap), 3 from sm up -->
        <div class="grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 sm:gap-x-8">
          <nav v-for="group in linkGroups" :key="group.title" aria-label="Footer navigation">
            <p class="mb-2.5 font-display text-[11px] font-bold uppercase tracking-wider text-gold-300 sm:text-xs">
              {{ group.title }}
            </p>
            <div class="space-y-1">
              <RouterLink
                v-for="link in group.links"
                :key="link.label"
                :to="link.to"
                class="block py-1 font-body text-sm text-navy-300 transition-colors hover:text-white"
              >
                {{ link.label }}
              </RouterLink>
            </div>
          </nav>
        </div>
      </div>

      <!-- CTA box — tutors get the job-hunting call, everyone else the tutor search -->
      <div class="mt-8 md:mt-10 flex flex-col gap-4 rounded-md border border-white/10 bg-white/[0.04] p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
        <div>
          <p class="font-display text-sm font-bold text-white">{{ cta.title }}</p>
          <p class="mt-1 font-body text-sm text-navy-300">{{ cta.subtitle }}</p>
        </div>
        <RouterLink
          :to="cta.to"
          class="flex w-full items-center justify-center rounded-sm bg-gold-400 px-6 py-2.5 font-display text-sm font-bold text-navy-900 transition-colors hover:bg-gold-300 sm:w-auto sm:shrink-0"
        >
          {{ cta.label }}
        </RouterLink>
      </div>

      <!-- Bottom bar -->
      <div class="mt-7 flex flex-col gap-2 border-t border-white/10 pt-5 font-body text-xs text-navy-400 sm:flex-row sm:items-center sm:justify-between">
        <span>© {{ currentYear }} Apex Tutor. All rights reserved.</span>
        <span>
          Design &amp; Development by
          <a
            href="https://www.innovatechbd.net/"
            target="_blank"
            rel="noopener noreferrer"
            class="font-display font-semibold text-gold-300 transition-colors hover:text-gold-200 hover:underline"
          >
            InnovaTech
          </a>
        </span>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

const auth = useAuthStore()
const currentYear = new Date().getFullYear()

// Tutors are looking for tuitions, not tutors — everyone else (guardians,
// students, admins, logged-out visitors) keeps the original tutor-search call.
const cta = computed(() => auth.isTutor
  ? {
      title:    'Ready to find tuition jobs?',
      subtitle: 'Browse open tuition jobs and apply to the ones that fit you.',
      label:    'Find Tuitions',
      to:       '/jobs',
    }
  : {
      title:    'Ready to find a tutor?',
      subtitle: 'Start with search, then shortlist profiles that fit your needs.',
      label:    'Find Tutors',
      to:       '/search',
    })

// Role-aware: signed-in users get links they can actually use, and never
// "Create Account" / "Log In". Guests keep the marketing-facing split.
const linkGroups = computed(() => {
  if (auth.isTutor) {
    return [
      { title: 'Teaching', links: [
        { label: 'Find Tuitions',      to: '/jobs'                     },
        { label: 'My Applications',    to: '/tutor/applications'       },
        { label: 'Confirmed Tuitions', to: '/tutor/confirmed-tuitions' },
      ]},
      { title: 'Account', links: [
        { label: 'Dashboard',  to: '/tutor/dashboard' },
        { label: 'My Profile', to: '/tutor/profile'   },
        { label: 'My Reviews', to: '/tutor/reviews'   },
      ]},
      { title: 'Platform', links: [
        { label: 'Home',     to: '/'              },
        { label: 'Support',  to: '/tutor/support' },
        { label: 'Settings', to: '/tutor/settings'},
      ]},
    ]
  }

  if (auth.isGuardian) {
    return [
      { title: 'Hiring', links: [
        { label: 'Find Tutors', to: '/search'            },
        { label: 'Post a Job',  to: '/guardian/jobs/post'},
        { label: 'My Jobs',     to: '/guardian/jobs'     },
      ]},
      { title: 'Account', links: [
        { label: 'Dashboard',          to: '/guardian/dashboard'           },
        { label: 'Shortlist',          to: '/guardian/shortlist'           },
        { label: 'Confirmed Tuitions', to: '/guardian/confirmed-tuitions'  },
      ]},
      { title: 'Platform', links: [
        { label: 'Home',     to: '/'                  },
        { label: 'Support',  to: '/guardian/support'  },
        { label: 'Settings', to: '/guardian/settings' },
      ]},
    ]
  }

  if (auth.isAuthenticated) {
    // Admin — no public marketing links worth showing.
    return [
      { title: 'Platform', links: [
        { label: 'Home',        to: '/'                 },
        { label: 'Dashboard',   to: '/admin/dashboard'  },
        { label: 'Find Tutors', to: '/search'           },
      ]},
    ]
  }

  return [
    { title: 'For Guardians', links: [
      { label: 'Find Tutors',    to: '/search'   },
      { label: 'Create Account', to: '/register' },
    ]},
    { title: 'For Tutors', links: [
      { label: 'Tuition Jobs',   to: '/jobs'     },
      { label: 'Become a Tutor', to: '/register' },
    ]},
    { title: 'Platform', links: [
      { label: 'Home',   to: '/'      },
      { label: 'Log In', to: '/login' },
    ]},
  ]
})
</script>
