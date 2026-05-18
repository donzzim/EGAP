import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import {
  CameraView,
  useCameraPermissions,
  type BarcodeScanningResult,
  type BarcodeType,
} from 'expo-camera';
import { router, useFocusEffect, type Href } from 'expo-router';
import { useCallback, useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Animated,
  Easing,
  Modal,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { BottomBar } from '@/components/bottom-bar';
import { bensApi, type BemPatrimonial } from '@/src/api/bens';
import { authApi, type MobileUser } from '@/src/api/auth';
import { ApiError, NetworkError } from '@/src/api/errors';
import { recentBensStorage, type RecentBem } from '@/src/storage/recentBens';

const BARCODE_TYPES: BarcodeType[] = [
  'ean13',
  'ean8',
  'upc_a',
  'upc_e',
  'code39',
  'code93',
  'code128',
  'codabar',
  'itf14',
];

type NotificationTone = 'success' | 'error' | 'info';

interface NotificationState {
  message: string;
  tone: NotificationTone;
}

function displayValue(value: unknown, fallback = '-'): string {
  if (value === null || value === undefined || value === '') {
    return fallback;
  }

  return String(value);
}

function padDatePart(value: number): string {
  return String(value).padStart(2, '0');
}

function buildLocalDate(
  year: number,
  month: number,
  day: number,
  hours = 0,
  minutes = 0,
  seconds = 0,
): Date | null {
  const date = new Date(year, month - 1, day, hours, minutes, seconds);

  if (
    date.getFullYear() !== year
    || date.getMonth() !== month - 1
    || date.getDate() !== day
  ) {
    return null;
  }

  return date;
}

function parseDateTime(value: unknown): Date | null {
  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? null : value;
  }

  if (typeof value === 'number') {
    if (!Number.isFinite(value)) {
      return null;
    }

    const timestamp = Math.abs(value) < 100000000000 ? value * 1000 : value;
    const date = new Date(timestamp);

    return Number.isNaN(date.getTime()) ? null : date;
  }

  if (typeof value !== 'string') {
    return null;
  }

  const trimmedValue = value.trim();

  if (!trimmedValue) {
    return null;
  }

  if (/^-?\d+(\.\d+)?$/.test(trimmedValue)) {
    return parseDateTime(Number(trimmedValue));
  }

  const brazilianDateMatch = trimmedValue.match(
    /^(\d{1,2})\/(\d{1,2})\/(\d{4})(?:[ T](\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?$/,
  );

  if (brazilianDateMatch) {
    return buildLocalDate(
      Number(brazilianDateMatch[3]),
      Number(brazilianDateMatch[2]),
      Number(brazilianDateMatch[1]),
      Number(brazilianDateMatch[4] ?? 0),
      Number(brazilianDateMatch[5] ?? 0),
      Number(brazilianDateMatch[6] ?? 0),
    );
  }

  const databaseDateMatch = trimmedValue.match(
    /^(\d{4})-(\d{1,2})-(\d{1,2})(?:[ T](\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?$/,
  );

  if (databaseDateMatch) {
    return buildLocalDate(
      Number(databaseDateMatch[1]),
      Number(databaseDateMatch[2]),
      Number(databaseDateMatch[3]),
      Number(databaseDateMatch[4] ?? 0),
      Number(databaseDateMatch[5] ?? 0),
      Number(databaseDateMatch[6] ?? 0),
    );
  }

  const parsedDate = new Date(trimmedValue);

  return Number.isNaN(parsedDate.getTime()) ? null : parsedDate;
}

function formatDateTime(value: unknown, fallback = '-'): string {
  if (value === null || value === undefined || value === '') {
    return fallback;
  }

  const date = parseDateTime(value);

  if (!date) {
    return String(value);
  }

  return [
    `${padDatePart(date.getDate())}/${padDatePart(date.getMonth() + 1)}/${date.getFullYear()}`,
    `${padDatePart(date.getHours())}:${padDatePart(date.getMinutes())}:${padDatePart(date.getSeconds())}`,
  ].join(' ');
}

function onlyDigits(value: string): string {
  return value.replace(/\D/g, '');
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

function getReferenciaNome(value: BemPatrimonial['setor']): string {
  if (typeof value === 'object' && value !== null && 'nome' in value) {
    return displayValue(value.nome);
  }

  return displayValue(value);
}

function formatMoney(value: unknown): string {
  if (value === null || value === undefined || value === '') {
    return '-';
  }

  const numericValue = typeof value === 'number'
    ? value
    : Number(String(value).replace(',', '.'));

  if (!Number.isFinite(numericValue)) {
    return String(value);
  }

  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(numericValue);
}

function getRequestErrorMessage(error: unknown): string {
  if (error instanceof ApiError || error instanceof NetworkError) {
    return error.message;
  }

  return 'Não foi possível consultar o patrimônio.';
}

function getRecentBemStatusColor(situacao: string): string {
  const normalizedSituacao = situacao
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase();

  if (normalizedSituacao.includes('pendente')) {
    return '#B7791F';
  }

  if (normalizedSituacao.includes('divergente') || normalizedSituacao.includes('outro')) {
    return '#1E4E79';
  }

  if (normalizedSituacao.includes('baixa') || normalizedSituacao.includes('erro')) {
    return '#C53030';
  }

  return '#2F855A';
}

function formatRecentConsultedAt(value: string): string {
  const consultedAt = new Date(value);

  if (Number.isNaN(consultedAt.getTime())) {
    return '';
  }

  return consultedAt.toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit',
  });
}

export default function PrincipalScreen() {
  const [user, setUser] = useState<MobileUser | null>(null);
  const [isLoadingSession, setIsLoadingSession] = useState(true);
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [cameraPermission, requestCameraPermission] = useCameraPermissions();
  const [isScannerActive, setIsScannerActive] = useState(false);
  const [hasScannedBarcode, setHasScannedBarcode] = useState(false);
  const [patrimonioCode, setPatrimonioCode] = useState('');
  const [notification, setNotification] = useState<NotificationState | null>(null);
  const [isConsultingPatrimonio, setIsConsultingPatrimonio] = useState(false);
  const [consultedBem, setConsultedBem] = useState<BemPatrimonial | null>(null);
  const [isPatrimonioModalVisible, setIsPatrimonioModalVisible] = useState(false);
  const [recentBens, setRecentBens] = useState<RecentBem[]>([]);
  const notificationProgress = useRef(new Animated.Value(0)).current;

  const loadRecentBens = useCallback(async (userId: number | string) => {
    try {
      const storedRecentBens = await recentBensStorage.list(userId);

      setRecentBens(storedRecentBens);
    } catch (error) {
      console.warn('não foi possivel carregar o historico de bens recentes.', error);
    }
  }, []);

  useEffect(() => {
    let isMounted = true;

    async function loadSession() {
      const session = await authApi.getStoredSession();

      if (!session) {
        router.replace('/');
        return;
      }

      if (isMounted) {
        setUser(session.user);
      }

      const isValid = await authApi.validateSession();

      if (!isValid) {
        router.replace('/');
        return;
      }

      if (isMounted) {
        await loadRecentBens(session.user.id);
      }

      if (isMounted) {
        setIsLoadingSession(false);
      }
    }

    loadSession();

    return () => {
      isMounted = false;
    };
  }, [loadRecentBens]);

  useFocusEffect(
    useCallback(() => {
      if (user?.id === undefined || user?.id === null) {
        return;
      }

      loadRecentBens(user.id);
    }, [loadRecentBens, user?.id]),
  );

  useEffect(() => {
    if (!notification) {
      return;
    }

    notificationProgress.setValue(0);

    Animated.timing(notificationProgress, {
      toValue: 1,
      duration: 260,
      easing: Easing.out(Easing.cubic),
      useNativeDriver: true,
    }).start();

    const timeoutId = setTimeout(() => {
      Animated.timing(notificationProgress, {
        toValue: 0,
        duration: 220,
        easing: Easing.in(Easing.cubic),
        useNativeDriver: true,
      }).start(({ finished }) => {
        if (finished) {
          setNotification(null);
        }
      });
    }, 3000);

    return () => {
      clearTimeout(timeoutId);
      notificationProgress.stopAnimation();
    };
  }, [notification, notificationProgress]);

  async function handleLogout() {
    setIsLoggingOut(true);

    try {
      await authApi.logout();
    } finally {
      router.replace('/');
    }
  }

  async function addBemToRecent(bem: BemPatrimonial) {
    const sessionUser = user ?? (await authApi.getStoredSession())?.user ?? null;

    if (!sessionUser) {
      return;
    }

    try {
      const nextRecentBens = await recentBensStorage.add(sessionUser.id, bem);

      setRecentBens(nextRecentBens);
    } catch (error) {
      console.warn('não foi possivel atualizar o historico de bens recentes.', error);
    }
  }

  async function handleStartScanner() {
    setNotification(null);

    if (isScannerActive) {
      handleCloseScanner();
      return;
    }

    if (!cameraPermission?.granted) {
      const permission = await requestCameraPermission();

      if (!permission.granted) {
        setNotification({
          message: 'Permita o acesso a camera para ler códigos de barras.',
          tone: 'error',
        });
        return;
      }
    }

    setHasScannedBarcode(false);
    setIsScannerActive(true);
  }

  function handleCloseScanner() {
    setIsScannerActive(false);
    setHasScannedBarcode(false);
    setNotification({
      message: 'Câmera fechada.',
      tone: 'info',
    });
  }

  function handleBarcodeScanned(result: BarcodeScanningResult) {
    if (hasScannedBarcode) {
      return;
    }

    setHasScannedBarcode(true);
    setIsScannerActive(false);
    setPatrimonioCode(onlyDigits(result.data));
    setConsultedBem(null);
    setNotification({
      message: 'Código de barras lido com sucesso.',
      tone: 'success',
    });
  }

  async function handleConsultPatrimonio() {
    const trimmedCode = onlyDigits(patrimonioCode);

    if (!trimmedCode) {
      setNotification({
        message: 'Informe ou leia um código de patrimônio antes de consultar.',
        tone: 'error',
      });
      return;
    }

    setIsConsultingPatrimonio(true);
    setConsultedBem(null);

    try {
      const bem = await bensApi.consultByPatrimonio(trimmedCode);

      if (!bem) {
        setNotification({
          message: 'Patrimônio não localizado.',
          tone: 'error',
        });
        return;
      }

      setConsultedBem(bem);
      await addBemToRecent(bem);
      setIsPatrimonioModalVisible(true);
      setNotification({
        message: 'Patrimônio consultado com sucesso.',
        tone: 'success',
      });
    } catch (error) {
      setNotification({
        message: getRequestErrorMessage(error),
        tone: 'error',
      });
    } finally {
      setIsConsultingPatrimonio(false);
    }
  }

  async function handleOpenRecentBem(asset: RecentBem) {
    const codigo = onlyDigits(asset.codigo) || asset.codigo.trim();

    if (!codigo) {
      return;
    }

    setPatrimonioCode(onlyDigits(codigo));
    setIsConsultingPatrimonio(true);
    setConsultedBem(null);

    try {
      const bem = await bensApi.consultByPatrimonio(codigo);

      if (!bem) {
        setNotification({
          message: 'Patrimônio não localizado.',
          tone: 'error',
        });
        return;
      }

      setConsultedBem(bem);
      await addBemToRecent(bem);
      setIsPatrimonioModalVisible(true);
    } catch (error) {
      setNotification({
        message: getRequestErrorMessage(error),
        tone: 'error',
      });
    } finally {
      setIsConsultingPatrimonio(false);
    }
  }

  if (isLoadingSession) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator color="#1E4E79" />
          <Text style={styles.loadingText}>Carregando sessão</Text>
        </View>
      </SafeAreaView>
    );
  }

  const renderDetailRow = (label: string, value: unknown) => (
    <View style={styles.modalDetailRow} key={label}>
      <Text style={styles.modalDetailLabel}>{label}</Text>
      <Text style={styles.modalDetailValue}>{displayValue(value)}</Text>
    </View>
  );

  const notificationAnimatedStyle = {
    opacity: notificationProgress,
    transform: [
      {
        translateY: notificationProgress.interpolate({
          inputRange: [0, 1],
          outputRange: [28, 0],
        }),
      },
    ],
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      {notification ? (
        <Animated.View
          style={[
            styles.notificationPopup,
            notificationAnimatedStyle,
            notification.tone === 'success'
              ? styles.notificationSuccess
              : notification.tone === 'error'
                ? styles.notificationError
                : styles.notificationInfo,
          ]}>
          <MaterialIcons
            name={notification.tone === 'success' ? 'check-circle' : notification.tone === 'error' ? 'error-outline' : 'info-outline'}
            size={21}
            color="#FFFFFF"
          />
          <Text style={styles.notificationText}>{notification.message}</Text>
        </Animated.View>
      ) : null}

      <Modal
        animationType="fade"
        transparent
        visible={isPatrimonioModalVisible}
        onRequestClose={() => setIsPatrimonioModalVisible(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.patrimonioModal}>
            <View style={styles.modalHeader}>
              <View style={styles.modalHeaderIcon}>
                <MaterialIcons name="inventory-2" size={24} color="#1E4E79" />
              </View>
              <View style={styles.modalHeaderText}>
                <Text style={styles.modalEyebrow}>Patrimônio consultado</Text>
                <Text style={styles.modalTitle}>{consultedBem ? getBemCodigo(consultedBem) : '-'}</Text>
              </View>
              <Pressable
                onPress={() => setIsPatrimonioModalVisible(false)}
                style={styles.modalCloseButton}>
                <MaterialIcons name="close" size={22} color="#1E4E79" />
              </Pressable>
            </View>

            {consultedBem ? (
              <ScrollView contentContainerStyle={styles.modalContent} showsVerticalScrollIndicator={false}>
                <View style={styles.modalSection}>
                  <Text style={styles.modalSectionTitle}>Identificação</Text>
                  <Text style={styles.modalDescription}>{getBemDescricao(consultedBem)}</Text>
                  {renderDetailRow('Situacao', consultedBem.situacao ?? consultedBem.estado)}
                  {renderDetailRow('Tipo do bem', consultedBem.tipo_bem)}
                  {renderDetailRow('Estado de conservação', consultedBem.estado_conservacao)}
                  {renderDetailRow('Patrimônio anterior', consultedBem.patrimonio_anterior)}
                </View>

                <View style={styles.modalSection}>
                  <Text style={styles.modalSectionTitle}>Características</Text>
                  {renderDetailRow('Marca', consultedBem.marca)}
                  {renderDetailRow('Modelo', consultedBem.modelo)}
                  {renderDetailRow('Número de série', consultedBem.numero_serie ?? consultedBem.serie)}
                  {renderDetailRow('Voltagem', consultedBem.voltagem)}
                  {renderDetailRow('Tombo SMARAPD', consultedBem.tombo_smarapd)}
                  {renderDetailRow('Num. tombo SMARAPD', consultedBem.num_tombo_smarapd)}
                </View>

                <View style={styles.modalSection}>
                  <Text style={styles.modalSectionTitle}>Localização</Text>
                  {renderDetailRow('Unidade Judiciária', getReferenciaNome(consultedBem.unidade_judiciaria))}
                  {renderDetailRow('Setor', getReferenciaNome(consultedBem.setor))}
                  {renderDetailRow('Complemento', getReferenciaNome(consultedBem.complemento_setor))}
                  {renderDetailRow('Andar', consultedBem.andar_setor)}
                </View>

                <View style={styles.modalSection}>
                  <Text style={styles.modalSectionTitle}>Valores e documentos</Text>
                  {renderDetailRow('Valor de aquisição', formatMoney(consultedBem.valor_aquisicao))}
                  {renderDetailRow('Valor atual', formatMoney(consultedBem.valor))}
                  {renderDetailRow('Data de incorporação', formatDateTime(consultedBem.data_incorporacao))}
                  {renderDetailRow('Data de cadastro', formatDateTime(consultedBem.data_cadastro))}
                  {renderDetailRow('Numero do processo', consultedBem.numero_processo)}
                  {renderDetailRow('Nota de empenho', consultedBem.nota_empenho)}
                  {renderDetailRow('Nota de liquidação', consultedBem.nota_liquidacao)}
                  {renderDetailRow('Data de liquidação', formatDateTime(consultedBem.data_liquidacao))}
                </View>

                {consultedBem.data_baixa || consultedBem.processo_baixa ? (
                  <View style={styles.modalSection}>
                    <Text style={styles.modalSectionTitle}>Baixa</Text>
                    {renderDetailRow('Data da baixa', formatDateTime(consultedBem.data_baixa))}
                    {renderDetailRow('Processo de baixa', consultedBem.processo_baixa)}
                  </View>
                ) : null}

                {consultedBem.observacao ? (
                  <View style={styles.modalSection}>
                    <Text style={styles.modalSectionTitle}>Observação</Text>
                    <Text style={styles.modalObservation}>{consultedBem.observacao}</Text>
                  </View>
                ) : null}
              </ScrollView>
            ) : null}
          </View>
        </View>
      </Modal>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.header}>
          <View style={styles.headerTextGroup}>
            <Text style={styles.eyebrow}>EGap Mobile</Text>
            <Text style={styles.title}>Seção Patrimonial</Text>
          </View>
          <Pressable
            disabled={isLoggingOut}
            onPress={handleLogout}
            style={({ pressed }) => [
              styles.logoutButton,
              (pressed || isLoggingOut) && styles.logoutButtonPressed,
            ]}>
            {isLoggingOut ? (
              <ActivityIndicator color="#1E4E79" />
            ) : (
              <MaterialIcons name="logout" size={21} color="#1E4E79" />
            )}
          </Pressable>
        </View>

        <View style={styles.sectorPanel}>
          <View style={styles.panelIcon}>
            <MaterialIcons name="apartment" size={24} color="#1E4E79" />
          </View>
          <View style={styles.sectorInfo}>
            <Text style={styles.panelLabel}>Sessão ativa</Text>
            <Text style={styles.sectorName}>{user?.name ?? user?.login ?? 'Usuário mobile'}</Text>
            <Text style={styles.sectorMeta}>
              Unidade {user?.unidade_judiciaria ?? '-'} | Setor {user?.setor ?? '-'}
            </Text>
          </View>
        </View>

        <View style={styles.scanPanel}>
          <View style={styles.scanHeader}>
            <View>
              <Text style={styles.sectionTitle}>Leitura patrimonial</Text>
              <Text style={styles.sectionDescription}>Câmera configurada apenas para código de barras.</Text>
            </View>
          </View>

          <Pressable
            disabled={isScannerActive}
            onPress={handleStartScanner}
            style={({ pressed }) => [
              styles.scannerMock,
              pressed && styles.scannerMockPressed,
            ]}>
            {isScannerActive && cameraPermission?.granted ? (
              <CameraView
                style={styles.cameraPreview}
                facing="back"
                barcodeScannerSettings={{ barcodeTypes: BARCODE_TYPES }}
                onBarcodeScanned={hasScannedBarcode ? undefined : handleBarcodeScanned}
              />
            ) : null}
            <View style={styles.scanCornerTopLeft} />
            <View style={styles.scanCornerTopRight} />
            {!isScannerActive ? (
              <>
                <MaterialIcons name="center-focus-strong" size={56} color="#1E4E79" />
                <Text style={styles.scannerText}>Toque para abrir a câmera</Text>
              </>
            ) : null}
            {isScannerActive ? (
              <Text style={styles.cameraHint}>Aponte para o código de barras da plaqueta</Text>
            ) : null}
            {isScannerActive ? (
              <Pressable onPress={handleCloseScanner} style={styles.closeCameraButton}>
                <MaterialIcons name="close" size={20} color="#FFFFFF" />
                <Text style={styles.closeCameraButtonText}>Fechar câmera</Text>
              </Pressable>
            ) : null}
            <View style={styles.scanLine} />
            <View style={styles.scanCornerBottomLeft} />
            <View style={styles.scanCornerBottomRight} />
          </Pressable>

          <View style={styles.manualEntry}>
            <MaterialIcons name="pin" size={20} color="#627D98" />
            <TextInput
              placeholder="Digite ou leia o código patrimonial"
              placeholderTextColor="#829AB1"
              autoCorrect={false}
              keyboardType="number-pad"
              inputMode="numeric"
              value={patrimonioCode}
              onChangeText={(value) => {
                setPatrimonioCode(onlyDigits(value));
                setConsultedBem(null);
              }}
              onSubmitEditing={handleConsultPatrimonio}
              style={styles.manualEntryInput}
            />
          </View>
          <Pressable
            disabled={isConsultingPatrimonio}
            onPress={handleConsultPatrimonio}
            style={({ pressed }) => [
              styles.consultButton,
              (pressed || isConsultingPatrimonio) && styles.actionButtonPressed,
            ]}>
            {isConsultingPatrimonio ? (
              <ActivityIndicator color="#FFFFFF" />
            ) : (
              <MaterialIcons name="search" size={21} color="#FFFFFF" />
            )}
            <Text style={styles.consultButtonText}>
              {isConsultingPatrimonio ? 'Consultando' : 'Consultar patrimônio'}
            </Text>
          </Pressable>

        </View>

        <View style={styles.assetsPanel}>
          <View style={styles.panelHeaderRow}>
            <Text style={styles.sectionTitle}>Últimas leituras</Text>
            <Text style={styles.panelHeaderMeta}>Hoje</Text>
          </View>

          {recentBens.length > 0 ? (
            recentBens.map((asset) => {
              const statusColor = getRecentBemStatusColor(asset.situacao);

              return (
                <Pressable
                  key={`${asset.id}-${asset.consultedAt}`}
                  onPress={() => handleOpenRecentBem(asset)}
                  style={({ pressed }) => [
                    styles.assetRow,
                    pressed && styles.assetRowPressed,
                  ]}>
                  <View style={styles.assetIcon}>
                    <MaterialIcons name="inventory-2" size={20} color="#1E4E79" />
                  </View>
                  <View style={styles.assetInfo}>
                    <View style={styles.assetTitleRow}>
                      <Text style={styles.assetCode}>{asset.codigo}</Text>
                      <Text style={styles.assetTime}>{formatRecentConsultedAt(asset.consultedAt)}</Text>
                    </View>
                    <Text style={styles.assetDescription} numberOfLines={2}>
                      {asset.descricao}
                    </Text>
                  </View>
                  <View style={[styles.statusBadge, { backgroundColor: `${statusColor}18` }]}>
                    <Text style={[styles.statusBadgeText, { color: statusColor }]} numberOfLines={1}>
                      {asset.situacao}
                    </Text>
                  </View>
                </Pressable>
              );
            })
          ) : (
            <View style={styles.emptyRecentPanel}>
              <MaterialIcons name="history" size={26} color="#627D98" />
              <Text style={styles.emptyRecentTitle}>Nenhuma consulta recente</Text>
              <Text style={styles.emptyRecentText}>
                Os últimos 5 patrimônios consultados aparecerão aqui.
              </Text>
            </View>
          )}
        </View>
      </ScrollView>
      <BottomBar />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F4F7FA',
  },
  notificationPopup: {
    position: 'absolute',
    bottom: 18,
    right: 20,
    left: 20,
    zIndex: 20,
    elevation: 6,
    minHeight: 48,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderRadius: 8,
    paddingHorizontal: 14,
    paddingVertical: 12,
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
    fontSize: 14,
    lineHeight: 19,
    fontWeight: '800',
  },
  modalOverlay: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: '#102A4399',
    padding: 18,
  },
  patrimonioModal: {
    maxHeight: '88%',
    overflow: 'hidden',
    borderRadius: 8,
    backgroundColor: '#FFFFFF',
  },
  modalHeader: {
    minHeight: 72,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#D9E2EC',
    padding: 14,
  },
  modalHeaderIcon: {
    width: 44,
    height: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  modalHeaderText: {
    flex: 1,
    gap: 3,
  },
  modalEyebrow: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  modalTitle: {
    color: '#102A43',
    fontSize: 22,
    fontWeight: '800',
  },
  modalCloseButton: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#B6D4EA',
    backgroundColor: '#EAF4FB',
  },
  modalContent: {
    gap: 12,
    padding: 14,
    paddingBottom: 18,
  },
  modalSection: {
    gap: 8,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#F8FAFC',
    padding: 12,
  },
  modalSectionTitle: {
    color: '#102A43',
    fontSize: 15,
    fontWeight: '800',
  },
  modalDescription: {
    color: '#334E68',
    fontSize: 14,
    lineHeight: 20,
    fontWeight: '700',
  },
  modalDetailRow: {
    gap: 2,
  },
  modalDetailLabel: {
    color: '#627D98',
    fontSize: 11,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  modalDetailValue: {
    color: '#102A43',
    fontSize: 14,
    lineHeight: 19,
    fontWeight: '700',
  },
  modalObservation: {
    color: '#334E68',
    fontSize: 13,
    lineHeight: 19,
    fontWeight: '700',
  },
  content: {
    gap: 16,
    padding: 20,
    paddingBottom: 28,
  },
  loadingContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 12,
    padding: 24,
  },
  loadingText: {
    color: '#334E68',
    fontSize: 14,
    fontWeight: '800',
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
  logoutButton: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#B6D4EA',
    backgroundColor: '#EAF4FB',
  },
  logoutButtonPressed: {
    opacity: 0.72,
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
  scannerMockPressed: {
    borderColor: '#1E4E79',
    backgroundColor: '#EAF4FB',
  },
  cameraPreview: {
    ...StyleSheet.absoluteFillObject,
  },
  scannerText: {
    color: '#334E68',
    fontSize: 15,
    fontWeight: '800',
  },
  cameraHint: {
    position: 'absolute',
    bottom: 26,
    maxWidth: '82%',
    overflow: 'hidden',
    borderRadius: 8,
    backgroundColor: '#102A43CC',
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '800',
    paddingHorizontal: 12,
    paddingVertical: 8,
    textAlign: 'center',
  },
  closeCameraButton: {
    position: 'absolute',
    top: 12,
    right: 12,
    zIndex: 3,
    minHeight: 38,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    borderRadius: 8,
    backgroundColor: '#102A43CC',
    paddingHorizontal: 10,
  },
  closeCameraButtonText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '800',
  },
  scanLine: {
    width: '78%',
    height: 2,
    borderRadius: 2,
    backgroundColor: '#2F855A',
    zIndex: 2,
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
  manualEntryInput: {
    flex: 1,
    color: '#102A43',
    fontSize: 15,
    fontWeight: '800',
  },
  consultButton: {
    minHeight: 50,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#1E4E79',
  },
  consultButtonText: {
    color: '#FFFFFF',
    fontSize: 15,
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
  actionButtonPressed: {
    opacity: 0.72,
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
  assetRowPressed: {
    opacity: 0.72,
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
  assetTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 8,
  },
  assetCode: {
    flex: 1,
    color: '#102A43',
    fontSize: 14,
    fontWeight: '800',
  },
  assetTime: {
    color: '#627D98',
    fontSize: 11,
    fontWeight: '800',
  },
  assetDescription: {
    color: '#52616B',
    fontSize: 12,
    fontWeight: '600',
  },
  statusBadge: {
    maxWidth: 116,
    borderRadius: 8,
    paddingHorizontal: 8,
    paddingVertical: 5,
  },
  statusBadgeText: {
    fontSize: 11,
    fontWeight: '800',
  },
  emptyRecentPanel: {
    alignItems: 'center',
    justifyContent: 'center',
    gap: 7,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    padding: 16,
  },
  emptyRecentTitle: {
    color: '#102A43',
    fontSize: 15,
    fontWeight: '800',
    textAlign: 'center',
  },
  emptyRecentText: {
    color: '#52616B',
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '700',
    textAlign: 'center',
  },
});
