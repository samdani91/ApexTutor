import http from './http.js'

export const feedbackApi = {
  getMyFeedback:  ()       => http.get('/feedback/platform'),
  submitFeedback: (quote)  => http.post('/feedback/platform', { quote }),

  // admin
  adminList:    (status = 'pending') => http.get('/admin/feedback', { params: { status } }),
  adminApprove: (id)                 => http.put(`/admin/feedback/${id}/approve`),
  adminReject:  (id)                 => http.put(`/admin/feedback/${id}/reject`),
}
