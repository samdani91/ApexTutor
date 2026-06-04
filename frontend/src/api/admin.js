import http from './http.js'
export const adminApi = {
  getDashboard: () => http.get('/admin/dashboard'),
  getAnalytics:  () => http.get('/admin/dashboard/analytics'),

  // Admin user management
  getAdmins: (params) => http.get('/admin/admins', { params }),
  getAdmin: (id) => http.get(`/admin/admins/${id}`),
  createAdmin: (data) => http.post('/admin/admins', data),
  updateAdmin: (id, data) => http.put(`/admin/admins/${id}`, data),
  deleteAdmin: (id) => http.delete(`/admin/admins/${id}`),

  // Tutors
  getTutors: (params) => http.get('/admin/tutors', { params }),
  getTutor: (id) => http.get(`/admin/tutors/${id}`),
  updateTutor: (id, data) => http.put(`/admin/tutors/${id}`, data),
  updateTutorStatus: (id, data) => http.put(`/admin/tutors/${id}/status`, data),

  // Guardians
  getGuardians: (params) => http.get('/admin/guardians', { params }),
  getGuardian: (id) => http.get(`/admin/guardians/${id}`),
  updateGuardian: (id, data) => http.put(`/admin/guardians/${id}`, data),
  updateGuardianStatus: (id, data) => http.put(`/admin/guardians/${id}/status`, data),

  // Verifications
  getVerificationQueue: (params) => http.get('/admin/verifications', { params }),
  approveTutor: (id) => http.put(`/admin/verifications/${id}/approve`),
  rejectTutor: (id, data) => http.put(`/admin/verifications/${id}/reject`, data),

  // Connections
  getConnections: (params) => http.get('/admin/connections', { params }),
  getConnection: (id) => http.get(`/admin/connections/${id}`),
  updateConnectionStatus: (id, data) => http.put(`/admin/connections/${id}/status`, data),
  addConnectionNotes: (id, data) => http.post(`/admin/connections/${id}/notes`, data),

  // Pending profile changes
  getPendingChanges: () => http.get('/admin/pending-changes'),
  approvePendingChange: (id) => http.put(`/admin/pending-changes/${id}/approve`),
  rejectPendingChange: (id, data) => http.put(`/admin/pending-changes/${id}/reject`, data),

  // Reviews
  getReviews: (params) => http.get('/admin/reviews', { params }),
  getPendingReviews: (params) => http.get('/admin/reviews/pending', { params }),
  approveReview: (id) => http.put(`/admin/reviews/${id}/approve`),
  rejectReview: (id, data) => http.put(`/admin/reviews/${id}/reject`, data),

  // Reference data
  getSubjects: (params) => http.get('/admin/reference/subjects', { params }),
  createSubject: (data) => http.post('/admin/reference/subjects', data),
  updateSubject: (id, data) => http.put(`/admin/reference/subjects/${id}`, data),
  deleteSubject: (id) => http.delete(`/admin/reference/subjects/${id}`),
  getDistricts: (params) => http.get('/admin/reference/districts', { params }),
  createDistrict: (data) => http.post('/admin/reference/districts', data),
  updateDistrict: (id, data) => http.put(`/admin/reference/districts/${id}`, data),
  deleteDistrict: (id) => http.delete(`/admin/reference/districts/${id}`),
  createArea: (data) => http.post('/admin/reference/areas', data),
  updateArea: (id, data) => http.put(`/admin/reference/areas/${id}`, data),
  deleteArea: (id) => http.delete(`/admin/reference/areas/${id}`),

  // Audit log
  getAuditLog: (params) => http.get('/admin/audit-log', { params }),
  getAuditActions: () => http.get('/admin/audit-log/actions'),

  // Notifications
  getNotifications: () => http.get('/admin/notifications'),
  markNotificationRead: (id) => http.put(`/admin/notifications/${id}/read`),
  markAllNotificationsRead: () => http.put('/admin/notifications/read-all'),
}
