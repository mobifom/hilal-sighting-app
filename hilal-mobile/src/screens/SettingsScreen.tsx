/**
 * Settings Screen
 * App settings including language, theme, notifications, and account
 */

import React from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  Switch,
  Alert,
  Linking,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useTheme, useLanguage } from '../context/AppContext';
import { Colors, Spacing, BorderRadius } from '../utils/theme';
import type { RootStackParamList } from '../../App';

export default function SettingsScreen() {
  const { colors, theme, setTheme } = useTheme();
  const { t, language, setLanguage } = useLanguage();
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();

  const SettingRow = ({
    icon,
    label,
    value,
    onPress,
    isLast = false,
    showArrow = true,
  }: {
    icon: keyof typeof Ionicons.glyphMap;
    label: string;
    value?: string;
    onPress?: () => void;
    isLast?: boolean;
    showArrow?: boolean;
  }) => (
    <TouchableOpacity
      style={[
        styles.settingRow,
        !isLast && { borderBottomWidth: 1, borderBottomColor: colors.border },
      ]}
      onPress={onPress}
      disabled={!onPress}
    >
      <View style={styles.settingLeft}>
        <Ionicons name={icon} size={20} color={Colors.dark.gold} />
        <Text style={[styles.settingLabel, { color: colors.text }]}>{label}</Text>
      </View>
      <View style={styles.settingRight}>
        {value && <Text style={[styles.settingValue, { color: colors.muted }]}>{value}</Text>}
        {showArrow && onPress && (
          <Ionicons name="chevron-forward" size={18} color={colors.muted} />
        )}
      </View>
    </TouchableOpacity>
  );

  const SectionHeader = ({ title }: { title: string }) => (
    <Text style={[styles.sectionHeader, { color: Colors.dark.gold }]}>{title}</Text>
  );

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      <ScrollView>
        {/* Header */}
        <View style={[styles.header, { borderBottomColor: colors.border }]}>
          <Text style={[styles.headerTitle, { color: colors.text }]}>
            {t('Settings', 'الإعدادات')}
          </Text>
        </View>

        {/* Appearance Section */}
        <SectionHeader title={t('APPEARANCE', 'المظهر')} />
        <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <SettingRow
            icon="language"
            label={t('Language', 'اللغة')}
            value={language === 'ar' ? 'العربية' : 'English'}
            onPress={() => {
              Alert.alert(
                t('Select Language', 'اختر اللغة'),
                '',
                [
                  { text: 'English', onPress: () => setLanguage('en') },
                  { text: 'العربية', onPress: () => setLanguage('ar') },
                  { text: t('Cancel', 'إلغاء'), style: 'cancel' },
                ]
              );
            }}
          />
          <View style={[styles.settingRow, { borderBottomWidth: 0 }]}>
            <View style={styles.settingLeft}>
              <Ionicons name={theme === 'dark' ? 'moon' : 'sunny'} size={20} color={Colors.dark.gold} />
              <Text style={[styles.settingLabel, { color: colors.text }]}>
                {t('Dark Mode', 'الوضع الداكن')}
              </Text>
            </View>
            <Switch
              value={theme === 'dark'}
              onValueChange={(value) => setTheme(value ? 'dark' : 'light')}
              trackColor={{ false: colors.border, true: Colors.dark.gold }}
              thumbColor="#fff"
            />
          </View>
        </View>

        {/* Notifications Section */}
        <SectionHeader title={t('NOTIFICATIONS', 'الإشعارات')} />
        <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <View style={[styles.settingRow, { borderBottomWidth: 1, borderBottomColor: colors.border }]}>
            <View style={styles.settingLeft}>
              <Ionicons name="notifications" size={20} color={Colors.dark.gold} />
              <Text style={[styles.settingLabel, { color: colors.text }]}>
                {t('Push Notifications', 'إشعارات الدفع')}
              </Text>
            </View>
            <Switch
              value={true}
              onValueChange={() => {}}
              trackColor={{ false: colors.border, true: Colors.dark.gold }}
              thumbColor="#fff"
            />
          </View>
          <SettingRow
            icon="calendar"
            label={t('New Month Announcements', 'إعلانات الأشهر الجديدة')}
            showArrow={false}
            isLast
          />
        </View>

        {/* Prayer Settings Section */}
        <SectionHeader title={t('PRAYER TIMES', 'أوقات الصلاة')} />
        <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <SettingRow
            icon="location"
            label={t('Default City', 'المدينة الافتراضية')}
            value="Auckland"
            onPress={() => {}}
          />
          <SettingRow
            icon="calculator"
            label={t('Calculation Method', 'طريقة الحساب')}
            value="MWL"
            onPress={() => {}}
            isLast
          />
        </View>

        {/* About Section */}
        <SectionHeader title={t('ABOUT', 'حول التطبيق')} />
        <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <SettingRow
            icon="information-circle"
            label={t('About Hilal', 'عن هلال')}
            onPress={() => Linking.openURL('https://hilal.nz/about')}
          />
          <SettingRow
            icon="document-text"
            label={t('Privacy Policy', 'سياسة الخصوصية')}
            onPress={() => Linking.openURL('https://hilal.nz/privacy')}
          />
          <SettingRow
            icon="help-circle"
            label={t('FAQ', 'الأسئلة الشائعة')}
            onPress={() => navigation.navigate('FAQ')}
          />
          <SettingRow
            icon="mail"
            label={t('Contact Support', 'التواصل مع الدعم')}
            onPress={() => Linking.openURL('mailto:support@hilal.nz')}
          />
          <SettingRow
            icon="star"
            label={t('Rate App', 'تقييم التطبيق')}
            onPress={() => {}}
            isLast
          />
        </View>

        {/* Version */}
        <Text style={[styles.version, { color: colors.muted }]}>
          Hilal v1.0.0
        </Text>

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
  },
  headerTitle: {
    fontSize: 28,
    fontWeight: '800',
  },
  sectionHeader: {
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginTop: 24,
    marginBottom: 8,
    marginHorizontal: Spacing.base,
  },
  card: {
    marginHorizontal: Spacing.base,
    borderRadius: 14,
    borderWidth: 1,
    overflow: 'hidden',
  },
  settingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 16,
  },
  settingLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  settingLabel: {
    fontSize: 15,
    fontWeight: '500',
  },
  settingRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  settingValue: {
    fontSize: 14,
  },
  version: {
    textAlign: 'center',
    fontSize: 12,
    marginTop: 24,
  },
});
