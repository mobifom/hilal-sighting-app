/**
 * Notification Settings Screen
 * Manage push notification preferences
 */

import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  Switch,
  TouchableOpacity,
  Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';

import { useTheme, useLanguage } from '../context/AppContext';
import { Colors, Spacing } from '../utils/theme';
import {
  notificationService,
  NotificationSettings,
} from '../services/notifications';

export default function NotificationSettingsScreen() {
  const navigation = useNavigation();
  const { colors } = useTheme();
  const { t } = useLanguage();

  const [settings, setSettings] = useState<NotificationSettings>(
    notificationService.getSettings()
  );
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    const loaded = await notificationService.loadSettings();
    setSettings(loaded);
  };

  const updateSetting = async (
    key: keyof NotificationSettings,
    value: boolean | number
  ) => {
    setLoading(true);

    const newSettings = { ...settings, [key]: value };
    setSettings(newSettings);

    await notificationService.saveSettings({ [key]: value });

    // If main toggle is turned on, request permissions
    if (key === 'enabled' && value) {
      const token = await notificationService.registerForPushNotifications();
      if (!token) {
        Alert.alert(
          t('Permission Required', 'الإذن مطلوب'),
          t(
            'Please enable notifications in your device settings.',
            'يرجى تمكين الإشعارات في إعدادات جهازك.'
          )
        );
        setSettings({ ...newSettings, enabled: false });
        await notificationService.saveSettings({ enabled: false });
      }
    }

    setLoading(false);
  };

  const SettingToggle = ({
    icon,
    title,
    description,
    value,
    onChange,
    disabled = false,
  }: {
    icon: keyof typeof Ionicons.glyphMap;
    title: string;
    description: string;
    value: boolean;
    onChange: (value: boolean) => void;
    disabled?: boolean;
  }) => (
    <View
      style={[
        styles.settingRow,
        { borderBottomColor: colors.border },
        disabled && styles.settingRowDisabled,
      ]}
    >
      <View style={styles.settingIcon}>
        <Ionicons
          name={icon}
          size={22}
          color={disabled ? colors.muted : Colors.dark.gold}
        />
      </View>
      <View style={styles.settingContent}>
        <Text
          style={[
            styles.settingTitle,
            { color: disabled ? colors.muted : colors.text },
          ]}
        >
          {title}
        </Text>
        <Text style={[styles.settingDescription, { color: colors.muted }]}>
          {description}
        </Text>
      </View>
      <Switch
        value={value}
        onValueChange={onChange}
        disabled={disabled || loading}
        trackColor={{ false: colors.border, true: Colors.dark.gold }}
        thumbColor="#fff"
      />
    </View>
  );

  return (
    <SafeAreaView
      style={[styles.container, { backgroundColor: colors.background }]}
      edges={['top']}
    >
      {/* Header */}
      <View style={[styles.header, { borderBottomColor: colors.border }]}>
        <TouchableOpacity
          style={styles.backBtn}
          onPress={() => navigation.goBack()}
        >
          <Ionicons name="arrow-back" size={24} color={colors.text} />
        </TouchableOpacity>
        <Text style={[styles.headerTitle, { color: colors.text }]}>
          {t('Notifications', 'الإشعارات')}
        </Text>
        <View style={styles.backBtn} />
      </View>

      <ScrollView>
        {/* Master Toggle */}
        <View
          style={[
            styles.masterToggle,
            { backgroundColor: colors.card, borderColor: colors.border },
          ]}
        >
          <View style={styles.masterToggleContent}>
            <View
              style={[
                styles.masterIcon,
                { backgroundColor: Colors.dark.goldDim },
              ]}
            >
              <Ionicons
                name="notifications"
                size={28}
                color={Colors.dark.gold}
              />
            </View>
            <View style={styles.masterText}>
              <Text style={[styles.masterTitle, { color: colors.text }]}>
                {t('Push Notifications', 'الإشعارات الفورية')}
              </Text>
              <Text style={[styles.masterDescription, { color: colors.muted }]}>
                {settings.enabled
                  ? t('Notifications are enabled', 'الإشعارات مفعلة')
                  : t('Notifications are disabled', 'الإشعارات معطلة')}
              </Text>
            </View>
          </View>
          <Switch
            value={settings.enabled}
            onValueChange={(value) => updateSetting('enabled', value)}
            disabled={loading}
            trackColor={{ false: colors.border, true: Colors.dark.gold }}
            thumbColor="#fff"
            style={styles.masterSwitch}
          />
        </View>

        {/* Notification Types */}
        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: Colors.dark.gold }]}>
            {t('NOTIFICATION TYPES', 'أنواع الإشعارات')}
          </Text>
          <View
            style={[
              styles.sectionCard,
              { backgroundColor: colors.card, borderColor: colors.border },
            ]}
          >
            <SettingToggle
              icon="megaphone"
              title={t('Announcements', 'الإعلانات')}
              description={t(
                'Official announcements and updates',
                'الإعلانات والتحديثات الرسمية'
              )}
              value={settings.announcements}
              onChange={(value) => updateSetting('announcements', value)}
              disabled={!settings.enabled}
            />
            <SettingToggle
              icon="moon"
              title={t('Moon Sighting', 'رؤية الهلال')}
              description={t(
                'Moon sighting confirmations',
                'تأكيدات رؤية الهلال'
              )}
              value={settings.moonSightings}
              onChange={(value) => updateSetting('moonSightings', value)}
              disabled={!settings.enabled}
            />
            <SettingToggle
              icon="calendar"
              title={t('New Month', 'الشهر الجديد')}
              description={t(
                'Islamic month start notifications',
                'إشعارات بداية الأشهر الهجرية'
              )}
              value={settings.monthStart}
              onChange={(value) => updateSetting('monthStart', value)}
              disabled={!settings.enabled}
            />
            <SettingToggle
              icon="star"
              title={t('Islamic Events', 'المناسبات الإسلامية')}
              description={t(
                'Eid, Ramadan, and other events',
                'العيد ورمضان والمناسبات الأخرى'
              )}
              value={settings.islamicEvents}
              onChange={(value) => updateSetting('islamicEvents', value)}
              disabled={!settings.enabled}
            />
          </View>
        </View>

        {/* Prayer Reminders */}
        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: Colors.dark.gold }]}>
            {t('PRAYER REMINDERS', 'تذكير الصلاة')}
          </Text>
          <View
            style={[
              styles.sectionCard,
              { backgroundColor: colors.card, borderColor: colors.border },
            ]}
          >
            <SettingToggle
              icon="alarm"
              title={t('Prayer Reminders', 'تذكير الصلاة')}
              description={t(
                'Get notified before prayer times',
                'احصل على إشعار قبل أوقات الصلاة'
              )}
              value={settings.prayerReminders}
              onChange={(value) => updateSetting('prayerReminders', value)}
              disabled={!settings.enabled}
            />

            {settings.prayerReminders && settings.enabled && (
              <View
                style={[
                  styles.reminderTimeRow,
                  { borderTopColor: colors.border },
                ]}
              >
                <Text style={[styles.reminderTimeLabel, { color: colors.text }]}>
                  {t('Remind me', 'ذكرني')}
                </Text>
                <View style={styles.reminderTimeOptions}>
                  {[5, 10, 15, 30].map((mins) => (
                    <TouchableOpacity
                      key={mins}
                      style={[
                        styles.reminderTimeBtn,
                        {
                          backgroundColor:
                            settings.prayerReminderMinutes === mins
                              ? Colors.dark.gold
                              : colors.background,
                          borderColor:
                            settings.prayerReminderMinutes === mins
                              ? Colors.dark.gold
                              : colors.border,
                        },
                      ]}
                      onPress={() =>
                        updateSetting('prayerReminderMinutes', mins)
                      }
                    >
                      <Text
                        style={[
                          styles.reminderTimeBtnText,
                          {
                            color:
                              settings.prayerReminderMinutes === mins
                                ? '#fff'
                                : colors.text,
                          },
                        ]}
                      >
                        {mins} {t('min', 'د')}
                      </Text>
                    </TouchableOpacity>
                  ))}
                </View>
              </View>
            )}
          </View>
        </View>

        {/* Info */}
        <View style={styles.infoSection}>
          <Ionicons name="information-circle" size={18} color={colors.muted} />
          <Text style={[styles.infoText, { color: colors.muted }]}>
            {t(
              'Push notifications require an internet connection. Prayer reminders are scheduled locally on your device.',
              'الإشعارات الفورية تتطلب اتصالاً بالإنترنت. يتم جدولة تذكيرات الصلاة محلياً على جهازك.'
            )}
          </Text>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.base,
    paddingVertical: 12,
    borderBottomWidth: 1,
  },
  backBtn: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerTitle: {
    fontSize: 17,
    fontWeight: '600',
  },
  masterToggle: {
    margin: Spacing.base,
    padding: 20,
    borderRadius: 14,
    borderWidth: 1,
  },
  masterToggleContent: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  masterIcon: {
    width: 56,
    height: 56,
    borderRadius: 28,
    alignItems: 'center',
    justifyContent: 'center',
  },
  masterText: {
    flex: 1,
    marginLeft: 16,
  },
  masterTitle: {
    fontSize: 18,
    fontWeight: '700',
  },
  masterDescription: {
    fontSize: 13,
    marginTop: 2,
  },
  masterSwitch: {
    marginTop: 16,
    alignSelf: 'flex-start',
  },
  section: {
    marginTop: 8,
  },
  sectionTitle: {
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginHorizontal: Spacing.base,
    marginBottom: 8,
  },
  sectionCard: {
    marginHorizontal: Spacing.base,
    borderRadius: 14,
    borderWidth: 1,
    overflow: 'hidden',
  },
  settingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
  },
  settingRowDisabled: {
    opacity: 0.5,
  },
  settingIcon: {
    width: 36,
    alignItems: 'center',
  },
  settingContent: {
    flex: 1,
    marginLeft: 8,
    marginRight: 12,
  },
  settingTitle: {
    fontSize: 15,
    fontWeight: '600',
  },
  settingDescription: {
    fontSize: 12,
    marginTop: 2,
  },
  reminderTimeRow: {
    padding: 16,
    borderTopWidth: 1,
  },
  reminderTimeLabel: {
    fontSize: 14,
    fontWeight: '500',
    marginBottom: 12,
  },
  reminderTimeOptions: {
    flexDirection: 'row',
    gap: 8,
  },
  reminderTimeBtn: {
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 8,
    borderWidth: 1,
  },
  reminderTimeBtnText: {
    fontSize: 14,
    fontWeight: '600',
  },
  infoSection: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
    margin: Spacing.base,
    marginTop: 24,
    padding: 16,
  },
  infoText: {
    flex: 1,
    fontSize: 13,
    lineHeight: 20,
  },
});
