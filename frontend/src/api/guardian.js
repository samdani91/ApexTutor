import http from './http.js'
export const guardianApi = {
  getProfile:      () => http.get('/guardian/profile'),
  updateProfile:   (data) => http.put('/guardian/profile', data),
  uploadNid:       (formData) => http.post('/guardian/profile/nid', formData, { headers: { 'Content-Type': 'multipart/form-data' } }),
  deleteNid:       () => http.delete('/guardian/profile/nid'),
  getRequirements: () => http.get('/guardian/requirements'),
  postRequirement: (data) => http.post('/guardian/requirements', data),
  deleteRequirement: (id) => http.delete(`/guardian/requirements/${id}`),
  getConnections:  () => http.get('/guardian/connections'),
  requestConnection: (data) => http.post('/guardian/connections', data),
  getShortlist:    () => http.get('/guardian/shortlist'),
  addShortlist:    (tutorProfileId) => http.post(`/guardian/shortlist/${tutorProfileId}`),
  removeShortlist: (tutorProfileId) => http.delete(`/guardian/shortlist/${tutorProfileId}`),
}
