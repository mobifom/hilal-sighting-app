/**
 * Hilal API Client
 * Handles all API communication with WordPress backend
 */

import * as SecureStore from 'expo-secure-store';
import Constants from 'expo-constants';

// API base URL - update this for production
// Use your computer's IP address for simulator/device access
// Using ?rest_route= format for wp-env compatibility
const WP_BASE_URL = Constants.expoConfig?.extra?.wpBaseUrl || 'http://192.168.4.212:8888';
const API_NAMESPACE = 'hilal/v1';

// Use rest_route parameter for better compatibility with wp-env
const useRestRoute = Constants.expoConfig?.extra?.useRestRoute ?? true;

// Token storage keys
const TOKEN_KEY = 'hilal_auth_token';
const REFRESH_TOKEN_KEY = 'hilal_refresh_token';

interface ApiResponse<T> {
  success: boolean;
  data: T;
}

interface ApiError {
  code: string;
  message: string;
  data?: { status: number };
}

class HilalAPIClient {
  private wpBaseUrl: string;
  private namespace: string;
  private useRestRoute: boolean;
  private language: 'en' | 'ar' = 'en';

  constructor() {
    this.wpBaseUrl = WP_BASE_URL;
    this.namespace = API_NAMESPACE;
    this.useRestRoute = useRestRoute;
    console.log('[HilalAPI] Config:', { wpBaseUrl: this.wpBaseUrl, useRestRoute: this.useRestRoute });
  }

  // Build the correct API URL based on configuration
  private buildUrl(endpoint: string): string {
    if (this.useRestRoute) {
      // Use ?rest_route= parameter (wp-env compatible)
      return `${this.wpBaseUrl}/?rest_route=/${this.namespace}/${endpoint}`;
    } else {
      // Use pretty permalinks
      return `${this.wpBaseUrl}/wp-json/${this.namespace}/${endpoint}`;
    }
  }

  // Set language for API requests
  setLanguage(lang: 'en' | 'ar') {
    this.language = lang;
  }

  // Get stored auth token
  private async getToken(): Promise<string | null> {
    try {
      return await SecureStore.getItemAsync(TOKEN_KEY);
    } catch {
      return null;
    }
  }

  // Store auth token
  async setToken(token: string): Promise<void> {
    await SecureStore.setItemAsync(TOKEN_KEY, token);
  }

  // Clear auth token
  async clearToken(): Promise<void> {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
    await SecureStore.deleteItemAsync(REFRESH_TOKEN_KEY);
  }

  // Make API request
  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    let urlString = this.buildUrl(endpoint);
    const separator = urlString.includes('?') ? '&' : '?';
    urlString += `${separator}lang=${this.language}`;

