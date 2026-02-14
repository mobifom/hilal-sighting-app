/**
 * App Context Provider
 * Manages global app state including language, theme, and user data
 */

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { api } from '../api/client';
import { Colors } from '../utils/theme';

type Theme = 'light' | 'dark';
type Language = 'en' | 'ar';

interface User {
  id: number;
  email: string;
  displayName: string;
}

interface HijriDate {
  day: number;
  month: number;
  year: number;
  monthName: string;
  monthNameEn: string;
  monthNameAr: string;
  status: string;
}

interface AppContextType {
  // Theme
  theme: Theme;
  setTheme: (theme: Theme) => void;
  colors: typeof Colors.dark | typeof Colors.light;

  // Language
  language: Language;
  setLanguage: (lang: Language) => void;
  isRTL: boolean;
  t: (en: string, ar: string) => string;

  // User
  user: User | null;
  setUser: (user: User | null) => void;
  isAuthenticated: boolean;
  logout: () => Promise<void>;

  // Hijri Date
  hijriDate: HijriDate | null;
  refreshHijriDate: () => Promise<void>;

  // Loading
  isLoading: boolean;
}

const AppContext = createContext<AppContextType | undefined>(undefined);

const STORAGE_KEYS = {
  THEME: 'hilal_theme',
  LANGUAGE: 'hilal_language',
  USER: 'hilal_user',
};

export function AppProvider({ children }: { children: ReactNode }) {
  const [theme, setThemeState] = useState<Theme>('dark');
  const [language, setLanguageState] = useState<Language>('en');
  const [user, setUserState] = useState<User | null>(null);
  const [hijriDate, setHijriDate] = useState<HijriDate | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Load persisted state on mount
  useEffect(() => {
    loadPersistedState();
  }, []);

  // Update API language when language changes
  useEffect(() => {
    api.setLanguage(language);
  }, [language]);

  const loadPersistedState = async () => {
    try {
      const [storedTheme, storedLanguage, storedUser] = await Promise.all([
        AsyncStorage.getItem(STORAGE_KEYS.THEME),
        AsyncStorage.getItem(STORAGE_KEYS.LANGUAGE),
        AsyncStorage.getItem(STORAGE_KEYS.USER),
      ]);

      if (storedTheme === 'light' || storedTheme === 'dark') {
        setThemeState(storedTheme);
      }

      if (storedLanguage === 'en' || storedLanguage === 'ar') {
        setLanguageState(storedLanguage);
      }

      if (storedUser) {
        setUserState(JSON.parse(storedUser));
      }

      // Fetch today's Hijri date
      await refreshHijriDate();
    } catch (error) {
      console.error('Error loading persisted state:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const setTheme = async (newTheme: Theme) => {
    setThemeState(newTheme);
    await AsyncStorage.setItem(STORAGE_KEYS.THEME, newTheme);
  };

  const setLanguage = async (newLang: Language) => {
    setLanguageState(newLang);
    await AsyncStorage.setItem(STORAGE_KEYS.LANGUAGE, newLang);
    api.setLanguage(newLang);
  };

  const setUser = async (newUser: User | null) => {
    setUserState(newUser);
    if (newUser) {
      await AsyncStorage.setItem(STORAGE_KEYS.USER, JSON.stringify(newUser));
    } else {
      await AsyncStorage.removeItem(STORAGE_KEYS.USER);
    }
  };

  const logout = async () => {
    await api.clearToken();
    await setUser(null);
  };

  const refreshHijriDate = async () => {
    try {
      const data = await api.getToday();
      setHijriDate({
        day: data.hijri_date.day,
        month: data.hijri_date.month,
        year: data.hijri_date.year,
        monthName: data.hijri_date.month_name,
        monthNameEn: data.hijri_date.month_name_en,
        monthNameAr: data.hijri_date.month_name_ar,
        status: data.hijri_date.status,
      });
    } catch (error) {
      console.error('Error fetching Hijri date:', error);
    }
  };

  // Translation helper
  const t = (en: string, ar: string): string => {
    return language === 'ar' ? ar : en;
  };

  const colors = theme === 'dark' ? Colors.dark : Colors.light;

  const value: AppContextType = {
    theme,
    setTheme,
    colors,
    language,
    setLanguage,
    isRTL: language === 'ar',
    t,
    user,
    setUser,
    isAuthenticated: !!user,
    logout,
    hijriDate,
    refreshHijriDate,
    isLoading,
  };

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

export function useApp() {
  const context = useContext(AppContext);
  if (context === undefined) {
    throw new Error('useApp must be used within an AppProvider');
  }
  return context;
}

// Export specific hooks for convenience
export const useTheme = () => {
  const { theme, setTheme, colors } = useApp();
  return { theme, setTheme, colors };
};

export const useLanguage = () => {
  const { language, setLanguage, isRTL, t } = useApp();
  return { language, setLanguage, isRTL, t };
};

export const useAuth = () => {
  const { user, setUser, isAuthenticated, logout } = useApp();
  return { user, setUser, isAuthenticated, logout };
};

export const useHijriDate = () => {
  const { hijriDate, refreshHijriDate } = useApp();
  return { hijriDate, refreshHijriDate };
};
