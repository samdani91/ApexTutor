export const CLASS_LEVELS = [
  { value: 'class_1', label: 'Class 1' }, { value: 'class_2', label: 'Class 2' },
  { value: 'class_3', label: 'Class 3' }, { value: 'class_4', label: 'Class 4' },
  { value: 'class_5', label: 'Class 5' }, { value: 'class_6', label: 'Class 6' },
  { value: 'class_7', label: 'Class 7' }, { value: 'class_8', label: 'Class 8' },
  { value: 'class_9', label: 'Class 9' }, { value: 'class_10', label: 'Class 10' },
  { value: 'ssc', label: 'SSC' }, { value: 'hsc', label: 'HSC' },
  { value: 'admission_test', label: 'University Admission Test' },
  { value: 'o_level', label: 'O Level' }, { value: 'a_level', label: 'A Level' },
  { value: 'dakhil', label: 'Dakhil' }, { value: 'alim', label: 'Alim' },
]

export const MEDIUMS = [
  { value: 'bangla_medium', label: 'Bangla Medium' },
  { value: 'english_medium', label: 'English Medium' },
  { value: 'english_version', label: 'English Version' },
  { value: 'madrasha', label: 'Madrasha' },
]

// Bangla Medium and English Version both follow the national curriculum.
const NATIONAL_CURRICULUM = [
  'class_1', 'class_2', 'class_3', 'class_4', 'class_5',
  'class_6', 'class_7', 'class_8', 'class_9', 'class_10',
  'ssc', 'hsc',
]

// English Medium covers primary years (Class 1–7) directly; Class 8–10 fall
// under O Level and Class 11–12 under A Level, so those are not repeated here.
const ENGLISH_MEDIUM = [
  'class_1', 'class_2', 'class_3', 'class_4', 'class_5', 'class_6', 'class_7',
  'o_level', 'a_level',
]

// Offered under every medium, so it is unioned in rather than repeated below.
const MEDIUM_AGNOSTIC_CLASS_LEVELS = ['admission_test']

// Madrasha primary years (Class 1–8) plus its board-exam levels: Dakhil (SSC
// equivalent) and Alim (HSC equivalent).
const MADRASHA = [
  'class_1', 'class_2', 'class_3', 'class_4', 'class_5', 'class_6', 'class_7', 'class_8',
  'dakhil', 'alim',
]

export const MEDIUM_CLASS_LEVELS = {
  bangla_medium: NATIONAL_CURRICULUM,
  english_version: NATIONAL_CURRICULUM,
  english_medium: ENGLISH_MEDIUM,
  madrasha: MADRASHA,
}

/**
 * Class levels valid for the given medium(s) — accepts a single value or an
 * array (tutors pick several). Falls back to every class level when nothing
 * recognisable is selected, so an unmapped medium never empties the dropdown.
 */
export function classLevelsFor(mediums) {
  const known = [].concat(mediums ?? []).filter((medium) => MEDIUM_CLASS_LEVELS[medium])
  if (!known.length) return CLASS_LEVELS

  const mediumSpecific = new Set(known.flatMap((medium) => MEDIUM_CLASS_LEVELS[medium]))
  const agnostic = new Set(MEDIUM_AGNOSTIC_CLASS_LEVELS)
  // Medium-specific levels keep CLASS_LEVELS order; the agnostic ones (Admission
  // Test) are appended so they always sit at the end for every medium.
  return [
    ...CLASS_LEVELS.filter((level) => mediumSpecific.has(level.value)),
    ...CLASS_LEVELS.filter((level) => agnostic.has(level.value)),
  ]
}

// Academic groups from Class 9 upward. Value is stored on subjects.group.
export const GROUPS = [
  { value: 'science', label: 'Science' },
  { value: 'business_studies', label: 'Business Studies' },
  { value: 'humanities', label: 'Humanities' },
]

// Class levels where students split into groups, so the group filter applies.
const GROUP_CLASS_LEVELS = ['class_9', 'class_10', 'ssc', 'hsc', 'admission_test']

export function hasGroups(classLevel) {
  return GROUP_CLASS_LEVELS.includes(classLevel)
}

export const DAYS = [
  { value: 'sat', label: 'Sat' }, { value: 'sun', label: 'Sun' },
  { value: 'mon', label: 'Mon' }, { value: 'tue', label: 'Tue' },
  { value: 'wed', label: 'Wed' }, { value: 'thu', label: 'Thu' },
  { value: 'fri', label: 'Fri' },
]

export const PREFERRED_TIMES = [
  { value: 'morning',   label: 'Morning',   hint: '6am – 12pm' },
  { value: 'afternoon', label: 'Afternoon', hint: '12pm – 4pm' },
  { value: 'evening',   label: 'Evening',   hint: '4pm – 8pm' },
  { value: 'night',     label: 'Night',     hint: '8pm – 11pm' },
]

export const TUTORING_STYLES = [
  { value: 'one_to_one', label: 'One-to-one' },
  { value: 'group', label: 'Group' },
  { value: 'online', label: 'Online' },
]

export const PLACE_OF_TUTORING = [
  { value: 'student_home', label: "Student's home" },
  { value: 'tutor_home', label: "Tutor's home" },
  { value: 'online', label: 'Online' },
]
