import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

const routes = [
  { path: '/', component: () => import('@/views/public/HomePage.vue'), name: 'home' },
  { path: '/search', component: () => import('@/views/public/SearchPage.vue'), name: 'search' },
  { path: '/tutors/:publicId', component: () => import('@/views/public/TutorProfilePage.vue'), name: 'tutor-profile' },
  { path: '/login', component: () => import('@/views/auth/LoginPage.vue'), name: 'login' },
  { path: '/register', component: () => import('@/views/auth/RegisterPage.vue'), name: 'register' },
  {
    path: '/tutor',
    component: () => import('@/layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true, role: 'tutor' },
    children: [
      { path: 'dashboard', component: () => import('@/views/tutor/TutorDashboard.vue'), name: 'tutor-dashboard' },
      { path: 'profile', component: () => import('@/views/tutor/ProfileBuilder.vue'), name: 'tutor-profile-builder' },
      { path: 'settings', component: () => import('@/views/shared/SettingsPage.vue'), name: 'tutor-settings' },
    ]
  },
  {
    path: '/guardian',
    component: () => import('@/layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true, role: ['guardian', 'student'] },
    children: [
      { path: 'dashboard', component: () => import('@/views/guardian/GuardianDashboard.vue'), name: 'guardian-dashboard' },
      { path: 'profile',   component: () => import('@/views/guardian/GuardianProfilePage.vue'), name: 'guardian-profile' },
      { path: 'shortlist', component: () => import('@/views/guardian/Shortlist.vue'), name: 'shortlist' },
      { path: 'post-requirement', component: () => import('@/views/guardian/PostRequirement.vue'), name: 'post-requirement' },
      { path: 'settings', component: () => import('@/views/shared/SettingsPage.vue'), name: 'guardian-settings' },
    ]
  },
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, role: ['admin', 'super_admin'] },
    children: [
      { path: 'dashboard', component: () => import('@/views/admin/AdminDashboard.vue'), name: 'admin-dashboard' },
      { path: 'users', component: () => import('@/views/admin/AdminUsersList.vue'), name: 'admin-users' },
      { path: 'tutors/:id', component: () => import('@/views/admin/AdminTutorDetail.vue'), name: 'admin-tutor-detail' },
      { path: 'guardians/:id', component: () => import('@/views/admin/AdminGuardianDetail.vue'), name: 'admin-guardian-detail' },
      { path: 'admins/:id', component: () => import('@/views/admin/AdminUserDetail.vue'), name: 'admin-user-detail' },
      { path: 'verifications', component: () => import('@/views/admin/TutorVerification.vue'), name: 'admin-verifications' },
      { path: 'connections', component: () => import('@/views/admin/ConnectionManagement.vue'), name: 'admin-connections' },
      { path: 'pending-changes', component: () => import('@/views/admin/AdminPendingChanges.vue'), name: 'admin-pending-changes' },
      { path: 'notifications', component: () => import('@/views/admin/AdminNotifications.vue'), name: 'admin-notifications' },
      { path: 'settings', component: () => import('@/views/admin/AdminSettings.vue'), name: 'admin-settings' },
    ]
  },
  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Ensure user object is populated whenever a token exists
  if (authStore.token && !authStore.user) {
    await authStore.fetchMe()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.role && authStore.user) {
    const roles = Array.isArray(to.meta.role) ? to.meta.role : [to.meta.role]
    const userRole = authStore.user.role
    const allowed = roles.includes(userRole) || (roles.includes('admin') && userRole === 'super_admin')
    if (!allowed) return next({ name: 'home' })
  }

  next()
})

export default router
