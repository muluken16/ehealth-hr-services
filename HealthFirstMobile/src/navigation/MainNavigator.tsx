import React from 'react';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import {useSelector} from 'react-redux';
import Icon from 'react-native-vector-icons/MaterialIcons';
import {RootState} from '../store';
import {theme} from '../theme';

// HR Officer Navigators
import HRDashboardNavigator from './hr/HRDashboardNavigator';
import EmployeeNavigator from './hr/EmployeeNavigator';
import LeaveNavigator from './hr/LeaveNavigator';
import PayrollNavigator from './hr/PayrollNavigator';

// Health Officer Navigators
import HealthDashboardNavigator from './health/HealthDashboardNavigator';
import PatientNavigator from './health/PatientNavigator';
import AppointmentNavigator from './health/AppointmentNavigator';
import InventoryNavigator from './health/InventoryNavigator';

// Employee Self-Service Navigator
import EmployeeSelfServiceNavigator from './employee/EmployeeSelfServiceNavigator';

// Shared Navigators
import ProfileNavigator from './shared/ProfileNavigator';
import NotificationNavigator from './shared/NotificationNavigator';

export type MainTabParamList = {
  Dashboard: undefined;
  Employees?: undefined;
  Leave?: undefined;
  Payroll?: undefined;
  Patients?: undefined;
  Appointments?: undefined;
  Inventory?: undefined;
  SelfService?: undefined;
  Profile: undefined;
  Notifications: undefined;
};

const Tab = createBottomTabNavigator<MainTabParamList>();

const MainNavigator: React.FC = () => {
  const {user} = useSelector((state: RootState) => state.auth);
  const role = user?.role;

  const getTabScreens = () => {
    switch (role) {
      case 'kebele_hr':
      case 'wereda_hr':
      case 'zone_hr':
        return (
          <>
            <Tab.Screen
              name="Dashboard"
              component={HRDashboardNavigator}
              options={{
                tabBarLabel: 'Dashboard',
                tabBarIcon: ({color, size}) => (
                  <Icon name="dashboard" color={color} size={size} />
                ),
              }}
            />
            <Tab.Screen
              name="Employees"
              component={EmployeeNavigator}
              options={{
                tabBarLabel: 'Employees',
                tabBarIcon: ({color, size}) => (
                  <Icon name="people" color={color} size={size} />
                ),
              }}
            />
            <Tab.Screen
              name="Leave"
              component={LeaveNavigator}
              options={{
                tabBarLabel: 'Leave',
                tabBarIcon: ({color, size}) => (
                  <Icon name="beach-access" color={color} size={size} />
                ),
              }}
            />
            <Tab.Screen
              name="Payroll"
              component={PayrollNavigator}
              options={{
                tabBarLabel: 'Payroll',
                tabBarIcon: ({color, size}) => (
                  <Icon name="account-balance-wallet" color={color} size={size} />
                ),
              }}
            />
          </>
        );

      case 'kebele_health_officer':
      case 'wereda_health_officer':
      case 'zone_health_officer':
        return (
          <>
            <Tab.Screen
              name="Dashboard"
              component={HealthDashboardNavigator}
              options={{
                tabBarLabel: 'Dashboard',
                tabBarIcon: ({color, size}) => (
                  <Icon name="dashboard" color={color} size={size} />
                ),
              }}
            />
            <Tab.Screen
              name="Patients"
              component={PatientNavigator}
              options={{
                tabBarLabel: 'Patients',
                tabBarIcon: ({color, size}) => (
                  <Icon name="local-hospital" color={color} size={size} />
                ),
              }}
            />
            <Tab.Screen
              name="Appointments"
              component={AppointmentNavigator}
              options={{
                tabBarLabel: 'Appointments',
                tabBarIcon: ({color, size}) => (
                  <Icon name="event" color={color} size={size} />
                ),
              }}
            />
            <Tab.Screen
              name="Inventory"
              component={InventoryNavigator}
              options={{
                tabBarLabel: 'Inventory',
                tabBarIcon: ({color, size}) => (
                  <Icon name="inventory" color={color} size={size} />
                ),
              }}
            />
          </>
        );

      default:
        // Employee self-service
        return (
          <>
            <Tab.Screen
              name="SelfService"
              component={EmployeeSelfServiceNavigator}
              options={{
                tabBarLabel: 'Home',
                tabBarIcon: ({color, size}) => (
                  <Icon name="home" color={color} size={size} />
                ),
              }}
            />
          </>
        );
    }
  };

  return (
    <Tab.Navigator
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: theme.colors.primary,
        tabBarInactiveTintColor: theme.colors.placeholder,
        tabBarStyle: {
          backgroundColor: theme.colors.surface,
          borderTopColor: theme.colors.borderColor,
          paddingBottom: 5,
          height: 60,
        },
        tabBarLabelStyle: {
          fontSize: 12,
          fontWeight: '500',
        },
      }}>
      {getTabScreens()}
      
      {/* Common tabs for all roles */}
      <Tab.Screen
        name="Notifications"
        component={NotificationNavigator}
        options={{
          tabBarLabel: 'Notifications',
          tabBarIcon: ({color, size}) => (
            <Icon name="notifications" color={color} size={size} />
          ),
          tabBarBadge: 3, // This would be dynamic based on unread notifications
        }}
      />
      <Tab.Screen
        name="Profile"
        component={ProfileNavigator}
        options={{
          tabBarLabel: 'Profile',
          tabBarIcon: ({color, size}) => (
            <Icon name="person" color={color} size={size} />
          ),
        }}
      />
    </Tab.Navigator>
  );
};

export default MainNavigator;