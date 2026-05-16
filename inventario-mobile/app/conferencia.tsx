import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import { router } from 'expo-router';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

const workflowSteps = [
  {
    icon: 'playlist-add-check',
    title: 'Preparacao',
    description: 'Resumo do setor, filtros e lista base da conferencia.',
  },
  {
    icon: 'qr-code-scanner',
    title: 'Leituras',
    description: 'Area reservada para leitura em lote e validacao dos bens encontrados.',
  },
  {
    icon: 'rule',
    title: 'Divergencias',
    description: 'Painel visual para pendencias, itens nao localizados e bens fora do setor.',
  },
] as const;

export default function ConferenciaScreen() {
  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.header}>
          <Pressable onPress={() => router.back()} style={styles.iconButton}>
            <MaterialIcons name="arrow-back" size={23} color="#1E4E79" />
          </Pressable>

          <View style={styles.headerTextGroup}>
            <Text style={styles.eyebrow}>Conferencia de bens</Text>
            <Text style={styles.title}>Conferencia patrimonial</Text>
          </View>
        </View>

        <View style={styles.heroPanel}>
          <View style={styles.heroIcon}>
            <MaterialIcons name="fact-check" size={34} color="#FFFFFF" />
          </View>
          <View style={styles.heroTextGroup}>
            <Text style={styles.heroTitle}>Fluxo de conferencia</Text>
            <Text style={styles.heroDescription}>
              Tela preparada para concentrar a leitura, comparacao e fechamento dos bens do setor.
            </Text>
          </View>
        </View>

        <View style={styles.statusPanel}>
          <View style={styles.statusItem}>
            <Text style={styles.statusValue}>0</Text>
            <Text style={styles.statusLabel}>Lidos</Text>
          </View>
          <View style={styles.statusDivider} />
          <View style={styles.statusItem}>
            <Text style={styles.statusValue}>0</Text>
            <Text style={styles.statusLabel}>Pendentes</Text>
          </View>
          <View style={styles.statusDivider} />
          <View style={styles.statusItem}>
            <Text style={styles.statusValue}>0</Text>
            <Text style={styles.statusLabel}>Divergencias</Text>
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Etapas previstas</Text>

          {workflowSteps.map((step) => (
            <View key={step.title} style={styles.stepRow}>
              <View style={styles.stepIcon}>
                <MaterialIcons name={step.icon} size={22} color="#1E4E79" />
              </View>
              <View style={styles.stepTextGroup}>
                <Text style={styles.stepTitle}>{step.title}</Text>
                <Text style={styles.stepDescription}>{step.description}</Text>
              </View>
            </View>
          ))}
        </View>

        <View style={styles.placeholderPanel}>
          <MaterialIcons name="construction" size={28} color="#627D98" />
          <Text style={styles.placeholderTitle}>Fluxos em definicao</Text>
          <Text style={styles.placeholderText}>
            A estrutura visual esta pronta para receber as regras de conferencia na proxima etapa.
          </Text>
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
    alignItems: 'center',
    gap: 12,
  },
  iconButton: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#B6D4EA',
    backgroundColor: '#EAF4FB',
  },
  headerTextGroup: {
    flex: 1,
    gap: 3,
  },
  eyebrow: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  title: {
    color: '#102A43',
    fontSize: 24,
    fontWeight: '800',
  },
  heroPanel: {
    flexDirection: 'row',
    gap: 14,
    borderRadius: 8,
    backgroundColor: '#1E4E79',
    padding: 16,
  },
  heroIcon: {
    width: 54,
    height: 54,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#2F855A',
  },
  heroTextGroup: {
    flex: 1,
    gap: 5,
  },
  heroTitle: {
    color: '#FFFFFF',
    fontSize: 19,
    fontWeight: '800',
  },
  heroDescription: {
    color: '#D9E8F5',
    fontSize: 14,
    lineHeight: 20,
    fontWeight: '700',
  },
  statusPanel: {
    minHeight: 82,
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    paddingVertical: 12,
  },
  statusItem: {
    flex: 1,
    alignItems: 'center',
    gap: 3,
  },
  statusValue: {
    color: '#1E4E79',
    fontSize: 24,
    fontWeight: '800',
  },
  statusLabel: {
    color: '#52616B',
    fontSize: 12,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  statusDivider: {
    width: 1,
    alignSelf: 'stretch',
    backgroundColor: '#D9E2EC',
  },
  section: {
    gap: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 16,
  },
  sectionTitle: {
    color: '#102A43',
    fontSize: 18,
    fontWeight: '800',
  },
  stepRow: {
    minHeight: 72,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    padding: 12,
  },
  stepIcon: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  stepTextGroup: {
    flex: 1,
    gap: 3,
  },
  stepTitle: {
    color: '#102A43',
    fontSize: 15,
    fontWeight: '800',
  },
  stepDescription: {
    color: '#52616B',
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '700',
  },
  placeholderPanel: {
    alignItems: 'center',
    gap: 8,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 18,
  },
  placeholderTitle: {
    color: '#102A43',
    fontSize: 17,
    fontWeight: '800',
    textAlign: 'center',
  },
  placeholderText: {
    color: '#52616B',
    fontSize: 14,
    lineHeight: 20,
    fontWeight: '700',
    textAlign: 'center',
  },
});
