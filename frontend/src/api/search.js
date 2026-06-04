import http from './http.js'
export const searchApi = {
  landingStats: () => http.get('/landing/stats'),
  tutors: (params) => http.get('/search/tutors', { params }),
  resolve: (q) => http.get('/search/resolve', { params: { q } }),
  subjects: (params) => http.get('/search/subjects', { params }),
  districts: () => http.get('/search/districts'),
  areas: (districtId) => http.get('/search/areas', { params: { district_id: districtId } }),
  getTutor: (publicId) => http.get(`/tutors/${publicId}`),
  getTutorReviews: (publicId, params) => http.get(`/tutors/${publicId}/reviews`, { params }),
}
