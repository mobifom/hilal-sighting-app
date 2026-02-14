/**
 * FAQ Screen
 * Displays frequently asked questions with accordion UI
 */

import React, { useEffect, useState, useCallback } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  RefreshControl,
  ActivityIndicator,
  TextInput,
  LayoutAnimation,
  Platform,
  UIManager,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';

import { useTheme, useLanguage } from '../context/AppContext';
import { api } from '../api/client';
import { Colors, Spacing } from '../utils/theme';

// Enable LayoutAnimation for Android
if (Platform.OS === 'android' && UIManager.setLayoutAnimationEnabledExperimental) {
  UIManager.setLayoutAnimationEnabledExperimental(true);
}

interface FAQ {
  id: number;
  question_en: string;
  question_ar: string;
  answer_en: string;
  answer_ar: string;
  category: string;
  category_label: string;
  category_label_ar?: string;
  is_featured?: boolean;
}

interface FAQGroup {
  key: string;
  label: string;
  label_ar: string;
  faqs: FAQ[];
}

const CATEGORIES = [
  { key: 'all', label_en: 'All', label_ar: 'الكل' },
  { key: 'general', label_en: 'General', label_ar: 'عام' },
  { key: 'moon_sighting', label_en: 'Moon Sighting', label_ar: 'رؤية الهلال' },
  { key: 'prayer_times', label_en: 'Prayer Times', label_ar: 'أوقات الصلاة' },
  { key: 'calendar', label_en: 'Calendar', label_ar: 'التقويم' },
  { key: 'technical', label_en: 'Technical', label_ar: 'تقني' },
];

