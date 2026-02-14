/**
 * Push Notification Service
 * Handles Expo push notifications for the Hilal app
 */

import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { api } from '../api/client';

// Storage keys
const NOTIFICATION_TOKEN_KEY = 'hilal_push_token';
const NOTIFICATION_SETTINGS_KEY = 'hilal_notification_settings';

// Notification types
export type NotificationType =
  | 'announcement'
  | 'moon_sighting'
  | 'prayer_reminder'
  | 'month_start'
  | 'islamic_event';

export interface NotificationSettings {
  enabled: boolean;
  announcements: boolean;
  moonSightings: boolean;
  prayerReminders: boolean;
  monthStart: boolean;
  islamicEvents: boolean;
  prayerReminderMinutes: number; // minutes before prayer
}

const DEFAULT_SETTINGS: NotificationSettings = {
  enabled: true,
  announcements: true,
  moonSightings: true,
  prayerReminders: false,
  monthStart: true,
  islamicEvents: true,
  prayerReminderMinutes: 15,
};

// Configure notification handler
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

class NotificationService {
  private expoPushToken: string | null = null;
  private settings: NotificationSettings = DEFAULT_SETTINGS;
  private notificationListener: Notifications.Subscription | null = null;
  private responseListener: Notifications.Subscription | null = null;

  /**
   * Initialize notification service
   */
  async initialize(): Promise<void> {
    // Load saved settings
    await this.loadSettings();

    // Request permissions and get token
    if (this.settings.enabled) {
      await this.registerForPushNotifications();
    }

    // Set up notification listeners
    this.setupListeners();
  }

  /**
   * Register for push notifications
   */
  async registerForPushNotifications(): Promise<string | null> {
    if (!Device.isDevice) {
      console.log('Push notifications require a physical device');
      return null;
    }

    try {
      // Check existing permissions
      const { status: existingStatus } = await Notifications.getPermissionsAsync();
      let finalStatus = existingStatus;

      // Request permissions if not granted
      if (existingStatus !== 'granted') {
        const { status } = await Notifications.requestPermissionsAsync();
        finalStatus = status;
      }

      if (finalStatus !== 'granted') {
        console.log('Push notification permission denied');
        return null;
      }

      // Get Expo push token
      const tokenData = await Notifications.getExpoPushTokenAsync({
        projectId: 'hilal-nz', // Update with your Expo project ID
      });

      this.expoPushToken = tokenData.data;

      // Save token locally
      await AsyncStorage.setItem(NOTIFICATION_TOKEN_KEY, this.expoPushToken);

      // Register token with backend
      await this.registerTokenWithBackend();

      // Configure Android channel
      if (Platform.OS === 'android') {
        await this.setupAndroidChannels();
      }

      console.log('Push token registered:', this.expoPushToken);
      return this.expoPushToken;
    } catch (error) {
      console.error('Error registering for push notifications:', error);
      return null;
    }
  }

  /**
   * Set up Android notification channels
   */
  private async setupAndroidChannels(): Promise<void> {
    // Main announcement channel
    await Notifications.setNotificationChannelAsync('announcements', {
      name: 'Announcements',
      importance: Notifications.AndroidImportance.HIGH,
      vibrationPattern: [0, 250, 250, 250],
      lightColor: '#D4A843',
      sound: 'default',
    });

    // Moon sighting channel
    await Notifications.setNotificationChannelAsync('moon_sighting', {
      name: 'Moon Sighting',
      importance: Notifications.AndroidImportance.MAX,
      vibrationPattern: [0, 500, 250, 500],
      lightColor: '#D4A843',
      sound: 'default',
    });

    // Prayer reminder channel
    await Notifications.setNotificationChannelAsync('prayer_reminder', {
      name: 'Prayer Reminders',
      importance: Notifications.AndroidImportance.HIGH,
      vibrationPattern: [0, 250],
      lightColor: '#D4A843',
      sound: 'adhan.wav', // Custom sound if available
    });

    // Islamic events channel
    await Notifications.setNotificationChannelAsync('islamic_events', {
      name: 'Islamic Events',
      importance: Notifications.AndroidImportance.DEFAULT,
      lightColor: '#D4A843',
    });
  }