    const token = await this.getToken();

    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    };

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    try {
      const response = await fetch(urlString, {
        ...options,
        headers,
      });

      const json = await response.json();

      if (!response.ok) {
        const error = json as ApiError;
        throw new Error(error.message || 'API request failed');
      }

      return (json as ApiResponse<T>).data;
    } catch (error) {
      if (error instanceof Error) {
        throw error;
      }
      throw new Error('Network error');
    }
  }

  // GET request
  async get<T>(endpoint: string, params?: Record<string, string>): Promise<T> {
    let url = endpoint;
    if (params) {
      const searchParams = new URLSearchParams(params);
      url += `?${searchParams.toString()}`;
    }
    return this.request<T>(url, { method: 'GET' });
  }

  // POST request
  async post<T>(endpoint: string, data?: unknown): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'POST',
      body: data ? JSON.stringify(data) : undefined,
    });
  }

  // Upload file
  async uploadFile(endpoint: string, file: {
    uri: string;
    name: string;
    type: string;
  }): Promise<{ url: string }> {
    const token = await this.getToken();
    const formData = new FormData();
    formData.append('file', file as unknown as Blob);

    const response = await fetch(this.buildUrl(endpoint), {
      method: 'POST',
      headers: {
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: formData,
    });

    const json = await response.json();
    if (!response.ok) {
      throw new Error(json.message || 'Upload failed');
    }

    return json.data;
  }

  // ========================================
  // Calendar & Hijri Date Endpoints
  // ========================================

  async getToday() {
    return this.get<{
      hijri_date: {
        day: number;
        month: number;
        year: number;
        month_name: string;
        month_name_en: string;
        month_name_ar: string;
        formatted: string;
        status: string;
        source: string;
      };
      gregorian_date: {
        date: string;
        formatted: string;
      };
      upcoming_events: Array<{
        id: number;
        name: string;
        hijri_day: number;
        hijri_month: number;
        hijri_month_name: string;
      }>;
      next_significant_month: {
        month: number;
        month_name_en: string;
        month_name_ar: string;
        year: number;
      } | null;
    }>('today');
  }

  async getCalendar(year?: number) {
    const endpoint = year ? `hijri-calendar/${year}` : 'hijri-calendar';
    return this.get<{
      year: number;
      months: Array<{
        month_number: number;
        hijri_year: number;
        month_name: string;
        month_name_en: string;
        month_name_ar: string;
        gregorian_start: string | null;
        gregorian_end: string | null;
        days_count: number | null;
        status: string;
        events: Array<{
          id: number;
          name: string;
          hijri_day: number;
          category: string;
        }>;
      }>;
    }>(endpoint);
  }

  async getUpcomingEvents(limit = 5) {
    return this.get<{
      events: Array<{
        id: number;
        name: string;
        hijri_day: number;
        hijri_month: number;
        hijri_month_name: string;
        category: string;
      }>;
    }>('upcoming-events', { limit: String(limit) });
  }

  // ========================================
  // Announcements Endpoints
  // ========================================

  async getAnnouncements(params?: {
    page?: number;
    per_page?: number;
    type?: string;
  }) {
    return this.get<{
      announcements: Array<{
        id: number;
        slug: string;
        title: string;
        title_en: string;
        title_ar: string;
        body: string;
        body_en: string;
        body_ar: string;
        type: string;
        type_label: string;
        priority: string;
        thumbnail: string | null;
        published_at: string;
        published_date: string;
        url: string;
      }>;
      pagination: {
        total: number;
        total_pages: number;
        current_page: number;
        per_page: number;
      };
    }>('announcements', params as Record<string, string>);
  }

  async getAnnouncement(id: number) {
    return this.get<{
      id: number;
      title: string;
      title_en: string;
      title_ar: string;
      body: string;
      body_en: string;
      body_ar: string;
      type: string;
      priority: string;
      published_date: string;
    }>(`announcements/${id}`);
  }

  async getLatestAnnouncement() {
    return this.get<{
      id: number;
      title: string;
      title_en: string;
      title_ar: string;
      body: string;
      type: string;
      priority: string;
    } | null>('announcements/latest');
  }

  // ========================================
  // Prayer Times Endpoints
  // ========================================

  async getPrayerTimes(params: {
    lat?: number;
    lng?: number;
    city?: string;
    date?: string;
    method?: string;
  }) {
    return this.get<{
      date: string;
      location: {
        lat: number;
        lng: number;
        timezone: string;
      };
      method: string;
      times: {
        fajr: string;
        sunrise: string;
        dhuhr: string;
        asr: string;
        maghrib: string;
        isha: string;
      };
      city?: {
        name: string;
        name_ar: string;
      };
      next_prayer: {
        name: string;
        time: string;
        minutes_until: number;
        tomorrow?: boolean;
      } | null;
    }>('prayer-times', params as Record<string, string>);
  }

  async getPrayerTimesByCity(city: string, date?: string) {
    return this.get<{
      date: string;
      times: {
        fajr: string;
        sunrise: string;
        dhuhr: string;
        asr: string;
        maghrib: string;
        isha: string;
      };
      city: {
        name: string;
        name_ar: string;
      };
      next_prayer: {
        name: string;
        time: string;
        minutes_until: number;
      } | null;
    }>(`prayer-times/city/${city}`, date ? { date } : undefined);
  }

  async getCities() {
    return this.get<{
      cities: Array<{
        slug: string;
        name: string;
        name_en: string;
        name_ar: string;
        lat: number;
        lng: number;
        timezone: string;
      }>;
    }>('prayer-times/cities');
  }

  // ========================================
  // My-Masjid.com Integration
  // ========================================

  /**
   * Fetch prayer times from my-masjid.com for mosques with integration
   * Note: my-masjid.com requires JavaScript rendering, so we scrape the timing screen
   */
  async getMyMasjidPrayerTimes(masjidId: string): Promise<{
    times: {
      fajr: { adhan: string; iqama: string };
      sunrise: string;
      dhuhr: { adhan: string; iqama: string };
      asr: { adhan: string; iqama: string };
      maghrib: { adhan: string; iqama: string };
      isha: { adhan: string; iqama: string };
      jumuah?: { adhan: string; iqama: string };
    };
    source: 'my-masjid';
    lastUpdated: string;
  } | null> {
    try {
      // My-Masjid uses a WebSocket/JavaScript-based system
      // For now, we'll proxy through our WordPress backend which can scrape/cache the data
      const response = await this.get<{
        times: {
          fajr: { adhan: string; iqama: string };
          sunrise: string;
          dhuhr: { adhan: string; iqama: string };
          asr: { adhan: string; iqama: string };
          maghrib: { adhan: string; iqama: string };
          isha: { adhan: string; iqama: string };
          jumuah?: { adhan: string; iqama: string };
        };
        source: 'my-masjid';
        lastUpdated: string;
      }>(`prayer-times/my-masjid/${masjidId}`);
      return response;
    } catch (error) {
      console.error('Error fetching my-masjid times:', error);
      return null;
    }
  }

  /**
   * Get prayer times for a specific mosque
   * Uses my-masjid.com if available, otherwise falls back to location calculation
   */
  async getMosquePrayerTimes(mosque: {
    id: string;
    lat: number;
    lng: number;
    myMasjidId?: string;
  }): Promise<{
    times: {
      fajr: string;
      sunrise: string;
      dhuhr: string;
      asr: string;
      maghrib: string;
      isha: string;
    };
    iqamaTimes?: {
      fajr: string;
      dhuhr: string;
      asr: string;
      maghrib: string;
      isha: string;
    };
    source: 'my-masjid' | 'calculation';
    next_prayer: {
      name: string;
      time: string;
      minutes_until: number;
    } | null;
  }> {
    // If mosque has my-masjid integration, try to fetch from there first
    if (mosque.myMasjidId) {
      const myMasjidData = await this.getMyMasjidPrayerTimes(mosque.myMasjidId);
      if (myMasjidData) {
        const times = {
          fajr: myMasjidData.times.fajr.adhan,
          sunrise: myMasjidData.times.sunrise,
          dhuhr: myMasjidData.times.dhuhr.adhan,
          asr: myMasjidData.times.asr.adhan,
          maghrib: myMasjidData.times.maghrib.adhan,
          isha: myMasjidData.times.isha.adhan,
        };
        const iqamaTimes = {
          fajr: myMasjidData.times.fajr.iqama,
          dhuhr: myMasjidData.times.dhuhr.iqama,
          asr: myMasjidData.times.asr.iqama,
          maghrib: myMasjidData.times.maghrib.iqama,
          isha: myMasjidData.times.isha.iqama,
        };
        return {
          times,
          iqamaTimes,
          source: 'my-masjid',
          next_prayer: this.calculateNextPrayer(times),
        };
      }
    }

    // Fallback to location-based calculation
    const data = await this.getPrayerTimes({ lat: mosque.lat, lng: mosque.lng });
    return {
      times: data.times,
      source: 'calculation',
      next_prayer: data.next_prayer,
    };
  }

  /**
   * Calculate next prayer from times
   */
  private calculateNextPrayer(times: {
    fajr: string;
    sunrise: string;
    dhuhr: string;
    asr: string;
    maghrib: string;
    isha: string;
  }): { name: string; time: string; minutes_until: number } | null {
    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes();

    const prayers = [
      { name: 'fajr', time: times.fajr },
      { name: 'sunrise', time: times.sunrise },
      { name: 'dhuhr', time: times.dhuhr },
      { name: 'asr', time: times.asr },
      { name: 'maghrib', time: times.maghrib },
      { name: 'isha', time: times.isha },
    ];

    for (const prayer of prayers) {
      const [hours, minutes] = prayer.time.split(':').map(Number);
      const prayerMinutes = hours * 60 + minutes;
      if (prayerMinutes > currentTime) {
        return {
          name: prayer.name,
          time: prayer.time,
          minutes_until: prayerMinutes - currentTime,
        };
      }
    }

    // Next prayer is Fajr tomorrow
    const [fajrHours, fajrMinutes] = times.fajr.split(':').map(Number);
    const fajrTomorrow = fajrHours * 60 + fajrMinutes + (24 * 60 - currentTime);
    return {
      name: 'fajr',
      time: times.fajr,
      minutes_until: fajrTomorrow,
    };
  }

  // ========================================
  // Qibla Endpoint
  // ========================================

  async getQibla(lat: number, lng: number) {
    return this.get<{
      qibla: {
        bearing: number;
        bearing_rounded: number;
        description_en: string;
        description_ar: string;
      };
      distance: {
        km: number;
        miles: number;
      };
    }>('qibla', { lat: String(lat), lng: String(lng) });
  }

  // ========================================
  // Sighting Report Endpoints
  // ========================================

  async submitSightingReport(data: {
    hijri_month_id: number;
    location_lat: number;
    location_lng: number;
    location_name: string;
    observation_datetime: string;
    photo_url?: string;
    sky_conditions: string;
    visibility_method: string;
    notes?: string;
  }) {
    return this.post<{ id: number; message: string }>('sighting-report', data);
  }

  async uploadSightingPhoto(file: {
    uri: string;
    name: string;
    type: string;
  }) {
    return this.uploadFile('sighting/upload-photo', file);
  }

  async getMyReports() {
    return this.get<{
      reports: Array<{
        id: number;
        hijri_month: {
          month_name: string;
          year: number;
        };
        location_name: string;
        observation_datetime: string;
        status: string;
        created_at: string;
      }>;
    }>('my-reports');
  }

  async getApprovedSightings() {
    return this.get<{
      sightings: Array<{
        id: number;
        title: string;
        details: string;
        attachment?: {
          id: number;
          url: string;
          filename: string;
          filesize: number;
        };
        submitted_at: string;
      }>;
    }>('sightings/approved');
  }

  // ========================================
  // Authentication Endpoints
  // ========================================

  async login(username: string, password: string) {
    const jwtUrl = this.useRestRoute
      ? `${this.wpBaseUrl}/?rest_route=/jwt-auth/v1/token`
      : `${this.wpBaseUrl}/wp-json/jwt-auth/v1/token`;

    const response = await fetch(jwtUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password }),
    });

    const json = await response.json();
    if (!response.ok) {
      throw new Error(json.message || 'Login failed');
    }

    await this.setToken(json.token);
    return {
      token: json.token,
      user: {
        email: json.user_email,
        displayName: json.user_display_name,
      },
    };
  }

  async register(email: string, username: string, password: string) {
    return this.post<{ id: number; message: string }>('register', {
      email,
      username,
      password,
    });
  }

  // ========================================
  // Notifications Endpoint
  // ========================================

  async registerDeviceToken(token: string, platform: 'ios' | 'android') {
    return this.post<{ success: boolean }>('device-token', {
      token,
      platform,
    });
  }

  async subscribe(email: string) {
    return this.post<{ success: boolean; message: string }>('subscribe', { email });
  }
}

// Export singleton instance
export const api = new HilalAPIClient();
export default api;
