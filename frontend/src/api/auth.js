import http from './http.js'
export const authApi = {
  register:              (data) => http.post('/auth/register', data),
  login:                 (data) => http.post('/auth/login', data),
  logout:                ()     => http.post('/auth/logout'),
  me:                    ()     => http.get('/auth/me'),
  verifyEmail:           (data) => http.post('/auth/verify-email', data),
  resendVerification:    (data) => http.post('/auth/resend-verification', data),
  sendOtp:               (data) => http.post('/auth/otp/send', data),
  verifyOtp:             (data) => http.post('/auth/otp/verify', data),
  uploadAvatar:          (formData) => http.post('/user/avatar', formData, { headers: { 'Content-Type': 'multipart/form-data' } }),
  updateProfile:         (data) => http.put('/user/profile', data),
  forgotPassword:        (data) => http.post('/auth/forgot-password', data),
  verifyResetOtp:        (data) => http.post('/auth/verify-reset-otp', data),
  resetPassword:         (data) => http.post('/auth/reset-password', data),
  requestPasswordChange: (data) => http.post('/user/password/request-change', data),
  changePassword:        (data) => http.put('/user/password', data),
  getReferral:           ()     => http.get('/user/referral'),
}