  /**
   * Register token with backend
   */
  private async registerTokenWithBackend(): Promise<void> {
    if (!this.expoPushToken) return;

    try {
      await api.registerDeviceToken(this.expoPushToken, Platform.OS as 'ios' | 'android');
    } catch (error) {
      console.error('Error registering token with backend:', error);
    }
  }

  /**
   * Set up notification listeners
   */
  private setupListeners(): void {
    // Handle incoming notifications while app is foregrounded
    this.notificationListener = Notifications.addNotificationReceivedListener(
      (notification) => {
        console.log('Notification received:', notification);
        this.handleNotificationReceived(notification);
      }
    );

    // Handle notification tap
    this.responseListener = Notifications.addNotificationResponseReceivedListener(
      (response) => {
        console.log('Notification response:', response);
        this.handleNotificationResponse(response);
      }
    );
  }

  /**
   * Handle received notification
   */
  private handleNotificationReceived(
    notification: Notifications.Notification
  ): void {
    const data = notification.request.content.data;
    const type = data?.type as NotificationType;

    // Check if notification type is enabled
    if (!this.isNotificationTypeEnabled(type)) {
      return;
    }

    // Custom handling based on type
    switch (type) {
      case 'prayer_reminder':
        // Could play adhan or special sound
        break;
      case 'moon_sighting':
        // Could show special UI
        break;
      default:
        break;
    }
  }

  /**
   * Handle notification tap response
   */
  private handleNotificationResponse(
    response: Notifications.NotificationResponse
  ): void {
    const data = response.notification.request.content.data;
    const type = data?.type as NotificationType;

    // Return navigation action based on type
    // This would be handled by the app's navigation
    const navigationAction = this.getNavigationForNotification(type, data);

    // Store navigation action for app to handle
    if (navigationAction) {
      AsyncStorage.setItem('pending_notification_navigation', JSON.stringify(navigationAction));
    }
  }

  /**
   * Get navigation destination for notification type
   */
  private getNavigationForNotification(
    type: NotificationType,
    data: Record<string, unknown>
  ): { screen: string; params?: Record<string, unknown> } | null {
    switch (type) {
      case 'announcement':
      case 'moon_sighting':
      case 'month_start':
        return {
          screen: 'AnnouncementDetail',
          params: { id: data.announcement_id },
        };
      case 'prayer_reminder':
        return { screen: 'PrayerTimes' };
      case 'islamic_event':
        return { screen: 'Calendar' };
      default:
        return null;
    }
  }

  /**
   * Check if notification type is enabled
   */
  private isNotificationTypeEnabled(type: NotificationType): boolean {
    if (!this.settings.enabled) return false;

    switch (type) {
      case 'announcement':
        return this.settings.announcements;
      case 'moon_sighting':
        return this.settings.moonSightings;
      case 'prayer_reminder':
        return this.settings.prayerReminders;
      case 'month_start':
        return this.settings.monthStart;
      case 'islamic_event':
        return this.settings.islamicEvents;
      default:
        return true;
    }
  }

  /**
   * Load settings from storage
   */
  async loadSettings(): Promise<NotificationSettings> {
    try {
      const saved = await AsyncStorage.getItem(NOTIFICATION_SETTINGS_KEY);
      if (saved) {
        try {
          this.settings = { ...DEFAULT_SETTINGS, ...JSON.parse(saved) };
        } catch {
          // Invalid JSON, reset to defaults
          await AsyncStorage.removeItem(NOTIFICATION_SETTINGS_KEY);
        }
      }
    } catch (error) {
      // Error loading settings, use defaults
    }
    return this.settings;
  }

