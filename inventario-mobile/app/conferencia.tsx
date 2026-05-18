import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import {
  CameraView,
  useCameraPermissions,
  type BarcodeScanningResult,
  type BarcodeType,
} from 'expo-camera';
import { router } from 'expo-router';
import { useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Modal,
  Pressable,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { BottomBar } from '@/components/bottom-bar';
import { authApi, type MobileUser } from '@/src/api/auth';
import { ApiError, NetworkError } from '@/src/api/errors';
import {
  conferenciaApi,
  type BemConferencia,
  type ConferenciaBensResult,
  type ConferenciaInfo,
  type ConferenciaStatus,
  type ResultadoLeitura,
} from '@/src/api/conferencia';

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

type FilterStatus = 'todos' | ConferenciaStatus;
type NotificationTone = 'success' | 'error' | 'info';

interface NotificationState {
  message: string;
  tone: NotificationTone;
}

const FILTERS: { key: FilterStatus; label: string }[] = [
  { key: 'todos', label: 'Todos' },
  { key: 'pendente', label: 'Pendentes' },
  { key: 'localizado', label: 'Localizados' },
  { key: 'nao_localizado', label: 'Não localizados' },
  { key: 'divergente', label: 'Divergentes' },
  { key: 'em_transferencia', label: 'Transferência' },
  { key: 'cadastrado_manualmente', label: 'Manuais' },
];

function onlyDigits(value: string): string {
  return value.replace(/\D/g, '');
}

function displayValue(value: unknown, fallback = '-'): string {
  if (value === null || value === undefined || value === '') {
    return fallback;
  }

  return String(value);
}

function getRequestErrorMessage(error: unknown): string {
  if (error instanceof ApiError || error instanceof NetworkError) {
    return error.message;
  }

  return 'Não foi possível concluir a operação.';
}

function getBemCodigo(bem: BemConferencia): string {
  return displayValue(bem.patrimonio ?? bem.codigo ?? bem.codigo_patrimonial ?? bem.id, 'Sem código');
}

function getBemDescricao(bem: BemConferencia): string {
  return displayValue(bem.descricao_resumida ?? bem.descricao ?? bem.denominacao, 'Bem patrimonial');
}

function getReferenciaNome(value: BemConferencia['setor']): string {
  if (typeof value === 'object' && value !== null && 'nome' in value) {
    return displayValue(value.nome);
  }

  return displayValue(value);
}

function getStatus(bem: BemConferencia): ConferenciaStatus {
  return bem.conferencia?.status ?? 'pendente';
}

function getStatusLabel(bem: BemConferencia): string {
  return bem.conferencia?.status_label ?? 'Pendente';
}

function getStatusColor(status: ConferenciaStatus): string {
  switch (status) {
    case 'localizado':
      return '#2F855A';
    case 'pendente':
    case 'cadastrado_manualmente':
      return '#B7791F';
    case 'nao_localizado':
      return '#C53030';
    case 'divergente':
    case 'em_transferencia':
      return '#1E4E79';
    default:
      return '#627D98';
  }
}

export default function ConferenciaScreen() {
  const [user, setUser] = useState<MobileUser | null>(null);
  const [info, setInfo] = useState<ConferenciaInfo | null>(null);
  const [bens, setBens] = useState<BemConferencia[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [manualCode, setManualCode] = useState('');
  const [resultadoLeitura, setResultadoLeitura] = useState<ResultadoLeitura | null>(null);
  const [notification, setNotification] = useState<NotificationState | null>(null);
  const [filterStatus, setFilterStatus] = useState<FilterStatus>('pendente');
  const [cameraPermission, requestCameraPermission] = useCameraPermissions();
  const [isScannerActive, setIsScannerActive] = useState(false);
  const [hasScannedBarcode, setHasScannedBarcode] = useState(false);
  const [isValidating, setIsValidating] = useState(false);
  const [actionLoading, setActionLoading] = useState<string | null>(null);
  const [selectedBem, setSelectedBem] = useState<BemConferencia | null>(null);
  const [naoLocalizadoBem, setNaoLocalizadoBem] = useState<BemConferencia | null>(null);
  const [divergenciaBem, setDivergenciaBem] = useState<BemConferencia | null>(null);
  const [justificativa, setJustificativa] = useState('');
  const [camposDivergentes, setCamposDivergentes] = useState('');
  const [observacaoDivergencia, setObservacaoDivergencia] = useState('');

  async function loadConferencia(showLoader = true) {
    if (showLoader) {
      setIsLoading(true);
    }

    try {
      const session = await authApi.getStoredSession();

      if (!session) {
        router.replace('/');
        return;
      }

      setUser(session.user);
      const data: ConferenciaBensResult = await conferenciaApi.listarBens();
      setInfo({
        inventario: data.inventario,
        atividade: data.atividade,
        resumo: data.resumo,
        scope: data.scope,
      });
      setBens(data.bens);
    } catch (error) {
      setNotification({ message: getRequestErrorMessage(error), tone: 'error' });
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  }

  useEffect(() => {
    loadConferencia();
  }, []);

  const filteredBens = useMemo(() => {
    if (filterStatus === 'todos') {
      return bens;
    }

    return bens.filter((bem) => getStatus(bem) === filterStatus);
  }, [bens, filterStatus]);

  const lastReadBem = resultadoLeitura?.bem ?? null;
  const canEdit = info?.atividade.pode_editar ?? false;

  function showNotification(message: string, tone: NotificationTone) {
    setNotification({ message, tone });
  }

  function mergeBens(updatedBens: BemConferencia[]) {
    if (updatedBens.length === 0) {
      return;
    }

    setBens((currentBens) => {
      const updatedById = new Map(updatedBens.map((bem) => [String(bem.id), bem]));

      return currentBens.map((bem) => updatedById.get(String(bem.id)) ?? bem);
    });
  }

  function applyActionResult(result: {
    message?: string;
    bem?: BemConferencia | null;
    bens?: BemConferencia[];
    resumo?: ConferenciaInfo['resumo'];
    inventario?: ConferenciaInfo['inventario'];
    atividade?: ConferenciaInfo['atividade'];
  }) {
    setInfo((currentInfo) => {
      if (!currentInfo) {
        return currentInfo;
      }

      return {
        inventario: result.inventario ?? currentInfo.inventario,
        atividade: result.atividade ?? currentInfo.atividade,
        resumo: result.resumo ?? currentInfo.resumo,
        scope: currentInfo.scope,
      };
    });

    mergeBens([
      ...(result.bem ? [result.bem] : []),
      ...(result.bens ?? []),
    ]);

    if (result.message) {
      showNotification(result.message, 'success');
    }
  }

  async function handleRefresh() {
    setIsRefreshing(true);
    await loadConferencia(false);
  }

  async function handleStartScanner() {
    setNotification(null);

    if (isScannerActive) {
      setIsScannerActive(false);
      return;
    }

    if (!cameraPermission?.granted) {
      const permission = await requestCameraPermission();

      if (!permission.granted) {
        showNotification('Permita o acesso à câmera para ler o patrimônio.', 'error');
        return;
      }
    }

    setHasScannedBarcode(false);
    setIsScannerActive(true);
  }

  async function validateCode(code: string) {
    const trimmedCode = onlyDigits(code);

    if (!trimmedCode) {
      showNotification('Informe ou leia um patrimônio.', 'error');
      return;
    }

    setIsValidating(true);
    setResultadoLeitura(null);

    try {
      const result = await conferenciaApi.validarLeitura(trimmedCode);

      setResultadoLeitura(result);
      setManualCode(trimmedCode);
      showNotification(result.message, result.pode_localizar ? 'success' : 'info');
    } catch (error) {
      showNotification(getRequestErrorMessage(error), 'error');
    } finally {
      setIsValidating(false);
    }
  }

  function handleBarcodeScanned(result: BarcodeScanningResult) {
    if (hasScannedBarcode) {
      return;
    }

    setHasScannedBarcode(true);
    setIsScannerActive(false);
    validateCode(result.data);
  }

  async function handleLocalizar(bem?: BemConferencia | null) {
    const targetBem = bem ?? lastReadBem;
    const payload = targetBem?.id ? { bem_id: targetBem.id } : { codigo: manualCode };

    setActionLoading('localizar');

    try {
      const result = await conferenciaApi.localizar(payload);
      setResultadoLeitura(null);
      setManualCode('');
      applyActionResult(result);
    } catch (error) {
      showNotification(getRequestErrorMessage(error), 'error');
    } finally {
      setActionLoading(null);
    }
  }

  async function handleRegistrarNaoLocalizado() {
    if (!naoLocalizadoBem) {
      return;
    }

    if (!justificativa.trim()) {
      showNotification('Justificativa obrigatória.', 'error');
      return;
    }

    setActionLoading('nao-localizado');

    try {
      const result = await conferenciaApi.registrarNaoLocalizado([naoLocalizadoBem.id], justificativa.trim());
      setNaoLocalizadoBem(null);
      setJustificativa('');
      applyActionResult(result);
    } catch (error) {
      showNotification(getRequestErrorMessage(error), 'error');
    } finally {
      setActionLoading(null);
    }
  }

  async function handleRegistrarDivergencia() {
    if (!divergenciaBem) {
      return;
    }

    if (!observacaoDivergencia.trim()) {
      showNotification('Observação obrigatória.', 'error');
      return;
    }

    const campos = camposDivergentes
      .split(',')
      .map((campo) => campo.trim())
      .filter(Boolean);

    setActionLoading('divergencia');

    try {
      const result = await conferenciaApi.registrarDivergencia({
        bem_id: divergenciaBem.id,
        campos,
        observacao: observacaoDivergencia.trim(),
      });
      setDivergenciaBem(null);
      setCamposDivergentes('');
      setObservacaoDivergencia('');
      applyActionResult(result);
    } catch (error) {
      showNotification(getRequestErrorMessage(error), 'error');
    } finally {
      setActionLoading(null);
    }
  }

  function handleFinalizar() {
    Alert.alert(
      'Finalizar conferência',
      'A atividade do setor será bloqueada para novas alterações.',
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Finalizar',
          style: 'destructive',
          onPress: async () => {
            setActionLoading('finalizar');

            try {
              const result = await conferenciaApi.finalizar();
              applyActionResult(result);
            } catch (error) {
              showNotification(getRequestErrorMessage(error), 'error');
            } finally {
              setActionLoading(null);
            }
          },
        },
      ],
    );
  }

  const renderMetric = (label: string, value: number, color = '#1E4E79') => (
    <View style={styles.metricItem} key={label}>
      <Text style={[styles.metricValue, { color }]}>{value}</Text>
      <Text style={styles.metricLabel}>{label}</Text>
    </View>
  );

  const renderBemRow = (bem: BemConferencia) => {
    const status = getStatus(bem);
    const statusColor = getStatusColor(status);
    const canLocalizarBem = canEdit && ['pendente', 'cadastrado_manualmente'].includes(status);
    const canNaoLocalizadoBem = canEdit && status === 'pendente';

    return (
      <Pressable
        key={`${bem.id}-${getBemCodigo(bem)}`}
        onPress={() => setSelectedBem(bem)}
        style={({ pressed }) => [styles.assetRow, pressed && styles.pressed]}>
        <View style={styles.assetIcon}>
          <MaterialIcons name="inventory-2" size={20} color="#1E4E79" />
        </View>

        <View style={styles.assetInfo}>
          <View style={styles.assetTitleRow}>
            <Text style={styles.assetCode}>{getBemCodigo(bem)}</Text>
            <View style={[styles.statusBadge, { backgroundColor: `${statusColor}18` }]}>
              <Text style={[styles.statusBadgeText, { color: statusColor }]} numberOfLines={1}>
                {getStatusLabel(bem)}
              </Text>
            </View>
          </View>
          <Text style={styles.assetDescription} numberOfLines={2}>{getBemDescricao(bem)}</Text>
          <Text style={styles.assetMeta} numberOfLines={1}>
            {displayValue(bem.marca)} | {displayValue(bem.modelo)}
          </Text>

          {canEdit ? (
            <View style={styles.inlineActions}>
              {canLocalizarBem ? (
                <Pressable onPress={() => handleLocalizar(bem)} style={styles.inlineActionPrimary}>
                  <MaterialIcons name="check" size={17} color="#FFFFFF" />
                  <Text style={styles.inlineActionPrimaryText}>Localizar</Text>
                </Pressable>
              ) : null}
              {canNaoLocalizadoBem ? (
                <Pressable onPress={() => setNaoLocalizadoBem(bem)} style={styles.inlineActionSecondary}>
                  <MaterialIcons name="block" size={17} color="#C53030" />
                  <Text style={styles.inlineActionDangerText}>Não localizado</Text>
                </Pressable>
              ) : null}
              <Pressable onPress={() => setDivergenciaBem(bem)} style={styles.inlineActionSecondary}>
                <MaterialIcons name="report-problem" size={17} color="#1E4E79" />
                <Text style={styles.inlineActionSecondaryText}>Divergência</Text>
              </Pressable>
            </View>
          ) : null}
        </View>
      </Pressable>
    );
  };

  if (isLoading) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator color="#1E4E79" />
          <Text style={styles.loadingText}>Carregando conferência</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <Modal transparent animationType="fade" visible={selectedBem !== null} onRequestClose={() => setSelectedBem(null)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalPanel}>
            <View style={styles.modalHeader}>
              <View style={styles.modalHeaderText}>
                <Text style={styles.modalEyebrow}>Patrimônio</Text>
                <Text style={styles.modalTitle}>{selectedBem ? getBemCodigo(selectedBem) : '-'}</Text>
              </View>
              <Pressable onPress={() => setSelectedBem(null)} style={styles.iconButton}>
                <MaterialIcons name="close" size={22} color="#1E4E79" />
              </Pressable>
            </View>
            {selectedBem ? (
              <ScrollView contentContainerStyle={styles.modalContent}>
                <Text style={styles.modalDescription}>{getBemDescricao(selectedBem)}</Text>
                <View style={styles.detailGrid}>
                  <Text style={styles.detailLabel}>Status</Text>
                  <Text style={styles.detailValue}>{getStatusLabel(selectedBem)}</Text>
                  <Text style={styles.detailLabel}>Setor</Text>
                  <Text style={styles.detailValue}>{getReferenciaNome(selectedBem.setor)}</Text>
                  <Text style={styles.detailLabel}>Complemento</Text>
                  <Text style={styles.detailValue}>{getReferenciaNome(selectedBem.complemento_setor)}</Text>
                  <Text style={styles.detailLabel}>Série</Text>
                  <Text style={styles.detailValue}>{displayValue(selectedBem.numero_serie)}</Text>
                  <Text style={styles.detailLabel}>Conservação</Text>
                  <Text style={styles.detailValue}>{displayValue(selectedBem.estado_conservacao)}</Text>
                  <Text style={styles.detailLabel}>Observação</Text>
                  <Text style={styles.detailValue}>{displayValue(selectedBem.conferencia?.observacao_item ?? selectedBem.observacao)}</Text>
                </View>
              </ScrollView>
            ) : null}
          </View>
        </View>
      </Modal>

      <Modal transparent animationType="fade" visible={naoLocalizadoBem !== null} onRequestClose={() => setNaoLocalizadoBem(null)}>
        <View style={styles.modalOverlay}>
          <View style={styles.formModal}>
            <Text style={styles.modalTitle}>Bem não localizado</Text>
            <Text style={styles.formModalText}>{naoLocalizadoBem ? getBemCodigo(naoLocalizadoBem) : '-'}</Text>
            <TextInput
              placeholder="Justificativa"
              placeholderTextColor="#829AB1"
              multiline
              value={justificativa}
              onChangeText={setJustificativa}
              style={styles.textArea}
            />
            <View style={styles.modalActions}>
              <Pressable onPress={() => setNaoLocalizadoBem(null)} style={styles.secondaryButton}>
                <Text style={styles.secondaryButtonText}>Cancelar</Text>
              </Pressable>
              <Pressable onPress={handleRegistrarNaoLocalizado} style={styles.dangerButton}>
                {actionLoading === 'nao-localizado' ? <ActivityIndicator color="#FFFFFF" /> : <MaterialIcons name="block" size={19} color="#FFFFFF" />}
                <Text style={styles.primaryButtonText}>Registrar</Text>
              </Pressable>
            </View>
          </View>
        </View>
      </Modal>

      <Modal transparent animationType="fade" visible={divergenciaBem !== null} onRequestClose={() => setDivergenciaBem(null)}>
        <View style={styles.modalOverlay}>
          <View style={styles.formModal}>
            <Text style={styles.modalTitle}>Divergência</Text>
            <Text style={styles.formModalText}>{divergenciaBem ? getBemCodigo(divergenciaBem) : '-'}</Text>
            <TextInput
              placeholder="Campos divergentes"
              placeholderTextColor="#829AB1"
              value={camposDivergentes}
              onChangeText={setCamposDivergentes}
              style={styles.input}
            />
            <TextInput
              placeholder="Observação"
              placeholderTextColor="#829AB1"
              multiline
              value={observacaoDivergencia}
              onChangeText={setObservacaoDivergencia}
              style={styles.textArea}
            />
            <View style={styles.modalActions}>
              <Pressable onPress={() => setDivergenciaBem(null)} style={styles.secondaryButton}>
                <Text style={styles.secondaryButtonText}>Cancelar</Text>
              </Pressable>
              <Pressable onPress={handleRegistrarDivergencia} style={styles.primaryButton}>
                {actionLoading === 'divergencia' ? <ActivityIndicator color="#FFFFFF" /> : <MaterialIcons name="save" size={19} color="#FFFFFF" />}
                <Text style={styles.primaryButtonText}>Salvar</Text>
              </Pressable>
            </View>
          </View>
        </View>
      </Modal>

      <ScrollView
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={isRefreshing} onRefresh={handleRefresh} />}>
        <View style={styles.header}>
          <View style={styles.headerTextGroup}>
            <Text style={styles.eyebrow}>Conferência de bens</Text>
            <Text style={styles.title}>Inventário</Text>
          </View>
        </View>

        {notification ? (
          <View style={[
            styles.notification,
            notification.tone === 'success' ? styles.notificationSuccess : notification.tone === 'error' ? styles.notificationError : styles.notificationInfo,
          ]}>
            <MaterialIcons
              name={notification.tone === 'success' ? 'check-circle' : notification.tone === 'error' ? 'error-outline' : 'info-outline'}
              size={20}
              color="#FFFFFF"
            />
            <Text style={styles.notificationText}>{notification.message}</Text>
          </View>
        ) : null}

        <View style={styles.contextPanel}>
          <View style={styles.contextIcon}>
            <MaterialIcons name="apartment" size={23} color="#1E4E79" />
          </View>
          <View style={styles.contextText}>
            <Text style={styles.contextName}>{user?.name ?? user?.login ?? 'Usuário mobile'}</Text>
            <Text style={styles.contextMeta}>Unidade {user?.unidade_judiciaria ?? '-'} | Setor {user?.setor ?? '-'}</Text>
            <Text style={styles.contextMeta}>Atividade: {info?.atividade.situacao ?? '-'}</Text>
          </View>
          <View style={[styles.lockBadge, canEdit ? styles.editableBadge : styles.blockedBadge]}>
            <Text style={[styles.lockBadgeText, canEdit ? styles.editableBadgeText : styles.blockedBadgeText]}>
              {canEdit ? 'Aberta' : 'Bloqueada'}
            </Text>
          </View>
        </View>

        <View style={styles.metricsPanel}>
          {renderMetric('Total', info?.resumo.total ?? 0)}
          {renderMetric('Localizados', info?.resumo.localizados ?? 0, '#2F855A')}
          {renderMetric('Pendentes', info?.resumo.pendentes ?? 0, '#B7791F')}
          {renderMetric('Não localizados', info?.resumo.nao_localizados ?? 0, '#C53030')}
          {renderMetric('Divergentes', info?.resumo.divergentes ?? 0, '#1E4E79')}
          {renderMetric('Transferência', info?.resumo.em_transferencia ?? 0, '#1E4E79')}
          {renderMetric('Manuais', info?.resumo.cadastrados_manualmente ?? 0, '#B7791F')}
        </View>

        <View style={styles.scanPanel}>
          <View style={styles.panelHeader}>
            <Text style={styles.sectionTitle}>Leitura patrimonial</Text>
            <Pressable onPress={handleStartScanner} style={styles.iconActionButton}>
              <MaterialIcons name={isScannerActive ? 'close' : 'qr-code-scanner'} size={22} color="#1E4E79" />
            </Pressable>
          </View>

          {isScannerActive && cameraPermission?.granted ? (
            <View style={styles.cameraBox}>
              <CameraView
                style={styles.cameraPreview}
                facing="back"
                barcodeScannerSettings={{ barcodeTypes: BARCODE_TYPES }}
                onBarcodeScanned={hasScannedBarcode ? undefined : handleBarcodeScanned}
              />
              <View style={styles.scanLine} />
            </View>
          ) : null}

          <View style={styles.manualEntry}>
            <MaterialIcons name="pin" size={20} color="#627D98" />
            <TextInput
              placeholder="Patrimônio"
              placeholderTextColor="#829AB1"
              keyboardType="number-pad"
              inputMode="numeric"
              autoCorrect={false}
              value={manualCode}
              onChangeText={(value) => setManualCode(onlyDigits(value))}
              onSubmitEditing={() => validateCode(manualCode)}
              style={styles.manualEntryInput}
            />
          </View>

          <Pressable
            disabled={isValidating}
            onPress={() => validateCode(manualCode)}
            style={({ pressed }) => [styles.primaryButtonFull, pressed && styles.pressed]}>
            {isValidating ? <ActivityIndicator color="#FFFFFF" /> : <MaterialIcons name="search" size={20} color="#FFFFFF" />}
            <Text style={styles.primaryButtonText}>{isValidating ? 'Validando' : 'Validar leitura'}</Text>
          </Pressable>

          {resultadoLeitura ? (
            <View style={styles.readResultPanel}>
              <Text style={styles.readResultStatus}>{resultadoLeitura.message}</Text>
              {lastReadBem ? (
                <>
                  <Text style={styles.readResultCode}>{getBemCodigo(lastReadBem)}</Text>
                  <Text style={styles.readResultDescription}>{getBemDescricao(lastReadBem)}</Text>
                </>
              ) : null}
              {canEdit && resultadoLeitura.pode_localizar ? (
                <Pressable onPress={() => handleLocalizar()} style={styles.confirmButton}>
                  {actionLoading === 'localizar' ? <ActivityIndicator color="#FFFFFF" /> : <MaterialIcons name="check-circle" size={20} color="#FFFFFF" />}
                  <Text style={styles.primaryButtonText}>Confirmar localização</Text>
                </Pressable>
              ) : null}
            </View>
          ) : null}
        </View>

        <View style={styles.filtersPanel}>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.filterList}>
            {FILTERS.map((filter) => {
              const isActive = filterStatus === filter.key;

              return (
                <Pressable
                  key={filter.key}
                  onPress={() => setFilterStatus(filter.key)}
                  style={[styles.filterButton, isActive && styles.filterButtonActive]}>
                  <Text style={[styles.filterButtonText, isActive && styles.filterButtonTextActive]}>{filter.label}</Text>
                </Pressable>
              );
            })}
          </ScrollView>
        </View>

        <View style={styles.assetsPanel}>
          <View style={styles.panelHeader}>
            <Text style={styles.sectionTitle}>Bens do setor</Text>
            <Text style={styles.panelMeta}>{filteredBens.length}</Text>
          </View>

          {filteredBens.length > 0 ? (
            filteredBens.map(renderBemRow)
          ) : (
            <View style={styles.emptyPanel}>
              <MaterialIcons name="inventory" size={28} color="#627D98" />
              <Text style={styles.emptyTitle}>Nenhum bem neste filtro</Text>
            </View>
          )}
        </View>

        <Pressable
          disabled={!info?.atividade.pode_finalizar || actionLoading === 'finalizar'}
          onPress={handleFinalizar}
          style={({ pressed }) => [
            styles.finalizeButton,
            (!info?.atividade.pode_finalizar || actionLoading === 'finalizar') && styles.finalizeButtonDisabled,
            pressed && styles.pressed,
          ]}>
          {actionLoading === 'finalizar' ? <ActivityIndicator color="#FFFFFF" /> : <MaterialIcons name="task-alt" size={21} color="#FFFFFF" />}
          <Text style={styles.primaryButtonText}>Finalizar conferência</Text>
        </Pressable>
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
  content: {
    gap: 14,
    padding: 18,
    paddingBottom: 30,
  },
  loadingContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 12,
  },
  loadingText: {
    color: '#334E68',
    fontSize: 14,
    fontWeight: '800',
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
    fontSize: 23,
    fontWeight: '800',
  },
  notification: {
    minHeight: 46,
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
  contextPanel: {
    minHeight: 84,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
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
    gap: 2,
  },
  contextName: {
    color: '#102A43',
    fontSize: 16,
    fontWeight: '800',
  },
  contextMeta: {
    color: '#52616B',
    fontSize: 12,
    fontWeight: '700',
  },
  lockBadge: {
    borderRadius: 8,
    paddingHorizontal: 8,
    paddingVertical: 6,
  },
  editableBadge: {
    backgroundColor: '#E3F4E9',
  },
  blockedBadge: {
    backgroundColor: '#EEF2F6',
  },
  lockBadgeText: {
    fontSize: 11,
    fontWeight: '800',
  },
  editableBadgeText: {
    color: '#2F855A',
  },
  blockedBadgeText: {
    color: '#627D98',
  },
  metricsPanel: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  metricItem: {
    minWidth: '31%',
    flexGrow: 1,
    gap: 2,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 12,
  },
  metricValue: {
    fontSize: 22,
    fontWeight: '800',
  },
  metricLabel: {
    color: '#52616B',
    fontSize: 11,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  scanPanel: {
    gap: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 14,
  },
  panelHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
  },
  sectionTitle: {
    color: '#102A43',
    fontSize: 17,
    fontWeight: '800',
  },
  panelMeta: {
    color: '#627D98',
    fontSize: 13,
    fontWeight: '800',
  },
  iconActionButton: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#B6D4EA',
    backgroundColor: '#EAF4FB',
  },
  cameraBox: {
    height: 210,
    overflow: 'hidden',
    borderRadius: 8,
    backgroundColor: '#102A43',
  },
  cameraPreview: {
    ...StyleSheet.absoluteFillObject,
  },
  scanLine: {
    position: 'absolute',
    top: '50%',
    left: '12%',
    width: '76%',
    height: 2,
    backgroundColor: '#2F855A',
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
  primaryButtonFull: {
    minHeight: 49,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#1E4E79',
  },
  readResultPanel: {
    gap: 7,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    padding: 12,
  },
  readResultStatus: {
    color: '#1E4E79',
    fontSize: 14,
    fontWeight: '800',
  },
  readResultCode: {
    color: '#102A43',
    fontSize: 20,
    fontWeight: '800',
  },
  readResultDescription: {
    color: '#52616B',
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '700',
  },
  confirmButton: {
    minHeight: 44,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#2F855A',
  },
  filtersPanel: {
    minHeight: 44,
  },
  filterList: {
    gap: 8,
    paddingRight: 4,
  },
  filterButton: {
    justifyContent: 'center',
    minHeight: 38,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 12,
  },
  filterButtonActive: {
    borderColor: '#1E4E79',
    backgroundColor: '#EAF4FB',
  },
  filterButtonText: {
    color: '#52616B',
    fontSize: 13,
    fontWeight: '800',
  },
  filterButtonTextActive: {
    color: '#1E4E79',
  },
  assetsPanel: {
    gap: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 14,
  },
  assetRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    padding: 10,
  },
  assetIcon: {
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#EAF4FB',
  },
  assetInfo: {
    flex: 1,
    gap: 5,
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
    fontSize: 15,
    fontWeight: '800',
  },
  assetDescription: {
    color: '#334E68',
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '700',
  },
  assetMeta: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '700',
  },
  statusBadge: {
    maxWidth: 128,
    borderRadius: 8,
    paddingHorizontal: 8,
    paddingVertical: 5,
  },
  statusBadgeText: {
    fontSize: 11,
    fontWeight: '800',
  },
  inlineActions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 7,
    paddingTop: 3,
  },
  inlineActionPrimary: {
    minHeight: 34,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    borderRadius: 8,
    backgroundColor: '#2F855A',
    paddingHorizontal: 9,
  },
  inlineActionPrimaryText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '800',
  },
  inlineActionSecondary: {
    minHeight: 34,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 9,
  },
  inlineActionSecondaryText: {
    color: '#1E4E79',
    fontSize: 12,
    fontWeight: '800',
  },
  inlineActionDangerText: {
    color: '#C53030',
    fontSize: 12,
    fontWeight: '800',
  },
  emptyPanel: {
    alignItems: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    padding: 18,
  },
  emptyTitle: {
    color: '#52616B',
    fontSize: 14,
    fontWeight: '800',
  },
  finalizeButton: {
    minHeight: 50,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#2F855A',
  },
  finalizeButtonDisabled: {
    backgroundColor: '#9FB3C8',
  },
  primaryButton: {
    minHeight: 45,
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#1E4E79',
  },
  dangerButton: {
    minHeight: 45,
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#C53030',
  },
  secondaryButton: {
    minHeight: 45,
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#B6D4EA',
    backgroundColor: '#FFFFFF',
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '800',
  },
  secondaryButtonText: {
    color: '#1E4E79',
    fontSize: 14,
    fontWeight: '800',
  },
  pressed: {
    opacity: 0.72,
  },
  modalOverlay: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: '#102A4399',
    padding: 18,
  },
  modalPanel: {
    maxHeight: '86%',
    overflow: 'hidden',
    borderRadius: 8,
    backgroundColor: '#FFFFFF',
  },
  formModal: {
    gap: 12,
    borderRadius: 8,
    backgroundColor: '#FFFFFF',
    padding: 16,
  },
  modalHeader: {
    minHeight: 68,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#D9E2EC',
    padding: 14,
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
    fontSize: 19,
    fontWeight: '800',
  },
  modalContent: {
    gap: 12,
    padding: 14,
  },
  modalDescription: {
    color: '#334E68',
    fontSize: 14,
    lineHeight: 20,
    fontWeight: '700',
  },
  detailGrid: {
    gap: 6,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    padding: 12,
  },
  detailLabel: {
    color: '#627D98',
    fontSize: 11,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  detailValue: {
    color: '#102A43',
    fontSize: 14,
    lineHeight: 19,
    fontWeight: '700',
    marginBottom: 4,
  },
  formModalText: {
    color: '#52616B',
    fontSize: 13,
    fontWeight: '800',
  },
  input: {
    minHeight: 46,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    color: '#102A43',
    fontSize: 14,
    fontWeight: '700',
    paddingHorizontal: 12,
  },
  textArea: {
    minHeight: 100,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    color: '#102A43',
    fontSize: 14,
    lineHeight: 19,
    fontWeight: '700',
    padding: 12,
    textAlignVertical: 'top',
  },
  modalActions: {
    flexDirection: 'row',
    gap: 9,
  },
});
