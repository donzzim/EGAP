import { createContext, type ReactNode, useContext, useEffect, useMemo, useState } from 'react';
import { Appearance, useColorScheme as useNativeColorScheme } from 'react-native';
import { appStorage } from '@/src/storage/appStorage';

export type AppThemeMode = 'light' | 'dark';

const THEME_STORAGE_KEY = 'egap-mobile-theme-mode';

export const APP_THEME_COLORS = {
  light: {
    screen: '#F4F7FA',
    surface: '#FFFFFF',
    surfaceMuted: '#F8FAFC',
    surfaceAccent: '#EAF4FB',
    border: '#D9E2EC',
    borderAccent: '#B6D4EA',
    text: '#102A43',
    textMuted: '#627D98',
    textSubtle: '#829AB1',
    primary: '#1E4E79',
    primarySoft: '#EAF4FB',
    primaryText: '#FFFFFF',
    overlay: '#102A4399',
    track: '#D9E2EC',
    input: '#FFFFFF',
    success: '#2F855A',
    successSoft: '#E6F4EA',
    warning: '#B7791F',
    warningSoft: '#FFF4D6',
    info: '#1E4E79',
    infoSoft: '#D9E8F5',
    purple: '#805AD5',
    purpleSoft: '#F0EBFF',
    danger: '#C53030',
    dangerSoft: '#FFF5F5',
    dangerPressed: '#FFE3E3',
  },
  dark: {
    screen: '#07111D',
    surface: '#0F1B2A',
    surfaceMuted: '#132438',
    surfaceAccent: '#17324A',
    border: '#29435C',
    borderAccent: '#3E6E95',
    text: '#F3F8FC',
    textMuted: '#A8BED2',
    textSubtle: '#7F98AD',
    primary: '#7CC7F7',
    primarySoft: '#12304A',
    primaryText: '#07111D',
    overlay: '#030913D9',
    track: '#26394D',
    input: '#0B1624',
    success: '#5FD6A2',
    successSoft: '#123528',
    warning: '#F6C85F',
    warningSoft: '#392B12',
    info: '#7CC7F7',
    infoSoft: '#12304A',
    purple: '#B9A4FF',
    purpleSoft: '#261F45',
    danger: '#FF8F8F',
    dangerSoft: '#351A24',
    dangerPressed: '#522832',
  },
};

interface AppThemeContextValue {
  colors: typeof APP_THEME_COLORS.light;
  isDark: boolean;
  mode: AppThemeMode;
  setMode: (mode: AppThemeMode) => Promise<void>;
}

const AppThemeContext = createContext<AppThemeContextValue | null>(null);

function normalizeThemeMode(value: string | null): AppThemeMode | null {
  if (value === 'light' || value === 'dark') {
    return value;
  }

  return null;
}

export function AppThemeProvider({ children }: { children: ReactNode }) {
  const nativeColorScheme = useNativeColorScheme();
  const [mode, setModeState] = useState<AppThemeMode>(
    nativeColorScheme === 'dark' ? 'dark' : 'light',
  );

  useEffect(() => {
    let isMounted = true;

    async function loadThemeMode() {
      const storedMode = normalizeThemeMode(await appStorage.getItem(THEME_STORAGE_KEY));

      if (isMounted && storedMode) {
        setModeState(storedMode);
        Appearance.setColorScheme(storedMode);
      }
    }

    loadThemeMode();

    return () => {
      isMounted = false;
    };
  }, []);

  const value = useMemo<AppThemeContextValue>(() => {
    const setMode = async (nextMode: AppThemeMode) => {
      setModeState(nextMode);
      Appearance.setColorScheme(nextMode);
      await appStorage.setItem(THEME_STORAGE_KEY, nextMode);
    };

    return {
      colors: APP_THEME_COLORS[mode],
      isDark: mode === 'dark',
      mode,
      setMode,
    };
  }, [mode]);

  return (
    <AppThemeContext.Provider value={value}>
      {children}
    </AppThemeContext.Provider>
  );
}

export function useAppTheme() {
  const context = useContext(AppThemeContext);

  if (!context) {
    throw new Error('useAppTheme must be used inside AppThemeProvider.');
  }

  return context;
}

export function useAppColorScheme(): AppThemeMode {
  return useAppTheme().mode;
}
