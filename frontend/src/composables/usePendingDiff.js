/**
 * Pure diff-building utilities for the pending-changes review UI.
 *
 * Extracted from AdminPendingChanges.vue so the 560-line view component
 * only needs to import usePendingDiff() instead of carrying all this logic inline.
 *
 * All functions are stateless — no reactive refs — so they can also be
 * imported directly into unit tests without a Vue context.
 */

export const PREF_FIELDS = [
  { key: '_subject_names',         label: 'Subjects'           },
  { key: '_district_name',         label: 'Preferred district' },
  { key: '_location_names',        label: 'Preferred areas'    },
  { key: 'expected_salary_min',    label: 'Min salary (BDT)'   },
  { key: 'expected_salary_max',    label: 'Max salary (BDT)'   },
  { key: 'total_experience_years', label: 'Experience (years)' },
  { key: 'days_per_week',          label: 'Days per week'      },
  { key: 'hours_per_day',          label: 'Hours per day'      },
  { key: 'preferred_classes',      label: 'Preferred classes'  },
  { key: 'tutoring_methods',       label: 'Tutoring methods'   },
  { key: 'place_of_tutoring',      label: 'Place of tutoring'  },
  { key: 'preferred_time',         label: 'Preferred time'     },
]

export const PERSONAL_FIELDS = [
  { key: 'gender',            label: 'Gender'            },
  { key: 'date_of_birth',     label: 'Date of birth'     },
  { key: 'religion',          label: 'Religion'          },
  { key: 'nationality',       label: 'Nationality'       },
  { key: 'present_address',   label: 'Present address'   },
  { key: 'permanent_address', label: 'Permanent address' },
  { key: 'additional_phone',  label: 'Additional phone'  },
  { key: 'national_id',       label: 'National ID'       },
  { key: 'facebook_url',      label: 'Facebook'          },
  { key: 'linkedin_url',      label: 'LinkedIn'          },
  { key: 'fathers_name',      label: "Father's name"     },
  { key: 'fathers_phone',     label: "Father's phone"    },
  { key: 'mothers_name',      label: "Mother's name"     },
  { key: 'mothers_phone',     label: "Mother's phone"    },
]

export const EMERGENCY_FIELDS = [
  { key: 'name',     label: 'Emergency contact name'     },
  { key: 'relation', label: 'Emergency contact relation' },
  { key: 'phone',    label: 'Emergency contact phone'    },
  { key: 'address',  label: 'Emergency contact address'  },
]

/** Pretty-print a value for display in a diff table cell. */
export function display(val) {
  if (val === null || val === undefined || val === '') return '—'
  if (Array.isArray(val)) {
    if (!val.length) return '—'
    const first = val[0]
    if (first !== null && typeof first === 'object') {
      if ('name'      in first) return val.map(v => v.name).join(', ')
      if ('day'       in first) return val.map(v => v.day).join(', ')
      if ('area_name' in first) return val.map(v => v.area_name).join(', ')
      return '—'
    }
    return val.join(', ')
  }
  return String(val)
}

export function uniqueSubjectNames(subjects) {
  return [...new Set((subjects || []).filter(Boolean).map(s => String(s).trim()).filter(Boolean))].sort()
}

/** Normalised form for equality checks — sorts arrays so order differences don't count. */
export function norm(val) {
  if (val === null || val === undefined || val === '') return '\0empty'
  if (Array.isArray(val)) {
    if (!val.length) return '\0empty'
    const first = val[0]
    if (first !== null && typeof first === 'object') {
      if ('name'      in first) return [...val].map(v => v.name).sort().join('|')
      if ('day'       in first) return [...val].map(v => v.day).sort().join('|')
      if ('area_name' in first) return [...val].map(v => v.area_name).sort().join('|')
      return '\0empty'
    }
    return [...val].sort().join('|')
  }
  return String(val)
}

