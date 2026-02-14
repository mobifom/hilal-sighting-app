/**
 * Calendar Screen
 * Displays the full Hijri calendar year with month cards
 * Matches wireframe design
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
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';

import { useTheme, useLanguage, useHijriDate } from '../context/AppContext';
import { api } from '../api/client';
import { Colors, Spacing, BorderRadius } from '../utils/theme';

const { width } = Dimensions.get('window');
const CARD_WIDTH = (width - 48) / 2;

interface CalendarMonth {
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
}

export default function CalendarScreen() {
  const { colors, theme } = useTheme();
  const { t, language } = useLanguage();
  const { hijriDate } = useHijriDate();

  const [refreshing, setRefreshing] = useState(false);
  const [selectedYear, setSelectedYear] = useState(hijriDate?.year || 1446);
  const [months, setMonths] = useState<CalendarMonth[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadCalendar();
  }, [selectedYear]);

  const loadCalendar = async () => {
    try {
      setLoading(true);
      const data = await api.getCalendar(selectedYear);
      if (data?.months) {
        setMonths(data.months);
      }
    } catch (error) {
      console.error('Error loading calendar:', error);
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    await loadCalendar();
    setRefreshing(false);
  }, [selectedYear]);

  const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  };

  const isCurrentMonth = (monthNum: number) => {
    return selectedYear === hijriDate?.year && monthNum === hijriDate?.month;
  };

  const isPastMonth = (monthNum: number) => {
    return (
      selectedYear < (hijriDate?.year || 0) ||
      (selectedYear === hijriDate?.year && monthNum < (hijriDate?.month || 0))
    );
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* Header */}
      <View style={[styles.header, { borderBottomColor: colors.border }]}>
        <Text style={[styles.headerTitle, { color: colors.text }]}>
          {t('Hijri Calendar', 'التقويم الهجري')} — {selectedYear}{' '}
          {language === 'ar' ? 'هـ' : 'AH'}
        </Text>
        <Text style={[styles.headerSubtitle, { color: colors.muted }]}>
          {t('Complete year with Gregorian dates', 'السنة الكاملة مع التواريخ الميلادية')}
        </Text>
      </View>

      {/* Year Navigation */}
      <View style={styles.yearNav}>
        <TouchableOpacity
          style={[styles.yearNavBtn, { backgroundColor: colors.card, borderColor: colors.border }]}
          onPress={() => setSelectedYear(selectedYear - 1)}
        >
          <Ionicons name="chevron-back" size={18} color={colors.muted} />
          <Text style={[styles.yearNavBtnText, { color: colors.muted }]}>
            {t('Previous', 'السابقة')}
          </Text>
        </TouchableOpacity>

        <View style={styles.yearDisplay}>
          <Text style={[styles.yearText, { color: Colors.dark.gold }]}>
            {selectedYear} {language === 'ar' ? 'هـ' : 'AH'}
          </Text>
        </View>

        <TouchableOpacity
          style={[styles.yearNavBtn, { backgroundColor: colors.card, borderColor: colors.border }]}
          onPress={() => setSelectedYear(selectedYear + 1)}
        >
          <Text style={[styles.yearNavBtnText, { color: colors.muted }]}>
            {t('Next', 'القادمة')}
          </Text>
          <Ionicons name="chevron-forward" size={18} color={colors.muted} />
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={Colors.dark.gold}
          />
        }
      >
        {/* Calendar Grid */}
        <View style={styles.grid}>
          {months.map((month) => {
            const isCurrent = isCurrentMonth(month.month_number);
            const isPast = isPastMonth(month.month_number);

            return (
              <View
                key={month.month_number}
                style={[
                  styles.monthCard,
                  {
                    backgroundColor: isCurrent ? Colors.light.goldLight : colors.card,
                    borderColor: isCurrent ? Colors.light.goldBorder : colors.border,
                  },
                ]}
              >
                {/* Current Month Badge */}
                {isCurrent && (
                  <View style={styles.currentBadge}>
                    <Text style={styles.currentBadgeText}>
                      {t('Current', 'الحالي')}
                    </Text>
                  </View>
                )}

                {/* Month Header */}
                <View style={styles.monthHeader}>
                  <View
                    style={[
                      styles.monthNum,
                      isCurrent && styles.monthNumCurrent,
                    ]}
                  >
                    <Text
                      style={[
                        styles.monthNumText,
                        { color: isCurrent ? '#fff' : colors.muted },
                      ]}
                    >
                      {month.month_number}
                    </Text>
                  </View>
                  <View style={styles.monthNames}>
                    <Text style={[styles.monthName, { color: colors.text }]}>
                      {language === 'ar' ? month.month_name_ar : month.month_name_en}
                    </Text>
                    <Text style={[styles.monthNameAlt, { color: colors.muted }]}>
                      {language === 'ar' ? month.month_name_en : month.month_name_ar}
                    </Text>
                  </View>
                </View>

                {/* Month Details */}
                <View style={[styles.monthDetails, { borderTopColor: isCurrent ? Colors.light.goldBorder : colors.border }]}>
                  <View>
                    <Text style={[styles.detailLabel, { color: colors.muted }]}>
                      {t('Starts', 'يبدأ')}
                    </Text>
                    <Text style={[styles.detailValue, { color: colors.text }]}>
                      {formatDate(month.gregorian_start)}
                    </Text>
                  </View>
                  <View style={{ alignItems: 'flex-end' }}>
                    <Text style={[styles.detailLabel, { color: colors.muted }]}>
                      {t('Duration', 'المدة')}
                    </Text>
                    <Text style={[styles.detailValue, { color: colors.text }]}>
                      {month.days_count || '—'} {t('days', 'يوم')}
                    </Text>
                  </View>
                </View>

                {/* Status */}
                <View style={styles.statusRow}>
                  {month.status === 'confirmed' ? (
                    <>
                      <Ionicons name="checkmark-circle" size={14} color={Colors.status.success} />
                      <Text style={[styles.statusText, { color: Colors.status.success }]}>
                        {t('Confirmed by sighting', 'مؤكد بالرؤية')}
                      </Text>
                    </>
                  ) : month.status === 'pending_sighting' ? (
                    <>
                      <Ionicons name="eye" size={14} color={Colors.status.warning} />
                      <Text style={[styles.statusText, { color: Colors.status.warning }]}>
                        {t('Pending sighting', 'في انتظار الرؤية')}
                      </Text>
                    </>
                  ) : (
                    <>
                      <Ionicons name="calculator" size={14} color={colors.muted} />
                      <Text style={[styles.statusText, { color: colors.muted }]}>
                        {t('Estimated (Umm al-Qura)', 'تقديري (أم القرى)')}
                      </Text>
                    </>
                  )}
                </View>

                {/* Events */}
                {month.events && month.events.length > 0 && (
                  <View style={[styles.eventsSection, { borderTopColor: colors.border }]}>
                    {month.events.slice(0, 2).map((event) => (
                      <View
                        key={event.id}
                        style={[
                          styles.eventTag,
                          {
                            backgroundColor:
                              event.category === 'eid' || event.category === 'religious'
                                ? Colors.light.goldLight
                                : 'rgba(72, 176, 122, 0.1)',
                          },
                        ]}
                      >
                        <Text
                          style={[
                            styles.eventTagText,
                            {
                              color:
                                event.category === 'eid' || event.category === 'religious'
                                  ? Colors.light.gold
                                  : Colors.status.success,
                            },
                          ]}
                          numberOfLines={1}
                        >
                          {event.hijri_day} - {event.name}
                        </Text>
                      </View>
                    ))}
                  </View>
                )}
              </View>
            );
          })}
        </View>

        {/* Legend */}
        <View style={[styles.legend, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <View style={styles.legendItem}>
            <Ionicons name="checkmark-circle" size={16} color={Colors.status.success} />
            <Text style={[styles.legendText, { color: colors.text }]}>
              {t('Confirmed', 'مؤكد')}
            </Text>
            <Text style={[styles.legendDesc, { color: colors.muted }]}>
              {t('Moon sighted', 'تم رؤية الهلال')}
            </Text>
          </View>
          <View style={styles.legendItem}>
            <Ionicons name="eye" size={16} color={Colors.status.warning} />
            <Text style={[styles.legendText, { color: colors.text }]}>
              {t('Pending', 'في الانتظار')}
            </Text>
            <Text style={[styles.legendDesc, { color: colors.muted }]}>
              {t('Awaiting sighting', 'في انتظار رؤية الهلال')}
            </Text>
          </View>
          <View style={styles.legendItem}>
            <Ionicons name="calculator" size={16} color={colors.muted} />
            <Text style={[styles.legendText, { color: colors.text }]}>
              {t('Estimated', 'تقديري')}
            </Text>
            <Text style={[styles.legendDesc, { color: colors.muted }]}>
              {t('Calculated estimates', 'تقديرات حسابية')}
            </Text>
          </View>
        </View>

        <View style={{ height: 40 }} />
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    paddingHorizontal: Spacing.base,
    paddingVertical: Spacing.lg,
    borderBottomWidth: 1,
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '800',
  },
  headerSubtitle: {
    fontSize: 14,
    marginTop: 4,
  },
  yearNav: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: Spacing.base,
    paddingVertical: Spacing.md,
  },
  yearNavBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: BorderRadius.md,
    borderWidth: 1,
  },
  yearNavBtnText: {
    fontSize: 13,
    fontWeight: '500',
  },
  yearDisplay: {
    alignItems: 'center',
  },
  yearText: {
    fontSize: 20,
    fontWeight: '700',
  },
  scrollView: {
    flex: 1,
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    paddingHorizontal: Spacing.base,
    gap: 12,
    justifyContent: 'center',
  },
  monthCard: {
    width: CARD_WIDTH,
    borderRadius: 14,
    borderWidth: 1,
    padding: 16,
    position: 'relative',
  },
  currentBadge: {
    position: 'absolute',
    top: 8,
    right: 10,
    backgroundColor: Colors.light.gold,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 10,
  },
  currentBadgeText: {
    color: '#fff',
    fontSize: 9,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  monthHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 12,
  },
  monthNum: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: '#F0F0EC',
    justifyContent: 'center',
    alignItems: 'center',
  },
  monthNumCurrent: {
    backgroundColor: Colors.light.gold,
  },
  monthNumText: {
    fontSize: 14,
    fontWeight: '800',
  },
  monthNames: {
    flex: 1,
  },
  monthName: {
    fontSize: 14,
    fontWeight: '700',
  },
  monthNameAlt: {
    fontSize: 13,
  },
  monthDetails: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingTop: 12,
    borderTopWidth: 1,
  },
  detailLabel: {
    fontSize: 10,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  detailValue: {
    fontSize: 14,
    fontWeight: '600',
    marginTop: 2,
  },
  statusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 10,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  eventsSection: {
    marginTop: 10,
    paddingTop: 10,
    borderTopWidth: 1,
    gap: 4,
  },
  eventTag: {
    paddingVertical: 4,
    paddingHorizontal: 8,
    borderRadius: 6,
  },
  eventTagText: {
    fontSize: 11,
    fontWeight: '500',
  },
  legend: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 32,
    marginHorizontal: Spacing.base,
    marginTop: Spacing.xl,
    paddingVertical: Spacing.md,
    borderRadius: 14,
    borderWidth: 1,
  },
  legendItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  legendText: {
    fontSize: 13,
    fontWeight: '500',
  },
  legendDesc: {
    fontSize: 11,
  },
});
