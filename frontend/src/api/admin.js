import http from './http.js'
export const adminApi = {
  getDashboard: () => http.get('/admin/dashboard'),
  getAnalytics:  (params = {}) => http.get('/admin/dashboard/analytics', { params }),

  // Admin user management
  getAdmins: (params) => http.get('/admin/admins', { params }),
  getAdmin: (id) => http.get(`/admin/admins/${id}`),
  createAdmin: (data) => http.post('/admin/admins', data),
  updateAdmin: (id, data) => http.put(`/admin/admins/${id}`, data),

  // Tutors
  getTutors: (params) => http.get('/admin/tutors', { params }),
  getTutor: (id) => http.get(`/admin/tutors/${id}`),
  updateTutor: (id, data) => http.put(`/admin/tutors/${id}`, data),
  updateTutorStatus: (id, data) => http.put(`/admin/tutors/${id}/status`, data),
  uploadTutorDocument: (id, formData) => http.post(`/admin/tutors/${id}/documents`, formData, { headers: { 'Content-Type': 'multipart/form-data' } }),
  deleteTutorDocument: (id, docId) => http.delete(`/admin/tutors/${id}/documents/${docId}`),
  updateTutorVideo: (id, videoId, data) => http.put(`/admin/tutors/${id}/videos/${videoId}`, data),
  reviewTutorVideo: (id, videoId, data) => http.put(`/admin/tutors/${id}/videos/${videoId}/review`, data),
  deleteTutorVideo: (id, videoId) => http.delete(`/admin/tutors/${id}/videos/${videoId}`),

  // Guardians
  getGuardians: (params) => http.get('/admin/guardians', { params }),
  getGuardian: (id) => http.get(`/admin/guardians/${id}`),
  updateGuardian: (id, data) => http.put(`/admin/guardians/${id}`, data),
  updateGuardianStatus: (id, data) => http.put(`/admin/guardians/${id}/status`, data),
  uploadGuardianNid: (id, formData) => http.post(`/admin/guardians/${id}/nid`, formData, { headers: { 'Content-Type': 'multipart/form-data' } }),
  deleteGuardianNid: (id) => http.delete(`/admin/guardians/${id}/nid`),

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

  // Pending avatar approvals (standalone: guardians / unverified tutors)
  approvePendingAvatar: (id) => http.put(`/admin/pending-avatars/${id}/approve`),
  rejectPendingAvatar: (id, data) => http.put(`/admin/pending-avatars/${id}/reject`, data),

  // Admin direct avatar management (replace / remove on profile detail views)
  replaceUserAvatar: (id, formData) => http.post(`/admin/users/${id}/avatar`, formData, { headers: { 'Content-Type': 'multipart/form-data' } }),
  removeUserAvatar: (id) => http.delete(`/admin/users/${id}/avatar`),

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
  getUniversities: (params) => http.get('/admin/reference/universities', { params }),
  createUniversity: (data) => http.post('/admin/reference/universities', data),
  updateUniversity: (id, data) => http.put(`/admin/reference/universities/${id}`, data),
  deleteUniversity: (id) => http.delete(`/admin/reference/universities/${id}`),
  uploadUniversityLogo: (id, formData) => http.post(`/admin/reference/universities/${id}/logo`, formData, { headers: { 'Content-Type': 'multipart/form-data' } }),
  removeUniversityLogo: (id) => http.delete(`/admin/reference/universities/${id}/logo`),

  // Audit log
  getAuditLog: (params) => http.get('/admin/audit-log', { params }),
  getAuditActions: () => http.get('/admin/audit-log/actions'),

  // Notifications
  getNotifications: (params = {}) => http.get('/admin/notifications', { params }),
  markNotificationRead: (id) => http.put(`/admin/notifications/${id}/read`),
  markAllNotificationsRead: () => http.put('/admin/notifications/read-all'),

  // Tuition Jobs
  getTuitionJobs:             (params = {})                   => http.get(`/admin/tuition-jobs`, { params }),
  getTuitionJob:              (publicId)                       => http.get(`/admin/tuition-jobs/${publicId}`),
  closeTuitionJob:            (publicId)                       => http.put(`/admin/tuition-jobs/${publicId}/close`),
  reopenTuitionJob:           (publicId)                       => http.put(`/admin/tuition-jobs/${publicId}/reopen`),
  getTuitionJobApplications:  (publicId, params = {})          => http.get(`/admin/tuition-jobs/${publicId}/applications`, { params }),
  shortlistApplicant:         (publicId, appId)                => http.patch(`/admin/tuition-jobs/${publicId}/applications/${appId}/shortlist`),
  appointApplicant:           (publicId, appId)                => http.patch(`/admin/tuition-jobs/${publicId}/applications/${appId}/appoint`),
  confirmApplicant:           (publicId, appId)                => http.patch(`/admin/tuition-jobs/${publicId}/applications/${appId}/confirm`),
  removeApplicant:            (publicId, appId)                => http.patch(`/admin/tuition-jobs/${publicId}/applications/${appId}/remove`),
  changeApplicantStatus:      (publicId, appId, status)        => http.patch(`/admin/tuition-jobs/${publicId}/applications/${appId}/status`, { status }),
}
