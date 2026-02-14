/**
 * Prayer Times Screen
 * Displays prayer times for selected mosque or current location
 */

import React, { useEffect, useState, useCallback, useMemo, useRef } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  RefreshControl,
  ActivityIndicator,
  TextInput,
  Modal,
  FlatList,
  Animated,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import * as Location from 'expo-location';

import { useTheme, useLanguage } from '../context/AppContext';
import { api } from '../api/client';
import { Colors, Spacing, PrayerNames } from '../utils/theme';
import { NZ_MOSQUES, NZ_REGIONS, getMosquesByRegion, Mosque } from '../data/nzMosques';

interface PrayerTimes {
  fajr: string;
  sunrise: string;
  dhuhr: string;
  asr: string;
  maghrib: string;
  isha: string;
}

interface IqamaTimes {
  fajr: string;
  dhuhr: string;
  asr: string;
  maghrib: string;
  isha: string;
}

export default function PrayerTimesScreen() {
  const { colors } = useTheme();
  const { t, language } = useLanguage();

  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [selectedMosque, setSelectedMosque] = useState<Mosque | null>(null);
  const [prayerTimes, setPrayerTimes] = useState<PrayerTimes | null>(null);
  const [iqamaTimes, setIqamaTimes] = useState<IqamaTimes | null>(null);
  const [nextPrayer, setNextPrayer] = useState<{ name: string; time: string; minutes_until: number } | null>(null);
  const [countdown, setCountdown] = useState<{ hours: number; minutes: number; seconds: number } | null>(null);
  const [source, setSource] = useState<'my-masjid' | 'calculation' | 'location'>('calculation');
  const [showMosquePicker, setShowMosquePicker] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [useLocation, setUseLocation] = useState(false);

  const countdownRef = useRef<NodeJS.Timeout | null>(null);
  const pulseAnim = useRef(new Animated.Value(1)).current;

  // Set default mosque (Masjid e Umar - popular Auckland mosque)
  useEffect(() => {
    const defaultMosque = NZ_MOSQUES.find(m => m.id === 'masjid-e-umar') || NZ_MOSQUES[0];
    setSelectedMosque(defaultMosque);
  }, []);

  // Live countdown timer
  useEffect(() => {
    if (nextPrayer?.time && prayerTimes) {
      // Clear existing interval
      if (countdownRef.current) {
        clearInterval(countdownRef.current);
      }

      const updateCountdown = () => {
        const now = new Date();
        const [hours, minutes] = nextPrayer.time.split(':').map(Number);
        const prayerTime = new Date();
        prayerTime.setHours(hours, minutes, 0, 0);

        // If prayer time has passed, it might be for tomorrow
        if (prayerTime <= now) {
          prayerTime.setDate(prayerTime.getDate() + 1);
        }

        const diff = prayerTime.getTime() - now.getTime();
        if (diff > 0) {
          const totalSeconds = Math.floor(diff / 1000);
          const hrs = Math.floor(totalSeconds / 3600);
          const mins = Math.floor((totalSeconds % 3600) / 60);
          const secs = totalSeconds % 60;
          setCountdown({ hours: hrs, minutes: mins, seconds: secs });
        } else {
          setCountdown(null);
        }
      };

      // Update immediately and then every second
      updateCountdown();
      countdownRef.current = setInterval(updateCountdown, 1000);

      return () => {
        if (countdownRef.current) {
          clearInterval(countdownRef.current);
        }
      };
    }
  }, [nextPrayer, prayerTimes]);

  // Pulse animation for countdown when under 5 minutes
  useEffect(() => {
    if (countdown && countdown.hours === 0 && countdown.minutes < 5) {
      const pulse = Animated.loop(
        Animated.sequence([
          Animated.timing(pulseAnim, {
            toValue: 1.05,
            duration: 500,
            useNativeDriver: true,
          }),
          Animated.timing(pulseAnim, {
            toValue: 1,
            duration: 500,
            useNativeDriver: true,
          }),
        ])
      );
      pulse.start();
      return () => pulse.stop();
    }
  }, [countdown]);

  useEffect(() => {
    if (selectedMosque && !useLocation) {
      loadPrayerTimes();
    }
  }, [selectedMosque]);

  const loadPrayerTimes = async () => {
    if (!selectedMosque) return;

    try {
      setLoading(true);
      const data = await api.getMosquePrayerTimes({
        id: selectedMosque.id,
        lat: selectedMosque.lat,
        lng: selectedMosque.lng,
        myMasjidId: selectedMosque.myMasjidId,
      });

      setPrayerTimes(data.times);
      setIqamaTimes(data.iqamaTimes || null);
      setNextPrayer(data.next_prayer);
      setSource(data.source);
      setUseLocation(false);
    } catch {
      // Fallback to basic calculation if API fails
      loadLocationPrayerTimes(selectedMosque.lat, selectedMosque.lng);
    } finally {
      setLoading(false);
    }
  };

  const loadLocationPrayerTimes = async (lat: number, lng: number) => {
    try {
      setLoading(true);
      const data = await api.getPrayerTimes({ lat, lng });
      setPrayerTimes(data.times);
      setIqamaTimes(null);
      setNextPrayer(data.next_prayer);
      setSource('location');
    } catch (error) {
      console.error('Error loading location prayer times:', error);
    } finally {
      setLoading(false);
    }
  };

  const useCurrentLocation = async () => {
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') return;

      setLoading(true);
      setUseLocation(true);
      setSelectedMosque(null);

      const location = await Location.getCurrentPositionAsync({});
      await loadLocationPrayerTimes(location.coords.latitude, location.coords.longitude);
    } catch (error) {
      console.error('Error getting location:', error);
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    if (useLocation) {
      const location = await Location.getCurrentPositionAsync({});
      await loadLocationPrayerTimes(location.coords.latitude, location.coords.longitude);
    } else if (selectedMosque) {
      await loadPrayerTimes();
    }
    setRefreshing(false);
  }, [selectedMosque, useLocation]);

  const formatCountdown = () => {
    if (!countdown) return '';
    const { hours, minutes, seconds } = countdown;
    if (language === 'ar') {
      if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
      }
      return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }
    if (hours > 0) {
      return `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
  };

  const isCountdownUrgent = countdown && countdown.hours === 0 && countdown.minutes < 5;

  // Filter mosques based on search
  const filteredMosques = useMemo(() => {
    if (!searchQuery.trim()) return NZ_MOSQUES;
    const query = searchQuery.toLowerCase();
    return NZ_MOSQUES.filter(
      m =>
        m.name.toLowerCase().includes(query) ||
        m.nameAr.includes(searchQuery) ||
        m.city.toLowerCase().includes(query) ||
        m.address.toLowerCase().includes(query)
    );
  }, [searchQuery]);

  // Group mosques by region for display
  const groupedMosques = useMemo(() => {
    const groups: { region: string; mosques: Mosque[] }[] = [];
    NZ_REGIONS.forEach(region => {
      const regionMosques = filteredMosques.filter(m => m.region === region);
      if (regionMosques.length > 0) {
        groups.push({ region, mosques: regionMosques });
      }
    });
    return groups;
  }, [filteredMosques]);

  const prayers = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'] as const;

  const getLocationName = () => {
    if (useLocation) {
      return t('Your Location', 'موقعك الحالي');
    }
    if (selectedMosque) {
      return language === 'ar' ? selectedMosque.nameAr : selectedMosque.name;
    }
    return t('Select a Mosque', 'اختر مسجداً');
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['bottom']}>
      <ScrollView
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={Colors.dark.gold} />
        }
      >
        {/* Mosque Selector */}
        <View style={styles.mosqueSelector}>
          <Text style={[styles.selectorLabel, { color: colors.muted }]}>
            {t('SELECT MOSQUE', 'اختر المسجد')}
          </Text>
          <TouchableOpacity
            style={[styles.mosqueButton, { backgroundColor: colors.card, borderColor: colors.border }]}
            onPress={() => setShowMosquePicker(true)}
          >
            <Ionicons name="business" size={18} color={Colors.dark.gold} />
            <View style={styles.mosqueButtonContent}>
              <Text style={[styles.mosqueButtonText, { color: colors.text }]} numberOfLines={1}>
                {getLocationName()}
              </Text>
              {selectedMosque && (
                <Text style={[styles.mosqueAddress, { color: colors.muted }]} numberOfLines={1}>
                  {selectedMosque.address}, {selectedMosque.city}
                </Text>
              )}
            </View>
            <Ionicons name="chevron-down" size={18} color={colors.muted} />
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.locationBtn, { backgroundColor: colors.card, borderColor: colors.border }]}
            onPress={useCurrentLocation}
          >
            <Ionicons name="locate" size={16} color={Colors.dark.gold} />
            <Text style={[styles.locationBtnText, { color: colors.muted }]}>
              {t('Use My Location', 'استخدم موقعي')}
            </Text>
          </TouchableOpacity>

          {/* Source indicator */}
          {!loading && source && (
            <View style={styles.sourceIndicator}>
              <Ionicons
                name={source === 'my-masjid' ? 'checkmark-circle' : 'calculator'}
                size={14}
                color={source === 'my-masjid' ? Colors.status.success : colors.muted}
              />
              <Text style={[styles.sourceText, { color: colors.muted }]}>
                {source === 'my-masjid'
                  ? t('Times from mosque (my-masjid.com)', 'الأوقات من المسجد')
                  : t('Calculated times (MWL)', 'أوقات محسوبة')}
              </Text>
            </View>
          )}
        </View>

        {/* Prayer Times Card */}
        <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <View style={styles.cardHeader}>
            <Text style={[styles.dateText, { color: colors.muted }]}>
              {new Date().toLocaleDateString(language === 'ar' ? 'ar-SA' : 'en-US', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
              })}
            </Text>
          </View>

          {loading ? (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color={Colors.dark.gold} />
            </View>
          ) : (
            <View style={styles.prayerList}>
              {/* Header row for Iqama times */}
              {iqamaTimes && (
                <View style={[styles.headerRow, { borderBottomColor: colors.border }]}>
                  <Text style={[styles.headerLabel, { color: colors.muted }]}>
                    {t('Prayer', 'الصلاة')}
                  </Text>
                  <Text style={[styles.headerLabel, { color: colors.muted }]}>
                    {t('Adhan', 'الأذان')}
                  </Text>
                  <Text style={[styles.headerLabel, { color: Colors.dark.gold }]}>
                    {t('Iqama', 'الإقامة')}
                  </Text>
                </View>
              )}

              {prayers.map((prayer, index) => {
                const isNext = nextPrayer?.name === prayer;
                const prayerName = language === 'ar' ? PrayerNames.ar[prayer] : PrayerNames.en[prayer];
                const time = prayerTimes?.[prayer] || '--:--';
                const iqamaTime = prayer !== 'sunrise' && iqamaTimes ? iqamaTimes[prayer as keyof IqamaTimes] : null;

                return (
                  <View
                    key={prayer}
                    style={[
                      styles.prayerItem,
                      isNext && styles.prayerItemNext,
                      index < prayers.length - 1 && {
                        borderBottomWidth: 1,
                        borderBottomColor: colors.border,
                      },
                    ]}
                  >
                    <Text
                      style={[
                        styles.prayerName,
                        { color: isNext ? Colors.dark.gold : colors.text },
                      ]}
                    >
                      {prayerName}
                    </Text>
                    <Text
                      style={[
                        styles.prayerTime,
                        { color: isNext ? Colors.dark.gold : colors.text },
                        iqamaTimes && styles.prayerTimeWithIqama,
                      ]}
                    >
                      {time}
                    </Text>
                    {iqamaTimes && (
                      <Text
                        style={[
                          styles.iqamaTime,
                          { color: isNext ? Colors.dark.gold : Colors.dark.gold },
                        ]}
                      >
                        {iqamaTime || '-'}
                      </Text>
                    )}
                  </View>
                );
              })}
            </View>
          )}

          {/* Next Prayer Info */}
          {nextPrayer && (
            <View style={[styles.nextPrayerInfo, { backgroundColor: colors.background }]}>
              <View style={styles.nextPrayerHeader}>
                <Ionicons name="time-outline" size={16} color={colors.muted} />
                <Text style={[styles.nextPrayerLabel, { color: colors.muted }]}>
                  {t('Next Prayer', 'الصلاة القادمة')}
                </Text>
              </View>
              <Text style={[styles.nextPrayerName, { color: Colors.dark.gold }]}>
                {language === 'ar'
                  ? PrayerNames.ar[nextPrayer.name as keyof typeof PrayerNames.ar]
                  : PrayerNames.en[nextPrayer.name as keyof typeof PrayerNames.en]}
              </Text>
              <Text style={[styles.nextPrayerTime, { color: colors.text }]}>
                {nextPrayer.time}
              </Text>
              {countdown && (
                <Animated.View
                  style={[
                    styles.countdownContainer,
                    isCountdownUrgent && styles.countdownUrgent,
                    { transform: [{ scale: pulseAnim }] },
                  ]}
                >
                  <Text style={[styles.countdownText, isCountdownUrgent && styles.countdownTextUrgent]}>
                    {formatCountdown()}
                  </Text>
                  <Text style={[styles.countdownLabel, isCountdownUrgent && styles.countdownLabelUrgent]}>
                    {t('remaining', 'متبقي')}
                  </Text>
                </Animated.View>
              )}
            </View>
          )}
        </View>

        {/* Mosque Info */}
        {selectedMosque && !useLocation && (
          <View style={[styles.mosqueInfo, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <View style={styles.mosqueInfoHeader}>
              <Ionicons name="location" size={18} color={Colors.dark.gold} />
              <Text style={[styles.mosqueInfoTitle, { color: colors.text }]}>
                {t('Mosque Address', 'عنوان المسجد')}
              </Text>
            </View>
            <Text style={[styles.mosqueInfoText, { color: colors.muted }]}>
              {selectedMosque.address}
            </Text>
            <Text style={[styles.mosqueInfoText, { color: colors.muted }]}>
              {selectedMosque.city}, New Zealand
            </Text>
            {selectedMosque.phone && (
              <View style={styles.mosqueInfoRow}>
                <Ionicons name="call" size={14} color={colors.muted} />
                <Text style={[styles.mosqueInfoText, { color: colors.muted }]}>
                  {selectedMosque.phone}
                </Text>
              </View>
            )}
            {selectedMosque.hasIqamaTimes && (
              <View style={[styles.myMasjidBadge, { backgroundColor: Colors.light.goldLight }]}>
                <Text style={[styles.myMasjidBadgeText, { color: Colors.dark.gold }]}>
                  {t('Iqama times available via my-masjid.com', 'أوقات الإقامة متوفرة')}
                </Text>
              </View>
            )}
          </View>
        )}

        {/* Calculation Method Info */}
        <Text style={[styles.methodInfo, { color: colors.muted }]}>
          {source === 'my-masjid'
            ? t('Prayer times provided by the mosque', 'أوقات الصلاة مقدمة من المسجد')
            : t('Calculation Method: Muslim World League (MWL)', 'طريقة الحساب: رابطة العالم الإسلامي')}
        </Text>
      </ScrollView>

      {/* Mosque Picker Modal */}
      <Modal
        visible={showMosquePicker}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setShowMosquePicker(false)}
      >
        <SafeAreaView style={[styles.modalContainer, { backgroundColor: colors.background }]}>
          {/* Modal Header */}
          <View style={[styles.modalHeader, { borderBottomColor: colors.border }]}>
            <TouchableOpacity onPress={() => setShowMosquePicker(false)}>
              <Text style={[styles.modalCancel, { color: Colors.dark.gold }]}>
                {t('Cancel', 'إلغاء')}
              </Text>
            </TouchableOpacity>
            <Text style={[styles.modalTitle, { color: colors.text }]}>
              {t('Select Mosque', 'اختر المسجد')}
            </Text>
            <View style={{ width: 60 }} />
          </View>

          {/* Search Input */}
          <View style={styles.searchContainer}>
            <View style={[styles.searchInput, { backgroundColor: colors.card, borderColor: colors.border }]}>
              <Ionicons name="search" size={18} color={colors.muted} />
              <TextInput
                style={[styles.searchTextInput, { color: colors.text }]}
                placeholder={t('Search mosques...', 'ابحث عن مسجد...')}
                placeholderTextColor={colors.muted}
                value={searchQuery}
                onChangeText={setSearchQuery}
              />
              {searchQuery.length > 0 && (
                <TouchableOpacity onPress={() => setSearchQuery('')}>
                  <Ionicons name="close-circle" size={18} color={colors.muted} />
                </TouchableOpacity>
              )}
            </View>
          </View>

          {/* Mosque Stats */}
          <View style={styles.statsRow}>
            <Text style={[styles.statsText, { color: colors.muted }]}>
              {filteredMosques.length} {t('mosques in New Zealand', 'مسجد في نيوزيلندا')}
            </Text>
          </View>

          {/* Grouped Mosque List */}
          <FlatList
            data={groupedMosques}
            keyExtractor={(item) => item.region}
            renderItem={({ item }) => (
              <View>
                <Text style={[styles.regionHeader, { color: Colors.dark.gold, backgroundColor: colors.background }]}>
                  {item.region}
                </Text>
                {item.mosques.map((mosque) => (
                  <TouchableOpacity
                    key={mosque.id}
                    style={[
                      styles.mosqueOption,
                      { backgroundColor: colors.card },
                      selectedMosque?.id === mosque.id && { backgroundColor: Colors.light.goldLight },
                    ]}
                    onPress={() => {
                      setSelectedMosque(mosque);
                      setUseLocation(false);
                      setShowMosquePicker(false);
                    }}
                  >
                    <View style={styles.mosqueOptionContent}>
                      <View style={styles.mosqueOptionHeader}>
                        <Text
                          style={[
                            styles.mosqueOptionName,
                            { color: selectedMosque?.id === mosque.id ? Colors.dark.gold : colors.text },
                          ]}
                          numberOfLines={1}
                        >
                          {language === 'ar' ? mosque.nameAr : mosque.name}
                        </Text>
                        {mosque.hasIqamaTimes && (
                          <View style={[styles.iqamaBadge, { backgroundColor: Colors.status.success }]}>
                            <Text style={styles.iqamaBadgeText}>Iqama</Text>
                          </View>
                        )}
                      </View>
                      <Text style={[styles.mosqueOptionAddress, { color: colors.muted }]} numberOfLines={1}>
                        {mosque.address}, {mosque.city}
                      </Text>
                    </View>
                    {selectedMosque?.id === mosque.id && (
                      <Ionicons name="checkmark-circle" size={22} color={Colors.dark.gold} />
                    )}
                  </TouchableOpacity>
                ))}
              </View>
            )}
            ListEmptyComponent={
              <View style={styles.emptySearch}>
                <Ionicons name="search" size={48} color={colors.muted} />
                <Text style={[styles.emptySearchText, { color: colors.muted }]}>
                  {t('No mosques found', 'لم يتم العثور على مساجد')}
                </Text>
              </View>
            }
          />
        </SafeAreaView>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  mosqueSelector: {
    padding: Spacing.base,
    gap: 8,
  },
  selectorLabel: {
    fontSize: 11,
    fontWeight: '600',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  mosqueButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    padding: 14,
    borderRadius: 12,
    borderWidth: 1,
  },
  mosqueButtonContent: {
    flex: 1,
  },
  mosqueButtonText: {
    fontSize: 16,
    fontWeight: '600',
  },
  mosqueAddress: {
    fontSize: 12,
    marginTop: 2,
  },
  locationBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    padding: 12,
    borderRadius: 12,
    borderWidth: 1,
  },
  locationBtnText: {
    fontSize: 14,
    fontWeight: '500',
  },
  sourceIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingTop: 4,
  },
  sourceText: {
    fontSize: 11,
  },
  card: {
    marginHorizontal: Spacing.base,
    borderRadius: 14,
    borderWidth: 1,
    overflow: 'hidden',
  },
  cardHeader: {
    padding: 16,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(0,0,0,0.05)',
  },
  dateText: {
    fontSize: 14,
  },
  loadingContainer: {
    padding: 40,
    alignItems: 'center',
  },
  headerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderBottomWidth: 1,
  },
  headerLabel: {
    fontSize: 12,
    fontWeight: '600',
    textTransform: 'uppercase',
    flex: 1,
  },
  prayerList: {},
  prayerItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 16,
    paddingHorizontal: 20,
  },
  prayerItemNext: {
    backgroundColor: Colors.light.goldLight,
    borderLeftWidth: 3,
    borderLeftColor: Colors.dark.gold,
  },
  prayerName: {
    fontSize: 16,
    fontWeight: '600',
    flex: 1,
  },
  prayerTime: {
    fontSize: 18,
    fontWeight: '700',
  },
  prayerTimeWithIqama: {
    flex: 1,
    textAlign: 'center',
  },
  iqamaTime: {
    fontSize: 18,
    fontWeight: '700',
    flex: 1,
    textAlign: 'right',
  },
  nextPrayerInfo: {
    padding: 20,
    alignItems: 'center',
  },
  nextPrayerHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 8,
  },
  nextPrayerLabel: {
    fontSize: 12,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  nextPrayerName: {
    fontSize: 28,
    fontWeight: '700',
  },
  nextPrayerTime: {
    fontSize: 16,
    marginTop: 4,
  },
  countdownContainer: {
    marginTop: 12,
    paddingHorizontal: 20,
    paddingVertical: 10,
    backgroundColor: 'rgba(212, 175, 55, 0.1)',
    borderRadius: 12,
    alignItems: 'center',
  },
  countdownUrgent: {
    backgroundColor: 'rgba(231, 76, 60, 0.15)',
  },
  countdownText: {
    fontSize: 32,
    fontWeight: '800',
    fontVariant: ['tabular-nums'],
    color: Colors.dark.gold,
  },
  countdownTextUrgent: {
    color: Colors.status.danger,
  },
  countdownLabel: {
    fontSize: 11,
    marginTop: 2,
    color: Colors.dark.gold,
    textTransform: 'uppercase',
  },
  countdownLabelUrgent: {
    color: Colors.status.danger,
  },
  mosqueInfo: {
    marginHorizontal: Spacing.base,
    marginTop: Spacing.base,
    borderRadius: 14,
    borderWidth: 1,
    padding: 16,
  },
  mosqueInfoHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  mosqueInfoTitle: {
    fontSize: 14,
    fontWeight: '600',
  },
  mosqueInfoText: {
    fontSize: 13,
    marginBottom: 2,
  },
  mosqueInfoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginTop: 8,
  },
  myMasjidBadge: {
    marginTop: 12,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
    alignSelf: 'flex-start',
  },
  myMasjidBadgeText: {
    fontSize: 11,
    fontWeight: '600',
  },
  methodInfo: {
    textAlign: 'center',
    fontSize: 12,
    padding: Spacing.lg,
  },
  // Modal styles
  modalContainer: {
    flex: 1,
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 16,
    borderBottomWidth: 1,
  },
  modalCancel: {
    fontSize: 16,
    fontWeight: '500',
    width: 60,
  },
  modalTitle: {
    fontSize: 17,
    fontWeight: '600',
  },
  searchContainer: {
    padding: Spacing.base,
  },
  searchInput: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    padding: 12,
    borderRadius: 10,
    borderWidth: 1,
  },
  searchTextInput: {
    flex: 1,
    fontSize: 16,
  },
  statsRow: {
    paddingHorizontal: Spacing.base,
    paddingBottom: 8,
  },
  statsText: {
    fontSize: 12,
  },
  regionHeader: {
    fontSize: 13,
    fontWeight: '700',
    paddingHorizontal: Spacing.base,
    paddingVertical: 10,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  mosqueOption: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: 'rgba(0,0,0,0.1)',
  },
  mosqueOptionContent: {
    flex: 1,
  },
  mosqueOptionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  mosqueOptionName: {
    fontSize: 15,
    fontWeight: '600',
    flex: 1,
  },
  mosqueOptionAddress: {
    fontSize: 13,
    marginTop: 2,
  },
  iqamaBadge: {
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
  },
  iqamaBadgeText: {
    fontSize: 9,
    fontWeight: '700',
    color: '#fff',
    textTransform: 'uppercase',
  },
  emptySearch: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: 40,
  },
  emptySearchText: {
    fontSize: 16,
    marginTop: 16,
  },
});