  /**
   * Save settings to storage
   */
  async saveSettings(settings: Partial<NotificationSettings>): Promise<void> {
    this.settings = { ...this.settings, ...settings };
    await AsyncStorage.setItem(NOTIFICATION_SETTINGS_KEY, JSON.stringify(this.settings));

    // If notifications were just enabled, register for push
    if (settings.enabled && !this.expoPushToken) {
      await this.registerForPushNotifications();
    }
  }

  /**
   * Get current settings
   */
  getSettings(): NotificationSettings {
    return this.settings;
  }

  /**
   * Get push token
   */
  getToken(): string | null {
    return this.expoPushToken;
  }

  /**
   * Schedule a local notification (for prayer reminders)
   */
  async scheduleLocalNotification(
    title: string,
    body: string,
    trigger: Date | number,
    data?: Record<string, unknown>
  ): Promise<string> {
    const triggerConfig: Notifications.NotificationTriggerInput =
      typeof trigger === 'number'
        ? { seconds: trigger }
        : { date: trigger };

    return await Notifications.scheduleNotificationAsync({
      content: {
        title,
        body,
        data: data || {},
        sound: true,
      },
      trigger: triggerConfig,
    });
  }

  /**
   * Schedule prayer reminders for the day
   */
  async schedulePrayerReminders(
    prayerTimes: Record<string, string>,
    mosqueName: string
  ): Promise<void> {
    if (!this.settings.prayerReminders) return;

    // Cancel existing prayer reminders
    await this.cancelPrayerReminders();

    const now = new Date();
    const reminderMinutes = this.settings.prayerReminderMinutes;

    const prayers = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
    const prayerNamesEn: Record<string, string> = {
      fajr: 'Fajr',
      dhuhr: 'Dhuhr',
      asr: 'Asr',
      maghrib: 'Maghrib',
      isha: 'Isha',
    };

    for (const prayer of prayers) {
      const timeStr = prayerTimes[prayer];
      if (!timeStr) continue;

      const [hours, minutes] = timeStr.split(':').map(Number);
      const prayerDate = new Date(now);
      prayerDate.setHours(hours, minutes, 0, 0);

      // Subtract reminder minutes
      const reminderDate = new Date(prayerDate.getTime() - reminderMinutes * 60000);

      // Only schedule if in the future
      if (reminderDate > now) {
        await Notifications.scheduleNotificationAsync({
          content: {
            title: `${prayerNamesEn[prayer]} Prayer`,
            body: `${prayerNamesEn[prayer]} prayer in ${reminderMinutes} minutes at ${mosqueName}`,
            data: { type: 'prayer_reminder', prayer },
            sound: true,
            categoryIdentifier: 'prayer_reminder',
          },
          trigger: { date: reminderDate },
          identifier: `prayer_${prayer}`,
        });
      }
    }
  }

  /**
   * Cancel all prayer reminders
   */
  async cancelPrayerReminders(): Promise<void> {
    const prayers = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
    for (const prayer of prayers) {
      await Notifications.cancelScheduledNotificationAsync(`prayer_${prayer}`);
    }
  }

  /**
   * Cancel all scheduled notifications
   */
  async cancelAllNotifications(): Promise<void> {
    await Notifications.cancelAllScheduledNotificationsAsync();
  }

  /**
   * Get pending notifications
   */
  async getPendingNotifications(): Promise<Notifications.NotificationRequest[]> {
    return await Notifications.getAllScheduledNotificationsAsync();
  }

  /**
   * Clean up listeners
   */
  cleanup(): void {
    if (this.notificationListener) {
      Notifications.removeNotificationSubscription(this.notificationListener);
    }
    if (this.responseListener) {
      Notifications.removeNotificationSubscription(this.responseListener);
    }
  }
}

// Export singleton instance
export const notificationService = new NotificationService();
export default notificationService;
