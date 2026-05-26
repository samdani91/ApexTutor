import http from './http.js'
export const searchApi = {
  tutors: (params) => http.get('/search/tutors', { params }),
  subjects: (params) => http.get('/search/subjects', { params }),
  districts: () => http.get('/search/districts'),
  cities: (districtId) => http.get('/search/cities', { params: { district_id: districtId } }),
  getTutor: (publicId) => http.get(`/tutors/${publicId}`),
  getTutorReviews: (publicId, params) => http.get(`/tutors/${publicId}/reviews`, { params }),
}
