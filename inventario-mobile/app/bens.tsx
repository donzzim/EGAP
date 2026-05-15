import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import { router } from 'expo-router';
import { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  RefreshControl,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { authApi, type MobileUser } from '@/src/api/auth';
import { bensApi, type BemPatrimonial } from '@/src/api/bens';
import { ApiError, NetworkError } from '@/src/api/errors';

function displayValue(value: unknown, fallback = '-'): string {
  if (value === null || value === undefined || value === '') {
    return fallback;
  }

  return String(value);
}

function getBemCodigo(bem: BemPatrimonial): string {
  return displayValue(
    bem.codigo
      ?? bem.patrimonio
      ?? bem.codigo_patrimonial
      ?? bem.tombamento
      ?? bem.tombo_smarapd
      ?? bem.num_tombo_smarapd
      ?? bem.id,
    'Sem código',
  );
}

function getBemDescricao(bem: BemPatrimonial): string {
  return displayValue(bem.descricao_resumida ?? bem.descricao ?? bem.denominacao, 'Bem patrimonial');
}

function getBemSituacao(bem: BemPatrimonial): string {
  return displayValue(bem.situacao ?? bem.estado, 'No setor');
}

function getErrorMessage(error: unknown): string {
  if (error instanceof ApiError || error instanceof NetworkError) {
    return error.message;
  }

  return 'Nao foi possível carregar os bens do setor.';
}

export default function BensScreen() {
  const [user, setUser] = useState<MobileUser | null>(null);
  const [bens, setBens] = useState<BemPatrimonial[]>([]);
  const [total, setTotal] = useState(0);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const loadBens = useCallback(async (refreshing = false) => {
    if (refreshing) {
      setIsRefreshing(true);
    } else {
      setIsLoading(true);
    }

    setErrorMessage(null);

    try {
      const session = await authApi.getStoredSession();

      if (!session) {
        router.replace('/');
        return;
      }

      setUser(session.user);

      const result = await bensApi.listByUserSector();

      setBens(result.bens);
      setTotal(result.total);
    } catch (error) {
      setErrorMessage(getErrorMessage(error));
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  }, []);

  useEffect(() => {
    loadBens();
  }, [loadBens]);

  function renderBem({ item }: { item: BemPatrimonial }) {
    return (
      <View style={styles.bemRow}>
        <View style={styles.bemIcon}>
          <MaterialIcons name="inventory-2" size={21} color="#1E4E79" />
        </View>
        <View style={styles.bemInfo}>
          <Text style={styles.bemCodigo}>{getBemCodigo(item)}</Text>
          <Text style={styles.bemDescricao} numberOfLines={2}>
            {getBemDescricao(item)}
          </Text>
          <View style={styles.bemMetaRow}>
            <Text style={styles.bemMeta}>Marca: {displayValue(item.marca)}</Text>
            <Text style={styles.bemMeta}>Modelo: {displayValue(item.modelo)}</Text>
            <Text style={styles.bemMeta}>Série: {displayValue(item.numero_serie ?? item.serie)}</Text>
          </View>
        </View>
        <View style={styles.statusBadge}>
          <Text style={styles.statusBadgeText}>{getBemSituacao(item)}</Text>
        </View>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <Pressable onPress={() => router.back()} style={styles.iconButton}>
          <MaterialIcons name="arrow-back" size={23} color="#1E4E79" />
        </Pressable>

        <View style={styles.headerTextGroup}>
          <Text style={styles.eyebrow}>Bens do setor</Text>
          <Text style={styles.title}>Patrimônio localizado</Text>
        </View>
      </View>

      <View style={styles.contextPanel}>
        <View style={styles.contextIcon}>
          <MaterialIcons name="apartment" size={23} color="#1E4E79" />
        </View>
        <View style={styles.contextText}>
          <Text style={styles.contextLabel}>{user?.name ?? user?.login ?? 'Usuário mobile'}</Text>
          <Text style={styles.contextMeta}>
            Setor {user?.setor ?? '-'} | Unidade {user?.unidade_judiciaria ?? '-'}
          </Text>
        </View>
        <View style={styles.totalBadge}>
          <Text style={styles.totalValue}>{total}</Text>
          <Text style={styles.totalLabel}>bens</Text>
        </View>
      </View>

      {isLoading ? (
        <View style={styles.centerState}>
          <ActivityIndicator color="#1E4E79" />
          <Text style={styles.centerStateText}>Carregando bens do setor</Text>
        </View>
      ) : errorMessage ? (
        <View style={styles.centerState}>
          <MaterialIcons name="error-outline" size={30} color="#C53030" />
          <Text style={styles.errorTitle}>Falha ao carregar</Text>
          <Text style={styles.centerStateText}>{errorMessage}</Text>
          <Pressable onPress={() => loadBens()} style={styles.retryButton}>
            <MaterialIcons name="refresh" size={20} color="#FFFFFF" />
            <Text style={styles.retryButtonText}>Tentar novamente</Text>
          </Pressable>
        </View>
      ) : (
        <FlatList
          data={bens}
          keyExtractor={(item, index) => `${getBemCodigo(item)}-${item.id}-${index}`}
          renderItem={renderBem}
          contentContainerStyle={bens.length > 0 ? styles.listContent : styles.emptyContent}
          refreshControl={
            <RefreshControl
              refreshing={isRefreshing}
              onRefresh={() => loadBens(true)}
              tintColor="#1E4E79"
              colors={['#1E4E79']}
            />
          }
          ListEmptyComponent={
            <View style={styles.emptyPanel}>
              <MaterialIcons name="inventory" size={32} color="#627D98" />
              <Text style={styles.emptyTitle}>Nenhum bem encontrado</Text>
              <Text style={styles.emptyText}>
                A API nao retornou bens para o setor vinculado ao seu usuario.
              </Text>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F4F7FA',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    paddingHorizontal: 20,
    paddingTop: 10,
    paddingBottom: 14,
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
  contextPanel: {
    minHeight: 72,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginHorizontal: 20,
    marginBottom: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 14,
  },
  contextIcon: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  contextText: {
    flex: 1,
    gap: 3,
  },
  contextLabel: {
    color: '#102A43',
    fontSize: 15,
    fontWeight: '800',
  },
  contextMeta: {
    color: '#52616B',
    fontSize: 13,
    fontWeight: '700',
  },
  totalBadge: {
    minWidth: 58,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
    paddingHorizontal: 10,
    paddingVertical: 8,
  },
  totalValue: {
    color: '#1E4E79',
    fontSize: 18,
    fontWeight: '800',
  },
  totalLabel: {
    color: '#52616B',
    fontSize: 11,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  listContent: {
    gap: 10,
    paddingHorizontal: 20,
    paddingBottom: 24,
  },
  bemRow: {
    minHeight: 92,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 12,
  },
  bemIcon: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  bemInfo: {
    flex: 1,
    gap: 5,
  },
  bemCodigo: {
    color: '#102A43',
    fontSize: 15,
    fontWeight: '800',
  },
  bemDescricao: {
    color: '#334E68',
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '700',
  },
  bemMetaRow: {
    gap: 2,
  },
  bemMeta: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '700',
  },
  statusBadge: {
    maxWidth: 92,
    borderRadius: 8,
    backgroundColor: '#E6F4EA',
    paddingHorizontal: 8,
    paddingVertical: 6,
  },
  statusBadgeText: {
    color: '#2F855A',
    fontSize: 11,
    fontWeight: '800',
    textAlign: 'center',
  },
  centerState: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
    padding: 24,
  },
  centerStateText: {
    color: '#52616B',
    fontSize: 14,
    lineHeight: 20,
    fontWeight: '700',
    textAlign: 'center',
  },
  errorTitle: {
    color: '#9B2C2C',
    fontSize: 18,
    fontWeight: '800',
  },
  retryButton: {
    minHeight: 46,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#1E4E79',
    paddingHorizontal: 16,
  },
  retryButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '800',
  },
  emptyContent: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: 20,
    paddingBottom: 24,
  },
  emptyPanel: {
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 22,
  },
  emptyTitle: {
    color: '#102A43',
    fontSize: 18,
    fontWeight: '800',
  },
  emptyText: {
    color: '#52616B',
    fontSize: 14,
    lineHeight: 20,
    fontWeight: '700',
    textAlign: 'center',
  },
});
