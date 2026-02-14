/**
 * Qibla Screen
 * Displays Qibla direction compass
 */

import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  Animated,
  Easing,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import * as Location from 'expo-location';
import { Magnetometer } from 'expo-sensors';

import { useTheme, useLanguage } from '../context/AppContext';
import { api } from '../api/client';
import { Colors, Spacing } from '../utils/theme';

export default function QiblaScreen() {
  const { colors } = useTheme();
  const { t, language } = useLanguage();

  const [loading, setLoading] = useState(false);
  const [qiblaBearing, setQiblaBearing] = useState<number | null>(null);
  const [distance, setDistance] = useState<number | null>(null);
  const [description, setDescription] = useState('');
  const [compassHeading, setCompassHeading] = useState(0);
  const [subscription, setSubscription] = useState<any>(null);

  const rotateValue = useState(new Animated.Value(0))[0];

  useEffect(() => {
    return () => {
      if (subscription) {
        subscription.remove();
      }
    };
  }, [subscription]);

  useEffect(() => {
    if (qiblaBearing !== null) {
      // Animate compass rotation
      Animated.timing(rotateValue, {
        toValue: qiblaBearing,
        duration: 500,
        easing: Easing.out(Easing.cubic),
        useNativeDriver: true,
      }).start();
    }
  }, [qiblaBearing]);

  const startCompass = () => {
    const sub = Magnetometer.addListener((data) => {
      let angle = Math.atan2(data.y, data.x);
      angle = angle * (180 / Math.PI);
      angle = angle < 0 ? angle + 360 : angle;
      setCompassHeading(Math.round(angle));
    });

    Magnetometer.setUpdateInterval(100);
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
    } catch (error) {
      console.error('Error finding Qibla:', error);
      setDescription(t('Error finding Qibla direction', 'خطأ في تحديد اتجاه القبلة'));
    } finally {
      setLoading(false);
    }
  };

  const rotate = rotateValue.interpolate({
    inputRange: [0, 360],
    outputRange: ['0deg', '360deg'],
  });

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['bottom']}>
      <View style={styles.content}>
        {/* Compass */}
        <View style={styles.compassWrapper}>
          <View style={[styles.compass, { borderColor: Colors.dark.gold }]}>
            {/* Direction Labels */}
            <Text style={[styles.compassLabel, styles.compassN, { color: colors.muted }]}>N</Text>
            <Text style={[styles.compassLabel, styles.compassE, { color: colors.muted }]}>E</Text>
            <Text style={[styles.compassLabel, styles.compassS, { color: colors.muted }]}>S</Text>
            <Text style={[styles.compassLabel, styles.compassW, { color: colors.muted }]}>W</Text>

            {/* Needle */}
            <Animated.View
              style={[
                styles.needle,
                { transform: [{ rotate }] },
              ]}
            >
              <View style={styles.needleTop} />
              <View style={styles.needleBottom} />
            </Animated.View>

            {/* Center */}
            <View style={[styles.compassCenter, { backgroundColor: Colors.dark.gold }]} />
          </View>
        </View>

        {/* Qibla Info */}
        <View style={styles.infoSection}>
          {qiblaBearing !== null ? (
            <>
              <Text style={[styles.bearing, { color: Colors.dark.gold }]}>
                {qiblaBearing}°
              </Text>
              <Text style={[styles.description, { color: colors.muted }]}>
                {description}
              </Text>
              {distance && (
                <Text style={[styles.distance, { color: colors.muted }]}>
                  {t(`Distance to Kaaba: ${distance.toLocaleString()} km`, `المسافة إلى الكعبة: ${distance.toLocaleString()} كم`)}
                </Text>
              )}
            </>
          ) : (
            <Text style={[styles.description, { color: colors.muted }]}>
              {t('Tap the button below to find Qibla direction', 'اضغط على الزر أدناه لتحديد اتجاه القبلة')}
            </Text>
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
            <Text style={styles.findButtonText}>
              {qiblaBearing ? t('Update Direction', 'تحديث الاتجاه') : t('Find Qibla Direction', 'تحديد اتجاه القبلة')}
            </Text>
          )}
        </TouchableOpacity>

        {/* Instructions */}
        <View style={[styles.instructions, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <Text style={[styles.instructionsTitle, { color: colors.text }]}>
            {t('Instructions', 'تعليمات')}
          </Text>
          <Text style={[styles.instructionsText, { color: colors.muted }]}>
            {t(
              '1. Tap "Find Qibla Direction"\n2. Allow location access\n3. The compass needle will point towards the Kaaba\n4. Hold your device flat for accurate reading',
              '1. اضغط على "تحديد اتجاه القبلة"\n2. اسمح بالوصول إلى الموقع\n3. سيشير إبرة البوصلة نحو الكعبة\n4. أمسك جهازك بشكل مسطح للحصول على قراءة دقيقة'
            )}
          </Text>
        </View>

        {/* Kaaba Info */}
        <Text style={[styles.kaabaInfo, { color: colors.muted }]}>
          {t(
            'Qibla is the direction of the Holy Kaaba in Makkah, Saudi Arabia.',
            'القبلة هي اتجاه الكعبة المشرفة في مكة المكرمة، المملكة العربية السعودية.'
          )}
        </Text>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  content: {
    flex: 1,
    padding: Spacing.base,
    alignItems: 'center',
  },
  compassWrapper: {
    width: 250,
    height: 250,
    marginVertical: 24,
  },
  compass: {
    width: '100%',
    height: '100%',
    borderRadius: 125,
    borderWidth: 4,
    backgroundColor: '#f8f9fa',
    position: 'relative',
  },
  compassLabel: {
    position: 'absolute',
    fontWeight: '700',
    fontSize: 16,
  },
  compassN: {
    top: 10,
    left: '50%',
    marginLeft: -5,
  },
  compassE: {
    right: 10,
    top: '50%',
    marginTop: -10,
  },
  compassS: {
    bottom: 10,
    left: '50%',
    marginLeft: -5,
  },
  compassW: {
    left: 10,
    top: '50%',
    marginTop: -10,
  },
  needle: {
    position: 'absolute',
    top: '15%',
    left: '50%',
    width: 6,
    height: '35%',
    marginLeft: -3,
    alignItems: 'center',
  },
  needleTop: {
    width: 6,
    height: '50%',
    backgroundColor: Colors.status.danger,
    borderTopLeftRadius: 3,
    borderTopRightRadius: 3,
  },
  needleBottom: {
    width: 6,
    height: '50%',
    backgroundColor: '#ccc',
    borderBottomLeftRadius: 3,
    borderBottomRightRadius: 3,
  },
  compassCenter: {
    position: 'absolute',
    top: '50%',
    left: '50%',
    width: 16,
    height: 16,
    borderRadius: 8,
    marginTop: -8,
    marginLeft: -8,
  },
  infoSection: {
    alignItems: 'center',
    marginBottom: 24,
  },
  bearing: {
    fontSize: 48,
    fontWeight: '800',
  },
  description: {
    fontSize: 14,
    textAlign: 'center',
    marginTop: 8,
  },
  distance: {
    fontSize: 13,
    marginTop: 8,
  },
  findButton: {
    backgroundColor: Colors.dark.gold,
    paddingVertical: 16,
    paddingHorizontal: 32,
    borderRadius: 12,
    width: '100%',
    alignItems: 'center',
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
    marginTop: 24,
    padding: 16,
    borderRadius: 14,
    borderWidth: 1,
    width: '100%',
  },
  instructionsTitle: {
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 8,
  },
  instructionsText: {
    fontSize: 13,
    lineHeight: 22,
  },
  kaabaInfo: {
    fontSize: 12,
    textAlign: 'center',
    marginTop: 24,
    paddingHorizontal: 20,
  },
});
