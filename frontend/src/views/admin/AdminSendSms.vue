<template>
  <div class="mx-auto max-w-2xl space-y-5 px-0 sm:px-2">

    <!-- Header -->
    <div class="card">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="kicker">Communication</p>
          <h1 class="mt-1 font-display text-xl font-bold text-navy-900 sm:text-2xl">Send SMS</h1>
          <p class="mt-1.5 font-body text-sm text-paper-500 leading-relaxed max-w-lg">
            Send a direct SMS to any user, or broadcast a system-wide message to everyone on the platform.
          </p>
        </div>
        <span class="shrink-0 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 font-display text-xs font-bold text-emerald-700">
          Admin Only
        </span>
      </div>

      <!-- Mode toggle -->
      <div class="mt-4 flex overflow-hidden rounded-lg border border-paper-200 bg-paper-50 p-1">
        <button
          @click="switchMode('single')"
          class="flex flex-1 items-center justify-center gap-2 rounded-md py-2.5 font-display text-sm font-bold transition-all"
          :class="mode === 'single' ? 'bg-navy-800 text-white shadow-sm' : 'text-paper-500 hover:text-navy-700'"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
          </svg>
          Single User
        </button>
        <button
          @click="switchMode('broadcast')"
          class="flex flex-1 items-center justify-center gap-2 rounded-md py-2.5 font-display text-sm font-bold transition-all"
          :class="mode === 'broadcast' ? 'bg-navy-800 text-white shadow-sm' : 'text-paper-500 hover:text-navy-700'"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
          </svg>
          Broadcast to All
        </button>
      </div>
    </div>

    <!-- ════════════ SINGLE USER MODE ════════════ -->
    <template v-if="mode === 'single'">

      <!-- Step 1: Select user -->
      <div class="card">
        <step-label :n="1" :done="!!selectedUser">Choose a recipient</step-label>
        <p class="mb-4 font-body text-xs text-paper-500">Search by name, phone number, email address, or user ID <span class="font-semibold text-paper-600">(e.g. TUT-123456 or GRD-123456)</span></p>

        <!-- Search input -->
        <div v-if="!selectedUser" class="relative">
          <div class="flex items-center gap-2 rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-3 transition-all focus-within:border-navy-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-navy-100">
            <svg class="h-4 w-4 shrink-0 text-paper-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <input
              v-model="searchQuery"
              @input="onSearchInput"
              @focus="showDropdown = true"
              @keydown.escape="closeDropdown"
              @keydown.down.prevent="moveDown"
              @keydown.up.prevent="moveUp"
              @keydown.enter.prevent="selectHighlighted"
              type="text"
              placeholder="Name, phone, email, or ID (TUT-/GRD-)…"
              class="flex-1 bg-transparent font-body text-sm text-navy-900 placeholder-paper-400 outline-none"
              autocomplete="off"
            />
            <button v-if="searchQuery" @click="clearSearch" class="rounded p-0.5 text-paper-400 hover:bg-paper-200 hover:text-paper-600 transition-colors">
              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
              </svg>
            </button>
            <div v-if="searching" class="h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-navy-200 border-t-navy-700"></div>
          </div>

          <!-- Dropdown -->
          <Transition name="dropdown">
            <div v-if="showDropdown && (searchResults.length || noResults)"
              class="absolute z-30 mt-1.5 w-full overflow-hidden rounded-lg border border-paper-200 bg-white shadow-lg">
              <div v-if="noResults" class="px-4 py-6 text-center font-body text-sm text-paper-400">
                No users found for "{{ searchQuery }}".
              </div>
              <ul v-else>
                <li v-for="(user, i) in searchResults" :key="user.id"
                  @mousedown.prevent="selectUser(user)"
                  class="flex cursor-pointer items-center gap-3 px-4 py-3 transition-colors"
                  :class="i === highlightedIndex ? 'bg-navy-50' : 'hover:bg-paper-50'">
                  <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-display text-xs font-bold"
                    :class="roleColor(user.role)">
                    {{ initials(user.name) }}
                  </span>
                  <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-baseline gap-2">
                      <p class="truncate font-display text-sm font-bold text-navy-900">{{ user.name }}</p>
                      <span v-if="user.platform_id" class="shrink-0 font-display text-[10px] font-bold text-paper-400 tracking-wide">{{ user.platform_id }}</span>
                    </div>
                    <p class="truncate font-body text-xs text-paper-400">{{ user.phone }} · {{ user.email }}</p>
                  </div>
                  <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold font-display uppercase tracking-wide"
                    :class="roleBadge(user.role)">
                    {{ user.role }}
                  </span>
                </li>
              </ul>
            </div>
          </Transition>
        </div>

        <!-- Selected user card -->
        <Transition name="slide-up">
          <div v-if="selectedUser"
            class="flex flex-col gap-3 rounded-lg border-2 border-emerald-300 bg-emerald-50 p-4 sm:flex-row sm:items-center">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full font-display text-base font-bold"
              :class="roleColor(selectedUser.role)">
              {{ initials(selectedUser.name) }}
            </span>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-baseline gap-2">
                <p class="font-display text-base font-bold text-navy-900">{{ selectedUser.name }}</p>
                <span v-if="selectedUser.platform_id" class="font-display text-xs font-bold text-paper-400">{{ selectedUser.platform_id }}</span>
              </div>
              <p class="mt-0.5 font-body text-sm text-navy-700 font-semibold">{{ selectedUser.phone }}</p>
              <p class="font-body text-xs text-paper-500">{{ selectedUser.email }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-3 sm:flex-col sm:items-end">
              <span class="rounded-full px-2.5 py-1 text-xs font-bold font-display uppercase tracking-wide"
                :class="roleBadge(selectedUser.role)">
                {{ selectedUser.role }}
              </span>
              <button
                @click="clearSelection"
                class="flex items-center gap-1.5 rounded-md border border-paper-300 bg-white px-3 py-1.5 font-display text-xs font-bold text-paper-700 shadow-sm transition-colors hover:border-red-300 hover:bg-red-50 hover:text-red-600"
              >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
                Change
              </button>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Step 2: Message -->
      <div class="card" :class="{ 'opacity-50': !selectedUser }">
        <step-label :n="2" :done="!!selectedUser && message.trim().length > 0">Write your message</step-label>
        <p class="mb-3 font-body text-xs text-paper-500">A "Regards, Apex Tutor Team" signature is automatically added.</p>
        <message-box v-model="message" :disabled="!selectedUser" />
        <char-counter :message="message" />
      </div>

      <!-- Step 3: Send -->
      <div class="card" :class="{ 'opacity-50': !selectedUser || !message.trim() }">
        <step-label :n="3" :done="false">Review &amp; send</step-label>

        <message-preview v-if="selectedUser && message.trim()" :message="message" :footer="SMS_FOOTER" class="mb-4">
          <p class="font-body text-sm text-paper-600">
            Sending to <strong class="text-navy-900">{{ selectedUser.name }}</strong>
            at <strong class="text-navy-900">{{ selectedUser.phone }}</strong>
          </p>
        </message-preview>

        <button
          @click="openSingleModal"
          :disabled="!selectedUser || !message.trim()"
          class="send-btn w-full"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
          </svg>
          Send SMS to {{ selectedUser?.name || 'recipient' }}
        </button>
      </div>

    </template>

    <!-- ════════════ BROADCAST MODE ════════════ -->
    <template v-else>

      <!-- Warning banner -->
      <div class="flex gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
        </svg>
        <div>
          <p class="font-display text-sm font-bold text-amber-800">Broadcast SMS — Use with care</p>
          <p class="mt-0.5 font-body text-sm text-amber-700">
            Each SMS costs credits. Use this only for important announcements (e.g. maintenance, new features, policy updates).
          </p>
        </div>
      </div>

      <!-- Step 1: Target selector -->
      <div class="card">
        <step-label :n="1" :done="broadcastReady">Choose who to send to</step-label>

        <!-- 4-option grid -->
        <div class="mt-1 grid grid-cols-2 gap-2 sm:grid-cols-4">
          <button v-for="opt in TARGET_OPTS" :key="opt.value"
            @click="setBroadcastTarget(opt.value)"
            class="flex flex-col items-center gap-1.5 rounded-lg border-2 px-3 py-3 font-display text-xs font-bold transition-all"
            :class="broadcastTarget === opt.value
              ? 'border-navy-700 bg-navy-50 text-navy-800 shadow-sm'
              : 'border-paper-200 bg-white text-paper-500 hover:border-paper-300 hover:text-navy-700'"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" :d="opt.icon"/>
            </svg>
            {{ opt.label }}
          </button>
        </div>

        <!-- University picker -->
        <Transition name="slide-up">
          <div v-if="broadcastTarget === 'university'" class="mt-4">
            <label class="mb-1.5 block font-display text-xs font-bold text-navy-700">Select University</label>
            <div v-if="universitiesLoading" class="flex items-center gap-2 text-paper-500 font-body text-sm">
              <div class="h-4 w-4 animate-spin rounded-full border-2 border-navy-200 border-t-navy-700"></div>
              Loading universities…
            </div>
            <select v-else
              v-model="selectedUniversityId"
              @change="fetchBroadcastPreview"
              class="input text-sm w-full"
            >
              <option :value="null">— Select a university —</option>
              <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
            <p v-if="broadcastTarget === 'university' && !selectedUniversityId" class="mt-1.5 font-body text-xs text-amber-600">
              Please select a university to see the recipient count.
            </p>
          </div>
        </Transition>

        <!-- Recipient count -->
        <div class="mt-5 rounded-lg border border-paper-100 bg-paper-50 px-4 py-3">
          <div v-if="broadcastLoading" class="flex items-center gap-3">
            <div class="h-5 w-5 animate-spin rounded-full border-2 border-navy-200 border-t-navy-700"></div>
            <span class="font-body text-sm text-paper-500">Counting recipients…</span>
          </div>
          <div v-else-if="broadcastTarget === 'university' && !selectedUniversityId" class="font-body text-sm text-paper-400 italic">
            Select a university above to see the recipient count.
          </div>
          <div v-else class="flex flex-wrap items-center gap-4">
            <div>
              <p class="font-display text-3xl font-bold text-navy-900">{{ broadcastCount.toLocaleString() }}</p>
              <p class="font-body text-sm text-paper-500">{{ targetDescription }} with registered phone numbers</p>
            </div>
            <div v-if="broadcastCount === 0" class="rounded-md bg-red-50 px-3 py-2 font-body text-sm text-red-600">
              No phone numbers found — nothing to send.
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Message -->
      <div class="card" :class="{ 'opacity-50': !broadcastReady }">
        <step-label :n="2" :done="broadcastReady && message.trim().length > 0">Write your broadcast message</step-label>
        <p class="mb-3 font-body text-xs text-paper-500">Keep it short and clear. Example: "Platform maintenance on Friday 10 PM–12 AM."</p>
        <message-box v-model="message" :disabled="!broadcastReady" />
        <char-counter :message="message" />
      </div>

      <!-- Step 3: Send -->
      <div class="card" :class="{ 'opacity-50': !broadcastReady || !message.trim() }">
        <step-label :n="3" :done="false">Review &amp; broadcast</step-label>

        <message-preview v-if="broadcastReady && message.trim()" :message="message" :footer="SMS_FOOTER" class="mb-4">
          <p class="font-body text-sm text-paper-600">
            Sending to <strong class="text-navy-900">{{ broadcastCount.toLocaleString() }}</strong>
            <span class="text-navy-900"> {{ targetDescription }}</span>.
          </p>
        </message-preview>

        <button
          @click="openBroadcastModal"
          :disabled="!broadcastReady || !message.trim()"
          class="send-btn w-full bg-amber-600 hover:bg-amber-700"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
          </svg>
          Broadcast to {{ broadcastCount.toLocaleString() }} {{ targetDescription }}
        </button>
      </div>

    </template>

    <!-- ══════════════════════════════════════════ -->
    <!-- Single-user confirm modal                  -->
    <!-- ══════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showSingleModal" class="modal-backdrop" @click.self="showSingleModal = false">
          <div class="modal-box">
            <div class="modal-header">
              <span class="modal-icon bg-navy-100 text-navy-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                </svg>
              </span>
              <div class="min-w-0">
                <p class="font-display text-base font-bold text-navy-900">Confirm SMS</p>
                <p class="font-body text-xs text-paper-500">This cannot be undone once sent.</p>
              </div>
              <button @click="showSingleModal = false" class="modal-close-btn"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
            </div>

            <div class="modal-body">
              <!-- Recipient -->
              <div class="flex items-center gap-3 rounded-lg border border-paper-100 bg-paper-50 p-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full font-display text-sm font-bold" :class="roleColor(selectedUser?.role)">
                  {{ selectedUser ? initials(selectedUser.name) : '' }}
                </span>
                <div class="min-w-0">
                  <div class="flex flex-wrap items-baseline gap-1.5">
                    <p class="truncate font-display text-sm font-bold text-navy-900">{{ selectedUser?.name }}</p>
                    <span v-if="selectedUser?.platform_id" class="font-display text-[10px] font-bold text-paper-400">{{ selectedUser.platform_id }}</span>
                  </div>
                  <p class="font-body text-sm font-semibold text-navy-700">{{ selectedUser?.phone }}</p>
                </div>
              </div>

              <!-- Message preview -->
              <div>
                <p class="mb-1.5 kicker">Message</p>
                <div class="max-h-36 overflow-y-auto rounded-lg border border-paper-200 bg-white p-3.5">
                  <p class="whitespace-pre-wrap font-body text-sm text-navy-800">{{ message }}</p>
                  <p class="mt-2 whitespace-pre-wrap font-body text-xs text-paper-400">{{ SMS_FOOTER }}</p>
                </div>
              </div>

              <p class="font-body text-sm text-paper-600">
                Are you sure you want to send this SMS to <strong class="text-navy-900">{{ selectedUser?.name }}</strong> at <strong class="text-navy-900">{{ selectedUser?.phone }}</strong>?
              </p>
            </div>

            <div class="modal-footer">
              <button @click="showSingleModal = false" class="modal-cancel-btn">Cancel</button>
              <button @click="confirmSingleSend" :disabled="sending" class="modal-confirm-btn">
                <span v-if="sending" class="spinner"></span>
                {{ sending ? 'Sending…' : 'Yes, Send SMS' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══════════════════════════════════════════ -->
    <!-- Broadcast confirm modal                    -->
    <!-- ══════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showBroadcastModal" class="modal-backdrop" @click.self="showBroadcastModal = false">
          <div class="modal-box">
            <div class="modal-header bg-amber-50">
              <span class="modal-icon bg-amber-100 text-amber-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
              </span>
              <div class="min-w-0">
                <p class="font-display text-base font-bold text-navy-900">Confirm Broadcast</p>
                <p class="font-body text-xs text-amber-700 font-semibold">This will send to ALL users.</p>
              </div>
              <button @click="showBroadcastModal = false" class="modal-close-btn"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
            </div>

            <div class="modal-body">
              <!-- Recipients count -->
              <div class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 p-3">
                <svg class="h-8 w-8 shrink-0 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
                </svg>
                <div>
                  <p class="font-display text-xl font-bold text-navy-900">{{ broadcastCount.toLocaleString() }} {{ targetDescription }}</p>
                  <p class="font-body text-xs text-paper-500">will receive this SMS</p>
                </div>
              </div>

              <!-- Message preview -->
              <div>
                <p class="mb-1.5 kicker">Message to be sent</p>
                <div class="max-h-36 overflow-y-auto rounded-lg border border-paper-200 bg-white p-3.5">
                  <p class="whitespace-pre-wrap font-body text-sm text-navy-800">{{ message }}</p>
                  <p class="mt-2 whitespace-pre-wrap font-body text-xs text-paper-400">{{ SMS_FOOTER }}</p>
                </div>
              </div>

              <!-- Typed confirmation -->
              <div class="rounded-lg border border-amber-200 bg-amber-50 p-3.5">
                <p class="mb-2 font-body text-sm font-semibold text-amber-800">
                  To confirm, type <span class="rounded bg-amber-200 px-1.5 py-0.5 font-display font-bold">SEND</span> in the box below:
                </p>
                <input
                  v-model="broadcastConfirmText"
                  type="text"
                  placeholder="Type SEND here…"
                  class="w-full rounded-md border border-amber-300 bg-white px-3 py-2.5 font-display text-sm font-bold uppercase tracking-widest text-navy-900 outline-none placeholder:normal-case placeholder:font-normal placeholder:tracking-normal placeholder:text-paper-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                />
              </div>
            </div>

            <div class="modal-footer">
              <button @click="showBroadcastModal = false" class="modal-cancel-btn">Cancel</button>
              <button
                @click="confirmBroadcast"
                :disabled="broadcastConfirmText.trim().toUpperCase() !== 'SEND' || sending"
                class="modal-confirm-btn bg-amber-600 hover:bg-amber-700 disabled:bg-amber-300"
              >
                <span v-if="sending" class="spinner"></span>
                {{ sending ? 'Sending…' : `Broadcast to ${broadcastCount.toLocaleString()} ${targetDescription}` }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Toast feedback -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="feedbackMsg"
          class="fixed bottom-5 left-1/2 z-[9999] w-[calc(100vw-2rem)] max-w-sm -translate-x-1/2 rounded-xl px-5 py-4 shadow-xl"
          :class="feedbackOk ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'">
          <div class="flex items-start gap-3">
            <svg v-if="feedbackOk" class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <svg v-else class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <p class="font-body text-sm leading-snug">{{ feedbackMsg }}</p>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, defineComponent, h, computed } from 'vue'
import { adminApi } from '@/api/admin.js'

// ─── shared constants ─────────────────────────────────────────────────────────

const SMS_FOOTER = '\n\nRegards,\nApex Tutor Team'

// ─── sub-components (inline, no extra files) ──────────────────────────────────

const StepLabel = defineComponent({
  props: { n: Number, done: Boolean },
  slots: ['default'],
  setup(props, { slots }) {
    return () => h('div', { class: 'mb-3 flex items-center gap-2.5' }, [
      h('span', {
        class: [
          'flex h-7 w-7 shrink-0 items-center justify-center rounded-full font-display text-sm font-bold transition-colors',
          props.done ? 'bg-emerald-500 text-white' : 'bg-navy-800 text-white',
        ],
      }, props.done
        ? h('svg', { class: 'h-4 w-4', fill: 'none', stroke: 'currentColor', 'stroke-width': '2.5', viewBox: '0 0 24 24' },
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M4.5 12.75l6 6 9-13.5' }))
        : props.n),
      h('p', { class: 'font-display text-base font-bold text-navy-900' }, slots.default?.()),
    ])
  },
})

const MessageBox = defineComponent({
  props: { modelValue: String, disabled: Boolean },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    return () => h('textarea', {
      value: props.modelValue,
      disabled: props.disabled,
      rows: 5,
      maxlength: 1000,
      placeholder: 'Type your message here…',
      class: 'w-full resize-none rounded-lg border border-paper-200 bg-paper-50 p-3.5 font-body text-sm text-navy-900 placeholder-paper-400 outline-none transition-all focus:border-navy-500 focus:bg-white focus:ring-2 focus:ring-navy-100 disabled:cursor-not-allowed',
      onInput: (e) => emit('update:modelValue', e.target.value),
    })
  },
})

const CharCounter = defineComponent({
  props: { message: String },
  setup(props) {
    const len  = computed(() => props.message?.length ?? 0)
    const long = computed(() => (len.value + SMS_FOOTER.length) > 160)
    return () => h('div', { class: 'mt-2 flex flex-wrap items-center justify-between gap-2' }, [
      long.value
        ? h('p', { class: 'font-body text-xs text-amber-600 font-semibold' }, 'Long message — may use more SMS credit than usual.')
        : h('p', { class: 'font-body text-xs text-paper-400' }, 'Signature is added automatically at the bottom.'),
      h('span', { class: ['font-display text-xs font-semibold', len.value > 800 ? 'text-amber-600' : 'text-paper-400'] }, `${len.value} / 1000`),
    ])
  },
})

const MessagePreview = defineComponent({
  props: { message: String, footer: String, class: String },
  slots: ['default'],
  setup(props, { slots }) {
    return () => h('div', { class: ['rounded-lg border border-paper-100 bg-paper-50 p-4', props.class] }, [
      h('p', { class: 'kicker mb-2' }, 'Preview'),
      slots.default?.(),
      h('div', { class: 'mt-3 max-h-40 overflow-y-auto rounded-lg border border-paper-200 bg-white p-3.5' }, [
        h('p', { class: 'whitespace-pre-wrap font-body text-sm text-navy-800' }, props.message),
        h('p', { class: 'mt-2 whitespace-pre-wrap font-body text-xs text-paper-400' }, props.footer),
      ]),
    ])
  },
})

// ─── broadcast target options ─────────────────────────────────────────────────

const TARGET_OPTS = [
  {
    value: 'all',
    label: 'All Users',
    icon:  'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z',
  },
  {
    value: 'tutors',
    label: 'All Tutors',
    icon:  'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
  },
  {
    value: 'guardians',
    label: 'All Guardians',
    icon:  'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
  },
  {
    value: 'university',
    label: 'By University',
    icon:  'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z',
  },
]

// ─── mode ─────────────────────────────────────────────────────────────────────

const mode = ref('single')

function switchMode(m) {
  mode.value = m
  message.value = ''
  if (m === 'broadcast') {
    fetchBroadcastPreview()
    loadUniversities()
  }
}

// ─── single user ──────────────────────────────────────────────────────────────

const searchQuery      = ref('')
const searchResults    = ref([])
const searching        = ref(false)
const showDropdown     = ref(false)
const noResults        = ref(false)
const highlightedIndex = ref(-1)
const selectedUser     = ref(null)

let debounceTimer = null

function onSearchInput() {
  noResults.value = false
  highlightedIndex.value = -1
  clearTimeout(debounceTimer)
  if (!searchQuery.value.trim()) {
    searchResults.value = []
    showDropdown.value = false
    return
  }
  debounceTimer = setTimeout(runSearch, 300)
}

async function runSearch() {
  searching.value = true
  try {
    const { data } = await adminApi.searchUsersForSms(searchQuery.value.trim())
    searchResults.value = data.data || []
    noResults.value = searchResults.value.length === 0
    showDropdown.value = true
  } catch {
    searchResults.value = []
  } finally {
    searching.value = false
  }
}

function moveDown()  { highlightedIndex.value = Math.min(highlightedIndex.value + 1, searchResults.value.length - 1) }
function moveUp()    { highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0) }
function selectHighlighted() {
  if (highlightedIndex.value >= 0 && searchResults.value[highlightedIndex.value]) selectUser(searchResults.value[highlightedIndex.value])
}

function selectUser(user) {
  selectedUser.value  = user
  searchQuery.value   = ''
  searchResults.value = []
  showDropdown.value  = false
  noResults.value     = false
}

function closeDropdown()  { showDropdown.value = false; highlightedIndex.value = -1 }
function clearSearch()    { searchQuery.value = ''; searchResults.value = []; showDropdown.value = false; noResults.value = false; highlightedIndex.value = -1 }
function clearSelection() { selectedUser.value = null; clearSearch() }

// ─── broadcast ────────────────────────────────────────────────────────────────

const broadcastTarget       = ref('all')
const broadcastCount        = ref(0)
const broadcastLoading      = ref(false)
const broadcastConfirmText  = ref('')

// University picker
const universities      = ref([])
const universitiesLoading = ref(false)
const selectedUniversityId = ref(null)

const targetDescription = computed(() => {
  switch (broadcastTarget.value) {
    case 'tutors':     return 'tutors'
    case 'guardians':  return 'guardians & students'
    case 'university': {
      const u = universities.value.find(u => u.id === selectedUniversityId.value)
      return u ? `tutors from ${u.name}` : 'tutors from selected university'
    }
    default: return 'users'
  }
})

const broadcastReady = computed(() => {
  if (broadcastTarget.value === 'university' && !selectedUniversityId.value) return false
  return broadcastCount.value > 0
})

async function loadUniversities() {
  if (universities.value.length) return
  universitiesLoading.value = true
  try {
    const { data } = await adminApi.getSmsUniversities()
    universities.value = data.data || []
  } finally {
    universitiesLoading.value = false
  }
}

function setBroadcastTarget(value) {
  broadcastTarget.value = value
  if (value !== 'university') {
    selectedUniversityId.value = null
    fetchBroadcastPreview()
  } else {
    broadcastCount.value = 0
  }
}

async function fetchBroadcastPreview() {
  if (broadcastTarget.value === 'university' && !selectedUniversityId.value) {
    broadcastCount.value = 0
    return
  }
  broadcastLoading.value = true
  try {
    const params = { target: broadcastTarget.value }
    if (broadcastTarget.value === 'university') params.university_id = selectedUniversityId.value
    const { data } = await adminApi.getBroadcastPreview(params)
    broadcastCount.value = data.data?.recipients ?? 0
  } finally {
    broadcastLoading.value = false
  }
}

// ─── shared compose + modals ──────────────────────────────────────────────────

const message           = ref('')
const showSingleModal   = ref(false)
const showBroadcastModal = ref(false)
const sending           = ref(false)
const feedbackMsg       = ref('')
const feedbackOk        = ref(true)

function openSingleModal()    { if (selectedUser.value && message.value.trim()) showSingleModal.value = true }
function openBroadcastModal() { if (broadcastReady.value && message.value.trim()) { broadcastConfirmText.value = ''; showBroadcastModal.value = true } }

async function confirmSingleSend() {
  sending.value = true
  try {
    const { data } = await adminApi.sendSms({ user_id: selectedUser.value.id, message: message.value.trim() })
    showSingleModal.value = false
    showFeedback(true, data.message || 'SMS sent successfully.')
    message.value = ''
    selectedUser.value = null
  } catch (err) {
    showSingleModal.value = false
    showFeedback(false, err?.response?.data?.message || 'Failed to send SMS. Please try again.')
  } finally {
    sending.value = false
  }
}

async function confirmBroadcast() {
  if (broadcastConfirmText.value.trim().toUpperCase() !== 'SEND') return
  sending.value = true
  try {
    const payload = {
      message:       message.value.trim(),
      target:        broadcastTarget.value,
      university_id: broadcastTarget.value === 'university' ? selectedUniversityId.value : undefined,
    }
    const { data } = await adminApi.broadcastSms(payload)
    showBroadcastModal.value = false
    broadcastConfirmText.value = ''
    showFeedback(true, data.message || 'Broadcast queued. Delivery may take a few minutes.')
    message.value = ''
  } catch (err) {
    showBroadcastModal.value = false
    showFeedback(false, err?.response?.data?.message || 'Broadcast failed. Please try again.')
  } finally {
    sending.value = false
  }
}

function showFeedback(ok, msg) {
  feedbackOk.value  = ok
  feedbackMsg.value = msg
  setTimeout(() => { feedbackMsg.value = '' }, 6000)
}

// ─── helpers ──────────────────────────────────────────────────────────────────

function initials(name = '') {
  return name.split(' ').slice(0, 2).map(w => w[0]?.toUpperCase()).join('')
}

function roleColor(role) {
  return role === 'tutor' ? 'bg-navy-100 text-navy-700' : 'bg-gold-100 text-gold-700'
}

function roleBadge(role) {
  return role === 'tutor' ? 'bg-navy-50 text-navy-600 border border-navy-200' : 'bg-gold-50 text-gold-600 border border-gold-200'
}
</script>

<style scoped>
/* Layout helpers */
.card   { @apply rounded-xl border border-paper-200 bg-white p-5 shadow-sm md:p-6; }
.kicker { @apply font-display text-xs font-bold uppercase tracking-wide text-gold-600; }

/* Send button */
.send-btn {
  @apply flex items-center justify-center gap-2 rounded-lg bg-navy-800 px-6 py-3.5 font-display text-sm font-bold text-white
         transition-colors hover:bg-navy-900 disabled:cursor-not-allowed disabled:opacity-40;
}

/* Modals */
.modal-backdrop { @apply fixed inset-0 z-[9999] flex items-end justify-center p-0 sm:items-center sm:p-4 bg-black/50 backdrop-blur-sm; }
.modal-box      { @apply relative z-10 w-full max-w-md overflow-hidden rounded-t-2xl sm:rounded-xl bg-white shadow-2xl max-h-[90vh] flex flex-col; }
.modal-header   { @apply flex shrink-0 items-start gap-3 border-b border-paper-100 px-5 py-4 bg-paper-50; }
.modal-icon     { @apply flex h-9 w-9 shrink-0 items-center justify-center rounded-full; }
.modal-body     { @apply flex-1 overflow-y-auto space-y-4 p-5; }
.modal-footer   { @apply flex shrink-0 items-center gap-3 border-t border-paper-100 px-5 py-4; }
.modal-close-btn { @apply ml-auto shrink-0 rounded-full p-1 text-paper-400 hover:bg-paper-100 hover:text-navy-700 transition-colors; }
.modal-cancel-btn { @apply flex-1 rounded-lg border border-paper-200 bg-white py-3 font-display text-sm font-semibold text-paper-700 transition-colors hover:bg-paper-50; }
.modal-confirm-btn {
  @apply flex flex-1 items-center justify-center gap-2 rounded-lg bg-navy-800 py-3 font-display text-sm font-bold text-white
         transition-colors hover:bg-navy-900 disabled:cursor-not-allowed disabled:opacity-50;
}

.spinner { @apply h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white; }

/* Transitions */
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s, transform 0.15s; }
.dropdown-enter-from, .dropdown-leave-to       { opacity: 0; transform: translateY(-4px); }

.slide-up-enter-active, .slide-up-leave-active { transition: opacity 0.2s, transform 0.2s; }
.slide-up-enter-from, .slide-up-leave-to       { opacity: 0; transform: translateY(6px); }

.modal-enter-active, .modal-leave-active { transition: opacity 0.2s; }
.modal-enter-from, .modal-leave-to       { opacity: 0; }
.modal-enter-active .modal-box,
.modal-leave-active .modal-box           { transition: transform 0.25s cubic-bezier(.32,.72,0,1); }
.modal-enter-from  .modal-box            { transform: translateY(100%); }
.modal-leave-to    .modal-box            { transform: translateY(100%); }
@media (min-width: 640px) {
  .modal-enter-from  .modal-box { transform: scale(0.95); }
  .modal-leave-to    .modal-box { transform: scale(0.95); }
}

.toast-enter-active, .toast-leave-active { transition: opacity 0.3s, transform 0.3s; }
.toast-enter-from, .toast-leave-to       { opacity: 0; transform: translateX(-50%) translateY(10px); }
</style>
