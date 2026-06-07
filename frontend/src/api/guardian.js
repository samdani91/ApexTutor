import http from './http.js'
export const guardianApi = {
  getProfile:      () => http.get('/guardian/profile'),
  updateProfile:   (data) => http.put('/guardian/profile', data),
  uploadNid:       (formData) => http.post('/guardian/profile/nid', formData, { headers: { 'Content-Type': 'multipart/form-data' } }),
  deleteNid:       () => http.delete('/guardian/profile/nid'),
getConnections:       () => http.get('/guardian/connections'),
  requestConnection:    (data) => http.post('/guardian/connections', data),
  getConfirmedTuitions: () => http.get('/guardian/confirmed-tuitions'),
  getShortlist:    () => http.get('/guardian/shortlist'),
  addShortlist:    (tutorProfileId) => http.post(`/guardian/shortlist/${tutorProfileId}`),
  removeShortlist: (tutorProfileId) => http.delete(`/guardian/shortlist/${tutorProfileId}`),
  reviewEligibility: (tutorProfileId) => http.get(`/guardian/reviews/eligibility/${tutorProfileId}`),
  submitReview:    (data) => http.post('/guardian/reviews', data),
}
