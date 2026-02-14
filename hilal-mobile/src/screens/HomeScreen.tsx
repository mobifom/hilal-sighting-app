/**
 * Home Screen
 * Displays today's Hijri date, countdown, quick actions, and recent updates
 * Matches wireframe design with dark navy hero and gold accents
 */

import React, { useEffect, useState, useCallback } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  RefreshControl,
  Dimensions,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';

import { useTheme, useLanguage, useHijriDate } from '../context/AppContext';
import { api } from '../api/client';
import { Colors, Spacing, BorderRadius, Typography, toArabicNumerals } from '../utils/theme';
import { RootStackParamList } from '../../App';

const { width } = Dimensions.get('window');

interface UpcomingMonth {
  month_number: number;
  month_name: string;
  month_name_ar: string;
  gregorian_start: string | null;
  status: string;
}

interface Announcement {
  id: number;
  title: string;
  title_ar: string;
  type: string;
  priority: string;
  published_date: string;
}

type NavigationProp = NativeStackNavigationProp<RootStackParamList>;

export default function HomeScreen() {
  const navigation = useNavigation<NavigationProp>();
  const { colors, theme } = useTheme();
  const { t, language, isRTL } = useLanguage();
  const { hijriDate, refreshHijriDate } = useHijriDate();

  const [refreshing, setRefreshing] = useState(false);
  const [upcomingMonths, setUpcomingMonths] = useState<UpcomingMonth[]>([]);
  const [announcements, setAnnouncements] = useState<Announcement[]>([]);
  const [latestAnnouncement, setLatestAnnouncement] = useState<Announcement | null>(null);
  const [countdown, setCountdown] = useState({ days: 0, hours: 0, minutes: 0 });

  useEffect(() => {
    loadData();
    // Update countdown every minute
    const timer = setInterval(updateCountdown, 60000);
    return () => clearInterval(timer);
  }, []);

  const loadData = async () => {
    try {
      const [calendarData, announcementsData, latestData] = await Promise.all([
        api.getCalendar(),
        api.getAnnouncements({ per_page: 4 }),
        api.getLatestAnnouncement(),
      ]);

      // Get upcoming months (current + next 4)
      if (calendarData?.months) {
        const currentMonth = hijriDate?.month || 1;
        const upcoming = calendarData.months
          .filter((m) => m.month_number >= currentMonth)
          .slice(0, 5);
        setUpcomingMonths(upcoming);
      }

      if (announcementsData?.announcements) {
        setAnnouncements(announcementsData.announcements);
      }

      if (latestData) {
        setLatestAnnouncement(latestData);
      }

      updateCountdown();
    } catch (error) {
      console.error('Error loading home data:', error);
    }
  };

  const updateCountdown = () => {
    // Calculate days until Ramadan (month 9)
    const currentMonth = hijriDate?.month || 1;
    const currentDay = hijriDate?.day || 1;

    if (currentMonth < 9) {
      let days = 30 - currentDay; // Remaining in current month
      for (let m = currentMonth + 1; m < 9; m++) {
        days += m % 2 === 0 ? 29 : 30;
      }

      const now = new Date();
      const hours = 23 - now.getHours();
      const minutes = 59 - now.getMinutes();

      setCountdown({ days, hours, minutes });
    }
  };

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    await Promise.all([refreshHijriDate(), loadData()]);
    setRefreshing(false);
  }, []);

  // Quick action cards data
  const quickActions = [
    {
      key: 'calendar',
      icon: 'calendar',
      title: t('Hijri Calendar', 'التقويم الهجري'),
      subtitle: t('Full year with confirmed dates', 'السنة الكاملة مع التواريخ المؤكدة'),
      colors: Colors.quickActions.calendar,
      onPress: () => navigation.navigate('Main', { screen: 'Calendar' } as never),
    },
    {
      key: 'announcements',
      icon: 'megaphone',
      title: t('Announcements', 'الإعلانات'),
      subtitle: t('Month confirmations & events', 'تأكيدات الأشهر والمناسبات'),
      colors: Colors.quickActions.announcements,
      onPress: () => navigation.navigate('Main', { screen: 'Announcements' } as never),
    },
    {
      key: 'sightings',
      icon: 'moon',
      title: t('Crescent Sightings', 'رؤية الهلال'),
      subtitle: t('Approved sightings from the community', 'رؤى معتمدة من المجتمع'),
      colors: Colors.quickActions.report,
      onPress: () => navigation.navigate('Main', { screen: 'Sightings' } as never),
    },
    {
      key: 'prayer',
      icon: 'time',
      title: t('Prayer Times', 'أوقات الصلاة'),
      subtitle: t('For New Zealand cities', 'لمدن نيوزيلندا'),
      colors: Colors.quickActions.prayerTimes,
      onPress: () => navigation.navigate('PrayerTimes'),
    },
  ];

  return (
    <ScrollView
      style={[styles.container, { backgroundColor: colors.background }]}
      refreshControl={
        <RefreshControl
          refreshing={refreshing}
          onRefresh={onRefresh}
          tintColor={Colors.dark.gold}
        />
      }
    >
      {/* Hero Section - Dark Navy with Gold */}
      <LinearGradient
        colors={['#1C2537', Colors.dark.background, Colors.dark.card]}
        style={styles.hero}
      >
        {/* Moon Icon */}
        <Text style={styles.moonIcon}>🌙</Text>

        {/* Hijri Date Display */}
        <View style={styles.dateDisplay}>
          <Text style={styles.hijriDay}>
            {hijriDate?.day || '—'}{' '}
            {language === 'ar' ? hijriDate?.monthNameAr : hijriDate?.monthNameEn}{' '}
            {hijriDate?.year || ''}
          </Text>
          <Text style={styles.hijriDayAr}>
            {language === 'ar'
              ? `${hijriDate?.day} ${hijriDate?.monthNameEn} ${hijriDate?.year} هـ`
              : `${toArabicNumerals(hijriDate?.day || '')} ${hijriDate?.monthNameAr} ${toArabicNumerals(hijriDate?.year || '')} هـ`}
          </Text>
        </View>

        {/* Gregorian Date */}
        <Text style={styles.gregorianDate}>
          {new Date().toLocaleDateString('en-US', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
          })}
        </Text>

        {/* Countdown to Ramadan */}
        {(hijriDate?.month || 1) < 9 && (
          <View style={styles.countdownWrapper}>
            <View style={styles.countdownItems}>
              <View style={styles.countdownItem}>
                <Text style={styles.countdownValue}>{countdown.days}</Text>
                <Text style={styles.countdownLabel}>{t('Days', 'يوم')}</Text>
              </View>
              <View style={styles.countdownSeparator} />
              <View style={styles.countdownItem}>
                <Text style={styles.countdownValue}>{countdown.hours}</Text>
                <Text style={styles.countdownLabel}>{t('Hours', 'ساعة')}</Text>
              </View>
              <View style={styles.countdownSeparator} />
              <View style={styles.countdownItem}>
                <Text style={styles.countdownValue}>{countdown.minutes}</Text>
                <Text style={styles.countdownLabel}>{t('Minutes', 'دقيقة')}</Text>
              </View>
            </View>
            <Text style={styles.countdownTitle}>
              {t(`UNTIL RAMADAN ${hijriDate?.year || ''}`, `حتى رمضان ${hijriDate?.year || ''}`)}
            </Text>
          </View>
        )}
      </LinearGradient>

      {/* Latest Announcement Banner */}
      {latestAnnouncement && (
        <TouchableOpacity
          style={styles.announcementBanner}
          onPress={() => navigation.navigate('AnnouncementDetail', { id: latestAnnouncement.id })}
        >
          <View style={styles.badgeNew}>
            <Text style={styles.badgeNewText}>{t('NEW', 'جديد')}</Text>
          </View>
          <View style={styles.announcementContent}>
            <Text style={styles.announcementTitle} numberOfLines={1}>
              {language === 'ar' ? latestAnnouncement.title_ar : latestAnnouncement.title} —
            </Text>
          </View>
          <Ionicons name="chevron-forward" size={18} color={Colors.light.muted} />
        </TouchableOpacity>
      )}

      {/* Quick Action Cards */}
      <View style={styles.section}>
        <View style={styles.quickActions}>
          {quickActions.map((action) => (
            <TouchableOpacity
              key={action.key}
              style={[
                styles.quickActionCard,
                {
                  backgroundColor: action.colors.bg,
                  borderColor: action.colors.border,
                },
              ]}
              onPress={action.onPress}
            >
              <Ionicons
                name={action.icon as keyof typeof Ionicons.glyphMap}
                size={24}
                color={Colors.light.text}
              />
              <Text style={styles.quickActionTitle}>{action.title}</Text>
              <Text style={styles.quickActionSubtitle}>{action.subtitle}</Text>
            </TouchableOpacity>
          ))}
        </View>
      </View>

      {/* Two Column Layout */}
      <View style={styles.section}>
        <View style={styles.twoColumns}>
          {/* Upcoming Months */}
          <View style={[styles.listCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <Text style={[styles.listTitle, { color: Colors.light.gold }]}>
              {t('Upcoming Months', 'الأشهر القادمة')}
            </Text>
            {upcomingMonths.map((month, index) => (
              <View
                key={month.month_number}
                style={[
                  styles.listItem,
                  index < upcomingMonths.length - 1 && {
                    borderBottomWidth: 1,
                    borderBottomColor: colors.border,
                  },
                ]}
              >
                <View style={styles.listItemLeft}>
                  <View
                    style={[
                      styles.monthNum,
                      month.month_number === hijriDate?.month && styles.monthNumCurrent,
                    ]}
                  >
                    <Text
                      style={[
                        styles.monthNumText,
                        month.month_number === hijriDate?.month && styles.monthNumTextCurrent,
                      ]}
                    >
                      {month.month_number}
                    </Text>
                  </View>
                  <View>
                    <Text style={[styles.monthName, { color: colors.text }]}>
                      {language === 'ar' ? month.month_name_ar : month.month_name}
                    </Text>
                    <Text style={[styles.monthNameAlt, { color: colors.muted }]}>
                      {language === 'ar' ? month.month_name : month.month_name_ar}
                    </Text>
                  </View>
                </View>
                <View style={styles.listItemRight}>
                  <Text style={[styles.monthDate, { color: colors.text }]}>
                    {month.gregorian_start
                      ? new Date(month.gregorian_start).toLocaleDateString('en-US', {
                          month: 'short',
                          day: 'numeric',
                        })
                      : '—'}
                  </Text>
                  <Text
                    style={[
                      styles.monthStatus,
                      {
                        color:
                          month.status === 'confirmed'
                            ? Colors.status.success
                            : month.status === 'pending_sighting'
                            ? Colors.status.warning
                            : colors.muted,
                      },
                    ]}
                  >
                    {month.status === 'confirmed'
                      ? t('✓ Confirmed', '✓ مؤكد')
                      : month.status === 'pending_sighting'
                      ? t('⏳ Pending', '⏳ في انتظار الرؤية')
                      : t('Estimated', 'تقديري')}
                  </Text>
                </View>
              </View>
            ))}
          </View>

          {/* Recent Announcements */}
          <View style={[styles.listCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <Text style={[styles.listTitle, { color: Colors.light.gold }]}>
              {t('Recent Announcements', 'أحدث الإعلانات')}
            </Text>
            {announcements.slice(0, 4).map((ann, index) => (
              <TouchableOpacity
                key={ann.id}
                style={[
                  styles.listItem,
                  index < 3 && {
                    borderBottomWidth: 1,
                    borderBottomColor: colors.border,
                  },
                ]}
                onPress={() => navigation.navigate('AnnouncementDetail', { id: ann.id })}
              >
                <View style={styles.listItemLeft}>
                  <View
                    style={[
                      styles.priorityDot,
                      {
                        backgroundColor:
                          ann.priority === 'high'
                            ? Colors.status.danger
                            : ann.priority === 'medium'
                            ? Colors.status.warning
                            : Colors.status.success,
                      },
                    ]}
                  />
                  <View style={{ flex: 1 }}>
                    <Text style={[styles.annTitle, { color: colors.text }]} numberOfLines={1}>
                      {language === 'ar' ? ann.title_ar : ann.title}
                    </Text>
                  </View>
                </View>
                <Text style={[styles.annDate, { color: colors.muted }]}>
                  {new Date(ann.published_date).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                  })}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>
      </View>

      {/* Subscribe Section */}
      <LinearGradient
        colors={['#1C2537', Colors.dark.card]}
        style={styles.subscribeSection}
      >
        <Text style={styles.subscribeIcon}>🔔</Text>
        <Text style={styles.subscribeTitle}>
          {t('Never Miss a Moon Sighting', 'لا تفوت رؤية الهلال')}
        </Text>
        <Text style={styles.subscribeText}>
          {t(
            'Subscribe to get instant push notifications for new month confirmations',
            'اشترك للحصول على إشعارات فورية عند تأكيد بداية الأشهر الجديدة'
          )}
        </Text>
        <View style={styles.subscribeButtons}>
          <TouchableOpacity style={styles.subscribeBtn}>
            <Ionicons name="phone-portrait" size={14} color="#fff" />
            <Text style={styles.subscribeBtnText}>{t('Get the App', 'حمل التطبيق')}</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.subscribeBtn}>
            <Ionicons name="mail" size={14} color="#fff" />
            <Text style={styles.subscribeBtnText}>{t('Email Alerts', 'تنبيهات البريد')}</Text>
          </TouchableOpacity>
        </View>
      </LinearGradient>

      <View style={{ height: 40 }} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  hero: {
    paddingTop: 60,
    paddingBottom: 40,
    paddingHorizontal: Spacing.lg,
    alignItems: 'center',
  },
  moonIcon: {
    fontSize: 48,
    marginBottom: Spacing.sm,
  },
  dateDisplay: {
    alignItems: 'center',
    marginBottom: Spacing.sm,
  },
  hijriDay: {
    fontSize: 26,
    fontWeight: '800',
    color: Colors.dark.gold,
    textAlign: 'center',
  },
  hijriDayAr: {
    fontSize: 18,
    color: 'rgba(255,255,255,0.7)',
    marginTop: 4,
    textAlign: 'center',
  },
  gregorianDate: {
    fontSize: 14,
    color: Colors.dark.muted,
    marginTop: Spacing.sm,
  },
  countdownWrapper: {
    marginTop: 28,
    backgroundColor: Colors.dark.goldDim,
    borderRadius: 16,
    paddingVertical: 18,
    paddingHorizontal: 36,
    borderWidth: 1,
    borderColor: Colors.dark.goldBorder,
    alignItems: 'center',
  },
  countdownItems: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 28,
  },
  countdownItem: {
    alignItems: 'center',
  },
  countdownValue: {
    color: Colors.dark.gold,
    fontSize: 36,
    fontWeight: '800',
  },
  countdownLabel: {
    color: Colors.dark.muted,
    fontSize: 11,
    textTransform: 'uppercase',
    letterSpacing: 1,
  },
  countdownSeparator: {
    width: 1,
    height: 40,
    backgroundColor: Colors.dark.goldBorder,
  },
  countdownTitle: {
    color: Colors.dark.gold,
    fontSize: 13,
    fontWeight: '600',
    marginTop: 12,
    letterSpacing: 0.5,
  },
  announcementBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.light.goldLight,
    borderBottomWidth: 1,
    borderBottomColor: Colors.light.goldBorder,
    paddingVertical: 12,
    paddingHorizontal: 16,
    gap: 10,
  },
  badgeNew: {
    backgroundColor: Colors.light.gold,
    paddingHorizontal: 10,
    paddingVertical: 3,
    borderRadius: 20,
  },
  badgeNewText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  announcementContent: {
    flex: 1,
  },
  announcementTitle: {
    color: Colors.light.text,
    fontSize: 14,
    fontWeight: '600',
  },
  section: {
    paddingHorizontal: Spacing.base,
    paddingTop: Spacing.lg,
  },
  quickActions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  quickActionCard: {
    width: (width - 44) / 2,
    padding: 20,
    borderRadius: 14,
    borderWidth: 1,
  },
  quickActionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: Colors.light.text,
    marginTop: 10,
  },
  quickActionSubtitle: {
    fontSize: 12,
    color: Colors.light.muted,
    marginTop: 4,
    lineHeight: 18,
  },
  twoColumns: {
    gap: 16,
  },
  listCard: {
    borderRadius: 14,
    borderWidth: 1,
    padding: 16,
  },
  listTitle: {
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 12,
  },
  listItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
  },
  listItemLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    flex: 1,
  },
  listItemRight: {
    alignItems: 'flex-end',
  },
  monthNum: {
    width: 30,
    height: 30,
    borderRadius: 15,
    backgroundColor: '#F0F0EC',
    justifyContent: 'center',
    alignItems: 'center',
  },
  monthNumCurrent: {
    backgroundColor: Colors.light.goldLight,
  },
  monthNumText: {
    fontSize: 11,
    fontWeight: '700',
    color: Colors.light.muted,
  },
  monthNumTextCurrent: {
    color: Colors.light.gold,
  },
  monthName: {
    fontSize: 14,
    fontWeight: '600',
  },
  monthNameAlt: {
    fontSize: 13,
  },
  monthDate: {
    fontSize: 13,
  },
  monthStatus: {
    fontSize: 10,
    marginTop: 2,
  },
  priorityDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  annTitle: {
    fontSize: 14,
    fontWeight: '600',
  },
  annDate: {
    fontSize: 11,
  },
  subscribeSection: {
    margin: Spacing.base,
    borderRadius: 16,
    padding: 32,
    alignItems: 'center',
  },
  subscribeIcon: {
    fontSize: 28,
    marginBottom: 8,
  },
  subscribeTitle: {
    color: '#fff',
    fontSize: 20,
    fontWeight: '700',
    textAlign: 'center',
  },
  subscribeText: {
    color: Colors.dark.muted,
    fontSize: 13,
    textAlign: 'center',
    marginTop: 6,
    marginBottom: 20,
  },
  subscribeButtons: {
    flexDirection: 'row',
    gap: 16,
  },
  subscribeBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: 'rgba(255,255,255,0.08)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.15)',
    borderRadius: 8,
    paddingVertical: 8,
    paddingHorizontal: 16,
  },
  subscribeBtnText: {
    color: '#fff',
    fontSize: 12,
  },
});
