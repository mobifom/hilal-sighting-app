/**
 * Mock API Server for Hilal Mobile App
 * Run with: node mock-server.js
 */

const http = require('http');

const PORT = 8888;

// Mock data
const hijriDate = {
  day: 15,
  month: 8,
  year: 1446,
  month_name: "Sha'ban",
  month_name_en: "Sha'ban",
  month_name_ar: "شعبان",
  formatted: "15 Sha'ban 1446",
  status: "confirmed",
  source: "calculation"
};

const months = [
  { month_number: 1, hijri_year: 1446, month_name: "Muharram", month_name_en: "Muharram", month_name_ar: "محرم", gregorian_start: "2024-07-07", gregorian_end: "2024-08-05", days_count: 30, status: "confirmed", events: [] },
  { month_number: 2, hijri_year: 1446, month_name: "Safar", month_name_en: "Safar", month_name_ar: "صفر", gregorian_start: "2024-08-06", gregorian_end: "2024-09-04", days_count: 29, status: "confirmed", events: [] },
  { month_number: 3, hijri_year: 1446, month_name: "Rabi al-Awwal", month_name_en: "Rabi al-Awwal", month_name_ar: "ربيع الأول", gregorian_start: "2024-09-05", gregorian_end: "2024-10-04", days_count: 30, status: "confirmed", events: [{ id: 1, name: "Mawlid al-Nabi", hijri_day: 12, category: "holiday" }] },
  { month_number: 4, hijri_year: 1446, month_name: "Rabi al-Thani", month_name_en: "Rabi al-Thani", month_name_ar: "ربيع الثاني", gregorian_start: "2024-10-05", gregorian_end: "2024-11-02", days_count: 29, status: "confirmed", events: [] },
  { month_number: 5, hijri_year: 1446, month_name: "Jumada al-Awwal", month_name_en: "Jumada al-Awwal", month_name_ar: "جمادى الأولى", gregorian_start: "2024-11-03", gregorian_end: "2024-12-01", days_count: 29, status: "confirmed", events: [] },
  { month_number: 6, hijri_year: 1446, month_name: "Jumada al-Thani", month_name_en: "Jumada al-Thani", month_name_ar: "جمادى الثانية", gregorian_start: "2024-12-02", gregorian_end: "2024-12-31", days_count: 30, status: "confirmed", events: [] },
  { month_number: 7, hijri_year: 1446, month_name: "Rajab", month_name_en: "Rajab", month_name_ar: "رجب", gregorian_start: "2025-01-01", gregorian_end: "2025-01-29", days_count: 29, status: "confirmed", events: [{ id: 2, name: "Isra and Mi'raj", hijri_day: 27, category: "holiday" }] },
  { month_number: 8, hijri_year: 1446, month_name: "Sha'ban", month_name_en: "Sha'ban", month_name_ar: "شعبان", gregorian_start: "2025-01-30", gregorian_end: "2025-02-27", days_count: 29, status: "confirmed", events: [] },
  { month_number: 9, hijri_year: 1446, month_name: "Ramadan", month_name_en: "Ramadan", month_name_ar: "رمضان", gregorian_start: "2025-02-28", gregorian_end: "2025-03-29", days_count: 30, status: "pending_sighting", events: [{ id: 3, name: "Laylat al-Qadr", hijri_day: 27, category: "holiday" }] },
  { month_number: 10, hijri_year: 1446, month_name: "Shawwal", month_name_en: "Shawwal", month_name_ar: "شوال", gregorian_start: "2025-03-30", gregorian_end: "2025-04-27", days_count: 29, status: "estimated", events: [{ id: 4, name: "Eid al-Fitr", hijri_day: 1, category: "holiday" }] },
  { month_number: 11, hijri_year: 1446, month_name: "Dhul Qa'dah", month_name_en: "Dhul Qa'dah", month_name_ar: "ذو القعدة", gregorian_start: "2025-04-28", gregorian_end: "2025-05-27", days_count: 30, status: "estimated", events: [] },
  { month_number: 12, hijri_year: 1446, month_name: "Dhul Hijjah", month_name_en: "Dhul Hijjah", month_name_ar: "ذو الحجة", gregorian_start: "2025-05-28", gregorian_end: "2025-06-25", days_count: 29, status: "estimated", events: [{ id: 5, name: "Eid al-Adha", hijri_day: 10, category: "holiday" }, { id: 6, name: "Day of Arafah", hijri_day: 9, category: "holiday" }] },
];

