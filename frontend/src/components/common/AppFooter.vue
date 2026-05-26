<template>
  <footer class="bg-navy-700 text-navy-100 py-10 md:py-14 mt-12 md:mt-16">
    <div class="max-w-7xl mx-auto px-4">
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-8">
        <!-- Brand -->
        <div class="col-span-2 sm:col-span-1">
          <p class="font-display font-bold text-white text-xl mb-2">TutorKhujo</p>
          <p class="text-sm text-navy-300 font-body leading-relaxed">Find a tutor you can trust — fast, verified, and near you.</p>
        </div>
        <!-- For students -->
        <div>
          <p class="font-display font-semibold text-white mb-3 text-xs uppercase tracking-widest">For students</p>
          <RouterLink to="/search" class="block text-sm text-navy-300 hover:text-white mb-2 transition-colors font-body">Find tutors</RouterLink>
          <RouterLink to="/register" class="block text-sm text-navy-300 hover:text-white mb-2 transition-colors font-body">Sign up free</RouterLink>
          <RouterLink to="/login" class="block text-sm text-navy-300 hover:text-white transition-colors font-body">Log in</RouterLink>
        </div>
        <!-- For tutors -->
        <div>
          <p class="font-display font-semibold text-white mb-3 text-xs uppercase tracking-widest">For tutors</p>
          <RouterLink to="/register" class="block text-sm text-navy-300 hover:text-white mb-2 transition-colors font-body">Become a tutor</RouterLink>
          <RouterLink to="/login" class="block text-sm text-navy-300 hover:text-white transition-colors font-body">Tutor log in</RouterLink>
        </div>
      </div>
      <!-- Bottom bar -->
      <div class="mt-8 md:mt-10 pt-6 border-t border-navy-600 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-navy-400 font-body">
        <span>© {{ new Date().getFullYear() }} TutorKhujo. All rights reserved.</span>
        <span>Built for Bangladesh</span>
      </div>
    </div>
  </footer>
</template>
<script setup>
import { RouterLink } from 'vue-router'
</script>
