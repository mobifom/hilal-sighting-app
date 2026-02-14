/**
 * Announcement Detail Screen
 * Displays full announcement content with bilingual support
 */

import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  Share,
  useWindowDimensions,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';

import { useTheme, useLanguage } from '../context/AppContext';
import { api } from '../api/client';
import { Colors, Spacing, BorderRadius } from '../utils/theme';
import { RootStackParamList } from '../../App';

type NavigationProp = NativeStackNavigationProp<RootStackParamList>;
type DetailRouteProp = RouteProp<RootStackParamList, 'AnnouncementDetail'>;

interface AnnouncementDetail {
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
  published_date: string;
  hijri_month?: {
    id: number;
    month_name: string;
    month_name_ar: string;
    hijri_year: number;
  };
}

export default function AnnouncementDetailScreen() {
  const navigation = useNavigation<NavigationProp>();
  const route = useRoute<DetailRouteProp>();
  const { colors } = useTheme();
  const { t, language } = useLanguage();
  const { width } = useWindowDimensions();

  const [announcement, setAnnouncement] = useState<AnnouncementDetail | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadAnnouncement();
  }, [route.params.id]);

  const loadAnnouncement = async () => {
    try {
      setLoading(true);
      const data = await api.getAnnouncement(route.params.id);
      if (data) {
        setAnnouncement(data);
      }
    } catch (error) {
      console.error('Error loading announcement:', error);
    } finally {
      setLoading(false);
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'high':
        return Colors.status.danger;
      case 'medium':
        return Colors.status.warning;
      case 'low':
        return Colors.status.success;
      default:
        return Colors.status.info;
    }
  };

  const getTypeLabel = (type: string) => {
    const labels: Record<string, { en: string; ar: string }> = {
      month_start: { en: 'Month Start', ar: 'بداية شهر' },
      moon_sighting: { en: 'Sighting', ar: 'رؤية' },
      islamic_event: { en: 'Event', ar: 'مناسبة' },
      general: { en: 'General', ar: 'عام' },
    };
    return language === 'ar' ? labels[type]?.ar || type : labels[type]?.en || type;
  };

  const handleShare = async () => {
    if (!announcement) return;

    const title = language === 'ar' ? announcement.title_ar : announcement.title_en;
    const body = language === 'ar'
      ? announcement.body_ar?.replace(/<[^>]*>/g, '').substring(0, 200)
      : announcement.body_en?.replace(/<[^>]*>/g, '').substring(0, 200);

    try {
      await Share.share({
        title,
        message: `${title}\n\n${body}...\n\nVia Hilal NZ App`,
      });
    } catch (error) {
      console.error('Error sharing:', error);
    }
  };

  // Simple HTML to text conversion (strip tags)
  const stripHtml = (html: string) => {
    return html
      ?.replace(/<br\s*\/?>/gi, '\n')
      .replace(/<\/p>/gi, '\n\n')
      .replace(/<[^>]*>/g, '')
      .replace(/&nbsp;/g, ' ')
      .replace(/&amp;/g, '&')
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .replace(/&quot;/g, '"')
      .trim() || '';
  };

  if (loading) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={Colors.dark.gold} />
        </View>
      </SafeAreaView>
    );
  }

  if (!announcement) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
        <View style={styles.errorContainer}>
          <Ionicons name="alert-circle" size={48} color={colors.muted} />
          <Text style={[styles.errorText, { color: colors.muted }]}>
            {t('Announcement not found', 'لم يتم العثور على الإعلان')}
          </Text>
          <TouchableOpacity
            style={[styles.backButton, { borderColor: colors.border }]}
            onPress={() => navigation.goBack()}
          >
            <Text style={[styles.backButtonText, { color: colors.text }]}>
              {t('Go Back', 'العودة')}
            </Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* Header */}
      <View style={[styles.header, { borderBottomColor: colors.border }]}>
        <TouchableOpacity style={styles.headerBtn} onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color={colors.text} />
        </TouchableOpacity>
        <Text style={[styles.headerTitle, { color: colors.text }]}>
          {t('Announcement', 'إعلان')}
        </Text>
        <TouchableOpacity style={styles.headerBtn} onPress={handleShare}>
          <Ionicons name="share-outline" size={24} color={colors.text} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Meta Info */}
        <View style={styles.metaRow}>
          <View style={[styles.priorityDot, { backgroundColor: getPriorityColor(announcement.priority) }]} />
          <View style={[styles.typeBadge, { backgroundColor: Colors.light.goldLight }]}>
            <Text style={[styles.typeBadgeText, { color: Colors.light.gold }]}>
              {getTypeLabel(announcement.type)}
            </Text>
          </View>
          <Text style={[styles.date, { color: colors.muted }]}>
            {new Date(announcement.published_date).toLocaleDateString('en-US', {
              weekday: 'long',
              day: 'numeric',
              month: 'long',
              year: 'numeric',
            })}
          </Text>
        </View>

        {/* Related Hijri Month */}
        {announcement.hijri_month && (
          <View style={[styles.hijriMonthBadge, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <Ionicons name="moon" size={14} color={Colors.dark.gold} />
            <Text style={[styles.hijriMonthText, { color: colors.text }]}>
              {language === 'ar'
                ? `${announcement.hijri_month.month_name_ar} ${announcement.hijri_month.hijri_year}`
                : `${announcement.hijri_month.month_name} ${announcement.hijri_month.hijri_year}`}
            </Text>
          </View>
        )}

        {/* Primary Title */}
        <Text style={[styles.title, { color: colors.text }]}>
          {language === 'ar' ? announcement.title_ar : announcement.title_en}
        </Text>

        {/* Secondary Title */}
        <Text style={[styles.titleAlt, { color: colors.muted }]}>
          {language === 'ar' ? announcement.title_en : announcement.title_ar}
        </Text>

        {/* Divider */}
        <View style={[styles.divider, { backgroundColor: colors.border }]} />

        {/* Primary Body */}
        <Text style={[styles.body, { color: colors.text }]}>
          {stripHtml(language === 'ar' ? announcement.body_ar : announcement.body_en)}
        </Text>

        {/* Secondary Body */}
        <View style={[styles.altBodyContainer, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <Text style={[styles.altBodyLabel, { color: colors.muted }]}>
            {language === 'ar' ? 'English' : 'العربية'}
          </Text>
          <Text style={[styles.altBody, { color: colors.muted }]}>
            {stripHtml(language === 'ar' ? announcement.body_en : announcement.body_ar)}
          </Text>
        </View>

        {/* Share CTA */}
        <TouchableOpacity
          style={[styles.shareButton, { backgroundColor: Colors.dark.gold }]}
          onPress={handleShare}
        >
          <Ionicons name="share-social" size={18} color="#fff" />
          <Text style={styles.shareButtonText}>
            {t('Share this Announcement', 'شارك هذا الإعلان')}
          </Text>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  errorContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.xl,
  },
  errorText: {
    fontSize: 16,
    marginTop: 16,
    marginBottom: 24,
  },
  backButton: {
    paddingVertical: 12,
    paddingHorizontal: 24,
    borderRadius: 8,
    borderWidth: 1,
  },
  backButtonText: {
    fontSize: 14,
    fontWeight: '600',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.base,
    paddingVertical: 12,
    borderBottomWidth: 1,
  },
  headerBtn: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerTitle: {
    fontSize: 17,
    fontWeight: '600',
  },
  scrollContent: {
    padding: Spacing.base,
    paddingBottom: 40,
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 16,
  },
  priorityDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
  },
  typeBadge: {
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 20,
  },
  typeBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  date: {
    fontSize: 13,
    marginLeft: 'auto',
  },
  hijriMonthBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    gap: 8,
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 8,
    borderWidth: 1,
    marginBottom: 16,
  },
  hijriMonthText: {
    fontSize: 13,
    fontWeight: '600',
  },
  title: {
    fontSize: 26,
    fontWeight: '800',
    lineHeight: 34,
    marginBottom: 8,
  },
  titleAlt: {
    fontSize: 20,
    lineHeight: 28,
    marginBottom: 20,
  },
  divider: {
    height: 1,
    marginBottom: 20,
  },
  body: {
    fontSize: 16,
    lineHeight: 28,
    marginBottom: 24,
  },
  altBodyContainer: {
    padding: 16,
    borderRadius: 12,
    borderWidth: 1,
    marginBottom: 24,
  },
  altBodyLabel: {
    fontSize: 12,
    fontWeight: '600',
    textTransform: 'uppercase',
    marginBottom: 10,
  },
  altBody: {
    fontSize: 14,
    lineHeight: 24,
  },
  shareButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
    paddingVertical: 16,
    borderRadius: 12,
  },
  shareButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '700',
  },
});
