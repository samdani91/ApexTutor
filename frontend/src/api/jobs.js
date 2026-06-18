import http from './http.js'

export const guardianJobsApi = {
  dashboardSummary:  ()                          => http.get('/guardian/jobs/dashboard-summary'),
  list:              (status = 'all')             => http.get('/guardian/jobs', { params: { status } }),
  post:              (data)                       => http.post('/guardian/jobs', data),
  show:              (publicId)                   => http.get(`/guardian/jobs/${publicId}`),
  update:            (publicId, data)             => http.put(`/guardian/jobs/${publicId}`, data),
  close:             (publicId)                   => http.patch(`/guardian/jobs/${publicId}/close`),
  reopen:            (publicId)                   => http.patch(`/guardian/jobs/${publicId}/reopen`),
  applicants:        (publicId, status)           => http.get(`/guardian/jobs/${publicId}/applicants`, { params: status ? { status } : {} }),
  shortlistApplicant:(publicId, appId)            => http.patch(`/guardian/jobs/${publicId}/applicants/${appId}/shortlist`),
  appointApplicant:  (publicId, appId)            => http.patch(`/guardian/jobs/${publicId}/applicants/${appId}/appoint`),
  confirmApplicant:  (publicId, appId)            => http.patch(`/guardian/jobs/${publicId}/applicants/${appId}/confirm`),
  removeApplicant:   (publicId, appId)            => http.patch(`/guardian/jobs/${publicId}/applicants/${appId}/remove`),
}

export const tutorJobsApi = {
  dashboardSummary:  ()        => http.get('/tutor/dashboard/job-summary'),
  list:              (params)  => http.get('/jobs', { params }),
  show:              (publicId)=> http.get(`/jobs/${publicId}`),
  apply:             (publicId)=> http.post(`/jobs/${publicId}/apply`),
  myApplications:    (params)  => http.get('/tutor/applications', { params }),
}
