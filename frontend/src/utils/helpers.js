export function formatCurrency(amount) {
  if (!amount) return '—'
  return `৳ ${Number(amount).toLocaleString('en-BD')}`
}

export function formatSalaryRange(min, max) {
  if (!min && !max) return '—'
  if (min && max) return `${formatCurrency(min)} – ${formatCurrency(max)}/mo`
  if (min) return `From ${formatCurrency(min)}/mo`
  return `Up to ${formatCurrency(max)}/mo`
}

export function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}

export function truncate(str, n = 120) {
  return str && str.length > n ? str.slice(0, n) + '…' : str
}

export function timeAgo(iso) {
  const diff = Math.floor((Date.now() - new Date(iso)) / 1000)
  if (diff < 60)    return 'just now'
  if (diff < 3600)  return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return `${Math.floor(diff / 86400)}d ago`
}
