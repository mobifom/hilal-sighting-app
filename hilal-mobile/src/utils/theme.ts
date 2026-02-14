/**
 * Hilal Mobile App Theme
 * Based on wireframe design from hilal-complete-v3.jsx
 */

export const Colors = {
  // Dark theme colors
  dark: {
    background: '#0C1425',
    backgroundSecondary: '#131E36',
    card: '#19274A',
    gold: '#D4A843',
    goldDim: 'rgba(212, 168, 67, 0.12)',
    goldBorder: 'rgba(212, 168, 67, 0.2)',
    text: '#ECE6D8',
    textSecondary: '#B8AD9E',
    muted: '#7E8FAB',
    border: 'rgba(255, 255, 255, 0.1)',
  },
  // Light theme colors
  light: {
    background: '#FAFAF6',
    backgroundSecondary: '#FFFFFF',
    card: '#FFFFFF',
    gold: '#B8922E',
    goldLight: '#FDF6E3',
    goldBorder: 'rgba(184, 146, 46, 0.3)',
    text: '#1C2537',
    textSecondary: '#4A5568',
    muted: '#6B7A90',
    border: '#E8E5DE',
  },
  // Status colors (shared)
  status: {
    success: '#48B07A',
    warning: '#E2A33E',
    danger: '#D45555',
    info: '#5B8DEF',
  },
  // Quick action card colors
  quickActions: {
    calendar: { bg: '#E8F4FD', border: '#B8D8ED' },
    announcements: { bg: '#FDF6E3', border: 'rgba(184, 146, 46, 0.3)' },
    report: { bg: '#E8FDF0', border: '#A8DFC0' },
    prayerTimes: { bg: '#F3E8FD', border: '#D0B8ED' },
    qibla: { bg: '#FDE8F0', border: '#EDB8CC' },
  },
};

export const Typography = {
  fontFamily: {
    regular: 'System',
    medium: 'System',
    bold: 'System',
    arabic: 'System',
  },
  fontSize: {
    xs: 10,
    sm: 12,
    base: 14,
    md: 16,
    lg: 18,
    xl: 20,
    '2xl': 24,
    '3xl': 30,
    '4xl': 36,
    '5xl': 48,
  },
  fontWeight: {
    normal: '400' as const,
    medium: '500' as const,
    semibold: '600' as const,
    bold: '700' as const,
    extrabold: '800' as const,
  },
};

export const Spacing = {
  xs: 4,
  sm: 8,
  md: 12,
  base: 16,
  lg: 20,
  xl: 24,
  '2xl': 32,
  '3xl': 40,
  '4xl': 48,
};

export const BorderRadius = {
  sm: 6,
  md: 10,
  lg: 14,
  xl: 20,
  full: 9999,
};

export const Shadows = {
  sm: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  md: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 4,
    elevation: 4,
  },
  lg: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 8,
  },
};

// Prayer names
export const PrayerNames = {
  en: {
    fajr: 'Fajr',
    sunrise: 'Sunrise',
    dhuhr: 'Dhuhr',
    asr: 'Asr',
    maghrib: 'Maghrib',
    isha: 'Isha',
  },
  ar: {
    fajr: 'الفجر',
    sunrise: 'الشروق',
    dhuhr: 'الظهر',
    asr: 'العصر',
    maghrib: 'المغرب',
    isha: 'العشاء',
  },
};

// Hijri month names
export const HijriMonthNames = {
  en: [
    'Muharram', 'Safar', "Rabi' al-Awwal", "Rabi' al-Thani",
    'Jumada al-Awwal', 'Jumada al-Thani', 'Rajab', "Sha'ban",
    'Ramadan', 'Shawwal', "Dhu al-Qi'dah", 'Dhu al-Hijjah',
  ],
  ar: [
    'محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني',
    'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان',
    'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة',
  ],
};

// Convert number to Arabic numerals
export const toArabicNumerals = (num: number | string): string => {
  const arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
  return String(num).replace(/[0-9]/g, (d) => arabicNumerals[parseInt(d)]);
};
