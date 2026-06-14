import http from './http.js'
export const searchApi = {
  landingStats:        () => http.get('/landing/stats'),
  landingTestimonials: () => http.get('/landing/testimonials'),
  tutors: (params) => http.get('/search/tutors', { params }),
  resolve: (q) => http.get('/search/resolve', { params: { q } }),
  subjects: (params) => http.get('/search/subjects', { params }),
  districts: () => http.get('/search/districts'),
  areas: (districtId) => http.get('/search/areas', { params: { district_id: districtId } }),
  universities: (params = {}) => http.get('/search/universities', { params }),
  getTutor: (publicId) => http.get(`/tutors/${publicId}`),
  getTutorReviews: (publicId, params) => http.get(`/tutors/${publicId}/reviews`, { params }),
}
