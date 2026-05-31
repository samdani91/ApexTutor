import http from './http.js'

export const notificationApi = {
  getAll:       (params = {}) => http.get('/notifications', { params }),
  markRead:     (id)          => http.put(`/notifications/${id}/read`),
  markAllRead:  ()            => http.put('/notifications/read-all'),
}
