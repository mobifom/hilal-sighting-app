/**
 * Sightings Screen
 * Displays approved crescent sightings from the community
 */

import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  RefreshControl,
  ActivityIndicator,
  Linking,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';

import { useTheme, useLanguage } from '../context/AppContext';
import { api } from '../api/client';
import { Colors, Spacing, BorderRadius } from '../utils/theme';

interface Sighting {
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
}

export default function SightingsScreen() {
  const { colors } = useTheme();
  const { t, language } = useLanguage();

  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [sightings, setSightings] = useState<Sighting[]>([]);

  useEffect(() => {
    loadSightings();
  }, []);

  const loadSightings = async () => {
    try {
      const data = await api.getApprovedSightings();
      if (data?.sightings) {
        setSightings(data.sightings);
      }
    } catch (error) {
      console.error('Error loading sightings:', error);
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    await loadSightings();
    setRefreshing(false);
  }, []);

  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
  };

  const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    });
  };

  const openAttachment = (url: string) => {
    Linking.openURL(url);
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

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      <ScrollView
        contentContainerStyle={styles.content}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={Colors.dark.gold}
          />
        }
      >
        {/* Header */}
        <View style={styles.header}>
          <Text style={styles.headerIcon}>🌙</Text>
          <Text style={[styles.headerTitle, { color: colors.text }]}>
            {t('Crescent Sightings', 'رؤية الهلال')}
          </Text>
          <Text style={[styles.headerSubtitle, { color: colors.muted }]}>
            {t('Approved sightings from the community', 'رؤى معتمدة من المجتمع')}
          </Text>
        </View>

        {/* Sightings List */}
        {sightings.length > 0 ? (
          sightings.map((sighting) => (
            <View
              key={sighting.id}
              style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}
            >
              {/* Header with date */}
              <View style={styles.cardHeader}>
                <Text style={[styles.cardTitle, { color: colors.text }]}>
                  {sighting.title}
                </Text>
                <Text style={[styles.cardDate, { color: colors.muted }]}>
                  {formatDate(sighting.submitted_at)}
                </Text>
              </View>

              {/* Details */}
              {sighting.details && (
                <Text style={[styles.cardDetails, { color: colors.text }]}>
                  {sighting.details}
                </Text>
              )}

              {/* PDF Attachment */}
              {sighting.attachment && (
                <TouchableOpacity
                  style={[styles.attachmentBox, { backgroundColor: colors.background }]}
                  onPress={() => openAttachment(sighting.attachment!.url)}
                >
                  <Text style={styles.attachmentIcon}>📄</Text>
                  <View style={styles.attachmentInfo}>
                    <Text style={[styles.attachmentName, { color: colors.text }]}>
                      {sighting.attachment.filename}
                    </Text>
                    <Text style={[styles.attachmentSize, { color: colors.muted }]}>
                      {formatFileSize(sighting.attachment.filesize)}
                    </Text>
                  </View>
                  <View style={[styles.downloadBtn, { borderColor: Colors.dark.gold }]}>
                    <Ionicons name="download-outline" size={16} color={Colors.dark.gold} />
                    <Text style={[styles.downloadBtnText, { color: Colors.dark.gold }]}>
                      {t('Download', 'تحميل')}
                    </Text>
                  </View>
                </TouchableOpacity>
              )}

              {/* Verified Badge */}
              <View style={styles.verifiedBadge}>
                <Ionicons name="checkmark-circle" size={16} color={Colors.status.success} />
                <Text style={[styles.verifiedText, { color: Colors.status.success }]}>
                  {t('Verified & Approved', 'تم التحقق والموافقة')}
                </Text>
              </View>
            </View>
          ))
        ) : (
          /* No Sightings */
          <View style={[styles.emptyCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <Text style={styles.emptyIcon}>🔭</Text>
            <Text style={[styles.emptyTitle, { color: colors.text }]}>
              {t('No Approved Sightings Yet', 'لا توجد رؤى معتمدة بعد')}
            </Text>
            <Text style={[styles.emptyText, { color: colors.muted }]}>
              {t('No crescent sightings have been approved yet.', 'لم تتم الموافقة على أي رؤية هلال حتى الآن.')}
            </Text>
          </View>
        )}
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
    justifyContent: 'center',
    alignItems: 'center',
  },
  content: {
    padding: Spacing.base,
  },
  header: {
    alignItems: 'center',
    marginBottom: Spacing.lg,
  },
  headerIcon: {
    fontSize: 40,
    marginBottom: 8,
  },
  headerTitle: {
    fontSize: 22,
    fontWeight: '800',
  },
  headerSubtitle: {
    fontSize: 14,
    marginTop: 4,
    textAlign: 'center',
  },
  card: {
    borderRadius: 14,
    borderWidth: 1,
    padding: 16,
    marginBottom: 16,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(0,0,0,0.05)',
    marginBottom: 12,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '600',
    flex: 1,
  },
  cardDate: {
    fontSize: 13,
  },
  cardDetails: {
    fontSize: 14,
    lineHeight: 22,
    marginBottom: 16,
  },
  attachmentBox: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
    borderRadius: 10,
    marginBottom: 16,
  },
  attachmentIcon: {
    fontSize: 28,
    marginRight: 12,
  },
  attachmentInfo: {
    flex: 1,
  },
  attachmentName: {
    fontSize: 14,
    fontWeight: '500',
  },
  attachmentSize: {
    fontSize: 12,
    marginTop: 2,
  },
  downloadBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 8,
    borderWidth: 1,
  },
  downloadBtnText: {
    fontSize: 13,
    fontWeight: '600',
  },
  verifiedBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: 'rgba(0,0,0,0.05)',
  },
  verifiedText: {
    fontSize: 13,
    fontWeight: '500',
  },
  emptyCard: {
    borderRadius: 14,
    borderWidth: 1,
    padding: 40,
    alignItems: 'center',
  },
  emptyIcon: {
    fontSize: 48,
    marginBottom: 16,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 8,
  },
  emptyText: {
    fontSize: 14,
    textAlign: 'center',
  },
});
