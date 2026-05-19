import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import { router, type Href } from 'expo-router';
import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Modal,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { AppMenuButton } from '@/components/app-menu-button';
import { ApiError, NetworkError } from '@/src/api/errors';
import {
  pedidosApi,
  type ComplementoSetorPedido,
  type MaterialPedido,
  type PedidoTipo,
  type TipoAtendimentoPermanente,
} from '@/src/api/pedidos';

type PedidoRoute = '/pedidos/consumo' | '/pedidos/permanentes';
type NotificationTone = 'success' | 'error' | 'info';

interface CartState {
  quantity: number;
  tipoAtendimento: TipoAtendimentoPermanente;
  justificativa: string;
  patrimonioSubstituido: string;
}

interface NotificationState {
  tone: NotificationTone;
  message: string;
}

interface PedidosCarrinhoScreenProps {
  tipo: PedidoTipo;
  title: string;
  subtitle: string;
  icon: keyof typeof MaterialIcons.glyphMap;
  accentColor: string;
  currentRoute: PedidoRoute;
  summaryTitle: string;
  helperText: string;
}

const PER_PAGE = 30;

const TABS: { href: PedidoRoute; label: string; icon: keyof typeof MaterialIcons.glyphMap }[] = [
  {
    href: '/pedidos/consumo',
    label: 'Consumo',
    icon: 'shopping-basket',
  },
  {
    href: '/pedidos/permanentes',
    label: 'Permanentes',
    icon: 'inventory-2',
  },
];

function formatMoney(value: number | null | undefined): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value ?? 0);
}

function getErrorMessage(error: unknown): string {
  if (error instanceof ApiError || error instanceof NetworkError) {
    return error.message;
  }

  return 'Nao foi possivel processar o pedido.';
}

function emptyCartState(): CartState {
  return {
    quantity: 0,
    tipoAtendimento: 'adicao',
    justificativa: '',
    patrimonioSubstituido: '',
  };
}

