<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="analytics-hero reveal">
      <div>
        <p class="font-display text-xs font-bold uppercase tracking-wide text-gold-600">Analytics</p>
        <h1 class="mt-1 font-display text-2xl font-bold text-navy-900 md:text-3xl">Platform Overview</h1>
        <p class="mt-2 max-w-2xl font-body text-sm leading-relaxed text-paper-600">
          A simple summary of how the platform is performing — how many tutors and guardians joined, how many tuition jobs were posted, and how many hires were made.
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="analytics-card flex items-center justify-center py-16 text-paper-500 font-body">
      <div class="mr-3 h-8 w-8 rounded-full border-4 border-navy-100 border-t-navy-700 animate-spin"></div>
      Loading data…
    </div>

    <template v-else>

      <!-- ── At a Glance ─────────────────────────────────────────────────────── -->
      <div class="rounded-md border border-navy-100 bg-navy-50 p-5 reveal">
        <p class="mb-3 font-display text-xs font-bold uppercase tracking-wide text-navy-500">Quick Summary</p>
        <ul class="space-y-2">
          <li v-for="(line, i) in atAGlance" :key="i" class="flex items-start gap-2.5 font-body text-sm text-navy-800">
            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-navy-700 text-[10px] font-bold text-white">{{ i + 1 }}</span>
            {{ line }}
          </li>
          <li v-if="!atAGlance.length" class="font-body text-sm text-navy-500">No activity yet — numbers will appear once guardians and tutors start using the platform.</li>
        </ul>
      </div>

      <!-- ── Section 1: Hire Requests (Connections) ─────────────────────────── -->
      <div>
        <div class="mb-3">
          <p class="font-display text-xs font-bold uppercase tracking-wide text-paper-400">Section 1 — Direct Hire Requests</p>
          <p class="mt-0.5 font-body text-xs text-paper-400">When a guardian contacts a tutor directly (not through a job post), it counts as a hire request.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

          <div class="summary-card reveal">
            <span class="summary-icon bg-navy-50 text-navy-700">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
              </svg>
            </span>
            <p class="summary-value text-navy-800">{{ totalConnections.toLocaleString() }}</p>
            <p class="summary-label">Total Hire Requests Sent</p>
            <p class="mt-1 font-body text-xs text-paper-400">Guardians who contacted a tutor directly</p>
          </div>

          <div class="summary-card reveal delay-1">
            <span class="summary-icon bg-emerald-50 text-emerald-700">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
              </svg>
            </span>
            <p class="summary-value text-emerald-600">{{ confirmedTotal.toLocaleString() }}</p>
            <p class="summary-label">Successful Direct Hires</p>
            <p class="mt-1 font-body text-xs text-paper-400">Requests that ended with a tutor being hired</p>
          </div>

          <div class="summary-card reveal delay-2">
            <span class="summary-icon bg-gold-50 text-gold-700">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941"/>
              </svg>
            </span>
            <p class="summary-value text-gold-600">{{ connectionConversionRate }}%</p>
            <p class="summary-label">Hire Success Rate</p>
            <p class="mt-1 font-body text-xs text-paper-400">{{ connectionSuccessText }}</p>
          </div>

          <div class="summary-card reveal delay-3">
            <span class="summary-icon bg-purple-50 text-purple-700">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
              </svg>
            </span>
            <p class="summary-value text-purple-700">{{ (rangeNewTutors + rangeNewGuardians).toLocaleString() }}</p>
            <p class="summary-label">New Sign-ups (in selected period)</p>
            <p class="mt-1 font-body text-xs text-paper-400">{{ rangeNewTutors }} tutors · {{ rangeNewGuardians }} guardians</p>
          </div>

        </div>
      </div>

      <!-- ── Section 2: Tuition Jobs ────────────────────────────────────────── -->
      <div>
        <div class="mb-3">
          <p class="font-display text-xs font-bold uppercase tracking-wide text-paper-400">Section 2 — Tuition Job Posts</p>
          <p class="mt-0.5 font-body text-xs text-paper-400">Guardians can also post a job ad for tutors to apply. These numbers cover all such job posts.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

          <div class="summary-card reveal">
            <span class="summary-icon bg-blue-50 text-blue-700">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
              </svg>
            </span>
            <p class="summary-value text-blue-700">{{ totalJobs.toLocaleString() }}</p>
            <p class="summary-label">Total Job Ads Posted</p>
            <p class="mt-1 font-body text-xs text-paper-400">Tuition job ads created by guardians</p>
          </div>

          <div class="summary-card reveal delay-1">
            <span class="summary-icon bg-emerald-50 text-emerald-700">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5"/>
              </svg>
            </span>
            <p class="summary-value text-emerald-600">{{ openJobs.toLocaleString() }}</p>
            <p class="summary-label">Still Looking for a Tutor</p>
            <p class="mt-1 font-body text-xs text-paper-400">Job ads that haven't found a tutor yet</p>
          </div>

          <div class="summary-card reveal delay-2">
            <span class="summary-icon bg-amber-50 text-amber-700">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z"/>
              </svg>
            </span>
            <p class="summary-value text-amber-600">{{ totalJobApplications.toLocaleString() }}</p>
            <p class="summary-label">Tutor Applications Received</p>
            <p class="mt-1 font-body text-xs text-paper-400">Times a tutor applied to a job ad</p>
          </div>

          <div class="summary-card reveal delay-3">
            <span class="summary-icon bg-teal-50 text-teal-700">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
              </svg>
            </span>
            <p class="summary-value text-teal-700">{{ confirmedViaJobs.toLocaleString() }}</p>
            <p class="summary-label">Hired Through Job Ads</p>
            <p class="mt-1 font-body text-xs text-paper-400">Applications that resulted in a confirmed hire</p>
          </div>

        </div>
      </div>

      <!-- ── Date Range Picker ──────────────────────────────────────────────── -->
      <div class="analytics-card reveal">
        <div class="flex flex-wrap items-start gap-4">
          <div>
            <p class="section-kicker">Choose a Time Period</p>
            <p class="mt-1 font-display text-sm font-semibold text-navy-900">All monthly charts below will update</p>
            <p class="mt-1 font-body text-xs text-paper-500">Select a start month and an end month, then press Apply.</p>
          </div>
          <div class="ml-auto flex flex-wrap items-end gap-2">
            <div>
              <span class="mb-1 block text-xs font-semibold font-display text-navy-700">From</span>
              <input type="month" v-model="rangeFrom" :max="rangeTo || currentMonth" class="input text-sm" />
            </div>
            <div>
              <span class="mb-1 block text-xs font-semibold font-display text-navy-700">To</span>
              <input type="month" v-model="rangeTo" :min="rangeFrom" :max="currentMonth" class="input text-sm" />
            </div>
            <button @click="applyRange" :disabled="rangeLoading"
              class="h-10 rounded-sm bg-navy-800 px-4 text-sm font-semibold font-display text-white transition-colors hover:bg-navy-900 disabled:opacity-50">
              <span v-if="rangeLoading" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
              <span v-else>Apply</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ── Chart 1: Direct Hire Requests Per Month ───────────────────────── -->
      <section class="analytics-card reveal">
        <div class="mb-1">
          <p class="section-kicker">Chart 1</p>
          <h2 class="section-title">Direct Hire Requests Each Month</h2>
        </div>
        <p class="mb-5 font-body text-sm text-paper-500">
          Each bar shows how many guardians sent a direct hire request that month. The <span class="font-semibold text-emerald-600">green</span> part shows how many of those were confirmed. Click any bar to see the details.
        </p>

        <div v-if="!monthlyData.length" class="empty-state">No data for the selected period. Try choosing a wider date range.</div>
        <div v-else>
          <div class="mb-3 flex items-center gap-5 text-xs font-display text-paper-500">
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-navy-200"></span>Hire Requests Sent</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-emerald-400"></span>Hires Confirmed</span>
          </div>
          <div class="overflow-x-auto pb-2">
            <div class="flex items-end gap-1.5" :style="{ minWidth: monthlyData.length > 12 ? monthlyData.length * 52 + 'px' : '100%' }">
              <div v-for="m in monthlyData" :key="m.month_key"
                class="group flex flex-1 min-w-[44px] cursor-pointer flex-col items-center gap-1.5"
                @click="selectedMonthKey = m.month_key">
                <div class="relative w-full overflow-hidden rounded-t-sm bg-paper-100" style="height:80px;">
                  <div class="absolute bottom-0 w-full rounded-t-sm bg-navy-200 transition-all duration-500" :style="{ height: pct(m.connections, maxConnections) + '%' }"></div>
                  <div class="absolute bottom-0 w-full bg-emerald-400 transition-all duration-500" :style="{ height: pct(m.confirmed, maxConnections) + '%' }"></div>
                  <div v-if="m.month_key === selectedMonthKey" class="pointer-events-none absolute inset-0 rounded-t-sm ring-2 ring-inset ring-navy-600"></div>
                </div>
                <span class="text-center font-display font-semibold leading-tight transition-colors"
                  :class="m.month_key === selectedMonthKey ? 'text-navy-900 text-[11px]' : 'text-paper-400 text-[10px] group-hover:text-paper-600'">
                  {{ shortLabel(m.month) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Selected month detail -->
          <div v-if="selectedMonth" class="mt-5 rounded-md border border-paper-200 bg-paper-50/60 p-4">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <p class="font-display text-base font-bold text-navy-900">{{ selectedMonth.month }}</p>
                <p class="mt-0.5 font-body text-sm text-paper-500">
                  Out of <strong>{{ Number(selectedMonth.connections || 0).toLocaleString() }}</strong> hire requests,
                  <strong class="text-emerald-600">{{ Number(selectedMonth.confirmed || 0).toLocaleString() }}</strong> were confirmed.
                </p>
              </div>
              <div class="shrink-0 text-right">
                <span class="inline-block rounded-full px-3 py-1 text-sm font-bold font-display"
                  :class="selectedMonthConnRate >= 50 ? 'bg-emerald-50 text-emerald-700' : selectedMonthConnRate >= 20 ? 'bg-gold-50 text-gold-700' : 'bg-red-50 text-red-600'">
                  {{ selectedMonthConnRate }}% success rate
                </span>
                <p class="mt-1 font-body text-xs text-paper-400">
                  {{ selectedMonthConnRate >= 50 ? 'Great result!' : selectedMonthConnRate >= 20 ? 'Room to improve' : 'Low — may need attention' }}
                </p>
              </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
              <div class="month-stat-card">
                <p class="stat-label">Requests Sent</p>
                <p class="stat-value text-navy-800">{{ Number(selectedMonth.connections || 0).toLocaleString() }}</p>
              </div>
              <div class="month-stat-card">
                <p class="stat-label">Hires Confirmed</p>
                <p class="stat-value text-emerald-600">{{ Number(selectedMonth.confirmed || 0).toLocaleString() }}</p>
              </div>
              <div class="month-stat-card">
                <p class="stat-label">Not Yet Confirmed</p>
                <p class="stat-value text-amber-600">{{ (Number(selectedMonth.connections||0) - Number(selectedMonth.confirmed||0)).toLocaleString() }}</p>
                <p class="mt-1 font-body text-xs text-paper-400">Still in progress or declined</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Chart 2: New Sign-ups Per Month ───────────────────────────────── -->
      <section class="analytics-card reveal">
        <div class="mb-1">
          <p class="section-kicker">Chart 2</p>
          <h2 class="section-title">New Sign-ups Each Month</h2>
        </div>
        <p class="mb-5 font-body text-sm text-paper-500">
          How many new <span class="font-semibold text-navy-700">tutors</span> (dark bars) and <span class="font-semibold text-gold-600">guardians</span> (gold bars) joined the platform each month.
          In the selected period: <strong class="text-navy-700">{{ rangeNewTutors.toLocaleString() }} tutors</strong> and <strong class="text-gold-600">{{ rangeNewGuardians.toLocaleString() }} guardians</strong> signed up.
        </p>

        <div v-if="!monthlyData.length || (rangeNewTutors + rangeNewGuardians) === 0" class="empty-state">No sign-up data for the selected period.</div>
        <div v-else>
          <div class="mb-3 flex items-center gap-5 text-xs font-display text-paper-500">
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-navy-600"></span>New Tutors</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-gold-400"></span>New Guardians / Students</span>
          </div>
          <div class="overflow-x-auto pb-2">
            <div class="flex items-end gap-1.5" :style="{ minWidth: monthlyData.length > 12 ? monthlyData.length * 56 + 'px' : '100%' }">
              <div v-for="m in monthlyData" :key="m.month_key" class="flex flex-1 min-w-[48px] flex-col items-center gap-1.5">
                <div class="flex w-full items-end gap-0.5" style="height:80px;">
                  <div class="flex-1 flex flex-col justify-end">
                    <div class="w-full rounded-t-sm bg-navy-600 transition-all duration-500" :style="{ height: pct(m.tutors_registered, maxReg) + '%', minHeight: m.tutors_registered ? '2px' : '0' }"></div>
                  </div>
                  <div class="flex-1 flex flex-col justify-end">
                    <div class="w-full rounded-t-sm bg-gold-400 transition-all duration-500" :style="{ height: pct(m.guardians_registered, maxReg) + '%', minHeight: m.guardians_registered ? '2px' : '0' }"></div>
                  </div>
                </div>
                <span class="text-center text-[10px] font-display font-semibold text-paper-400">{{ shortLabel(m.month) }}</span>
              </div>
            </div>
          </div>

          <!-- Busiest months -->
          <div v-if="topRegMonths.length" class="mt-4">
            <p class="mb-2 font-display text-xs font-bold uppercase tracking-wide text-paper-400">Busiest Months for Sign-ups</p>
            <div class="grid gap-2 sm:grid-cols-3">
              <div v-for="m in topRegMonths" :key="m.month_key" class="month-stat-card">
                <p class="font-display text-sm font-bold text-navy-900">{{ m.month }}</p>
                <p class="mt-1 font-body text-xs text-paper-500">
                  <span class="font-semibold text-navy-700">{{ m.tutors_registered }} new tutors</span>
                </p>
                <p class="font-body text-xs text-paper-500">
                  <span class="font-semibold text-gold-700">{{ m.guardians_registered }} new guardians</span>
                </p>
                <p class="mt-2 font-display text-xl font-bold text-navy-900">{{ (m.tutors_registered + m.guardians_registered).toLocaleString() }} total</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Chart 3: Tuition Job Activity Per Month ────────────────────────── -->
      <section class="analytics-card reveal">
        <div class="mb-1">
          <p class="section-kicker">Chart 3</p>
          <h2 class="section-title">Tuition Job Activity Each Month</h2>
        </div>
        <p class="mb-5 font-body text-sm text-paper-500">
          Each month shows three bars side by side:
          <span class="font-semibold text-blue-600">Job ads posted</span> by guardians ·
          <span class="font-semibold text-amber-600">Applications received</span> from tutors ·
          <span class="font-semibold text-emerald-600">Confirmed hires</span>.
        </p>

        <div v-if="!monthlyData.length || rangeJobsPosted === 0" class="empty-state">No tuition job data for the selected period.</div>
        <div v-else>
          <div class="mb-3 flex flex-wrap items-center gap-5 text-xs font-display text-paper-500">
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-blue-400"></span>Job Ads Posted</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-amber-400"></span>Tutor Applications</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-emerald-400"></span>Confirmed Hires</span>
          </div>
          <div class="overflow-x-auto pb-2">
            <div class="flex items-end gap-1.5" :style="{ minWidth: monthlyData.length > 12 ? monthlyData.length * 64 + 'px' : '100%' }">
              <div v-for="m in monthlyData" :key="m.month_key" class="flex flex-1 min-w-[52px] flex-col items-center gap-1.5">
                <div class="flex w-full items-end gap-0.5" style="height:80px;">
                  <div class="flex-1 flex flex-col justify-end">
                    <div class="w-full rounded-t-sm bg-blue-400 transition-all duration-500" :style="{ height: pct(m.jobs_posted, maxJob) + '%', minHeight: m.jobs_posted ? '2px' : '0' }"></div>
                  </div>
                  <div class="flex-1 flex flex-col justify-end">
                    <div class="w-full rounded-t-sm bg-amber-400 transition-all duration-500" :style="{ height: pct(m.job_applications, maxJob) + '%', minHeight: m.job_applications ? '2px' : '0' }"></div>
                  </div>
                  <div class="flex-1 flex flex-col justify-end">
                    <div class="w-full rounded-t-sm bg-emerald-400 transition-all duration-500" :style="{ height: pct(m.jobs_confirmed, maxJob) + '%', minHeight: m.jobs_confirmed ? '2px' : '0' }"></div>
                  </div>
                </div>
                <span class="text-center text-[10px] font-display font-semibold text-paper-400">{{ shortLabel(m.month) }}</span>
              </div>
            </div>
          </div>

          <!-- Period totals -->
          <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="month-stat-card border-blue-100">
              <p class="stat-label">Job Ads Posted</p>
              <p class="stat-value text-blue-700">{{ rangeJobsPosted.toLocaleString() }}</p>
              <p class="mt-1 font-body text-xs text-paper-400">in the selected period</p>
            </div>
            <div class="month-stat-card border-amber-100">
              <p class="stat-label">Tutor Applications</p>
              <p class="stat-value text-amber-600">{{ rangeJobApplications.toLocaleString() }}</p>
              <p class="mt-1 font-body text-xs text-paper-400">
                On average, each job received
                <strong>{{ rangeJobsPosted ? (rangeJobApplications / rangeJobsPosted).toFixed(1) : 0 }}</strong> applications
              </p>
            </div>
            <div class="month-stat-card border-emerald-100">
              <p class="stat-label">Confirmed Hires</p>
              <p class="stat-value text-emerald-600">{{ rangeJobsConfirmed.toLocaleString() }}</p>
              <p class="mt-1 font-body text-xs text-paper-400">
                {{ rangeJobsPosted ? Math.round((rangeJobsConfirmed / rangeJobsPosted) * 100) : 0 }}% of job ads led to a hire
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Where Are All the Hire Requests Right Now? ─────────────────────── -->
      <div class="grid gap-5 xl:grid-cols-2">

        <!-- Connection Status Breakdown -->
        <section class="analytics-card reveal">
          <div class="mb-1">
            <p class="section-kicker">Status Breakdown</p>
            <h2 class="section-title">Where Are the Hire Requests Right Now?</h2>
          </div>
          <p class="mb-5 font-body text-sm text-paper-500">
            All {{ totalConnections.toLocaleString() }} direct hire requests, grouped by their current status.
          </p>
          <div v-if="!connectionStatusRows.length" class="empty-state">No hire requests yet.</div>
          <div v-else class="space-y-3">
            <div v-for="row in connectionStatusRows" :key="row.status" class="rounded-md border border-paper-100 bg-paper-50/70 p-3">
              <div class="flex items-start justify-between gap-2 mb-2">
                <div>
                  <span class="rounded-full border px-2.5 py-0.5 text-xs font-bold font-display" :class="connStatusBadge(row.status)">{{ row.label }}</span>
                  <p class="mt-1.5 font-body text-xs text-paper-400">{{ connStatusDesc(row.status) }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                  <span class="font-display text-xs font-semibold text-paper-500">{{ row.percent }}%</span>
                  <span class="font-display text-sm font-bold text-navy-900 tabular-nums w-10 text-right">{{ row.count.toLocaleString() }}</span>
                </div>
              </div>
              <div class="h-2.5 overflow-hidden rounded-full bg-paper-100">
                <div class="h-full rounded-full transition-all duration-700" :class="connStatusBar(row.status)" :style="{ width: `${row.percent}%` }"></div>
              </div>
            </div>
          </div>
        </section>

        <!-- Job Status Breakdown -->
        <section class="analytics-card reveal delay-1">
          <div class="mb-1">
            <p class="section-kicker">Job Status</p>
            <h2 class="section-title">Have Guardians Found a Tutor?</h2>
          </div>
          <p class="mb-5 font-body text-sm text-paper-500">
            Of the {{ totalJobs.toLocaleString() }} tuition job ads posted, here's how many are still open vs. already filled.
          </p>
          <div v-if="!jobStatusRows.length" class="empty-state">No job ads yet.</div>
          <div v-else class="space-y-4">
            <div v-for="row in jobStatusRows" :key="row.status" class="rounded-md border border-paper-100 bg-paper-50/70 p-4">
              <div class="flex items-start justify-between gap-2 mb-2">
                <div>
                  <span class="rounded-full border px-2.5 py-0.5 text-xs font-bold font-display"
                    :class="row.status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-paper-100 text-paper-600 border-paper-200'">
                    {{ row.status === 'open' ? 'Still Open — Looking for a Tutor' : 'Closed — Tutor Found or Cancelled' }}
                  </span>
                  <p class="mt-1.5 font-body text-xs text-paper-400">
                    {{ row.status === 'open' ? 'These job ads are live and accepting tutor applications.' : 'These job ads are no longer accepting applications.' }}
                  </p>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                  <span class="font-display text-xs font-semibold text-paper-500">{{ row.percent }}%</span>
                  <span class="font-display text-sm font-bold text-navy-900 tabular-nums w-10 text-right">{{ row.count.toLocaleString() }}</span>
                </div>
              </div>
              <div class="h-2.5 overflow-hidden rounded-full bg-paper-100">
                <div class="h-full rounded-full transition-all duration-700" :class="row.status === 'open' ? 'bg-emerald-500' : 'bg-paper-400'" :style="{ width: `${row.percent}%` }"></div>
              </div>
            </div>
          </div>
        </section>

      </div>

      <!-- ── Hiring Funnel + Top Tutors ─────────────────────────────────────── -->
      <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(320px,0.7fr)]">

        <!-- Application Funnel -->
        <section class="analytics-card reveal">
          <div class="mb-1">
            <p class="section-kicker">Step-by-Step Hiring</p>
            <h2 class="section-title">How Tutors Get Hired Through Job Ads</h2>
          </div>
          <p class="mb-5 font-body text-sm text-paper-500">
            When a tutor applies to a job ad, they go through these stages before a guardian confirms them. Here's how many are at each stage right now across all {{ totalJobApplications.toLocaleString() }} applications.
          </p>
          <div v-if="!appStatusRows.length" class="empty-state">No applications yet.</div>
          <div v-else class="space-y-2.5">
            <div v-for="(row, i) in appStatusRows" :key="row.status" class="rounded-md border border-paper-100 bg-paper-50/70 p-3.5">
              <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex items-start gap-3">
                  <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full font-display text-xs font-bold"
                    :class="row.status === 'not_selected' ? 'bg-red-100 text-red-600' : 'bg-navy-100 text-navy-700'">
                    {{ row.status === 'not_selected' ? '✕' : i + 1 }}
                  </span>
                  <div>
                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-bold font-display" :class="appStatusBadge(row.status)">{{ row.label }}</span>
                    <p class="mt-1.5 font-body text-xs text-paper-400">{{ appStatusDesc(row.status) }}</p>
                  </div>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                  <span class="font-display text-xs font-semibold text-paper-500">{{ row.percent }}%</span>
                  <span class="font-display text-sm font-bold text-navy-900 tabular-nums w-10 text-right">{{ row.count.toLocaleString() }}</span>
                </div>
              </div>
              <div class="h-2.5 overflow-hidden rounded-full bg-paper-100">
                <div class="h-full rounded-full transition-all duration-700" :class="appStatusBar(row.status)" :style="{ width: `${row.percent}%` }"></div>
              </div>
            </div>

            <!-- Overall success note -->
            <div v-if="confirmedViaJobs && totalJobApplications" class="mt-2 rounded-md bg-emerald-50 border border-emerald-100 px-4 py-3">
              <p class="font-body text-sm text-emerald-800">
                Out of every 100 applications, about <strong>{{ Math.round((confirmedViaJobs / totalJobApplications) * 100) }}</strong> lead to a confirmed hire.
                {{ appSuccessText }}
              </p>
            </div>
          </div>
        </section>

        <!-- Top Tutors -->
        <section class="analytics-card reveal delay-2">
          <div class="mb-5 flex items-start justify-between gap-3">
            <div>
              <p class="section-kicker">Tutor Performance</p>
              <h2 class="section-title">Our Highest-Rated Tutors</h2>
              <p class="mt-1 font-body text-xs text-paper-400">Ranked by the number of reviews guardians have given them.</p>
            </div>
            <span class="mt-1 shrink-0 rounded-full bg-gold-50 px-3 py-1 text-xs font-semibold font-display text-gold-700">
              Top {{ analytics.top_tutors?.length || 0 }}
            </span>
          </div>
          <div v-if="!analytics.top_tutors?.length" class="empty-state">No reviews yet. Tutors will appear here once they receive reviews from guardians.</div>
          <div v-else class="space-y-2">
            <div v-for="(tutor, i) in analytics.top_tutors" :key="tutor.id"
              class="flex items-center gap-3 rounded-md border border-paper-100 bg-white p-3">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-display text-sm font-bold" :class="rankClass(i)">
                {{ i + 1 }}
              </span>
              <div class="min-w-0 flex-1">
                <p class="truncate font-display text-sm font-bold text-navy-900">{{ tutor.user?.name || 'Unnamed Tutor' }}</p>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                  <span class="inline-flex items-center gap-1 rounded-full bg-gold-50 px-2 py-0.5 text-xs font-semibold text-gold-700">
                    <span>★</span>{{ tutor.rating ?? '—' }} rating
                  </span>
                  <span class="text-xs font-body text-paper-500">{{ Number(tutor.review_count || 0).toLocaleString() }} reviews received</span>
                </div>
              </div>
            </div>
          </div>
        </section>

      </div>

    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { adminApi } from '@/api/admin.js'

const analytics    = ref({})
const loading      = ref(true)
const rangeLoading = ref(false)

const now = new Date()
const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
const defaultFrom  = `${now.getFullYear()}-01`

const rangeFrom = ref(defaultFrom)
const rangeTo   = ref(currentMonth)
const selectedMonthKey = ref('')

// ─── monthly data ──────────────────────────────────────────────────────────────

const monthlyData = computed(() => analytics.value.monthly ?? analytics.value.monthly_connections ?? [])

function pct(val, max) {
  return Math.round((Number(val || 0) / Math.max(1, max)) * 100)
}

function shortLabel(monthStr) {
  const parts = monthStr.split(' ')
  return parts.length === 2 ? `${parts[0]} '${parts[1].slice(2)}` : monthStr
}

// ─── connection chart ──────────────────────────────────────────────────────────

const maxConnections = computed(() => Math.max(1, ...monthlyData.value.map(m => Number(m.connections || 0))))

const selectedMonth = computed(() => monthlyData.value.find(m => m.month_key === selectedMonthKey.value) ?? null)

const selectedMonthConnRate = computed(() => {
  const m = selectedMonth.value
  if (!m || !m.connections) return 0
  return Math.round((Number(m.confirmed || 0) / Number(m.connections)) * 100)
})

// ─── connection summary ────────────────────────────────────────────────────────

const totalConnections = computed(() =>
  Object.values(analytics.value.connection_statuses ?? {}).reduce((a, b) => a + Number(b || 0), 0)
)

const confirmedTotal = computed(() => {
  const s = analytics.value.connection_statuses ?? {}
  return Number(s.confirmed ?? s.connected ?? 0)
})

const connectionConversionRate = computed(() =>
  totalConnections.value ? Math.round((confirmedTotal.value / totalConnections.value) * 100) : 0
)

const connectionSuccessText = computed(() => {
  const rate = connectionConversionRate.value
  if (!rate || !totalConnections.value) return 'No confirmed hires yet'
  const oneIn = Math.round(100 / rate)
  if (oneIn <= 1) return 'Nearly every request leads to a hire'
  return `About 1 in every ${oneIn} requests leads to a confirmed hire`
})

// ─── registration chart ────────────────────────────────────────────────────────

const maxReg = computed(() =>
  Math.max(1, ...monthlyData.value.map(m => Math.max(Number(m.tutors_registered || 0), Number(m.guardians_registered || 0))))
)

const rangeNewTutors    = computed(() => monthlyData.value.reduce((a, m) => a + Number(m.tutors_registered || 0), 0))
const rangeNewGuardians = computed(() => monthlyData.value.reduce((a, m) => a + Number(m.guardians_registered || 0), 0))

const topRegMonths = computed(() =>
  [...monthlyData.value]
    .sort((a, b) => (Number(b.tutors_registered || 0) + Number(b.guardians_registered || 0)) - (Number(a.tutors_registered || 0) + Number(a.guardians_registered || 0)))
    .slice(0, 3)
    .filter(m => (m.tutors_registered || 0) + (m.guardians_registered || 0) > 0)
)

// ─── job chart ─────────────────────────────────────────────────────────────────

const maxJob = computed(() =>
  Math.max(1, ...monthlyData.value.map(m => Math.max(Number(m.jobs_posted || 0), Number(m.job_applications || 0), Number(m.jobs_confirmed || 0))))
)

const rangeJobsPosted      = computed(() => monthlyData.value.reduce((a, m) => a + Number(m.jobs_posted || 0), 0))
const rangeJobApplications = computed(() => monthlyData.value.reduce((a, m) => a + Number(m.job_applications || 0), 0))
const rangeJobsConfirmed   = computed(() => monthlyData.value.reduce((a, m) => a + Number(m.jobs_confirmed || 0), 0))

// ─── job summary ───────────────────────────────────────────────────────────────

const totalJobs            = computed(() => Object.values(analytics.value.job_statuses ?? {}).reduce((a, b) => a + Number(b || 0), 0))
const openJobs             = computed(() => Number(analytics.value.job_statuses?.open ?? 0))
const totalJobApplications = computed(() => Object.values(analytics.value.application_statuses ?? {}).reduce((a, b) => a + Number(b || 0), 0))
const confirmedViaJobs     = computed(() => Number(analytics.value.application_statuses?.connected ?? 0))

const appSuccessText = computed(() => {
  if (!totalJobApplications.value || !confirmedViaJobs.value) return ''
  const rate = Math.round((confirmedViaJobs.value / totalJobApplications.value) * 100)
  if (!rate) return ''
  const oneIn = Math.round(100 / rate)
  if (oneIn <= 1) return 'Nearly every application results in a hire — excellent!'
  return `That means roughly 1 in every ${oneIn} applications results in a hire.`
})

// ─── at a glance ───────────────────────────────────────────────────────────────

const atAGlance = computed(() => {
  const lines = []

  if (totalConnections.value) {
    const rate = connectionConversionRate.value
    lines.push(
      `${totalConnections.value.toLocaleString()} guardians have sent direct hire requests to tutors. ${confirmedTotal.value.toLocaleString()} of those led to a confirmed hire (${rate}% success rate).`
    )
  }

  if (totalJobs.value) {
    lines.push(
      `${totalJobs.value.toLocaleString()} tuition job ${totalJobs.value === 1 ? 'ad has' : 'ads have'} been posted by guardians. ${openJobs.value} ${openJobs.value === 1 ? 'is' : 'are'} still open and looking for a tutor.`
    )
  }

  const newTotal = rangeNewTutors.value + rangeNewGuardians.value
  if (newTotal) {
    lines.push(
      `In the selected time period, ${rangeNewTutors.value} new ${rangeNewTutors.value === 1 ? 'tutor' : 'tutors'} and ${rangeNewGuardians.value} new ${rangeNewGuardians.value === 1 ? 'guardian' : 'guardians'} joined the platform.`
    )
  }

  return lines
})

// ─── connection status rows ────────────────────────────────────────────────────

const connStatusLabelMap = {
  pending:         'Waiting — No Action Yet',
  admin_reviewing: 'Being Reviewed by Team',
  tutor_contacted: 'Tutor Has Been Notified',
  connected:       'Tutor & Guardian Connected',
  confirmed:       'Hire Confirmed',
  declined:        'Request Declined',
  closed:          'Closed',
}

const connStatusDescriptions = {
  pending:         'The guardian sent a request but no one has acted on it yet.',
  admin_reviewing: 'Our team is currently looking into this request.',
  tutor_contacted: 'The tutor has been informed and is expected to respond.',
  connected:       'The tutor and guardian have been connected on the platform.',
  confirmed:       'The tutor has been hired and is working with the guardian.',
  declined:        'This request was turned down.',
  closed:          'This request is no longer active.',
}

function connStatusDesc(s) {
  return connStatusDescriptions[s] ?? ''
}

const connectionStatusRows = computed(() =>
  Object.entries(analytics.value.connection_statuses ?? {})
    .map(([status, count]) => {
      const numeric = Number(count || 0)
      return {
        status,
        count: numeric,
        label: connStatusLabelMap[status] ?? status.replace(/_/g, ' '),
        percent: totalConnections.value ? Math.round((numeric / totalConnections.value) * 100) : 0,
      }
    })
    .sort((a, b) => b.count - a.count)
)

function connStatusBadge(s) {
  const m = {
    pending:         'bg-paper-100 text-paper-600 border-paper-200',
    admin_reviewing: 'bg-blue-50 text-blue-700 border-blue-200',
    tutor_contacted: 'bg-purple-50 text-purple-700 border-purple-200',
    connected:       'bg-emerald-50 text-emerald-700 border-emerald-200',
    confirmed:       'bg-emerald-50 text-emerald-700 border-emerald-200',
    declined:        'bg-red-50 text-red-600 border-red-200',
    closed:          'bg-red-50 text-red-600 border-red-200',
  }
  return m[s] ?? 'bg-paper-100 text-paper-600 border-paper-200'
}

function connStatusBar(s) {
  const m = {
    pending:         'bg-paper-400',
    admin_reviewing: 'bg-blue-400',
    tutor_contacted: 'bg-purple-400',
    connected:       'bg-emerald-500',
    confirmed:       'bg-emerald-500',
    declined:        'bg-red-400',
    closed:          'bg-red-400',
  }
  return m[s] ?? 'bg-paper-300'
}

// ─── job status rows ───────────────────────────────────────────────────────────

const jobStatusRows = computed(() =>
  Object.entries(analytics.value.job_statuses ?? {})
    .map(([status, count]) => {
      const numeric = Number(count || 0)
      return { status, count: numeric, percent: totalJobs.value ? Math.round((numeric / totalJobs.value) * 100) : 0 }
    })
    .sort((a, b) => b.count - a.count)
)

// ─── application status rows ───────────────────────────────────────────────────

const appStatusOrder = ['applied', 'shortlisted', 'appointed', 'connected', 'not_selected']

const appStatusLabelMap = {
  applied:      'Applied',
  shortlisted:  'Shortlisted by Guardian',
  appointed:    'Demo Class Scheduled',
  connected:    'Hire Confirmed',
  not_selected: 'Not Selected',
}

const appStatusDescriptions = {
  applied:      'The tutor submitted their application to this job ad.',
  shortlisted:  'The guardian liked this tutor and saved them for consideration.',
  appointed:    'A trial or demo class has been arranged between tutor and guardian.',
  connected:    'The guardian confirmed this tutor — they are now hired and working.',
  not_selected: 'This tutor was not chosen for the position.',
}

function appStatusDesc(s) {
  return appStatusDescriptions[s] ?? ''
}

const appStatusRows = computed(() =>
  Object.entries(analytics.value.application_statuses ?? {})
    .map(([status, count]) => {
      const numeric = Number(count || 0)
      return {
        status,
        count: numeric,
        label: appStatusLabelMap[status] ?? status.replace(/_/g, ' '),
        percent: totalJobApplications.value ? Math.round((numeric / totalJobApplications.value) * 100) : 0,
      }
    })
    .sort((a, b) => (appStatusOrder.indexOf(a.status) ?? 99) - (appStatusOrder.indexOf(b.status) ?? 99))
)

function appStatusBadge(s) {
  const m = {
    applied:      'bg-blue-50 text-blue-700 border-blue-200',
    shortlisted:  'bg-gold-50 text-gold-700 border-gold-200',
    appointed:    'bg-purple-50 text-purple-700 border-purple-200',
    connected:    'bg-emerald-50 text-emerald-700 border-emerald-200',
    not_selected: 'bg-red-50 text-red-600 border-red-200',
  }
  return m[s] ?? 'bg-paper-100 text-paper-600 border-paper-200'
}

function appStatusBar(s) {
  const m = {
    applied:      'bg-blue-400',
    shortlisted:  'bg-gold-400',
    appointed:    'bg-purple-400',
    connected:    'bg-emerald-500',
    not_selected: 'bg-red-400',
  }
  return m[s] ?? 'bg-paper-300'
}

// ─── top tutors ────────────────────────────────────────────────────────────────

function rankClass(i) {
  if (i === 0) return 'bg-gold-400 text-navy-900'
  if (i === 1) return 'bg-paper-300 text-navy-800'
  if (i === 2) return 'bg-navy-100 text-navy-700'
  return 'bg-paper-100 text-paper-600'
}

// ─── data fetching ─────────────────────────────────────────────────────────────

async function fetchAnalytics(params = {}) {
  const { data } = await adminApi.getAnalytics(params)
  analytics.value = data.data || {}
  const months = analytics.value.monthly ?? analytics.value.monthly_connections ?? []
  if (months.length) selectedMonthKey.value = months[months.length - 1].month_key
}

async function applyRange() {
  rangeLoading.value = true
  try {
    await fetchAnalytics({ from: rangeFrom.value, to: rangeTo.value })
  } finally {
    rangeLoading.value = false
  }
}

onMounted(async () => {
  try {
    await fetchAnalytics({ from: rangeFrom.value, to: rangeTo.value })
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.analytics-hero,
.analytics-card,
.summary-card {
  @apply rounded-md border border-paper-200 bg-white shadow-sm;
}

.analytics-hero { @apply p-5 shadow-lg md:p-6; }
.analytics-card { @apply p-5 md:p-6; }
.summary-card   { @apply p-5; }

.summary-icon  { @apply mb-4 flex h-11 w-11 items-center justify-center rounded-md; }
.summary-value { @apply font-display text-3xl font-bold; }
.summary-label { @apply mt-1 font-display text-sm font-semibold text-navy-800; }

.section-kicker { @apply font-display text-xs font-bold uppercase tracking-wide text-gold-600; }
.section-title  { @apply mt-1 font-display text-xl font-bold text-navy-900; }

.month-stat-card { @apply rounded-md border border-paper-100 bg-white p-4; }
.stat-label      { @apply font-display text-xs font-bold uppercase tracking-wide text-paper-400; }
.stat-value      { @apply mt-1 font-display text-2xl font-bold; }

.empty-state {
  @apply rounded-md border border-dashed border-paper-200 bg-paper-50 px-4 py-8 text-center font-body text-sm text-paper-400;
}

.reveal { animation: reveal-up 0.54s ease both; }
.delay-1 { animation-delay: 70ms; }
.delay-2 { animation-delay: 140ms; }
.delay-3 { animation-delay: 210ms; }

@keyframes reveal-up {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
  .reveal { animation: none; }
}
</style>
