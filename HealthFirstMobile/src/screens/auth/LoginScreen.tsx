import React, {useState, useEffect} from 'react';
import {
  View,
  StyleSheet,
  Image,
  KeyboardAvoidingView,
  Platform,
  Alert,
} from 'react-native';
import {
  Text,
  TextInput,
  Button,
  Card,
  Checkbox,
  ActivityIndicator,
} from 'react-native-paper';
import {useDispatch, useSelector} from 'react-redux';
import Icon from 'react-native-vector-icons/MaterialIcons';
import {theme} from '../../theme';
import {RootState, AppDispatch} from '../../store';
import {loginUser, loginWithBiometrics} from '../../store/slices/authSlice';
import {checkBiometricAvailability} from '../../services/biometricService';
import {useTranslation} from 'react-i18next';

const LoginScreen: React.FC = () => {
  const {t} = useTranslation();
  const dispatch = useDispatch<AppDispatch>();
  const {isLoading, error} = useSelector((state: RootState) => state.auth);

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [biometricAvailable, setBiometricAvailable] = useState(false);

  useEffect(() => {
    checkBiometricSupport();
  }, []);

  const checkBiometricSupport = async () => {
    const available = await checkBiometricAvailability();
    setBiometricAvailable(available);
  };

  const handleLogin = async () => {
    if (!email.trim() || !password.trim()) {
      Alert.alert(t('error'), t('pleaseEnterEmailAndPassword'));
      return;
    }

    try {
      await dispatch(
        loginUser({
          email: email.trim(),
          password,
          rememberMe,
        }),
      ).unwrap();
    } catch (err: any) {
      Alert.alert(t('loginFailed'), err.message || t('invalidCredentials'));
    }
  };

  const handleBiometricLogin = async () => {
    try {
      await dispatch(loginWithBiometrics()).unwrap();
    } catch (err: any) {
      Alert.alert(t('biometricLoginFailed'), err.message);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <View style={styles.content}>
        {/* Logo and Title */}
        <View style={styles.header}>
          <Icon
            name="local-hospital"
            size={80}
            color={theme.colors.primary}
            style={styles.logo}
          />
          <Text style={styles.title}>HealthFirst</Text>
          <Text style={styles.subtitle}>{t('hospitalManagementSystem')}</Text>
        </View>

        {/* Login Form */}
        <Card style={styles.card}>
          <Card.Content style={styles.cardContent}>
            <Text style={styles.loginTitle}>{t('signIn')}</Text>

            <TextInput
              label={t('emailOrPhone')}
              value={email}
              onChangeText={setEmail}
              mode="outlined"
              style={styles.input}
              keyboardType="email-address"
              autoCapitalize="none"
              autoComplete="email"
              left={<TextInput.Icon icon="email" />}
              error={!!error}
            />

            <TextInput
              label={t('password')}
              value={password}
              onChangeText={setPassword}
              mode="outlined"
              style={styles.input}
              secureTextEntry={!showPassword}
              autoComplete="password"
              left={<TextInput.Icon icon="lock" />}
              right={
                <TextInput.Icon
                  icon={showPassword ? 'eye-off' : 'eye'}
                  onPress={() => setShowPassword(!showPassword)}
                />
              }
              error={!!error}
            />

            {error && (
              <Text style={styles.errorText}>{error}</Text>
            )}

            <View style={styles.checkboxContainer}>
              <Checkbox
                status={rememberMe ? 'checked' : 'unchecked'}
                onPress={() => setRememberMe(!rememberMe)}
                color={theme.colors.primary}
              />
              <Text style={styles.checkboxLabel}>{t('rememberMe')}</Text>
            </View>

            <Button
              mode="contained"
              onPress={handleLogin}
              style={styles.loginButton}
              contentStyle={styles.buttonContent}
              loading={isLoading}
              disabled={isLoading}>
              {t('signIn')}
            </Button>

            {biometricAvailable && (
              <>
                <View style={styles.divider}>
                  <View style={styles.dividerLine} />
                  <Text style={styles.dividerText}>{t('or')}</Text>
                  <View style={styles.dividerLine} />
                </View>

                <Button
                  mode="outlined"
                  onPress={handleBiometricLogin}
                  style={styles.biometricButton}
                  contentStyle={styles.buttonContent}
                  icon="fingerprint"
                  disabled={isLoading}>
                  {t('useBiometric')}
                </Button>
              </>
            )}

            <Button
              mode="text"
              onPress={() => {/* Navigate to forgot password */}}
              style={styles.forgotButton}
              textColor={theme.colors.primary}>
              {t('forgotPassword')}
            </Button>
          </Card.Content>
        </Card>

        {/* Language Selector */}
        <View style={styles.languageSelector}>
          <Button
            mode="text"
            onPress={() => {/* Change language */}}
            icon="language"
            textColor={theme.colors.placeholder}>
            English | አማርኛ
          </Button>
        </View>
      </View>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.colors.background,
  },
  content: {
    flex: 1,
    justifyContent: 'center',
    padding: theme.spacing.lg,
  },
  header: {
    alignItems: 'center',
    marginBottom: theme.spacing.xl,
  },
  logo: {
    marginBottom: theme.spacing.md,
  },
  title: {
    fontSize: 32,
    fontWeight: 'bold',
    color: theme.colors.primary,
    marginBottom: theme.spacing.xs,
  },
  subtitle: {
    fontSize: 16,
    color: theme.colors.placeholder,
    textAlign: 'center',
  },
  card: {
    ...theme.shadows.medium,
  },
  cardContent: {
    padding: theme.spacing.lg,
  },
  loginTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: theme.colors.text,
    textAlign: 'center',
    marginBottom: theme.spacing.lg,
  },
  input: {
    marginBottom: theme.spacing.md,
  },
  errorText: {
    color: theme.colors.warning,
    fontSize: 14,
    marginBottom: theme.spacing.md,
    textAlign: 'center',
  },
  checkboxContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: theme.spacing.lg,
  },
  checkboxLabel: {
    marginLeft: theme.spacing.sm,
    color: theme.colors.text,
  },
  loginButton: {
    marginBottom: theme.spacing.md,
  },
  buttonContent: {
    height: 48,
  },
  divider: {
    flexDirection: 'row',
    alignItems: 'center',
    marginVertical: theme.spacing.md,
  },
  dividerLine: {
    flex: 1,
    height: 1,
    backgroundColor: theme.colors.borderColor,
  },
  dividerText: {
    marginHorizontal: theme.spacing.md,
    color: theme.colors.placeholder,
  },
  biometricButton: {
    marginBottom: theme.spacing.md,
  },
  forgotButton: {
    alignSelf: 'center',
  },
  languageSelector: {
    alignItems: 'center',
    marginTop: theme.spacing.lg,
  },
});

export default LoginScreen;