import {createSlice, createAsyncThunk, PayloadAction} from '@reduxjs/toolkit';
import {authService} from '../../services/authService';
import {biometricService} from '../../services/biometricService';
import {storageService} from '../../services/storageService';

export interface User {
  id: number;
  email: string;
  name: string;
  role: string;
  zone?: string;
  woreda?: string;
  kebele?: string;
  avatar?: string;
  lastLogin?: string;
}

interface AuthState {
  user: User | null;
  token: string | null;
  refreshToken: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
  biometricEnabled: boolean;
  rememberMe: boolean;
}

const initialState: AuthState = {
  user: null,
  token: null,
  refreshToken: null,
  isAuthenticated: false,
  isLoading: false,
  error: null,
  biometricEnabled: false,
  rememberMe: false,
};

// Async thunks
export const loginUser = createAsyncThunk(
  'auth/loginUser',
  async (
    credentials: {email: string; password: string; rememberMe: boolean},
    {rejectWithValue},
  ) => {
    try {
      const response = await authService.login(credentials);
      
      // Store tokens securely
      await storageService.setSecureItem('accessToken', response.token);
      await storageService.setSecureItem('refreshToken', response.refreshToken);
      
      // Store remember me preference
      if (credentials.rememberMe) {
        await storageService.setItem('rememberMe', 'true');
        await storageService.setSecureItem('userEmail', credentials.email);
      }
      
      return response;
    } catch (error: any) {
      return rejectWithValue(error.message || 'Login failed');
    }
  },
);

export const loginWithBiometrics = createAsyncThunk(
  'auth/loginWithBiometrics',
  async (_, {rejectWithValue}) => {
    try {
      // Check if biometric is available and enrolled
      const isAvailable = await biometricService.isBiometricAvailable();
      if (!isAvailable) {
        throw new Error('Biometric authentication not available');
      }

      // Authenticate with biometrics
      const biometricResult = await biometricService.authenticate();
      if (!biometricResult.success) {
        throw new Error(biometricResult.error || 'Biometric authentication failed');
      }

      // Get stored credentials
      const storedEmail = await storageService.getSecureItem('userEmail');
      const storedToken = await storageService.getSecureItem('accessToken');
      
      if (!storedEmail || !storedToken) {
        throw new Error('No stored credentials found');
      }

      // Validate token with server
      const response = await authService.validateToken(storedToken);
      
      return response;
    } catch (error: any) {
      return rejectWithValue(error.message || 'Biometric login failed');
    }
  },
);

export const refreshToken = createAsyncThunk(
  'auth/refreshToken',
  async (_, {getState, rejectWithValue}) => {
    try {
      const state = getState() as {auth: AuthState};
      const currentRefreshToken = state.auth.refreshToken;
      
      if (!currentRefreshToken) {
        throw new Error('No refresh token available');
      }

      const response = await authService.refreshToken(currentRefreshToken);
      
      // Update stored tokens
      await storageService.setSecureItem('accessToken', response.token);
      await storageService.setSecureItem('refreshToken', response.refreshToken);
      
      return response;
    } catch (error: any) {
      return rejectWithValue(error.message || 'Token refresh failed');
    }
  },
);

export const logout = createAsyncThunk(
  'auth/logout',
  async (_, {getState}) => {
    try {
      const state = getState() as {auth: AuthState};
      const token = state.auth.token;
      
      if (token) {
        await authService.logout(token);
      }
    } catch (error) {
      // Continue with logout even if server call fails
      console.warn('Logout server call failed:', error);
    } finally {
      // Clear stored data
      await storageService.removeSecureItem('accessToken');
      await storageService.removeSecureItem('refreshToken');
      await storageService.removeSecureItem('userEmail');
      await storageService.removeItem('rememberMe');
    }
  },
);

export const enableBiometric = createAsyncThunk(
  'auth/enableBiometric',
  async (_, {rejectWithValue}) => {
    try {
      const isAvailable = await biometricService.isBiometricAvailable();
      if (!isAvailable) {
        throw new Error('Biometric authentication not available on this device');
      }

      const result = await biometricService.authenticate();
      if (!result.success) {
        throw new Error(result.error || 'Biometric setup failed');
      }

      await storageService.setItem('biometricEnabled', 'true');
      return true;
    } catch (error: any) {
      return rejectWithValue(error.message || 'Failed to enable biometric');
    }
  },
);

export const disableBiometric = createAsyncThunk(
  'auth/disableBiometric',
  async () => {
    await storageService.removeItem('biometricEnabled');
    return false;
  },
);

// Auth slice
const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    clearError: state => {
      state.error = null;
    },
    setUser: (state, action: PayloadAction<User>) => {
      state.user = action.payload;
    },
    updateUserProfile: (state, action: PayloadAction<Partial<User>>) => {
      if (state.user) {
        state.user = {...state.user, ...action.payload};
      }
    },
  },
  extraReducers: builder => {
    builder
      // Login user
      .addCase(loginUser.pending, state => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(loginUser.fulfilled, (state, action) => {
        state.isLoading = false;
        state.isAuthenticated = true;
        state.user = action.payload.user;
        state.token = action.payload.token;
        state.refreshToken = action.payload.refreshToken;
        state.rememberMe = action.meta.arg.rememberMe;
        state.error = null;
      })
      .addCase(loginUser.rejected, (state, action) => {
        state.isLoading = false;
        state.isAuthenticated = false;
        state.error = action.payload as string;
      })
      
      // Biometric login
      .addCase(loginWithBiometrics.pending, state => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(loginWithBiometrics.fulfilled, (state, action) => {
        state.isLoading = false;
        state.isAuthenticated = true;
        state.user = action.payload.user;
        state.token = action.payload.token;
        state.refreshToken = action.payload.refreshToken;
        state.error = null;
      })
      .addCase(loginWithBiometrics.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Refresh token
      .addCase(refreshToken.fulfilled, (state, action) => {
        state.token = action.payload.token;
        state.refreshToken = action.payload.refreshToken;
        state.user = action.payload.user;
      })
      .addCase(refreshToken.rejected, state => {
        state.isAuthenticated = false;
        state.user = null;
        state.token = null;
        state.refreshToken = null;
      })
      
      // Logout
      .addCase(logout.fulfilled, state => {
        state.user = null;
        state.token = null;
        state.refreshToken = null;
        state.isAuthenticated = false;
        state.error = null;
        state.rememberMe = false;
      })
      
      // Enable biometric
      .addCase(enableBiometric.fulfilled, (state, action) => {
        state.biometricEnabled = action.payload;
      })
      .addCase(enableBiometric.rejected, (state, action) => {
        state.error = action.payload as string;
      })
      
      // Disable biometric
      .addCase(disableBiometric.fulfilled, (state, action) => {
        state.biometricEnabled = action.payload;
      });
  },
});

export const {clearError, setUser, updateUserProfile} = authSlice.actions;
export default authSlice.reducer;