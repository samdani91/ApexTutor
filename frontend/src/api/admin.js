import http from './http.js'
export const adminApi = {
  getDashboard: () => http.get('/admin/dashboard'),

  // Admins
  getAdmins: (params) => http.get('/admin/admins', { params }),
  getAdmin: (id) => http.get(`/admin/admins/${id}`),

  // Tutors
  getTutors: (params) => http.get('/admin/tutors', { params }),
  getTutor: (id) => http.get(`/admin/tutors/${id}`),
  updateTutorStatus: (id, data) => http.put(`/admin/tutors/${id}/status`, data),

  // Guardians
  getGuardians: (params) => http.get('/admin/guardians', { params }),
  getGuardian: (id) => http.get(`/admin/guardians/${id}`),

  // Verifications
  getVerificationQueue: () => http.get('/admin/verifications'),
  approveTutor: (id) => http.put(`/admin/verifications/${id}/approve`),
  rejectTutor: (id, data) => http.put(`/admin/verifications/${id}/reject`, data),

  // Connections
  getConnections: (params) => http.get('/admin/connections', { params }),
  updateConnectionStatus: (id, data) => http.put(`/admin/connections/${id}/status`, data),

  // Pending profile changes
  getPendingChanges: () => http.get('/admin/pending-changes'),
  approvePendingChange: (id) => http.put(`/admin/pending-changes/${id}/approve`),
  rejectPendingChange: (id, data) => http.put(`/admin/pending-changes/${id}/reject`, data),

  // Reviews
  getPendingReviews: () => http.get('/admin/reviews/pending'),
  approveReview: (id) => http.put(`/admin/reviews/${id}/approve`),
  rejectReview: (id, data) => http.put(`/admin/reviews/${id}/reject`, data),

  // Notifications
  getNotifications: () => http.get('/admin/notifications'),
  markNotificationRead: (id) => http.put(`/admin/notifications/${id}/read`),
  markAllNotificationsRead: () => http.put('/admin/notifications/read-all'),
}