/** Build the full list of changed fields between pending and live data. */
export function buildDiff(item) {
  const pending = item.pending || {}
  const live    = item.live    || {}
  const rows    = []

  // Avatar change (shown as thumbnail images, not text)
  if (pending.avatar?.url) {
    rows.push({
      key:          'avatar',
      label:        'Profile Photo',
      oldAvatarUrl: live.avatar_url || null,
      newAvatarUrl: pending.avatar.url,
    })
  }

  for (const { key, label } of [{ key: 'bio', label: 'Bio' }, { key: 'status', label: 'Status' }]) {
    if (pending[key] === undefined) continue
    if (norm(live[key]) === norm(pending[key])) continue
    rows.push({ key, label, old: display(live[key]), new: display(pending[key]) })
  }

  if (pending.preferences) {
    const livePrefs = live.preferences || {}
    for (const { key, label } of PREF_FIELDS) {
      if (key === '_subject_names') {
        const liveSubs    = uniqueSubjectNames((livePrefs.subjects || []).map(s => s.name))
        const pendingSubs = uniqueSubjectNames(pending.preferences._subject_names || [])
        if (norm(liveSubs) === norm(pendingSubs)) continue
        rows.push({ key: `preferences.${key}`, label, old: display(liveSubs), new: display(pendingSubs) })
        continue
      }
      if (key === '_location_names') {
        const liveLocs    = (livePrefs.locations || []).map(l => l.area?.name).filter(Boolean)
        const pendingLocs = pending.preferences._location_names || []
        if (norm(liveLocs) === norm(pendingLocs)) continue
        rows.push({ key: `preferences.${key}`, label, old: display(liveLocs), new: display(pendingLocs) })
        continue
      }
      if (key === '_district_name') {
        const liveDistrict    = livePrefs.district?.name ?? null
        const pendingDistrict = pending.preferences._district_name ?? null
        if (norm(liveDistrict) === norm(pendingDistrict)) continue
        rows.push({ key: `preferences.${key}`, label, old: display(liveDistrict), new: display(pendingDistrict) })
        continue
      }
      if (!(key in pending.preferences)) continue
      const liveVal    = livePrefs[key]
      const pendingVal = pending.preferences[key]
      if (norm(liveVal) === norm(pendingVal)) continue
      rows.push({ key: `preferences.${key}`, label, old: display(liveVal), new: display(pendingVal) })
    }
  }

  for (const [section, fields, liveKey] of [
    ['personal_info',    PERSONAL_FIELDS,  'personal_info'],
    ['emergency_contact', EMERGENCY_FIELDS, 'emergency_contact'],
  ]) {
    if (!pending[section]) continue
    const liveSection = live[liveKey] || {}
    for (const { key, label } of fields) {
      if (!(key in pending[section])) continue
      const liveVal    = liveSection[key]
      const pendingVal = pending[section][key]
      if (norm(liveVal) === norm(pendingVal)) continue
      rows.push({ key: `${section}.${key}`, label, old: display(liveVal), new: display(pendingVal) })
    }
  }

  if (pending.education?.changes) {
    const liveEdu     = live.education || []
    const proposedEdu = applyEducationPreview(liveEdu, pending.education.changes)
    if (norm(educationSummary(liveEdu)) !== norm(educationSummary(proposedEdu))) {
      rows.push({
        key: 'education', label: 'Education',
        old: display(educationSummary(liveEdu)),
        new: display(educationSummary(proposedEdu)),
      })
    }
  }

  if (pending.documents) {
    const oldDocs = changedLiveDocuments(live.documents || [], pending.documents)
    const newDocs = changedPendingDocuments(pending.documents)
    if (oldDocs.length || newDocs.length) {
      rows.push({
        key: 'documents', label: 'Documents',
        old: display(documentSummary(oldDocs)),
        new: display(documentSummary(newDocs)),
        oldDocuments: oldDocs,
        newDocuments: newDocs,
      })
    }
  }

  return rows
}

// ── Education helpers ─────────────────────────────────────────────────────────

export function educationSummary(entries) {
  return (entries || []).map(e =>
    [e.level, e.degree_title, e.major_group, e.institute_name, e.year_of_passing].filter(Boolean).join(' - ')
  )
}

export function applyEducationPreview(liveEntries, changes) {
  const next = (liveEntries || []).map(e => ({ ...e }))
  Object.values(changes || {}).forEach(({ action, id, data = {} }) => {
    const idx = next.findIndex(e => e.id === id)
    if (action === 'delete') { if (idx !== -1) next.splice(idx, 1) }
    else if (action === 'update') { if (idx !== -1) next.splice(idx, 1, { ...next[idx], ...data }) }
    else if (action === 'create') { next.push(data) }
  })
  return next
}

// ── Document helpers ──────────────────────────────────────────────────────────

export function documentSummary(docs) {
  return (docs || []).map(d => `${documentLabel(d.type)}${d.file_name ? ` — ${d.file_name}` : ''}`).filter(Boolean)
}

export function changedLiveDocuments(liveDocs, changes) {
  const upsertTypes = Object.keys(changes.upsert || {})
  const deleteIds   = changes.delete || []
  return (liveDocs || [])
    .filter(d => deleteIds.includes(d.id) || upsertTypes.includes(d.type))
    .map(d => ({ ...d, label: documentLabel(d.type) }))
}

export function changedPendingDocuments(changes) {
  const uploaded = Object.entries(changes.upsert || {}).map(([type, doc]) => ({ ...doc, type, label: documentLabel(type) }))
  const deleted  = (changes.delete || []).map(id => ({
    id, type: 'deleted_document', label: 'Document removal requested',
    file_name: `Existing document #${id}`, file_url: null,
  }))
  return [...uploaded, ...deleted]
}

export function documentLabel(type) {
  return String(type || 'document').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// ── Composable wrapper ────────────────────────────────────────────────────────

export function usePendingDiff() {
  return { buildDiff, display, norm, uniqueSubjectNames, educationSummary, documentLabel }
}
