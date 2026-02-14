/**
 * Announcements Screen
 * Displays official moon sighting confirmations and Islamic events
 */

import React, { useEffect, useState, useCallback } from 'react';
import {
  View,
  Text,
  FlatList,
  StyleSheet,
  TouchableOpacity,
  RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';

import { useTheme, useLanguage } from '../context/AppContext';
import { api } from '../api/client';
import { Colors, Spacing, BorderRadius } from '../utils/theme';
import { RootStackParamList } from '../../App';

type NavigationProp = NativeStackNavigationProp<RootStackParamList>;

interface Announcement {
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
}

const FILTER_TYPES = [
  { key: '', label: 'All', labelAr: 'الكل' },
  { key: 'month_start', label: 'Month Start', labelAr: 'بداية شهر' },
  { key: 'moon_sighting', label: 'Sighting', labelAr: 'رؤية' },
  { key: 'islamic_event', label: 'Event', labelAr: 'مناسبة' },
  { key: 'general', label: 'General', labelAr: 'عام' },
];

export default function AnnouncementsScreen() {
  const navigation = useNavigation<NavigationProp>();
  const { colors } = useTheme();
  const { t, language } = useLanguage();

  const [announcements, setAnnouncements] = useState<Announcement[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [filter, setFilter] = useState('');
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);

  useEffect(() => {
    loadAnnouncements(true);
  }, [filter]);

  const loadAnnouncements = async (reset = false) => {
    try {
      if (reset) {
        setLoading(true);
        setPage(1);
      }

      const currentPage = reset ? 1 : page;
      const params: { page: number; per_page: number; type?: string } = {
        page: currentPage,
        per_page: 10,
      };

      if (filter) {
        params.type = filter;
      }

      const data = await api.getAnnouncements(params);

      if (data?.announcements) {
        if (reset) {
          setAnnouncements(data.announcements);
        } else {
          setAnnouncements((prev) => [...prev, ...data.announcements]);
        }
        setHasMore(data.pagination.current_page < data.pagination.total_pages);
        setPage(currentPage + 1);
      }
    } catch (error) {
      console.error('Error loading announcements:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    loadAnnouncements(true);
  }, [filter]);

  const onEndReached = () => {
    if (!loading && hasMore) {
      loadAnnouncements(false);
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

  const renderItem = ({ item }: { item: Announcement }) => (
    <TouchableOpacity
      style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}
      onPress={() => navigation.navigate('AnnouncementDetail', { id: item.id })}
    >
      <View style={styles.cardMeta}>
        <View style={[styles.priorityDot, { backgroundColor: getPriorityColor(item.priority) }]} />
        <View style={[styles.typeBadge, { backgroundColor: Colors.light.goldLight }]}>
          <Text style={[styles.typeBadgeText, { color: Colors.light.gold }]}>
            {getTypeLabel(item.type)}
          </Text>
        </View>
        <Text style={[styles.date, { color: colors.muted }]}>
          {new Date(item.published_date).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
          })}
        </Text>
      </View>

      <Text style={[styles.title, { color: colors.text }]}>
        {language === 'ar' ? item.title_ar : item.title_en}
      </Text>
      <Text style={[styles.titleAlt, { color: colors.muted }]}>
        {language === 'ar' ? item.title_en : item.title_ar}
      </Text>

      <Text style={[styles.body, { color: colors.muted }]} numberOfLines={3}>
        {language === 'ar'
          ? item.body_ar?.replace(/<[^>]*>/g, '')
          : item.body_en?.replace(/<[^>]*>/g, '')}
      </Text>

      <View style={styles.cardActions}>
        <TouchableOpacity style={[styles.actionBtn, { backgroundColor: colors.background }]}>
          <Ionicons name="share-outline" size={14} color={colors.muted} />
          <Text style={[styles.actionBtnText, { color: colors.muted }]}>
            {t('Share', 'مشاركة')}
          </Text>
        </TouchableOpacity>
        <View style={styles.readMore}>
          <Text style={[styles.readMoreText, { color: Colors.light.gold }]}>
            {t('Read More', 'اقرأ المزيد')}
          </Text>
          <Ionicons name="chevron-forward" size={14} color={Colors.light.gold} />
        </View>
      </View>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* Header */}
      <View style={[styles.header, { borderBottomColor: colors.border }]}>
        <Text style={[styles.headerTitle, { color: colors.text }]}>
          {t('Announcements', 'الإعلانات')}
        </Text>
        <Text style={[styles.headerSubtitle, { color: colors.muted }]}>
          {t(
            'Official moon sighting confirmations and Islamic events',
            'تأكيدات رؤية الهلال والمناسبات الإسلامية'
          )}
        </Text>
      </View>

      {/* Filter Tabs */}
      <View style={styles.filterContainer}>
        <FlatList
          horizontal
          showsHorizontalScrollIndicator={false}
          data={FILTER_TYPES}
          keyExtractor={(item) => item.key}
          contentContainerStyle={styles.filterList}
          renderItem={({ item }) => (
            <TouchableOpacity
              style={[
                styles.filterBtn,
                {
                  backgroundColor: filter === item.key ? Colors.dark.gold : 'transparent',
                  borderColor: filter === item.key ? Colors.dark.gold : colors.border,
                },
              ]}
              onPress={() => setFilter(item.key)}
            >
              <Text
                style={[
                  styles.filterBtnText,
                  { color: filter === item.key ? '#fff' : colors.muted },
                ]}
              >
                {language === 'ar' ? item.labelAr : item.label}
              </Text>
            </TouchableOpacity>
          )}
        />
      </View>

      {/* Announcements List */}
      <FlatList
        data={announcements}
        keyExtractor={(item) => item.id.toString()}
        renderItem={renderItem}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={Colors.dark.gold}
          />
        }
        onEndReached={onEndReached}
        onEndReachedThreshold={0.5}
        ListEmptyComponent={
          !loading ? (
            <View style={styles.empty}>
              <Text style={[styles.emptyText, { color: colors.muted }]}>
                {t('No announcements at this time.', 'لا توجد إعلانات حالياً.')}
              </Text>
            </View>
          ) : null
        }
      />
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
    fontSize: 13,
    marginTop: 4,
    textAlign: 'center',
  },
  filterContainer: {
    paddingVertical: Spacing.md,
  },
  filterList: {
    paddingHorizontal: Spacing.base,
    gap: 8,
  },
  filterBtn: {
    paddingVertical: 6,
    paddingHorizontal: 16,
    borderRadius: 20,
    borderWidth: 1,
    marginRight: 8,
  },
  filterBtnText: {
    fontSize: 12,
    fontWeight: '600',
  },
  listContent: {
    paddingHorizontal: Spacing.base,
    paddingBottom: 40,
  },
  card: {
    borderRadius: 14,
    borderWidth: 1,
    padding: 20,
    marginBottom: 14,
  },
  cardMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 10,
  },
  priorityDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  typeBadge: {
    paddingHorizontal: 10,
    paddingVertical: 2,
    borderRadius: 20,
  },
  typeBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  date: {
    fontSize: 12,
    marginLeft: 'auto',
  },
  title: {
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 2,
  },
  titleAlt: {
    fontSize: 16,
    marginBottom: 10,
  },
  body: {
    fontSize: 13,
    lineHeight: 22,
    marginBottom: 14,
  },
  cardActions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  actionBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingVertical: 6,
    paddingHorizontal: 14,
    borderRadius: 8,
  },
  actionBtnText: {
    fontSize: 11,
  },
  readMore: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  readMoreText: {
    fontSize: 13,
    fontWeight: '600',
  },
  empty: {
    padding: Spacing.xl * 2,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 14,
  },
});
