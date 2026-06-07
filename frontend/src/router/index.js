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
      { path: 'dashboard',           component: () => import('@/views/tutor/TutorDashboard.vue'),      name: 'tutor-dashboard' },
      { path: 'profile',             component: () => import('@/views/tutor/ProfileBuilder.vue'),       name: 'tutor-profile-builder' },
      { path: 'confirmed-tuitions',  component: () => import('@/views/tutor/ConfirmedTuitions.vue'),    name: 'tutor-confirmed-tuitions' },
      { path: 'reviews',             component: () => import('@/views/tutor/TutorReviews.vue'),         name: 'tutor-reviews' },
      { path: 'notifications',       component: () => import('@/views/shared/NotificationsPage.vue'),  name: 'tutor-notifications' },
      { path: 'settings',            component: () => import('@/views/shared/SettingsPage.vue'),        name: 'tutor-settings' },
    ]
  },
  {
    path: '/guardian',
    component: () => import('@/layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true, role: ['guardian', 'student'] },
    children: [
      { path: 'dashboard',           component: () => import('@/views/guardian/GuardianDashboard.vue'),    name: 'guardian-dashboard' },
      { path: 'profile',             component: () => import('@/views/guardian/GuardianProfilePage.vue'),  name: 'guardian-profile' },
      { path: 'shortlist',           component: () => import('@/views/guardian/Shortlist.vue'),             name: 'shortlist' },
      { path: 'confirmed-tuitions',  component: () => import('@/views/guardian/ConfirmedTuitions.vue'),     name: 'guardian-confirmed-tuitions' },
{ path: 'notifications',       component: () => import('@/views/shared/NotificationsPage.vue'),      name: 'guardian-notifications' },
      { path: 'settings',            component: () => import('@/views/shared/SettingsPage.vue'),            name: 'guardian-settings' },
    ]
  },
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, role: ['super_admin'] },
    children: [
      { path: 'dashboard', component: () => import('@/views/admin/AdminDashboard.vue'), name: 'admin-dashboard' },
      { path: 'users', component: () => import('@/views/admin/AdminUsersList.vue'), name: 'admin-users' },
      { path: 'tutors/:tutorId', component: () => import('@/views/admin/AdminTutorDetail.vue'), name: 'admin-tutor-detail' },
      { path: 'tutors/:tutorId/edit', component: () => import('@/views/admin/AdminTutorEdit.vue'), name: 'admin-tutor-edit' },
      { path: 'guardians/:guardianId', component: () => import('@/views/admin/AdminGuardianDetail.vue'), name: 'admin-guardian-detail' },
      { path: 'admins/create', component: () => import('@/views/admin/AdminCreateAdmin.vue'), name: 'admin-create-admin' },
      { path: 'admins/:id', component: () => import('@/views/admin/AdminUserDetail.vue'), name: 'admin-user-detail' },
      { path: 'verifications', component: () => import('@/views/admin/TutorVerification.vue'), name: 'admin-verifications' },
      { path: 'connections', component: () => import('@/views/admin/ConnectionManagement.vue'), name: 'admin-connections' },
      { path: 'pending-changes', component: () => import('@/views/admin/AdminPendingChanges.vue'), name: 'admin-pending-changes' },
      { path: 'reviews', component: () => import('@/views/admin/AdminReviews.vue'), name: 'admin-reviews' },
      { path: 'reference-data', component: () => import('@/views/admin/AdminReferenceData.vue'), name: 'admin-reference-data' },
      { path: 'analytics', component: () => import('@/views/admin/AdminAnalytics.vue'), name: 'admin-analytics' },
      { path: 'audit-log', component: () => import('@/views/admin/AdminAuditLog.vue'), name: 'admin-audit-log' },
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

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.role && authStore.isAuthenticated) {
    const roles = Array.isArray(to.meta.role) ? to.meta.role : [to.meta.role]
    if (!roles.includes(authStore.user.role)) return next({ name: 'home' })
  }

  next()
})

export default router