export default function FAQScreen() {
  const { colors } = useTheme();
  const { t, language } = useLanguage();

  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [faqs, setFaqs] = useState<FAQ[]>([]);
  const [groupedFaqs, setGroupedFaqs] = useState<FAQGroup[]>([]);
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [expandedIds, setExpandedIds] = useState<Set<number>>(new Set());
  const [searching, setSearching] = useState(false);

  useEffect(() => {
    loadFAQs();
  }, []);

  const loadFAQs = async () => {
    try {
      setLoading(true);
      const data = await api.getFAQs({ grouped: true });

      if (Array.isArray(data) && data.length > 0 && 'faqs' in data[0]) {
        // Grouped FAQs response
        setGroupedFaqs(data as FAQGroup[]);
        const allFaqs = (data as FAQGroup[]).flatMap(group => group.faqs);
        setFaqs(allFaqs);
      } else if (Array.isArray(data) && data.length > 0) {
        // Flat FAQs response
        setFaqs(data as FAQ[]);
      }
    } catch {
      // Error loading FAQs - will show empty state
      setFaqs([]);
      setGroupedFaqs([]);
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    await loadFAQs();
    setRefreshing(false);
  }, []);

  const handleSearch = async () => {
    if (searchQuery.trim().length < 2) return;

    try {
      setSearching(true);
      const result = await api.searchFAQs(searchQuery, language === 'ar' ? 'ar' : 'en');
      setFaqs(result.results as FAQ[]);
      setSelectedCategory('all');
    } catch {
      // Search error
    } finally {
      setSearching(false);
    }
  };

  const clearSearch = () => {
    setSearchQuery('');
    loadFAQs();
  };

  const toggleExpanded = (id: number) => {
    LayoutAnimation.configureNext(LayoutAnimation.Presets.easeInEaseOut);
    setExpandedIds(prev => {
      const newSet = new Set(prev);
      if (newSet.has(id)) {
        newSet.delete(id);
      } else {
        newSet.add(id);
      }
      return newSet;
    });
  };

  const getFilteredFaqs = () => {
    if (selectedCategory === 'all') {
      return faqs;
    }
    return faqs.filter(faq => faq.category === selectedCategory);
  };

  const filteredFaqs = getFilteredFaqs();

  const stripHtml = (html: string) => {
    return html
      .replace(/<[^>]*>/g, '')
      .replace(/&nbsp;/g, ' ')
      .replace(/&amp;/g, '&')
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .replace(/&quot;/g, '"')
      .trim();
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['bottom']}>
      <ScrollView
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={Colors.dark.gold} />
        }
      >
        {/* Page Header */}
        <View style={styles.pageHeader}>
          <Text style={[styles.pageTitle, { color: colors.text }]}>
            {t('Frequently Asked Questions', 'الأسئلة الشائعة')}
          </Text>
          <Text style={[styles.pageSubtitle, { color: colors.muted }]}>
            {t('Find answers to common questions', 'ابحث عن إجابات للأسئلة الشائعة')}
          </Text>
        </View>

        {/* Search Box */}
        <View style={styles.searchContainer}>
          <View style={[styles.searchBox, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <Ionicons name="search" size={18} color={colors.muted} />
            <TextInput
              style={[styles.searchInput, { color: colors.text }]}
              placeholder={t('Search questions...', 'ابحث في الأسئلة...')}
              placeholderTextColor={colors.muted}
              value={searchQuery}
              onChangeText={setSearchQuery}
              onSubmitEditing={handleSearch}
              returnKeyType="search"
            />
            {searchQuery.length > 0 && (
              <TouchableOpacity onPress={clearSearch}>
                <Ionicons name="close-circle" size={18} color={colors.muted} />
              </TouchableOpacity>
            )}
          </View>
          {searchQuery.length > 0 && (
            <TouchableOpacity
              style={[styles.searchButton, { backgroundColor: Colors.dark.gold }]}
              onPress={handleSearch}
              disabled={searching}
            >
              {searching ? (
                <ActivityIndicator size="small" color="#fff" />
              ) : (
                <Text style={styles.searchButtonText}>{t('Search', 'بحث')}</Text>
              )}
            </TouchableOpacity>
          )}
        </View>

        {/* Category Tabs */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={styles.categoryScroll}
          contentContainerStyle={styles.categoryContainer}
        >
          {CATEGORIES.map(cat => (
            <TouchableOpacity
              key={cat.key}
              style={[
                styles.categoryTab,
                { backgroundColor: colors.card, borderColor: colors.border },
                selectedCategory === cat.key && styles.categoryTabActive,
              ]}
              onPress={() => setSelectedCategory(cat.key)}
            >
              <Text
                style={[
                  styles.categoryTabText,
                  { color: selectedCategory === cat.key ? Colors.dark.gold : colors.muted },
                ]}
              >
                {language === 'ar' ? cat.label_ar : cat.label_en}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        {/* FAQ List */}
        {loading ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color={Colors.dark.gold} />
            <Text style={[styles.loadingText, { color: colors.muted }]}>
              {t('Loading FAQs...', 'جاري تحميل الأسئلة...')}
            </Text>
          </View>
        ) : filteredFaqs.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Ionicons name="help-circle-outline" size={64} color={colors.muted} />
            <Text style={[styles.emptyText, { color: colors.muted }]}>
              {t('No FAQs found', 'لم يتم العثور على أسئلة')}
            </Text>
            {searchQuery && (
              <TouchableOpacity onPress={clearSearch} style={styles.clearButton}>
                <Text style={[styles.clearButtonText, { color: Colors.dark.gold }]}>
                  {t('Clear search', 'مسح البحث')}
                </Text>
              </TouchableOpacity>
            )}
          </View>
        ) : (
          <View style={styles.faqList}>
            {filteredFaqs.map((faq, index) => {
              const isExpanded = expandedIds.has(faq.id);
              const question = language === 'ar' ? faq.question_ar : faq.question_en;
              const answer = language === 'ar' ? faq.answer_ar : faq.answer_en;
              const categoryLabel = language === 'ar' ? faq.category_label_ar : faq.category_label;

              return (
                <View
                  key={faq.id}
                  style={[
                    styles.faqItem,
                    { backgroundColor: colors.card, borderColor: colors.border },
                    index === 0 && styles.faqItemFirst,
                  ]}
                >
                  <TouchableOpacity
                    style={styles.faqQuestion}
                    onPress={() => toggleExpanded(faq.id)}
                    activeOpacity={0.7}
                  >
                    <View style={styles.faqQuestionContent}>
                      {selectedCategory === 'all' && (
                        <View style={[styles.categoryBadge, { backgroundColor: Colors.light.goldLight }]}>
                          <Text style={[styles.categoryBadgeText, { color: Colors.dark.gold }]}>
                            {categoryLabel}
                          </Text>
                        </View>
                      )}
                      <Text style={[styles.faqQuestionText, { color: colors.text }]}>
                        {question}
                      </Text>
                    </View>
                    <Ionicons
                      name={isExpanded ? 'remove' : 'add'}
                      size={24}
                      color={Colors.dark.gold}
                    />
                  </TouchableOpacity>

                  {isExpanded && (
                    <View style={[styles.faqAnswer, { borderTopColor: colors.border }]}>
                      <Text style={[styles.faqAnswerText, { color: colors.muted }]}>
                        {stripHtml(answer)}
                      </Text>
                    </View>
                  )}
                </View>
              );
            })}
          </View>
        )}

        {/* Contact Section */}
        <View style={[styles.contactSection, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <Ionicons name="mail-outline" size={32} color={Colors.dark.gold} />
          <Text style={[styles.contactTitle, { color: colors.text }]}>
            {t('Still have questions?', 'لا زال لديك أسئلة؟')}
          </Text>
          <Text style={[styles.contactText, { color: colors.muted }]}>
            {t(
              "If you couldn't find the answer you were looking for, feel free to contact us.",
              'إذا لم تجد الإجابة التي تبحث عنها، لا تتردد في التواصل معنا.'
            )}
          </Text>
          <TouchableOpacity style={[styles.contactButton, { backgroundColor: Colors.dark.gold }]}>
            <Ionicons name="mail" size={18} color="#fff" />
            <Text style={styles.contactButtonText}>{t('Contact Us', 'اتصل بنا')}</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  pageHeader: {
    paddingHorizontal: Spacing.base,
    paddingTop: Spacing.base,
    paddingBottom: 8,
  },
  pageTitle: {
    fontSize: 24,
    fontWeight: '700',
    marginBottom: 4,
  },
  pageSubtitle: {
    fontSize: 14,
    lineHeight: 20,
  },
  searchContainer: {
    flexDirection: 'row',
    paddingHorizontal: Spacing.base,
    paddingTop: Spacing.base,
    gap: 8,
  },
  searchBox: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    borderRadius: 10,
    borderWidth: 1,
    gap: 8,
  },
  searchInput: {
    flex: 1,
    paddingVertical: 12,
    fontSize: 15,
  },
  searchButton: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  searchButtonText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 14,
  },
  categoryScroll: {
    marginTop: 16,
  },
  categoryContainer: {
    paddingHorizontal: Spacing.base,
    gap: 8,
  },
  categoryTab: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    borderWidth: 1,
  },
  categoryTabActive: {
    borderColor: Colors.dark.gold,
    backgroundColor: Colors.light.goldLight,
  },
  categoryTabText: {
    fontSize: 13,
    fontWeight: '500',
  },
  loadingContainer: {
    padding: 60,
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 16,
    fontSize: 14,
  },
  emptyContainer: {
    padding: 60,
    alignItems: 'center',
  },
  emptyText: {
    marginTop: 16,
    fontSize: 16,
  },
  clearButton: {
    marginTop: 12,
    padding: 8,
  },
  clearButtonText: {
    fontSize: 14,
    fontWeight: '500',
  },
  faqList: {
    padding: Spacing.base,
    gap: 8,
  },
  faqItem: {
    borderRadius: 12,
    borderWidth: 1,
    overflow: 'hidden',
  },
  faqItemFirst: {
    marginTop: 8,
  },
  faqQuestion: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    gap: 12,
  },
  faqQuestionContent: {
    flex: 1,
    gap: 8,
  },
  categoryBadge: {
    alignSelf: 'flex-start',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
  },
  categoryBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  faqQuestionText: {
    fontSize: 15,
    fontWeight: '500',
    lineHeight: 22,
  },
  faqAnswer: {
    paddingHorizontal: 16,
    paddingBottom: 16,
    borderTopWidth: 1,
  },
  faqAnswerText: {
    fontSize: 14,
    lineHeight: 22,
    paddingTop: 12,
  },
  contactSection: {
    margin: Spacing.base,
    marginTop: 24,
    marginBottom: 40,
    padding: 24,
    borderRadius: 14,
    borderWidth: 1,
    alignItems: 'center',
  },
  contactTitle: {
    fontSize: 18,
    fontWeight: '700',
    marginTop: 12,
    marginBottom: 8,
  },
  contactText: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 16,
  },
  contactButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 10,
  },
  contactButtonText: {
    color: '#fff',
    fontSize: 15,
    fontWeight: '600',
  },
});
