import http from './http.js'
export const searchApi = {
  tutors: (params) => http.get('/search/tutors', { params }),
  subjects: (params) => http.get('/search/subjects', { params }),
  districts: () => http.get('/search/districts'),
  areas: (districtId) => http.get('/search/areas', { params: { district_id: districtId } }),
  getTutor: (publicId) => http.get(`/tutors/${publicId}`),
  getTutorReviews: (publicId, params) => http.get(`/tutors/${publicId}/reviews`, { params }),
}
