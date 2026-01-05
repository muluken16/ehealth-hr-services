import {configureStore} from '@reduxjs/toolkit';
import {persistStore, persistReducer} from 'redux-persist';
import AsyncStorage from '@react-native-async-storage/async-storage';
import {combineReducers} from '@reduxjs/toolkit';

// Import slices
import authSlice from './slices/authSlice';
import hrSlice from './slices/hrSlice';
import healthSlice from './slices/healthSlice';
import employeeSlice from './slices/employeeSlice';
import leaveSlice from './slices/leaveSlice';
import patientSlice from './slices/patientSlice';
import appointmentSlice from './slices/appointmentSlice';
import inventorySlice from './slices/inventorySlice';
import notificationSlice from './slices/notificationSlice';
import offlineSlice from './slices/offlineSlice';

// Persist config
const persistConfig = {
  key: 'root',
  storage: AsyncStorage,
  whitelist: ['auth', 'offline'], // Only persist auth and offline data
  blacklist: ['hr', 'health'], // Don't persist real-time data
};

// Root reducer
const rootReducer = combineReducers({
  auth: authSlice,
  hr: hrSlice,
  health: healthSlice,
  employee: employeeSlice,
  leave: leaveSlice,
  patient: patientSlice,
  appointment: appointmentSlice,
  inventory: inventorySlice,
  notification: notificationSlice,
  offline: offlineSlice,
});

// Persisted reducer
const persistedReducer = persistReducer(persistConfig, rootReducer);

// Configure store
export const store = configureStore({
  reducer: persistedReducer,
  middleware: getDefaultMiddleware =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: ['persist/PERSIST', 'persist/REHYDRATE'],
      },
    }),
  devTools: __DEV__,
});

// Persistor
export const persistor = persistStore(store);

// Types
export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;