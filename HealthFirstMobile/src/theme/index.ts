import {DefaultTheme} from 'react-native-paper';

export const theme = {
  ...DefaultTheme,
  colors: {
    ...DefaultTheme.colors,
    primary: '#2E86AB',      // Healthcare Blue
    accent: '#A23B72',       // Accent Pink
    success: '#F18F01',      // Ethiopian Gold
    warning: '#C73E1D',      // Alert Red
    background: '#F5F5F5',   // Light Gray
    surface: '#FFFFFF',      // White
    text: '#2C3E50',         // Dark Blue Gray
    placeholder: '#7F8C8D',  // Medium Gray
    disabled: '#BDC3C7',     // Light Gray
    backdrop: 'rgba(0, 0, 0, 0.5)',
    
    // Custom colors for the app
    cardBackground: '#FFFFFF',
    borderColor: '#E1E8ED',
    shadowColor: '#000000',
    
    // Status colors
    active: '#27AE60',
    inactive: '#E74C3C',
    pending: '#F39C12',
    approved: '#27AE60',
    rejected: '#E74C3C',
    
    // Department colors
    medical: '#3498DB',
    admin: '#9B59B6',
    technical: '#E67E22',
    support: '#1ABC9C',
  },
  fonts: {
    ...DefaultTheme.fonts,
    regular: {
      fontFamily: 'Roboto-Regular',
      fontWeight: 'normal' as const,
    },
    medium: {
      fontFamily: 'Roboto-Medium',
      fontWeight: 'normal' as const,
    },
    light: {
      fontFamily: 'Roboto-Light',
      fontWeight: 'normal' as const,
    },
    thin: {
      fontFamily: 'Roboto-Thin',
      fontWeight: 'normal' as const,
    },
  },
  roundness: 8,
  spacing: {
    xs: 4,
    sm: 8,
    md: 16,
    lg: 24,
    xl: 32,
  },
  shadows: {
    small: {
      shadowColor: '#000',
      shadowOffset: {
        width: 0,
        height: 2,
      },
      shadowOpacity: 0.1,
      shadowRadius: 3.84,
      elevation: 5,
    },
    medium: {
      shadowColor: '#000',
      shadowOffset: {
        width: 0,
        height: 4,
      },
      shadowOpacity: 0.15,
      shadowRadius: 6.27,
      elevation: 10,
    },
    large: {
      shadowColor: '#000',
      shadowOffset: {
        width: 0,
        height: 8,
      },
      shadowOpacity: 0.2,
      shadowRadius: 10.32,
      elevation: 16,
    },
  },
};

export type Theme = typeof theme;