const announcements = [
  {
    id: 1,
    slug: "ramadan-1446-announcement",
    title: "Ramadan 1446 Moon Sighting",
    title_en: "Ramadan 1446 Moon Sighting",
    title_ar: "رؤية هلال رمضان 1446",
    body: "The crescent moon for Ramadan 1446 will be sought on Thursday, February 27, 2025.",
    body_en: "The crescent moon for Ramadan 1446 will be sought on Thursday, February 27, 2025.",
    body_ar: "سيتم تحري هلال شهر رمضان 1446 يوم الخميس 27 فبراير 2025.",
    type: "moon_sighting",
    type_label: "Moon Sighting",
    priority: "high",
    thumbnail: null,
    published_at: "2025-02-14T10:00:00Z",
    published_date: "2025-02-14",
    url: "/announcements/ramadan-1446-announcement"
  },
  {
    id: 2,
    slug: "shaban-confirmed",
    title: "Sha'ban 1446 Confirmed",
    title_en: "Sha'ban 1446 Confirmed",
    title_ar: "تأكيد شهر شعبان 1446",
    body: "The month of Sha'ban 1446 has been confirmed to start on January 30, 2025.",
    body_en: "The month of Sha'ban 1446 has been confirmed to start on January 30, 2025.",
    body_ar: "تم تأكيد بداية شهر شعبان 1446 في 30 يناير 2025.",
    type: "month_start",
    type_label: "Month Start",
    priority: "medium",
    thumbnail: null,
    published_at: "2025-01-30T10:00:00Z",
    published_date: "2025-01-30",
    url: "/announcements/shaban-confirmed"
  },
  {
    id: 3,
    slug: "isra-miraj-reminder",
    title: "Isra and Mi'raj Reminder",
    title_en: "Isra and Mi'raj Reminder",
    title_ar: "تذكير بالإسراء والمعراج",
    body: "Isra and Mi'raj will be commemorated on the 27th of Rajab.",
    body_en: "Isra and Mi'raj will be commemorated on the 27th of Rajab.",
    body_ar: "سيتم إحياء ذكرى الإسراء والمعراج في 27 رجب.",
    type: "islamic_event",
    type_label: "Islamic Event",
    priority: "low",
    thumbnail: null,
    published_at: "2025-01-20T10:00:00Z",
    published_date: "2025-01-20",
    url: "/announcements/isra-miraj-reminder"
  }
];

const sightings = [
  {
    id: 1,
    title: "Sha'ban 1446 Sighting - Auckland",
    details: "The crescent moon was sighted in Auckland on January 29, 2025 at 8:45 PM. Clear skies with excellent visibility.",
    attachment: {
      id: 1,
      url: "https://example.com/sighting-report-shaban-1446.pdf",
      filename: "sighting-report-shaban-1446.pdf",
      filesize: 245000
    },
    submitted_at: "2025-01-29T20:45:00Z"
  },
  {
    id: 2,
    title: "Rajab 1446 Sighting - Wellington",
    details: "Moon sighted from Wellington observatory. Conditions were partly cloudy but the crescent was visible.",
    attachment: null,
    submitted_at: "2024-12-31T19:30:00Z"
  }
];

const prayerTimes = {
  date: new Date().toISOString().split('T')[0],
  location: { lat: -36.8509, lng: 174.7645, timezone: "Pacific/Auckland" },
  method: "MWL",
  times: {
    fajr: "04:52",
    sunrise: "06:28",
    dhuhr: "13:15",
    asr: "17:05",
    maghrib: "20:45",
    isha: "22:15"
  },
  city: { name: "Auckland", name_ar: "أوكلاند" },
  next_prayer: { name: "Maghrib", time: "20:45", minutes_until: 120 }
};

// API response wrapper
function apiResponse(data) {
  return JSON.stringify({ success: true, data });
}