export function PedidosCarrinhoScreen({
  tipo,
  title,
  subtitle,
  icon,
  accentColor,
  currentRoute,
  summaryTitle,
  helperText,
}: PedidosCarrinhoScreenProps) {
  const [materials, setMaterials] = useState<MaterialPedido[]>([]);
  const [complementos, setComplementos] = useState<ComplementoSetorPedido[]>([]);
  const [selectedComplementoId, setSelectedComplementoId] = useState<number | null>(null);
  const [cart, setCart] = useState<Record<number, CartState>>({});
  const [cartMaterials, setCartMaterials] = useState<Record<number, MaterialPedido>>({});
  const [search, setSearch] = useState('');
  const [submittedSearch, setSubmittedSearch] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isComplementoModalVisible, setIsComplementoModalVisible] = useState(false);
  const [justificativaGeral, setJustificativaGeral] = useState('');
  const [notification, setNotification] = useState<NotificationState | null>(null);

  const selectedItems = useMemo(() => {
    return Object.values(cartMaterials)
      .map((material) => ({
        material,
        state: cart[material.id] ?? emptyCartState(),
      }))
      .filter(({ state }) => state.quantity > 0);
  }, [cart, cartMaterials]);

  const selectedQuantity = selectedItems.reduce((total, item) => total + item.state.quantity, 0);
  const selectedComplemento = complementos.find((complemento) => complemento.id === selectedComplementoId) ?? null;

  const loadContext = useCallback(async () => {
    const data = await pedidosApi.contexto();

    setComplementos(data.complementos);
    setSelectedComplementoId((current) => current ?? data.complementos[0]?.id ?? null);
  }, []);

  const loadMaterials = useCallback(async ({
    pageToLoad = 1,
    append = false,
    searchTerm = '',
  }: {
    pageToLoad?: number;
    append?: boolean;
    searchTerm?: string;
  } = {}) => {
    if (append) {
      setIsLoadingMore(true);
    } else {
      setIsLoading(true);
    }

    try {
      const data = await pedidosApi.materiais({
        tipo,
        page: pageToLoad,
        perPage: PER_PAGE,
        search: searchTerm,
      });

      setMaterials((currentMaterials) => append
        ? [...currentMaterials, ...data.materiais]
        : data.materiais);
      setCurrentPage(data.meta.current_page);
      setHasMore(data.meta.has_more);
    } catch (error) {
      setNotification({
        tone: 'error',
        message: getErrorMessage(error),
      });
      setHasMore(false);
    } finally {
      setIsLoading(false);
      setIsLoadingMore(false);
    }
  }, [tipo]);

  useEffect(() => {
    let isMounted = true;

    async function loadInitialData() {
      setIsLoading(true);

      try {
        await loadContext();

        if (isMounted) {
          await loadMaterials({ pageToLoad: 1, searchTerm: '' });
        }
      } catch (error) {
        if (isMounted) {
          setNotification({
            tone: 'error',
            message: getErrorMessage(error),
          });
          setIsLoading(false);
        }
      }
    }

    loadInitialData();

    return () => {
      isMounted = false;
    };
  }, [loadContext, loadMaterials]);

  function updateCartItem(materialId: number, updater: (state: CartState) => CartState) {
    setCart((currentCart) => {
      const nextState = updater(currentCart[materialId] ?? emptyCartState());
      const nextCart = { ...currentCart };

      if (nextState.quantity <= 0) {
        delete nextCart[materialId];
      } else {
        nextCart[materialId] = nextState;
      }

      return nextCart;
    });
  }

  function incrementMaterial(material: MaterialPedido) {
    setCartMaterials((currentMaterials) => ({
      ...currentMaterials,
      [material.id]: material,
    }));

    updateCartItem(material.id, (state) => ({
      ...state,
      quantity: state.quantity + 1,
    }));
  }

  function decrementMaterial(material: MaterialPedido) {
    updateCartItem(material.id, (state) => ({
      ...state,
      quantity: Math.max(state.quantity - 1, 0),
    }));

    const currentQuantity = cart[material.id]?.quantity ?? 0;

    if (currentQuantity <= 1) {
      setCartMaterials((currentMaterials) => {
        const nextMaterials = { ...currentMaterials };
        delete nextMaterials[material.id];

        return nextMaterials;
      });
    }
  }

  function setTipoAtendimento(materialId: number, tipoAtendimento: TipoAtendimentoPermanente) {
    updateCartItem(materialId, (state) => ({
      ...state,
      quantity: state.quantity || 1,
      tipoAtendimento,
    }));
  }

  function setItemText(materialId: number, key: 'justificativa' | 'patrimonioSubstituido', value: string) {
    updateCartItem(materialId, (state) => ({
      ...state,
      quantity: state.quantity || 1,
      [key]: value,
    }));
  }

  function handleSearch() {
    const nextSearch = search.trim();

    setSubmittedSearch(nextSearch);
    loadMaterials({
      pageToLoad: 1,
      searchTerm: nextSearch,
    });
  }

  function handleLoadMore() {
    if (isLoading || isLoadingMore || !hasMore) {
      return;
    }

    loadMaterials({
      pageToLoad: currentPage + 1,
      append: true,
      searchTerm: submittedSearch,
    });
  }

  function clearCart() {
    setCart({});
    setCartMaterials({});
    setJustificativaGeral('');
    setNotification({
      tone: 'info',
      message: 'Carrinho limpo.',
    });
  }

  function validateBeforeSubmit(): boolean {
    if (!selectedComplementoId) {
      setNotification({
        tone: 'error',
        message: 'Selecione o complemento do setor.',
      });
      return false;
    }

    if (selectedItems.length === 0) {
      setNotification({
        tone: 'error',
        message: 'Adicione ao menos um material ao carrinho.',
      });
      return false;
    }

    if (tipo === 'consumo' && justificativaGeral.trim() === '') {
      setNotification({
        tone: 'error',
        message: 'Informe a justificativa do pedido.',
      });
      return false;
    }

    if (tipo === 'permanente') {
      const incomplete = selectedItems.find(({ state }) => {
        return state.justificativa.trim() === ''
          || (state.tipoAtendimento === 'substituicao' && state.patrimonioSubstituido.trim() === '');
      });

      if (incomplete) {
        setNotification({
          tone: 'error',
          message: 'Preencha justificativa e patrimonio quando houver substituicao.',
        });
        return false;
      }
    }

    return true;
  }

  async function submitPedido() {
    if (!validateBeforeSubmit() || !selectedComplementoId) {
      return;
    }

    setIsSubmitting(true);

    try {
      const response = await pedidosApi.criar({
        tipo,
        complemento_setor_id: selectedComplementoId,
        justificativa: justificativaGeral.trim(),
        itens: selectedItems.map(({ material, state }) => ({
          material_id: material.id,
          quantidade: state.quantity,
          tipo_atendimento: tipo === 'permanente' ? state.tipoAtendimento : undefined,
          justificativa: tipo === 'permanente' ? state.justificativa.trim() : undefined,
          patrimonio_substituido: tipo === 'permanente'
            ? state.patrimonioSubstituido.trim()
            : undefined,
        })),
      });

      setCart({});
      setCartMaterials({});
      setJustificativaGeral('');
      setNotification({
        tone: 'success',
        message: `Pedido #${response.pedido.id} cadastrado com sucesso.`,
      });
    } catch (error) {
      setNotification({
        tone: 'error',
        message: getErrorMessage(error),
      });
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <Modal
        animationType="fade"
        transparent
        visible={isComplementoModalVisible}
        onRequestClose={() => setIsComplementoModalVisible(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalPanel}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Complemento do setor</Text>
              <Pressable onPress={() => setIsComplementoModalVisible(false)} style={styles.modalCloseButton}>
                <MaterialIcons name="close" size={21} color="#1E4E79" />
              </Pressable>
            </View>
            <ScrollView contentContainerStyle={styles.modalContent}>
              {complementos.map((complemento) => {
                const isActive = complemento.id === selectedComplementoId;

                return (
                  <Pressable
                    key={complemento.id}
                    onPress={() => {
                      setSelectedComplementoId(complemento.id);
                      setIsComplementoModalVisible(false);
                    }}
                    style={({ pressed }) => [
                      styles.complementoOption,
                      isActive && styles.complementoOptionActive,
                      pressed && styles.pressed,
                    ]}>
                    <Text style={[styles.complementoOptionText, isActive && styles.complementoOptionTextActive]}>
                      {complemento.descricao}
                    </Text>
                    {isActive ? <MaterialIcons name="check" size={20} color="#FFFFFF" /> : null}
                  </Pressable>
                );
              })}
            </ScrollView>
          </View>
        </View>
      </Modal>

      <ScrollView
        style={styles.scroll}
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}>
        {notification ? (
          <View style={[
            styles.notification,
            notification.tone === 'success'
              ? styles.notificationSuccess
              : notification.tone === 'error'
                ? styles.notificationError
                : styles.notificationInfo,
          ]}>
            <MaterialIcons
              name={notification.tone === 'success' ? 'check-circle' : notification.tone === 'error' ? 'error-outline' : 'info-outline'}
              size={20}
              color="#FFFFFF"
            />
            <Text style={styles.notificationText}>{notification.message}</Text>
            <Pressable onPress={() => setNotification(null)}>
              <MaterialIcons name="close" size={18} color="#FFFFFF" />
            </Pressable>
          </View>
        ) : null}

        <View style={styles.header}>
          <AppMenuButton />
          <View style={styles.headerTextGroup}>
            <Text style={styles.eyebrow}>Pedidos</Text>
            <Text style={styles.title}>{title}</Text>
            <Text style={styles.subtitle}>{subtitle}</Text>
          </View>
          <View style={[styles.headerIcon, { backgroundColor: `${accentColor}18` }]}>
            <MaterialIcons name={icon} size={24} color={accentColor} />
          </View>
        </View>

        <View style={styles.tabs}>
          {TABS.map((tab) => {
            const isActive = tab.href === currentRoute;

            return (
              <Pressable
                key={tab.href}
                onPress={() => router.replace(tab.href as Href)}
                style={({ pressed }) => [
                  styles.tab,
                  isActive && { backgroundColor: accentColor },
                  pressed && !isActive && styles.pressed,
                ]}>
                <MaterialIcons
                  name={tab.icon}
                  size={18}
                  color={isActive ? '#FFFFFF' : '#1E4E79'}
                />
                <Text style={[styles.tabText, isActive && styles.tabTextActive]}>
                  {tab.label}
                </Text>
              </Pressable>
            );
          })}
        </View>

        <View style={styles.contextPanel}>
          <View style={styles.contextIcon}>
            <MaterialIcons name="storefront" size={22} color="#1E4E79" />
          </View>
          <View style={styles.contextText}>
            <Text style={styles.contextTitle}>Pedido em rascunho</Text>
            <Text style={styles.contextMeta}>{helperText}</Text>
          </View>
        </View>

        <View style={styles.searchPanel}>
          <MaterialIcons name="search" size={21} color="#627D98" />
          <TextInput
            placeholder="Buscar material"
            placeholderTextColor="#829AB1"
            value={search}
            onChangeText={setSearch}
            onSubmitEditing={handleSearch}
            autoCorrect={false}
            style={styles.searchInput}
          />
          <Pressable onPress={handleSearch} style={styles.filterButton}>
            <MaterialIcons name="arrow-forward" size={20} color="#1E4E79" />
          </Pressable>
        </View>

        <View style={styles.contentGrid}>
          <View style={styles.materialsPanel}>
            <View style={styles.sectionHeader}>
              <View>
                <Text style={styles.sectionTitle}>Materiais</Text>
                <Text style={styles.sectionMeta}>
                  {isLoading ? 'Carregando catalogo' : `${materials.length} material(is) carregado(s)`}
                </Text>
              </View>
              <View style={styles.countBadge}>
                <Text style={styles.countBadgeText}>{selectedQuantity}</Text>
              </View>
            </View>

            {isLoading ? (
              <View style={styles.centerState}>
                <ActivityIndicator color="#1E4E79" />
                <Text style={styles.centerStateText}>Carregando materiais</Text>
              </View>
            ) : materials.length === 0 ? (
              <View style={styles.centerState}>
                <MaterialIcons name="inventory" size={28} color="#627D98" />
                <Text style={styles.centerStateText}>Nenhum material encontrado</Text>
              </View>
            ) : (
              materials.map((material) => {
                const itemState = cart[material.id] ?? emptyCartState();

                return (
                  <View style={styles.materialCard} key={material.id}>
                    <View style={styles.materialThumb}>
                      <MaterialIcons name={icon} size={24} color={accentColor} />
                    </View>
                    <View style={styles.materialInfo}>
                      <Text style={styles.materialName}>{material.descricao}</Text>
                      <Text style={styles.materialDetail}>
                        {tipo === 'consumo'
                          ? `${material.unidade} | estoque ${material.quantidade_estoque ?? 0}`
                          : 'Material permanente'}
                      </Text>
                      <View style={styles.materialMetaRow}>
                        <Text style={styles.materialMeta}>{material.unidade}</Text>
                        <Text style={styles.materialPrice}>{formatMoney(material.preco_medio)}</Text>
                      </View>

                      {tipo === 'permanente' && itemState.quantity > 0 ? (
                        <View style={styles.permanentFields}>
                          <View style={styles.segmented}>
                            <Pressable
                              onPress={() => setTipoAtendimento(material.id, 'adicao')}
                              style={[
                                styles.segmentButton,
                                itemState.tipoAtendimento === 'adicao' && { backgroundColor: accentColor },
                              ]}>
                              <Text style={[
                                styles.segmentText,
                                itemState.tipoAtendimento === 'adicao' && styles.segmentTextActive,
                              ]}>
                                Adicao
                              </Text>
                            </Pressable>
                            <Pressable
                              onPress={() => setTipoAtendimento(material.id, 'substituicao')}
                              style={[
                                styles.segmentButton,
                                itemState.tipoAtendimento === 'substituicao' && { backgroundColor: accentColor },
                              ]}>
                              <Text style={[
                                styles.segmentText,
                                itemState.tipoAtendimento === 'substituicao' && styles.segmentTextActive,
                              ]}>
                                Substituicao
                              </Text>
                            </Pressable>
                          </View>

                          {itemState.tipoAtendimento === 'substituicao' ? (
                            <TextInput
                              placeholder="No. patrimonio substituido"
                              placeholderTextColor="#829AB1"
                              value={itemState.patrimonioSubstituido}
                              onChangeText={(value) => setItemText(material.id, 'patrimonioSubstituido', value)}
                              style={styles.inlineInput}
                            />
                          ) : null}

                          <TextInput
                            placeholder="Justificativa do item"
                            placeholderTextColor="#829AB1"
                            value={itemState.justificativa}
                            onChangeText={(value) => setItemText(material.id, 'justificativa', value)}
                            multiline
                            style={styles.inlineTextarea}
                          />
                        </View>
                      ) : null}
                    </View>
                    <View style={styles.quantityBox}>
                      <Pressable onPress={() => decrementMaterial(material)} style={styles.quantityButton}>
                        <MaterialIcons name="remove" size={18} color="#C53030" />
                      </Pressable>
                      <Text style={styles.quantityText}>{itemState.quantity}</Text>
                      <Pressable onPress={() => incrementMaterial(material)} style={styles.quantityButton}>
                        <MaterialIcons name="add" size={18} color="#2F855A" />
                      </Pressable>
                    </View>
                  </View>
                );
              })
            )}

            {!isLoading && hasMore ? (
              <Pressable
                disabled={isLoadingMore}
                onPress={handleLoadMore}
                style={({ pressed }) => [
                  styles.loadMoreButton,
                  pressed && styles.pressed,
                ]}>
                {isLoadingMore ? <ActivityIndicator color="#1E4E79" /> : <MaterialIcons name="expand-more" size={21} color="#1E4E79" />}
                <Text style={styles.loadMoreText}>{isLoadingMore ? 'Carregando' : 'Carregar mais'}</Text>
              </Pressable>
            ) : null}
          </View>

          <View style={styles.cartPanel}>
            <View style={styles.sectionHeader}>
              <View>
                <Text style={styles.sectionTitle}>{summaryTitle}</Text>
                <Text style={styles.sectionMeta}>{selectedQuantity} item(ns) selecionado(s)</Text>
              </View>
              <View style={[styles.cartIcon, { backgroundColor: `${accentColor}18` }]}>
                <MaterialIcons name="shopping-cart" size={22} color={accentColor} />
              </View>
            </View>

            {selectedItems.length > 0 ? (
              selectedItems.map(({ material, state }) => (
                <View style={styles.cartRow} key={material.id}>
                  <View style={styles.cartQuantity}>
                    <Text style={styles.cartQuantityText}>{state.quantity}</Text>
                  </View>
                  <View style={styles.cartInfo}>
                    <Text style={styles.cartName}>{material.descricao}</Text>
                    <Text style={styles.cartDetail}>
                      {tipo === 'permanente'
                        ? state.tipoAtendimento === 'substituicao' ? 'Substituicao' : 'Adicao'
                        : material.unidade}
                    </Text>
                  </View>
                </View>
              ))
            ) : (
              <View style={styles.emptyCart}>
                <MaterialIcons name="shopping-cart" size={26} color="#627D98" />
                <Text style={styles.emptyCartText}>Nenhum material adicionado</Text>
              </View>
            )}

            <View style={styles.formPreview}>
              <Text style={styles.formLabel}>Complemento do setor</Text>
              <Pressable onPress={() => setIsComplementoModalVisible(true)} style={styles.fakeInput}>
                <Text style={selectedComplemento ? styles.fakeInputValue : styles.fakeInputText}>
                  {selectedComplemento?.descricao ?? 'Selecionar destino'}
                </Text>
                <MaterialIcons name="keyboard-arrow-down" size={22} color="#627D98" />
              </Pressable>

              <Text style={styles.formLabel}>
                {tipo === 'consumo' ? 'Justificativa' : 'Observacao geral'}
              </Text>
              <TextInput
                placeholder={tipo === 'consumo'
                  ? 'Descrever necessidade do pedido'
                  : 'Opcional para o cabecalho do pedido'}
                placeholderTextColor="#829AB1"
                value={justificativaGeral}
                onChangeText={setJustificativaGeral}
                multiline
                style={styles.fakeTextarea}
              />
            </View>

            <View style={styles.cartActions}>
              <Pressable disabled={isSubmitting} onPress={clearCart} style={styles.secondaryButton}>
                <MaterialIcons name="delete-outline" size={20} color="#C53030" />
                <Text style={styles.secondaryButtonText}>Limpar</Text>
              </Pressable>
              <Pressable
                disabled={isSubmitting}
                onPress={submitPedido}
                style={[styles.primaryButton, { backgroundColor: accentColor }, isSubmitting && styles.disabledButton]}>
                {isSubmitting ? <ActivityIndicator color="#FFFFFF" /> : <MaterialIcons name="send" size={20} color="#FFFFFF" />}
                <Text style={styles.primaryButtonText}>{isSubmitting ? 'Enviando' : 'Enviar'}</Text>
              </Pressable>
            </View>
          </View>
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
  scroll: {
    flex: 1,
  },
  content: {
    gap: 14,
    padding: 18,
    paddingBottom: 28,
  },
  notification: {
    minHeight: 48,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 9,
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
  notificationSuccess: {
    backgroundColor: '#2F855A',
  },
  notificationError: {
    backgroundColor: '#C53030',
  },
  notificationInfo: {
    backgroundColor: '#1E4E79',
  },
  notificationText: {
    flex: 1,
    color: '#FFFFFF',
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '800',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
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
    fontSize: 26,
    fontWeight: '800',
  },
  subtitle: {
    color: '#52616B',
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '700',
  },
  headerIcon: {
    width: 44,
    height: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
  },
  tabs: {
    minHeight: 48,
    flexDirection: 'row',
    gap: 8,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 6,
  },
  tab: {
    minHeight: 36,
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    borderRadius: 8,
  },
  tabText: {
    color: '#1E4E79',
    fontSize: 13,
    fontWeight: '800',
  },
  tabTextActive: {
    color: '#FFFFFF',
  },
  contextPanel: {
    flexDirection: 'row',
    gap: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 14,
  },
  contextIcon: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  contextText: {
    flex: 1,
    gap: 2,
  },
  contextTitle: {
    color: '#102A43',
    fontSize: 15,
    fontWeight: '800',
  },
  contextMeta: {
    color: '#52616B',
    fontSize: 12,
    lineHeight: 17,
    fontWeight: '700',
  },
  searchPanel: {
    minHeight: 50,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 12,
  },
  searchInput: {
    flex: 1,
    color: '#102A43',
    fontSize: 14,
    fontWeight: '800',
  },
  filterButton: {
    width: 36,
    height: 36,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  contentGrid: {
    gap: 14,
  },
  materialsPanel: {
    gap: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 14,
  },
  sectionHeader: {
    minHeight: 42,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  sectionTitle: {
    color: '#102A43',
    fontSize: 17,
    fontWeight: '800',
  },
  sectionMeta: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '700',
  },
  countBadge: {
    minWidth: 34,
    height: 34,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  countBadgeText: {
    color: '#1E4E79',
    fontSize: 14,
    fontWeight: '800',
  },
  materialCard: {
    minHeight: 112,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E5EAF0',
    backgroundColor: '#F8FAFC',
    padding: 10,
  },
  materialThumb: {
    width: 54,
    height: 54,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#FFFFFF',
  },
  materialInfo: {
    flex: 1,
    gap: 4,
  },
  materialName: {
    color: '#102A43',
    fontSize: 15,
    fontWeight: '800',
  },
  materialDetail: {
    color: '#52616B',
    fontSize: 12,
    fontWeight: '700',
  },
  materialMetaRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  materialMeta: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '800',
  },
  materialPrice: {
    color: '#102A43',
    fontSize: 12,
    fontWeight: '800',
  },
  quantityBox: {
    width: 38,
    alignItems: 'center',
    gap: 5,
  },
  quantityButton: {
    width: 32,
    height: 32,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#FFFFFF',
  },
  quantityText: {
    color: '#102A43',
    fontSize: 16,
    fontWeight: '800',
  },
  permanentFields: {
    gap: 8,
    paddingTop: 4,
  },
  segmented: {
    minHeight: 38,
    flexDirection: 'row',
    gap: 6,
    borderRadius: 8,
    backgroundColor: '#EEF5FA',
    padding: 4,
  },
  segmentButton: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
  },
  segmentText: {
    color: '#1E4E79',
    fontSize: 12,
    fontWeight: '800',
  },
  segmentTextActive: {
    color: '#FFFFFF',
  },
  inlineInput: {
    minHeight: 42,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    color: '#102A43',
    fontSize: 13,
    fontWeight: '700',
    paddingHorizontal: 10,
  },
  inlineTextarea: {
    minHeight: 68,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    color: '#102A43',
    fontSize: 13,
    fontWeight: '700',
    padding: 10,
    textAlignVertical: 'top',
  },
  centerState: {
    minHeight: 120,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
  },
  centerStateText: {
    color: '#52616B',
    fontSize: 13,
    fontWeight: '800',
  },
  loadMoreButton: {
    minHeight: 44,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  loadMoreText: {
    color: '#1E4E79',
    fontSize: 13,
    fontWeight: '800',
  },
  cartPanel: {
    gap: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 14,
  },
  cartIcon: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
  },
  cartRow: {
    minHeight: 54,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    padding: 10,
  },
  cartQuantity: {
    width: 34,
    height: 34,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  cartQuantityText: {
    color: '#1E4E79',
    fontSize: 14,
    fontWeight: '800',
  },
  cartInfo: {
    flex: 1,
    gap: 2,
  },
  cartName: {
    color: '#102A43',
    fontSize: 14,
    fontWeight: '800',
  },
  cartDetail: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '700',
  },
  emptyCart: {
    minHeight: 86,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 7,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
  },
  emptyCartText: {
    color: '#52616B',
    fontSize: 13,
    fontWeight: '800',
  },
  formPreview: {
    gap: 8,
    borderTopWidth: 1,
    borderTopColor: '#E5EAF0',
    paddingTop: 12,
  },
  formLabel: {
    color: '#334E68',
    fontSize: 12,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  fakeInput: {
    minHeight: 44,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 8,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 12,
  },
  fakeTextarea: {
    minHeight: 86,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    color: '#102A43',
    fontSize: 13,
    fontWeight: '700',
    padding: 12,
    textAlignVertical: 'top',
  },
  fakeInputText: {
    flex: 1,
    color: '#829AB1',
    fontSize: 13,
    fontWeight: '700',
  },
  fakeInputValue: {
    flex: 1,
    color: '#102A43',
    fontSize: 13,
    fontWeight: '800',
  },
  cartActions: {
    flexDirection: 'row',
    gap: 10,
  },
  secondaryButton: {
    minHeight: 48,
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#F1C4C4',
    backgroundColor: '#FFF5F5',
  },
  secondaryButtonText: {
    color: '#C53030',
    fontSize: 14,
    fontWeight: '800',
  },
  primaryButton: {
    minHeight: 48,
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    borderRadius: 8,
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '800',
  },
  disabledButton: {
    opacity: 0.72,
  },
  modalOverlay: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: '#102A4399',
    padding: 18,
  },
  modalPanel: {
    maxHeight: '78%',
    overflow: 'hidden',
    borderRadius: 8,
    backgroundColor: '#FFFFFF',
  },
  modalHeader: {
    minHeight: 62,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#D9E2EC',
    padding: 14,
  },
  modalTitle: {
    color: '#102A43',
    fontSize: 18,
    fontWeight: '800',
  },
  modalCloseButton: {
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  modalContent: {
    gap: 8,
    padding: 14,
  },
  complementoOption: {
    minHeight: 48,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    paddingHorizontal: 12,
  },
  complementoOptionActive: {
    backgroundColor: '#1E4E79',
  },
  complementoOptionText: {
    flex: 1,
    color: '#102A43',
    fontSize: 13,
    fontWeight: '800',
  },
  complementoOptionTextActive: {
    color: '#FFFFFF',
  },
  pressed: {
    opacity: 0.72,
  },
});
