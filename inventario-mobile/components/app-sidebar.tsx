import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import { router, usePathname, type Href } from 'expo-router';
import { useState } from 'react';
import {
  Modal,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  useWindowDimensions,
  View,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import {
  setPatrimonioNavigationDirectionFromRoutes,
  type PatrimonioRoute,
} from '@/src/navigation/patrimonioNavigation';

const SIDEBAR_MAX_WIDTH = 360;
const SIDEBAR_MIN_WIDTH = 280;
const SIDEBAR_SCREEN_RATIO = 0.84;
const SIDEBAR_HORIZONTAL_MARGIN = 18;

interface SidebarItem {
  href: PatrimonioRoute;
  label: string;
  description: string;
  icon: keyof typeof MaterialIcons.glyphMap;
}

interface AppSidebarProps {
  visible: boolean;
  onClose: () => void;
}

const PATRIMONIO_ITEMS: SidebarItem[] = [
  {
    href: '/patrimonio/principal',
    label: 'Dashboard',
    description: 'Resumo, estatísticas e leitura patrimonial',
    icon: 'dashboard',
  },
  {
    href: '/patrimonio/bens',
    label: 'Bens do setor',
    description: 'Consulta da carga patrimonial vinculada',
    icon: 'inventory-2',
  },
  {
    href: '/patrimonio/conferencia',
    label: 'Conferência',
    description: 'Inventario, localização e divergências',
    icon: 'fact-check',
  },
];

const FUTURE_GROUPS = [
  {
    label: 'Almoxarifado',
    icon: 'warehouse' as keyof typeof MaterialIcons.glyphMap,
  },
  {
    label: 'Processos',
    icon: 'assignment' as keyof typeof MaterialIcons.glyphMap,
  },
  {
    label: 'Relatórios',
    icon: 'bar-chart' as keyof typeof MaterialIcons.glyphMap,
  },
];

export function AppSidebar({ visible, onClose }: AppSidebarProps) {
  const pathname = usePathname();
  const insets = useSafeAreaInsets();
  const { width: windowWidth } = useWindowDimensions();
  const [isPatrimonioOpen, setIsPatrimonioOpen] = useState(true);
  const sidebarWidth = Math.min(
    SIDEBAR_MAX_WIDTH,
    Math.max(
      Math.min(SIDEBAR_MIN_WIDTH, windowWidth - SIDEBAR_HORIZONTAL_MARGIN),
      windowWidth * SIDEBAR_SCREEN_RATIO,
    ),
  );

  function handleNavigate(href: SidebarItem['href']) {
    onClose();

    if (pathname === href) {
      return;
    }

    setPatrimonioNavigationDirectionFromRoutes(pathname, href);
    router.replace(href as Href);
  }

  function handleOpenPatrimonio() {
    onClose();

    if (pathname.startsWith('/patrimonio')) {
      return;
    }

    router.replace('/patrimonio' as Href);
  }

  return (
    <Modal
      animationType="fade"
      transparent
      visible={visible}
      statusBarTranslucent
      onRequestClose={onClose}>
      <View style={styles.overlay}>
        <Pressable style={styles.backdrop} onPress={onClose} />
        <View
          style={[
            styles.sidebar,
            {
              width: sidebarWidth,
              paddingTop: insets.top,
              paddingBottom: insets.bottom,
            },
          ]}>
          <View style={styles.header}>
            <View style={styles.brandIcon}>
              <MaterialIcons name="apps" size={22} color="#1E4E79" />
            </View>
            <View style={styles.headerText}>
              <Text style={styles.headerLabel}>EGap Mobile</Text>
              <Text style={styles.headerTitle}>Módulos</Text>
            </View>
            <Pressable onPress={onClose} style={styles.iconButton}>
              <MaterialIcons name="close" size={21} color="#1E4E79" />
            </Pressable>
          </View>

          <ScrollView
            style={styles.scroll}
            contentContainerStyle={styles.content}
            showsVerticalScrollIndicator={false}>
            <View style={styles.group}>
              <View style={styles.groupHeader}>
                <Pressable
                  onPress={handleOpenPatrimonio}
                  style={({ pressed }) => [
                    styles.groupMain,
                    pressed && styles.pressed,
                  ]}>
                  <View style={styles.groupIcon}>
                    <MaterialIcons name="account-balance" size={21} color="#1E4E79" />
                  </View>
                  <View style={styles.groupText}>
                    <Text style={styles.groupTitle}>Patrimônio</Text>
                    <Text style={styles.groupMeta}>{PATRIMONIO_ITEMS.length} funcionalidades</Text>
                  </View>
                </Pressable>
                <Pressable
                  onPress={() => setIsPatrimonioOpen((currentValue) => !currentValue)}
                  style={({ pressed }) => [
                    styles.collapseButton,
                    pressed && styles.pressed,
                  ]}>
                  <MaterialIcons
                    name={isPatrimonioOpen ? 'keyboard-arrow-up' : 'keyboard-arrow-down'}
                    size={23}
                    color="#627D98"
                  />
                </Pressable>
              </View>

              {isPatrimonioOpen ? (
                <View style={styles.items}>
                  {PATRIMONIO_ITEMS.map((item) => {
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
                        <View style={[styles.itemIcon, isActive && styles.itemIconActive]}>
                          <MaterialIcons
                            name={item.icon}
                            size={19}
                            color={isActive ? '#FFFFFF' : '#1E4E79'}
                          />
                        </View>
                        <View style={styles.itemText}>
                          <Text style={[styles.itemTitle, isActive && styles.itemTitleActive]}>
                            {item.label}
                          </Text>
                          <Text
                            style={[styles.itemDescription, isActive && styles.itemDescriptionActive]}
                            numberOfLines={2}>
                            {item.description}
                          </Text>
                        </View>
                      </Pressable>
                    );
                  })}
                </View>
              ) : null}
            </View>

            <View style={styles.futureGroup}>
              <Text style={styles.futureTitle}>Próximos grupos</Text>
              {FUTURE_GROUPS.map((group) => (
                <View style={styles.futureItem} key={group.label}>
                  <MaterialIcons name={group.icon} size={19} color="#9FB3C8" />
                  <Text style={styles.futureItemText}>{group.label}</Text>
                  <Text style={styles.futureBadge}>Em breve</Text>
                </View>
              ))}
            </View>
          </ScrollView>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    flexDirection: 'row',
    backgroundColor: '#102A4399',
  },
  backdrop: {
    ...StyleSheet.absoluteFillObject,
  },
  sidebar: {
    backgroundColor: '#FFFFFF',
    borderTopRightRadius: 8,
    borderBottomRightRadius: 8,
    overflow: 'hidden',
  },
  header: {
    minHeight: 78,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 11,
    borderBottomWidth: 1,
    borderBottomColor: '#D9E2EC',
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  brandIcon: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  headerText: {
    flex: 1,
    gap: 2,
  },
  headerLabel: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  headerTitle: {
    color: '#102A43',
    fontSize: 20,
    fontWeight: '800',
  },
  iconButton: {
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#B6D4EA',
    backgroundColor: '#EAF4FB',
  },
  scroll: {
    flex: 1,
  },
  content: {
    gap: 14,
    padding: 14,
    paddingBottom: 24,
  },
  group: {
    gap: 10,
  },
  groupHeader: {
    minHeight: 58,
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#F8FAFC',
    overflow: 'hidden',
  },
  groupMain: {
    minHeight: 58,
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    padding: 10,
  },
  collapseButton: {
    width: 46,
    minHeight: 58,
    alignItems: 'center',
    justifyContent: 'center',
  },
  groupIcon: {
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  groupText: {
    flex: 1,
    gap: 2,
  },
  groupTitle: {
    color: '#102A43',
    fontSize: 15,
    fontWeight: '800',
  },
  groupMeta: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '700',
  },
  items: {
    gap: 8,
  },
  item: {
    minHeight: 68,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    padding: 10,
  },
  itemActive: {
    backgroundColor: '#1E4E79',
  },
  itemPressed: {
    backgroundColor: '#EAF4FB',
  },
  itemIcon: {
    width: 36,
    height: 36,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  itemIconActive: {
    backgroundColor: '#FFFFFF22',
  },
  itemText: {
    flex: 1,
    gap: 2,
  },
  itemTitle: {
    color: '#102A43',
    fontSize: 14,
    fontWeight: '800',
  },
  itemTitleActive: {
    color: '#FFFFFF',
  },
  itemDescription: {
    color: '#627D98',
    fontSize: 12,
    lineHeight: 16,
    fontWeight: '700',
  },
  itemDescriptionActive: {
    color: '#D9E8F5',
  },
  futureGroup: {
    gap: 8,
    borderTopWidth: 1,
    borderTopColor: '#E5EAF0',
    paddingTop: 14,
  },
  futureTitle: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  futureItem: {
    minHeight: 42,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 9,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    paddingHorizontal: 10,
  },
  futureItemText: {
    flex: 1,
    color: '#9FB3C8',
    fontSize: 13,
    fontWeight: '800',
  },
  futureBadge: {
    color: '#9FB3C8',
    fontSize: 11,
    fontWeight: '800',
  },
  pressed: {
    opacity: 0.72,
  },
});
