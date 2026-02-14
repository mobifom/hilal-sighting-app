/**
 * Qibla Screen
 * Displays Qibla direction compass with live compass mode
 */

import React, { useEffect, useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  Animated,
  ScrollView,
  Switch,
  Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import * as Location from 'expo-location';
import { Magnetometer } from 'expo-sensors';
import Svg, { Circle, Path, G, Text as SvgText, Polygon } from 'react-native-svg';

import { useTheme, useLanguage } from '../context/AppContext';
import { api } from '../api/client';
import { Colors, Spacing } from '../utils/theme';

export default function QiblaScreen() {
  const { colors, theme } = useTheme();
  const { t, language } = useLanguage();

  const [loading, setLoading] = useState(false);
  const [qiblaBearing, setQiblaBearing] = useState<number | null>(null);
  const [distance, setDistance] = useState<number | null>(null);
  const [description, setDescription] = useState('');
  const [compassHeading, setCompassHeading] = useState(0);
  const [subscription, setSubscription] = useState<any>(null);
  const [liveMode, setLiveMode] = useState(true);
  const [isAligned, setIsAligned] = useState(false);

  const compassRotation = useRef(new Animated.Value(0)).current;
  const qiblaRotation = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    return () => {
      if (subscription) {
        subscription.remove();
      }
    };
  }, [subscription]);

  // Update compass rotation smoothly in live mode
  useEffect(() => {
    if (liveMode && qiblaBearing !== null) {
      // Calculate the rotation needed to point Qibla up
      const targetRotation = -compassHeading;
      const qiblaTarget = qiblaBearing - compassHeading;

      // Check if aligned with Qibla (within 5 degrees)
      const diff = Math.abs(((qiblaTarget % 360) + 360) % 360);
      setIsAligned(diff < 5 || diff > 355);

      Animated.parallel([
        Animated.timing(compassRotation, {
          toValue: targetRotation,
          duration: 100,
          useNativeDriver: true,
        }),
        Animated.timing(qiblaRotation, {
          toValue: qiblaTarget,
          duration: 100,
          useNativeDriver: true,
        }),
      ]).start();
    }
  }, [compassHeading, qiblaBearing, liveMode]);

  // Static mode rotation
  useEffect(() => {
    if (!liveMode && qiblaBearing !== null) {
      Animated.timing(qiblaRotation, {
        toValue: qiblaBearing,
        duration: 500,
        useNativeDriver: true,
      }).start();
    }
  }, [qiblaBearing, liveMode]);

  const startCompass = () => {
    Magnetometer.setUpdateInterval(50);
    const sub = Magnetometer.addListener((data) => {
      let angle = Math.atan2(data.y, data.x);
      angle = angle * (180 / Math.PI);
      angle = (angle + 90) % 360; // Adjust for device orientation
      angle = angle < 0 ? angle + 360 : angle;
      setCompassHeading(Math.round(angle));
    });
    setSubscription(sub);
  };

  const findQibla = async () => {
    setLoading(true);
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        setDescription(t('Location permission denied', 'تم رفض إذن الموقع'));
        return;
      }

      const location = await Location.getCurrentPositionAsync({});
      const data = await api.getQibla(
        location.coords.latitude,
        location.coords.longitude
      );

      if (data) {
        setQiblaBearing(data.qibla.bearing_rounded);
        setDistance(data.distance.km);
        setDescription(
          language === 'ar' ? data.qibla.description_ar : data.qibla.description_en
        );

        // Start compass for live direction
        startCompass();
      }
    } catch {
      setDescription(t('Error finding Qibla direction', 'خطأ في تحديد اتجاه القبلة'));
    } finally {
      setLoading(false);
    }
  };

  const compassRotate = compassRotation.interpolate({
    inputRange: [-360, 0, 360],
    outputRange: ['-360deg', '0deg', '360deg'],
  });

  const qiblaRotate = qiblaRotation.interpolate({
    inputRange: [-360, 0, 360],
    outputRange: ['-360deg', '0deg', '360deg'],
  });

  const compassSize = 280;
  const center = compassSize / 2;
  const radius = center - 20;

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['bottom']}>
      <ScrollView contentContainerStyle={styles.scrollContent}>
        <View style={styles.content}>
          {/* Compass Container */}
          <View style={[styles.compassContainer, isAligned && qiblaBearing !== null && styles.compassAligned]}>
            {/* Animated Compass Background */}
            <Animated.View
              style={[
                styles.compassWrapper,
                liveMode && { transform: [{ rotate: compassRotate }] },
              ]}
            >
              <Svg width={compassSize} height={compassSize} viewBox={`0 0 ${compassSize} ${compassSize}`}>
                {/* Outer circle */}
                <Circle
                  cx={center}
                  cy={center}
                  r={radius}
                  stroke={theme === 'dark' ? '#444' : '#ddd'}
                  strokeWidth={2}
                  fill={theme === 'dark' ? '#1a1a1a' : '#f8f9fa'}
                />

                {/* Degree marks */}
                {Array.from({ length: 72 }).map((_, i) => {
                  const angle = (i * 5 * Math.PI) / 180;
                  const isMajor = i % 6 === 0;
                  const innerR = isMajor ? radius - 15 : radius - 8;
                  const x1 = center + radius * Math.sin(angle);
                  const y1 = center - radius * Math.cos(angle);
                  const x2 = center + innerR * Math.sin(angle);
                  const y2 = center - innerR * Math.cos(angle);
                  return (
                    <Path
                      key={i}
                      d={`M ${x1} ${y1} L ${x2} ${y2}`}
                      stroke={isMajor ? (theme === 'dark' ? '#666' : '#999') : (theme === 'dark' ? '#444' : '#ccc')}
                      strokeWidth={isMajor ? 2 : 1}
                    />
                  );
                })}

                {/* Cardinal directions */}
                <G>
                  <SvgText
                    x={center}
                    y={45}
                    fill={Colors.dark.gold}
                    fontSize={18}
                    fontWeight="bold"
                    textAnchor="middle"
                  >
                    N
                  </SvgText>
                  <SvgText
                    x={compassSize - 35}
                    y={center + 6}
                    fill={theme === 'dark' ? '#888' : '#666'}
                    fontSize={16}
                    fontWeight="600"
                    textAnchor="middle"
                  >
                    E
                  </SvgText>
                  <SvgText
                    x={center}
                    y={compassSize - 30}
                    fill={theme === 'dark' ? '#888' : '#666'}
                    fontSize={16}
                    fontWeight="600"
                    textAnchor="middle"
                  >
                    S
                  </SvgText>
                  <SvgText
                    x={35}
                    y={center + 6}
                    fill={theme === 'dark' ? '#888' : '#666'}
                    fontSize={16}
                    fontWeight="600"
                    textAnchor="middle"
                  >
                    W
                  </SvgText>
                </G>

                {/* Center dot */}
                <Circle
                  cx={center}
                  cy={center}
                  r={6}
                  fill={Colors.dark.gold}
                />
              </Svg>
            </Animated.View>

            {/* Qibla Needle (rotates independently) */}
            {qiblaBearing !== null && (
              <Animated.View
                style={[
                  styles.needleContainer,
                  { transform: [{ rotate: liveMode ? qiblaRotate : `${qiblaBearing}deg` }] },
                ]}
              >
                <View style={styles.needleWrapper}>
                  <View style={styles.needleArrow}>
                    <Ionicons name="location" size={32} color={Colors.dark.gold} />
                  </View>
                  <Text style={styles.kaabaLabel}>{t('Kaaba', 'الكعبة')}</Text>
                </View>
              </Animated.View>
            )}

            {/* Center indicator */}
            <View style={styles.centerIndicator}>
              {qiblaBearing !== null ? (
                <View style={[styles.centerDot, isAligned && styles.centerDotAligned]} />
              ) : (
                <Ionicons name="compass-outline" size={24} color={colors.muted} />
              )}
            </View>
          </View>

          {/* Current Heading */}
          {qiblaBearing !== null && liveMode && (
            <View style={styles.headingDisplay}>
              <Text style={[styles.headingText, { color: colors.muted }]}>
                {t('Current Heading', 'الاتجاه الحالي')}: {compassHeading}°
              </Text>
            </View>
          )}

          {/* Qibla Info */}
          <View style={[styles.infoCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
            {qiblaBearing !== null ? (
              <>
                <View style={styles.bearingRow}>
                  <View style={styles.bearingInfo}>
                    <Text style={[styles.bearingLabel, { color: colors.muted }]}>
                      {t('Qibla Bearing', 'اتجاه القبلة')}
                    </Text>
                    <Text style={[styles.bearing, { color: Colors.dark.gold }]}>
                      {qiblaBearing}°
                    </Text>
                  </View>
                  {distance && (
                    <View style={styles.distanceInfo}>
                      <Text style={[styles.distanceLabel, { color: colors.muted }]}>
                        {t('Distance', 'المسافة')}
                      </Text>
                      <Text style={[styles.distanceValue, { color: colors.text }]}>
                        {distance.toLocaleString()} km
                      </Text>
                    </View>
                  )}
                </View>
                <Text style={[styles.description, { color: colors.muted }]}>
                  {description}
                </Text>

                {/* Live Mode Toggle */}
                <View style={[styles.liveModeRow, { borderTopColor: colors.border }]}>
                  <View style={styles.liveModeInfo}>
                    <Ionicons
                      name={liveMode ? 'compass' : 'compass-outline'}
                      size={20}
                      color={liveMode ? Colors.dark.gold : colors.muted}
                    />
                    <Text style={[styles.liveModeLabel, { color: colors.text }]}>
                      {t('Live Compass Mode', 'وضع البوصلة المباشر')}
                    </Text>
                  </View>
                  <Switch
                    value={liveMode}
                    onValueChange={setLiveMode}
                    trackColor={{ false: colors.border, true: Colors.light.goldLight }}
                    thumbColor={liveMode ? Colors.dark.gold : '#f4f3f4'}
                  />
                </View>

                {/* Aligned Indicator */}
                {isAligned && (
                  <View style={styles.alignedBanner}>
                    <Ionicons name="checkmark-circle" size={20} color="#fff" />
                    <Text style={styles.alignedText}>
                      {t('Facing Qibla!', 'أنت تواجه القبلة!')}
                    </Text>
                  </View>
                )}
              </>
            ) : (
              <View style={styles.placeholderInfo}>
                <Ionicons name="compass-outline" size={48} color={colors.muted} />
                <Text style={[styles.placeholderText, { color: colors.muted }]}>
                  {t('Tap the button below to find Qibla direction', 'اضغط على الزر أدناه لتحديد اتجاه القبلة')}
                </Text>
              </View>
            )}
          </View>

          {/* Find Qibla Button */}
          <TouchableOpacity
            style={[styles.findButton, loading && styles.findButtonDisabled]}
            onPress={findQibla}
            disabled={loading}
          >
            {loading ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <>
                <Ionicons name="locate" size={20} color="#fff" />
                <Text style={styles.findButtonText}>
                  {qiblaBearing ? t('Update Direction', 'تحديث الاتجاه') : t('Find Qibla Direction', 'تحديد اتجاه القبلة')}
                </Text>
              </>
            )}
          </TouchableOpacity>

          {/* Instructions */}
          <View style={[styles.instructions, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <View style={styles.instructionsHeader}>
              <Ionicons name="information-circle" size={20} color={Colors.dark.gold} />
              <Text style={[styles.instructionsTitle, { color: colors.text }]}>
                {t('Instructions', 'تعليمات')}
              </Text>
            </View>
            <Text style={[styles.instructionsText, { color: colors.muted }]}>
              {t(
                '1. Tap "Find Qibla Direction" to locate Qibla\n2. Enable "Live Compass Mode" for real-time tracking\n3. Rotate your device until you see "Facing Qibla!"\n4. Hold your device flat for accurate reading\n5. Keep away from metal objects for better accuracy',
                '1. اضغط على "تحديد اتجاه القبلة"\n2. فعّل "وضع البوصلة المباشر" للتتبع المباشر\n3. قم بتدوير جهازك حتى ترى "أنت تواجه القبلة!"\n4. أمسك جهازك بشكل مسطح للحصول على قراءة دقيقة\n5. ابتعد عن الأجسام المعدنية للحصول على دقة أفضل'
              )}
            </Text>
          </View>

          {/* Kaaba Info */}
          <View style={[styles.kaabaInfo, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <Ionicons name="cube" size={20} color={Colors.dark.gold} />
            <Text style={[styles.kaabaInfoText, { color: colors.muted }]}>
              {t(
                'The Kaaba is the sacred building at the center of the Grand Mosque in Makkah, Saudi Arabia. Muslims around the world face towards the Kaaba during prayers.',
                'الكعبة هي البناء المقدس في وسط المسجد الحرام في مكة المكرمة، المملكة العربية السعودية. يتجه المسلمون حول العالم نحو الكعبة أثناء الصلاة.'
              )}
            </Text>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
  },
  content: {
    flex: 1,
    padding: Spacing.base,
    alignItems: 'center',
  },
  compassContainer: {
    width: 300,
    height: 300,
    marginVertical: 16,
    justifyContent: 'center',
    alignItems: 'center',
    borderRadius: 150,
    borderWidth: 3,
    borderColor: 'transparent',
  },
  compassAligned: {
    borderColor: Colors.status.success,
    shadowColor: Colors.status.success,
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.5,
    shadowRadius: 20,
    elevation: 10,
  },
  compassWrapper: {
    width: 280,
    height: 280,
    justifyContent: 'center',
    alignItems: 'center',
  },
  needleContainer: {
    position: 'absolute',
    width: 280,
    height: 280,
    justifyContent: 'flex-start',
    alignItems: 'center',
  },
  needleWrapper: {
    alignItems: 'center',
    paddingTop: 20,
  },
  needleArrow: {
    alignItems: 'center',
  },
  kaabaLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: Colors.dark.gold,
    marginTop: 2,
  },
  centerIndicator: {
    position: 'absolute',
    width: 30,
    height: 30,
    justifyContent: 'center',
    alignItems: 'center',
  },
  centerDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: Colors.dark.gold,
  },
  centerDotAligned: {
    backgroundColor: Colors.status.success,
  },
  headingDisplay: {
    marginBottom: 12,
  },
  headingText: {
    fontSize: 14,
  },
  infoCard: {
    width: '100%',
    borderRadius: 14,
    borderWidth: 1,
    padding: 16,
    marginBottom: 16,
  },
  bearingRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  bearingInfo: {
    flex: 1,
  },
  bearingLabel: {
    fontSize: 12,
    marginBottom: 4,
  },
  bearing: {
    fontSize: 42,
    fontWeight: '800',
  },
  distanceInfo: {
    alignItems: 'flex-end',
  },
  distanceLabel: {
    fontSize: 12,
    marginBottom: 4,
  },
  distanceValue: {
    fontSize: 18,
    fontWeight: '700',
  },
  description: {
    fontSize: 14,
    lineHeight: 20,
  },
  liveModeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: 16,
    paddingTop: 16,
    borderTopWidth: 1,
  },
  liveModeInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  liveModeLabel: {
    fontSize: 14,
    fontWeight: '500',
  },
  alignedBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    marginTop: 16,
    paddingVertical: 12,
    backgroundColor: Colors.status.success,
    borderRadius: 10,
  },
  alignedText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '700',
  },
  placeholderInfo: {
    alignItems: 'center',
    padding: 20,
  },
  placeholderText: {
    fontSize: 14,
    textAlign: 'center',
    marginTop: 12,
  },
  findButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: Colors.dark.gold,
    paddingVertical: 16,
    paddingHorizontal: 32,
    borderRadius: 12,
    width: '100%',
  },
  findButtonDisabled: {
    opacity: 0.6,
  },
  findButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '700',
  },
  instructions: {
    marginTop: 16,
    padding: 16,
    borderRadius: 14,
    borderWidth: 1,
    width: '100%',
  },
  instructionsHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  instructionsTitle: {
    fontSize: 15,
    fontWeight: '700',
  },
  instructionsText: {
    fontSize: 13,
    lineHeight: 22,
  },
  kaabaInfo: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    marginTop: 16,
    marginBottom: 24,
    padding: 16,
    borderRadius: 14,
    borderWidth: 1,
    width: '100%',
  },
  kaabaInfoText: {
    flex: 1,
    fontSize: 13,
    lineHeight: 20,
  },
});
