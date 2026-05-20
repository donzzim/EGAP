import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import { router, type Href, useLocalSearchParams } from 'expo-router';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

function getParamValue(value: string | string[] | undefined): string | undefined {
  return Array.isArray(value) ? value[0] : value;
}

export default function AppErrorScreen() {
  const params = useLocalSearchParams<{
    kind?: string;
    title?: string;
    message?: string;
    status?: string;
  }>();

  const kind = getParamValue(params.kind);
  const title = getParamValue(params.title) ?? 'Algo deu errado';
  const message = getParamValue(params.message)
    ?? 'Não foi possível concluir a operação. Verifique sua conexão e tente novamente.';
  const status = getParamValue(params.status);
  const isNetworkError = kind === 'network';

  function handleBack() {
    if (router.canGoBack()) {
      router.back();
      return;
    }

    router.replace('/' as Href);
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.content}>
        <View style={styles.iconShell}>
          <MaterialIcons
            name={isNetworkError ? 'wifi-off' : 'cloud-off'}
            size={38}
            color="#C53030"
          />
        </View>

        <View style={styles.textGroup}>
          <Text style={styles.eyebrow}>{isNetworkError ? 'Conexão indisponível' : 'Falha na aplicação'}</Text>
          <Text style={styles.title}>{title}</Text>
          <Text style={styles.message}>{message}</Text>
          {status ? <Text style={styles.status}>Código HTTP {status}</Text> : null}
        </View>

        <View style={styles.actions}>
          <Pressable onPress={handleBack} style={({ pressed }) => [styles.secondaryButton, pressed && styles.pressed]}>
            <MaterialIcons name="arrow-back" size={20} color="#1E4E79" />
            <Text style={styles.secondaryButtonText}>Voltar</Text>
          </Pressable>
          <Pressable
            onPress={() => router.replace('/' as Href)}
            style={({ pressed }) => [styles.primaryButton, pressed && styles.pressed]}>
            <MaterialIcons name="home" size={20} color="#FFFFFF" />
            <Text style={styles.primaryButtonText}>Início</Text>
          </Pressable>
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F4F7FA',
  },
  content: {
    flex: 1,
    justifyContent: 'center',
    gap: 22,
    padding: 22,
  },
  iconShell: {
    width: 78,
    height: 78,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#F1C4C4',
    backgroundColor: '#FFF5F5',
  },
  textGroup: {
    gap: 8,
  },
  eyebrow: {
    color: '#627D98',
    fontSize: 12,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  title: {
    color: '#102A43',
    fontSize: 28,
    fontWeight: '800',
  },
  message: {
    color: '#52616B',
    fontSize: 15,
    lineHeight: 22,
    fontWeight: '700',
  },
  status: {
    color: '#C53030',
    fontSize: 13,
    fontWeight: '800',
  },
  actions: {
    flexDirection: 'row',
    gap: 10,
  },
  secondaryButton: {
    minHeight: 48,
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 7,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
  },
  secondaryButtonText: {
    color: '#1E4E79',
    fontSize: 14,
    fontWeight: '800',
  },
  primaryButton: {
    minHeight: 48,
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 7,
    borderRadius: 8,
    backgroundColor: '#1E4E79',
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '800',
  },
  pressed: {
    opacity: 0.72,
  },
});
