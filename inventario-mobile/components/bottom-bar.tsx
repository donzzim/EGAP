import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import { router, usePathname, type Href } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, Pressable, StyleSheet, Text, View } from 'react-native';
import { authApi } from '@/src/api/auth';
import {
  setPatrimonioNavigationDirectionFromRoutes,
  type PatrimonioRoute,
} from '@/src/navigation/patrimonioNavigation';

interface BottomBarItem {
  href: PatrimonioRoute;
  label: string;
  icon: keyof typeof MaterialIcons.glyphMap;
}

const ITEMS: BottomBarItem[] = [
  {
    href: '/patrimonio/principal',
    label: 'Início',
    icon: 'home',
  },
  {
    href: '/patrimonio/bens',
    label: 'Bens',
    icon: 'inventory-2',
  },
  {
    href: '/patrimonio/conferencia',
    label: 'Conferência',
    icon: 'fact-check',
  },
];

export function BottomBar() {
  const pathname = usePathname();
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  function handleNavigate(href: BottomBarItem['href']) {
    if (pathname === href) {
      return;
    }

    setPatrimonioNavigationDirectionFromRoutes(pathname, href);
    router.replace(href as Href);
  }

  async function handleLogout() {
    setIsLoggingOut(true);

    try {
      await authApi.logout();
    } finally {
      router.replace('/');
    }
  }

  return (
    <View style={styles.container}>
      {ITEMS.map((item) => {
        const isActive = pathname === item.href;

        return (
          <Pressable
            key={item.href}
            onPress={() => handleNavigate(item.href)}
            style={({ pressed }) => [
              styles.item,
              isActive && styles.itemActive,
              pressed && !isActive && styles.itemPressed,
            ]}>
            <MaterialIcons
              name={item.icon}
              size={22}
              color={isActive ? '#FFFFFF' : '#1E4E79'}
            />
            <Text style={[styles.label, isActive && styles.labelActive]}>
              {item.label}
            </Text>
          </Pressable>
        );
      })}
      <Pressable
        disabled={isLoggingOut}
        onPress={handleLogout}
        style={({ pressed }) => [
          styles.item,
          styles.logoutItem,
          (pressed || isLoggingOut) && styles.itemPressed,
        ]}>
        {isLoggingOut ? (
          <ActivityIndicator color="#C53030" />
        ) : (
          <MaterialIcons name="logout" size={22} color="#C53030" />
        )}
        <Text style={[styles.label, styles.logoutLabel]}>
          Sair
        </Text>
      </Pressable>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    minHeight: 68,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderTopWidth: 1,
    borderTopColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 12,
    paddingTop: 8,
    paddingBottom: 8,
  },
  item: {
    minHeight: 50,
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 3,
    borderRadius: 8,
  },
  itemActive: {
    backgroundColor: '#1E4E79',
  },
  itemPressed: {
    backgroundColor: '#EAF4FB',
  },
  logoutItem: {
    maxWidth: 72,
  },
  label: {
    color: '#1E4E79',
    fontSize: 11,
    fontWeight: '800',
  },
  labelActive: {
    color: '#FFFFFF',
  },
  logoutLabel: {
    color: '#C53030',
  },
});
