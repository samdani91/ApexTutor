import http from './http.js'

export const ticketApi = {
  getCounts: ()           => http.get('/tickets/counts'),
  getAll:    (params = {}) => http.get('/tickets', { params }),
  getOne:    (id)          => http.get(`/tickets/${id}`),
  create:    (data)        => http.post('/tickets', data),
  reply:     (id, data)    => http.post(`/tickets/${id}/reply`, data),
}

export const adminTicketApi = {
  getAll:       (params = {}) => http.get('/admin/tickets', { params }),
  getCounts:    ()            => http.get('/admin/tickets/counts'),
  getOne:       (id)          => http.get(`/admin/tickets/${id}`),
  updateStatus: (id, data)    => http.put(`/admin/tickets/${id}/status`, data),
  reply:        (id, data)    => http.post(`/admin/tickets/${id}/reply`, data),
}
