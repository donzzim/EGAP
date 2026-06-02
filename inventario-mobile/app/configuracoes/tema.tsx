import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import { useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { AppMenuButton } from '@/components/app-menu-button';
import { type AppThemeMode, useAppTheme } from '@/src/theme/appTheme';

const THEME_OPTIONS: {
  icon: keyof typeof MaterialIcons.glyphMap;
  label: string;
  mode: AppThemeMode;
}[] = [
  {
    icon: 'light-mode',
    label: 'Claro',
    mode: 'light',
  },
  {
    icon: 'dark-mode',
    label: 'Escuro',
    mode: 'dark',
  },
];

export default function TemaScreen() {
  const { colors, mode, setMode } = useAppTheme();
  const [savingMode, setSavingMode] = useState<AppThemeMode | null>(null);

  async function handleSelectMode(nextMode: AppThemeMode) {
    if (nextMode === mode || savingMode) {
      return;
    }

    setSavingMode(nextMode);

    try {
      await setMode(nextMode);
    } finally {
      setSavingMode(null);
    }
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: colors.screen }]}>
      <View style={[styles.header, { borderBottomColor: colors.border }]}>
        <AppMenuButton />
        <View style={styles.headerText}>
          <Text style={[styles.headerLabel, { color: colors.textMuted }]}>Configurações</Text>
          <Text style={[styles.headerTitle, { color: colors.text }]}>Tema</Text>
        </View>
      </View>

      <ScrollView
        style={styles.scroll}
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}>
        <View
          style={[
            styles.panel,
            {
              backgroundColor: colors.surface,
              borderColor: colors.border,
            },
          ]}>
          <View style={[styles.panelIcon, { backgroundColor: colors.primarySoft }]}>
            <MaterialIcons name="palette" size={26} color={colors.primary} />
          </View>
          <View style={styles.panelText}>
            <Text style={[styles.panelTitle, { color: colors.text }]}>Aparência</Text>
            <Text style={[styles.panelMeta, { color: colors.textMuted }]}>
              {mode === 'dark' ? 'Modo escuro ativo' : 'Modo claro ativo'}
            </Text>
          </View>
        </View>

        <View
          style={[
            styles.segmentedControl,
            {
              backgroundColor: colors.surface,
              borderColor: colors.border,
            },
          ]}>
          {THEME_OPTIONS.map((option) => {
            const isActive = mode === option.mode;
            const isSaving = savingMode === option.mode;

            return (
              <Pressable
                key={option.mode}
                disabled={Boolean(savingMode)}
                onPress={() => handleSelectMode(option.mode)}
                style={({ pressed }) => [
                  styles.segment,
                  {
                    backgroundColor: isActive ? colors.primary : colors.surfaceMuted,
                    borderColor: isActive ? colors.primary : colors.border,
                  },
                  pressed && !isActive && styles.pressed,
                ]}>
                <View
                  style={[
                    styles.segmentIcon,
                    {
                      backgroundColor: isActive ? '#FFFFFF22' : colors.primarySoft,
                    },
                  ]}>
                  {isSaving ? (
                    <ActivityIndicator color={isActive ? colors.primaryText : colors.primary} />
                  ) : (
                    <MaterialIcons
                      name={option.icon}
                      size={23}
                      color={isActive ? colors.primaryText : colors.primary}
                    />
                  )}
                </View>
                <Text
                  style={[
                    styles.segmentLabel,
                    { color: isActive ? colors.primaryText : colors.text },
                  ]}>
                  {option.label}
                </Text>
                <MaterialIcons
                  name={isActive ? 'radio-button-checked' : 'radio-button-unchecked'}
                  size={20}
                  color={isActive ? colors.primaryText : colors.textMuted}
                />
              </Pressable>
            );
          })}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
  },
  header: {
    minHeight: 76,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    borderBottomWidth: 1,
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  headerText: {
    flex: 1,
    gap: 2,
  },
  headerLabel: {
    fontSize: 12,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  headerTitle: {
    fontSize: 22,
    fontWeight: '800',
  },
  scroll: {
    flex: 1,
  },
  content: {
    gap: 14,
    padding: 16,
    paddingBottom: 28,
  },
  panel: {
    minHeight: 88,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    borderWidth: 1,
    borderRadius: 8,
    padding: 14,
  },
  panelIcon: {
    width: 48,
    height: 48,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
  },
  panelText: {
    flex: 1,
    gap: 4,
  },
  panelTitle: {
    fontSize: 17,
    fontWeight: '800',
  },
  panelMeta: {
    fontSize: 13,
    fontWeight: '700',
  },
  segmentedControl: {
    gap: 10,
    borderWidth: 1,
    borderRadius: 8,
    padding: 10,
  },
  segment: {
    minHeight: 62,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    borderWidth: 1,
    borderRadius: 8,
    paddingHorizontal: 12,
  },
  segmentIcon: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
  },
  segmentLabel: {
    flex: 1,
    fontSize: 15,
    fontWeight: '800',
  },
  pressed: {
    opacity: 0.72,
  },
});
