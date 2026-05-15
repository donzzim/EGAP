import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

const summaryItems = [
  { label: 'Esperados', value: '128', color: '#1E4E79', icon: 'inventory' },
  { label: 'Conferidos', value: '76', color: '#2F855A', icon: 'check-circle' },
  { label: 'Pendentes', value: '42', color: '#B7791F', icon: 'schedule' },
  { label: 'Divergentes', value: '10', color: '#C53030', icon: 'report-problem' },
] as const;

const assetPreview = [
  {
    code: '00012489',
    description: 'Monitor LED 24 polegadas',
    status: 'Localizado',
    statusColor: '#2F855A',
  },
  {
    code: '00013072',
    description: 'Cadeira giratória operacional',
    status: 'Pendente',
    statusColor: '#B7791F',
  },
  {
    code: '00011803',
    description: 'Notebook administrativo',
    status: 'Outro setor',
    statusColor: '#1E4E79',
  },
] as const;

export default function PrincipalScreen() {
  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.header}>
          <View style={styles.headerTextGroup}>
            <Text style={styles.eyebrow}>EGap Mobile</Text>
            <Text style={styles.title}>Conferência patrimonial</Text>
          </View>
          <View style={styles.modeBadge}>
            <Text style={styles.modeBadgeText}>Visual</Text>
          </View>
        </View>

        <View style={styles.sectorPanel}>
          <View style={styles.panelIcon}>
            <MaterialIcons name="apartment" size={24} color="#1E4E79" />
          </View>
          <View style={styles.sectorInfo}>
            <Text style={styles.panelLabel}>Setor em operação</Text>
            <Text style={styles.sectorName}>Coordenadoria Administrativa</Text>
            <Text style={styles.sectorMeta}>Unidade Central - Sala 204</Text>
          </View>
        </View>

        <View style={styles.scanPanel}>
          <View style={styles.scanHeader}>
            <View>
              <Text style={styles.sectionTitle}>Leitura patrimonial</Text>
              <Text style={styles.sectionDescription}>Área preparada para scanner ou digitação.</Text>
            </View>
            <MaterialIcons name="qr-code-scanner" size={28} color="#1E4E79" />
          </View>

          <View style={styles.scannerMock}>
            <View style={styles.scanCornerTopLeft} />
            <View style={styles.scanCornerTopRight} />
            <MaterialIcons name="center-focus-strong" size={56} color="#1E4E79" />
            <Text style={styles.scannerText}>Aponte para a plaqueta do bem</Text>
            <View style={styles.scanLine} />
            <View style={styles.scanCornerBottomLeft} />
            <View style={styles.scanCornerBottomRight} />
          </View>

          <View style={styles.manualEntry}>
            <MaterialIcons name="pin" size={20} color="#627D98" />
            <Text style={styles.manualEntryText}>Código patrimonial</Text>
            <Text style={styles.manualEntryPlaceholder}>00000000</Text>
          </View>
        </View>

        <View style={styles.summaryGrid}>
          {summaryItems.map((item) => (
            <View key={item.label} style={styles.summaryCard}>
              <MaterialIcons name={item.icon} size={22} color={item.color} />
              <Text style={[styles.summaryValue, { color: item.color }]}>{item.value}</Text>
              <Text style={styles.summaryLabel}>{item.label}</Text>
            </View>
          ))}
        </View>

        <View style={styles.actionsPanel}>
          <Text style={styles.sectionTitle}>Ações do serviço</Text>
          <View style={styles.actionsGrid}>
            <View style={styles.actionButtonPrimary}>
              <MaterialIcons name="qr-code-scanner" size={22} color="#FFFFFF" />
              <Text style={styles.actionButtonPrimaryText}>Iniciar leitura</Text>
            </View>
            <View style={styles.actionButtonSecondary}>
              <MaterialIcons name="list-alt" size={22} color="#1E4E79" />
              <Text style={styles.actionButtonSecondaryText}>Bens do setor</Text>
            </View>
          </View>
        </View>

        <View style={styles.assetsPanel}>
          <View style={styles.panelHeaderRow}>
            <Text style={styles.sectionTitle}>Últimas leituras</Text>
            <Text style={styles.panelHeaderMeta}>Hoje</Text>
          </View>

          {assetPreview.map((asset) => (
            <View key={asset.code} style={styles.assetRow}>
              <View style={styles.assetIcon}>
                <MaterialIcons name="inventory-2" size={20} color="#1E4E79" />
              </View>
              <View style={styles.assetInfo}>
                <Text style={styles.assetCode}>{asset.code}</Text>
                <Text style={styles.assetDescription}>{asset.description}</Text>
              </View>
              <View style={[styles.statusBadge, { backgroundColor: `${asset.statusColor}18` }]}>
                <Text style={[styles.statusBadgeText, { color: asset.statusColor }]}>
                  {asset.status}
                </Text>
              </View>
            </View>
          ))}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F4F7FA',
  },
  content: {
    gap: 16,
    padding: 20,
    paddingBottom: 28,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 12,
  },
  headerTextGroup: {
    flex: 1,
    gap: 4,
  },
  eyebrow: {
    color: '#627D98',
    fontSize: 13,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  title: {
    color: '#102A43',
    fontSize: 28,
    fontWeight: '800',
  },
  modeBadge: {
    borderRadius: 8,
    backgroundColor: '#D9E8F5',
    paddingHorizontal: 10,
    paddingVertical: 7,
  },
  modeBadgeText: {
    color: '#1E4E79',
    fontSize: 12,
    fontWeight: '800',
  },
  sectorPanel: {
    flexDirection: 'row',
    gap: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 16,
  },
  panelIcon: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  sectorInfo: {
    flex: 1,
    gap: 3,
  },
  panelLabel: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  sectorName: {
    color: '#102A43',
    fontSize: 18,
    fontWeight: '800',
  },
  sectorMeta: {
    color: '#52616B',
    fontSize: 14,
    fontWeight: '600',
  },
  scanPanel: {
    gap: 14,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 16,
  },
  scanHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  sectionTitle: {
    color: '#102A43',
    fontSize: 18,
    fontWeight: '800',
  },
  sectionDescription: {
    color: '#52616B',
    fontSize: 14,
    fontWeight: '600',
  },
  scannerMock: {
    minHeight: 210,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 12,
    overflow: 'hidden',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#BCCCDC',
    backgroundColor: '#F8FAFC',
  },
  scannerText: {
    color: '#334E68',
    fontSize: 15,
    fontWeight: '800',
  },
  scanLine: {
    width: '78%',
    height: 2,
    borderRadius: 2,
    backgroundColor: '#2F855A',
  },
  scanCornerTopLeft: {
    position: 'absolute',
    top: 18,
    left: 18,
    width: 34,
    height: 34,
    borderTopWidth: 3,
    borderLeftWidth: 3,
    borderColor: '#1E4E79',
  },
  scanCornerTopRight: {
    position: 'absolute',
    top: 18,
    right: 18,
    width: 34,
    height: 34,
    borderTopWidth: 3,
    borderRightWidth: 3,
    borderColor: '#1E4E79',
  },
  scanCornerBottomLeft: {
    position: 'absolute',
    bottom: 18,
    left: 18,
    width: 34,
    height: 34,
    borderBottomWidth: 3,
    borderLeftWidth: 3,
    borderColor: '#1E4E79',
  },
  scanCornerBottomRight: {
    position: 'absolute',
    right: 18,
    bottom: 18,
    width: 34,
    height: 34,
    borderRightWidth: 3,
    borderBottomWidth: 3,
    borderColor: '#1E4E79',
  },
  manualEntry: {
    minHeight: 48,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 12,
  },
  manualEntryText: {
    flex: 1,
    color: '#334E68',
    fontSize: 14,
    fontWeight: '800',
  },
  manualEntryPlaceholder: {
    color: '#829AB1',
    fontSize: 15,
    fontWeight: '800',
  },
  summaryGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  summaryCard: {
    width: '48.5%',
    gap: 5,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 14,
  },
  summaryValue: {
    fontSize: 25,
    fontWeight: '800',
  },
  summaryLabel: {
    color: '#52616B',
    fontSize: 13,
    fontWeight: '800',
  },
  actionsPanel: {
    gap: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 16,
  },
  actionsGrid: {
    gap: 10,
  },
  actionButtonPrimary: {
    minHeight: 50,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#1E4E79',
  },
  actionButtonPrimaryText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '800',
  },
  actionButtonSecondary: {
    minHeight: 50,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#B6D4EA',
    backgroundColor: '#EAF4FB',
  },
  actionButtonSecondaryText: {
    color: '#1E4E79',
    fontSize: 15,
    fontWeight: '800',
  },
  assetsPanel: {
    gap: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 16,
  },
  panelHeaderRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  panelHeaderMeta: {
    color: '#627D98',
    fontSize: 13,
    fontWeight: '800',
  },
  assetRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    padding: 10,
  },
  assetIcon: {
    width: 36,
    height: 36,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  assetInfo: {
    flex: 1,
    gap: 2,
  },
  assetCode: {
    color: '#102A43',
    fontSize: 14,
    fontWeight: '800',
  },
  assetDescription: {
    color: '#52616B',
    fontSize: 12,
    fontWeight: '600',
  },
  statusBadge: {
    borderRadius: 8,
    paddingHorizontal: 8,
    paddingVertical: 5,
  },
  statusBadgeText: {
    fontSize: 11,
    fontWeight: '800',
  },
});
