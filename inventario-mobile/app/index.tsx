import MaterialIcons from '@expo/vector-icons/MaterialIcons';
import { Link, type Href } from 'expo-router';
import {
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

export default function LoginScreen() {
  return (
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={styles.keyboardContainer}>
        <ScrollView
          contentContainerStyle={styles.scrollContent}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}>
          <View style={styles.brandHeader}>
            <View style={styles.logoMark}>
              <MaterialIcons name="inventory-2" size={30} color="#FFFFFF" />
            </View>
            <View style={styles.brandTextGroup}>
              <Text style={styles.eyebrow}>Conferência patrimonial</Text>
              <Text style={styles.title}>E-Gap Mobile</Text>
            </View>
          </View>

          <View style={styles.formCard}>
            <View style={styles.formHeader}>
              <Text style={styles.formTitle}>Acesso ao sistema</Text>
              <Text style={styles.formDescription}>
                Informe suas credenciais para acessar a rotina mobile de inventário.
              </Text>
            </View>

            <View style={styles.field}>
              <Text style={styles.label}>Usuário</Text>
              <View style={styles.inputWrapper}>
                <MaterialIcons name="person-outline" size={22} color="#627D98" />
                <TextInput
                  placeholder="Digite seu usuário"
                  placeholderTextColor="#829AB1"
                  autoCapitalize="none"
                  autoCorrect={false}
                  style={styles.input}
                />
              </View>
            </View>

            <View style={styles.field}>
              <Text style={styles.label}>Senha</Text>
              <View style={styles.inputWrapper}>
                <MaterialIcons name="lock-outline" size={22} color="#627D98" />
                <TextInput
                  placeholder="Digite sua senha"
                  placeholderTextColor="#829AB1"
                  secureTextEntry
                  style={styles.input}
                />
              </View>
            </View>

            <Link href={'/principal' as Href} asChild>
              <Pressable style={styles.primaryButton}>
                <MaterialIcons name="login" size={21} color="#FFFFFF" />
                <Text style={styles.primaryButtonText}>Entrar</Text>
              </Pressable>
            </Link>
          </View>

          <View style={styles.footerPanel}>
            <MaterialIcons name="verified-user" size={20} color="#1E4E79" />
            <Text style={styles.footerText}>
              Ambiente preparado para acesso institucional ao controle de bens do setor.
            </Text>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F4F7FA',
  },
  keyboardContainer: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
    gap: 24,
    padding: 24,
  },
  brandHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
  },
  logoMark: {
    width: 58,
    height: 58,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: '#1E4E79',
  },
  brandTextGroup: {
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
    fontSize: 33,
    fontWeight: '800',
  },
  formCard: {
    gap: 18,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E2EC',
    backgroundColor: '#FFFFFF',
    padding: 18,
  },
  formHeader: {
    gap: 6,
  },
  formTitle: {
    color: '#102A43',
    fontSize: 21,
    fontWeight: '800',
  },
  formDescription: {
    color: '#52616B',
    fontSize: 14,
    lineHeight: 20,
    fontWeight: '600',
  },
  field: {
    gap: 8,
  },
  label: {
    color: '#334E68',
    fontSize: 14,
    fontWeight: '800',
  },
  inputWrapper: {
    minHeight: 52,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#BCCCDC',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 12,
  },
  input: {
    flex: 1,
    color: '#102A43',
    fontSize: 16,
    fontWeight: '700',
  },
  primaryButton: {
    minHeight: 52,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    borderRadius: 8,
    backgroundColor: '#1E4E79',
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '800',
  },
  footerPanel: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D9E8F5',
    backgroundColor: '#EAF4FB',
    padding: 14,
  },
  footerText: {
    flex: 1,
    color: '#334E68',
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '700',
  },
});