// Request handler
function handleRequest(req, res) {
  const url = new URL(req.url, `http://localhost:${PORT}`);
  const path = url.pathname;

  // CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  res.setHeader('Content-Type', 'application/json');

  if (req.method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }

  console.log(`${req.method} ${path}`);

  // Route handling
  if (path === '/wp-json/hilal/v1/today') {
    res.writeHead(200);
    res.end(apiResponse({
      hijri_date: hijriDate,
      gregorian_date: {
        date: new Date().toISOString().split('T')[0],
        formatted: new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
      },
      upcoming_events: [
        { id: 3, name: "Laylat al-Qadr", hijri_day: 27, hijri_month: 9, hijri_month_name: "Ramadan" }
      ],
      next_significant_month: { month: 9, month_name_en: "Ramadan", month_name_ar: "رمضان", year: 1446 }
    }));
  }
  else if (path.match(/\/wp-json\/hilal\/v1\/hijri-calendar(\/\d+)?/)) {
    res.writeHead(200);
    res.end(apiResponse({ year: 1446, months }));
  }
  else if (path === '/wp-json/hilal/v1/announcements') {
    res.writeHead(200);
    res.end(apiResponse({
      announcements,
      pagination: { total: announcements.length, total_pages: 1, current_page: 1, per_page: 10 }
    }));
  }
  else if (path === '/wp-json/hilal/v1/announcements/latest') {
    res.writeHead(200);
    res.end(apiResponse(announcements[0]));
  }
  else if (path.match(/\/wp-json\/hilal\/v1\/announcements\/\d+/)) {
    const id = parseInt(path.split('/').pop());
    const announcement = announcements.find(a => a.id === id) || announcements[0];
    res.writeHead(200);
    res.end(apiResponse(announcement));
  }
  else if (path === '/wp-json/hilal/v1/sightings/approved') {
    res.writeHead(200);
    res.end(apiResponse({ sightings }));
  }
  else if (path === '/wp-json/hilal/v1/prayer-times' || path.match(/\/wp-json\/hilal\/v1\/prayer-times\/city\/.+/)) {
    res.writeHead(200);
    res.end(apiResponse(prayerTimes));
  }
  else if (path === '/wp-json/hilal/v1/prayer-times/cities') {
    res.writeHead(200);
    res.end(apiResponse({
      cities: [
        { slug: "auckland", name: "Auckland", name_en: "Auckland", name_ar: "أوكلاند", lat: -36.8509, lng: 174.7645, timezone: "Pacific/Auckland" },
        { slug: "wellington", name: "Wellington", name_en: "Wellington", name_ar: "ويلينغتون", lat: -41.2866, lng: 174.7756, timezone: "Pacific/Auckland" },
        { slug: "christchurch", name: "Christchurch", name_en: "Christchurch", name_ar: "كرايستشيرش", lat: -43.5321, lng: 172.6362, timezone: "Pacific/Auckland" }
      ]
    }));
  }
  else if (path === '/wp-json/hilal/v1/qibla') {
    const lat = parseFloat(url.searchParams.get('lat')) || -36.8509;
    const lng = parseFloat(url.searchParams.get('lng')) || 174.7645;
    res.writeHead(200);
    res.end(apiResponse({
      qibla: { bearing: 261.5, bearing_rounded: 262, description_en: "West-Northwest", description_ar: "غرب-شمال غرب" },
      distance: { km: 14200, miles: 8824 }
    }));
  }
  else if (path === '/wp-json/hilal/v1/upcoming-events') {
    res.writeHead(200);
    res.end(apiResponse({
      events: [
        { id: 3, name: "Ramadan", hijri_day: 1, hijri_month: 9, hijri_month_name: "Ramadan", category: "month" },
        { id: 4, name: "Eid al-Fitr", hijri_day: 1, hijri_month: 10, hijri_month_name: "Shawwal", category: "holiday" }
      ]
    }));
  }
  else {
    res.writeHead(404);
    res.end(JSON.stringify({ success: false, message: "Endpoint not found", path }));
  }
}

// Create server
const server = http.createServer(handleRequest);

server.listen(PORT, '0.0.0.0', () => {
  console.log(`\n🌙 Hilal Mock API Server running at:`);
  console.log(`   http://localhost:${PORT}/wp-json/hilal/v1/`);
  console.log(`   http://192.168.4.212:${PORT}/wp-json/hilal/v1/`);
  console.log(`\nAvailable endpoints:`);
  console.log(`   GET /wp-json/hilal/v1/today`);
  console.log(`   GET /wp-json/hilal/v1/hijri-calendar`);
  console.log(`   GET /wp-json/hilal/v1/announcements`);
  console.log(`   GET /wp-json/hilal/v1/announcements/latest`);
  console.log(`   GET /wp-json/hilal/v1/sightings/approved`);
  console.log(`   GET /wp-json/hilal/v1/prayer-times`);
  console.log(`   GET /wp-json/hilal/v1/qibla`);
  console.log(`\nPress Ctrl+C to stop\n`);
});
