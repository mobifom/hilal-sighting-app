/**
 * Hilal Mobile App
 * Islamic Moon Sighting Platform for New Zealand
 */

import React from 'react';
import { StatusBar } from 'expo-status-bar';
import { NavigationContainer } from '@react-navigation/native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { Ionicons } from '@expo/vector-icons';
import { View, ActivityIndicator, StyleSheet } from 'react-native';

import { AppProvider, useApp, useTheme, useLanguage } from './src/context/AppContext';
import { Colors } from './src/utils/theme';

// Screens
import HomeScreen from './src/screens/HomeScreen';
import CalendarScreen from './src/screens/CalendarScreen';
import AnnouncementsScreen from './src/screens/AnnouncementsScreen';
import SightingsScreen from './src/screens/SightingsScreen';
import SettingsScreen from './src/screens/SettingsScreen';
import PrayerTimesScreen from './src/screens/PrayerTimesScreen';
import QiblaScreen from './src/screens/QiblaScreen';
import AnnouncementDetailScreen from './src/screens/AnnouncementDetailScreen';

// Navigation types
export type RootStackParamList = {
  Main: undefined;
  PrayerTimes: undefined;
  Qibla: undefined;
  AnnouncementDetail: { id: number };
};

export type TabParamList = {
  Home: undefined;
  Calendar: undefined;
  Sightings: undefined;
  Announcements: undefined;
  Settings: undefined;
};

const Stack = createNativeStackNavigator<RootStackParamList>();
const Tab = createBottomTabNavigator<TabParamList>();

// Tab Navigator
function TabNavigator() {
  const { colors, theme } = useTheme();
  const { t } = useLanguage();

  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        tabBarIcon: ({ focused, color, size }) => {
          let iconName: keyof typeof Ionicons.glyphMap;

          switch (route.name) {
            case 'Home':
              iconName = focused ? 'home' : 'home-outline';
              break;
            case 'Calendar':
              iconName = focused ? 'calendar' : 'calendar-outline';
              break;
            case 'Sightings':
              iconName = focused ? 'moon' : 'moon-outline';
              break;
            case 'Announcements':
              iconName = focused ? 'megaphone' : 'megaphone-outline';
              break;
            case 'Settings':
              iconName = focused ? 'settings' : 'settings-outline';
              break;
            default:
              iconName = 'ellipse';
          }

          return <Ionicons name={iconName} size={size} color={color} />;
        },
        tabBarActiveTintColor: theme === 'dark' ? Colors.dark.gold : Colors.light.gold,
        tabBarInactiveTintColor: colors.muted,
        tabBarStyle: {
          backgroundColor: theme === 'dark' ? Colors.dark.backgroundSecondary : Colors.light.card,
          borderTopColor: colors.border,
          borderTopWidth: 1,
          paddingTop: 4,
          height: 85,
          paddingBottom: 25,
        },
        tabBarLabelStyle: {
          fontSize: 10,
          fontWeight: '600',
        },
        headerShown: false,
      })}
    >
      <Tab.Screen
        name="Home"
        component={HomeScreen}
        options={{ tabBarLabel: t('Home', 'الرئيسية') }}
      />
      <Tab.Screen
        name="Calendar"
        component={CalendarScreen}
        options={{ tabBarLabel: t('Calendar', 'التقويم') }}
      />
      <Tab.Screen
        name="Sightings"
        component={SightingsScreen}
        options={{ tabBarLabel: t('Sightings', 'الرؤى') }}
      />
      <Tab.Screen
        name="Announcements"
        component={AnnouncementsScreen}
        options={{ tabBarLabel: t('News', 'الإعلانات') }}
      />
      <Tab.Screen
        name="Settings"
        component={SettingsScreen}
        options={{ tabBarLabel: t('Settings', 'الإعدادات') }}
      />
    </Tab.Navigator>
  );
}

// Main Navigation Stack
function Navigation() {
  const { colors, theme } = useTheme();

  return (
    <NavigationContainer
      theme={{
        dark: theme === 'dark',
        colors: {
          primary: Colors.dark.gold,
          background: colors.background,
          card: colors.card,
          text: colors.text,
          border: colors.border,
          notification: Colors.status.danger,
        },
      }}
    >
      <Stack.Navigator
        screenOptions={{
          headerShown: false,
          contentStyle: { backgroundColor: colors.background },
        }}
      >
        <Stack.Screen name="Main" component={TabNavigator} />
        <Stack.Screen
          name="PrayerTimes"
          component={PrayerTimesScreen}
          options={{
            headerShown: true,
            headerTitle: 'Prayer Times',
            headerStyle: { backgroundColor: colors.card },
            headerTintColor: colors.text,
          }}
        />
        <Stack.Screen
          name="Qibla"
          component={QiblaScreen}
          options={{
            headerShown: true,
            headerTitle: 'Qibla Direction',
            headerStyle: { backgroundColor: colors.card },
            headerTintColor: colors.text,
          }}
        />
        <Stack.Screen
          name="AnnouncementDetail"
          component={AnnouncementDetailScreen}
          options={{
            headerShown: true,
            headerTitle: '',
            headerStyle: { backgroundColor: colors.card },
            headerTintColor: colors.text,
          }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
}

// Loading Screen
function LoadingScreen() {
  return (
    <View style={styles.loading}>
      <ActivityIndicator size="large" color={Colors.dark.gold} />
    </View>
  );
}

// Main App Component
function AppContent() {
  const { isLoading, theme } = useApp();

  if (isLoading) {
    return <LoadingScreen />;
  }

  return (
    <>
      <StatusBar style={theme === 'dark' ? 'light' : 'dark'} />
      <Navigation />
    </>
  );
}

export default function App() {
  return (
    <AppProvider>
      <AppContent />
    </AppProvider>
  );
}

const styles = StyleSheet.create({
  loading: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: Colors.dark.background,
  },
});
