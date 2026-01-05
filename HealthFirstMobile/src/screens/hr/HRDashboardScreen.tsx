import React, {useEffect, useState} from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  RefreshControl,
  Dimensions,
} from 'react-native';
import {
  Text,
  Card,
  FAB,
  Badge,
  ActivityIndicator,
} from 'react-native-paper';
import {useDispatch, useSelector} from 'react-redux';
import Icon from 'react-native-vector-icons/MaterialIcons';
import {LineChart} from 'react-native-chart-kit';
import {theme} from '../../theme';
import {RootState, AppDispatch} from '../../store';
import {fetchHRStats} from '../../store/slices/hrSlice';
import StatCard from '../../components/StatCard';
import QuickActionGrid from '../../components/QuickActionGrid';
import RecentActivityList from '../../components/RecentActivityList';
import {useTranslation} from 'react-i18next';

const {width} = Dimensions.get('window');

const HRDashboardScreen: React.FC = () => {
  const {t} = useTranslation();
  const dispatch = useDispatch<AppDispatch>();
  const {stats, isLoading, recentActivity} = useSelector(
    (state: RootState) => state.hr,
  );
  const {user} = useSelector((state: RootState) => state.auth);

  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      await dispatch(fetchHRStats()).unwrap();
    } catch (error) {
      console.error('Failed to load HR stats:', error);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadDashboardData();
    setRefreshing(false);
  };

  const quickActions = [
    {
      id: 'add_employee',
      title: t('addEmployee'),
      icon: 'person-add',
      color: theme.colors.primary,
      onPress: () => {/* Navigate to add employee */},
    },
    {
      id: 'approve_leave',
      title: t('approveLeave'),
      icon: 'check-circle',
      color: theme.colors.success,
      badge: stats?.pendingLeaveRequests || 0,
      onPress: () => {/* Navigate to leave requests */},
    },
    {
      id: 'process_payroll',
      title: t('processPayroll'),
      icon: 'account-balance-wallet',
      color: theme.colors.accent,
      onPress: () => {/* Navigate to payroll */},
    },
    {
      id: 'generate_report',
      title: t('generateReport'),
      icon: 'assessment',
      color: theme.colors.warning,
      onPress: () => {/* Navigate to reports */},
    },
    {
      id: 'view_attendance',
      title: t('viewAttendance'),
      icon: 'schedule',
      color: theme.colors.medical,
      onPress: () => {/* Navigate to attendance */},
    },
    {
      id: 'post_job',
      title: t('postJob'),
      icon: 'work',
      color: theme.colors.technical,
      onPress: () => {/* Navigate to job posting */},
    },
  ];

  const chartData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [
      {
        data: [20, 45, 28, 80, 99, 43],
        color: (opacity = 1) => `rgba(46, 134, 171, ${opacity})`,
        strokeWidth: 2,
      },
    ],
  };

  if (isLoading && !stats) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={theme.colors.primary} />
        <Text style={styles.loadingText}>{t('loadingDashboard')}</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <ScrollView
        style={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }>
        {/* Header */}
        <View style={styles.header}>
          <View>
            <Text style={styles.greeting}>
              {t('goodMorning')}, {user?.name}
            </Text>
            <Text style={styles.role}>{t('hrOfficer')}</Text>
          </View>
          <View style={styles.headerActions}>
            <Icon
              name="notifications"
              size={24}
              color={theme.colors.text}
              style={styles.notificationIcon}
            />
            <Badge style={styles.notificationBadge}>3</Badge>
          </View>
        </View>

        {/* Statistics Cards */}
        <View style={styles.statsContainer}>
          <StatCard
            title={t('totalEmployees')}
            value={stats?.totalEmployees || 0}
            icon="people"
            color={theme.colors.primary}
            trend="+12%"
            trendUp={true}
          />
          <StatCard
            title={t('activeEmployees')}
            value={stats?.activeEmployees || 0}
            icon="person"
            color={theme.colors.success}
            trend="+5%"
            trendUp={true}
          />
          <StatCard
            title={t('onLeave')}
            value={stats?.onLeaveToday || 0}
            icon="beach-access"
            color={theme.colors.warning}
            trend="-2%"
            trendUp={false}
          />
          <StatCard
            title={t('pendingRequests')}
            value={stats?.pendingLeaveRequests || 0}
            icon="pending-actions"
            color={theme.colors.accent}
            trend="+8%"
            trendUp={true}
          />
        </View>

        {/* Quick Actions */}
        <Card style={styles.card}>
          <Card.Content>
            <Text style={styles.sectionTitle}>{t('quickActions')}</Text>
            <QuickActionGrid actions={quickActions} />
          </Card.Content>
        </Card>

        {/* Employee Trends Chart */}
        <Card style={styles.card}>
          <Card.Content>
            <Text style={styles.sectionTitle}>{t('employeeTrends')}</Text>
            <LineChart
              data={chartData}
              width={width - 64}
              height={220}
              chartConfig={{
                backgroundColor: theme.colors.surface,
                backgroundGradientFrom: theme.colors.surface,
                backgroundGradientTo: theme.colors.surface,
                decimalPlaces: 0,
                color: (opacity = 1) => `rgba(46, 134, 171, ${opacity})`,
                labelColor: (opacity = 1) => `rgba(44, 62, 80, ${opacity})`,
                style: {
                  borderRadius: 16,
                },
                propsForDots: {
                  r: '6',
                  strokeWidth: '2',
                  stroke: theme.colors.primary,
                },
              }}
              bezier
              style={styles.chart}
            />
          </Card.Content>
        </Card>

        {/* Recent Activity */}
        <Card style={styles.card}>
          <Card.Content>
            <Text style={styles.sectionTitle}>{t('recentActivity')}</Text>
            <RecentActivityList activities={recentActivity || []} />
          </Card.Content>
        </Card>

        {/* Leave Requests Summary */}
        <Card style={styles.card}>
          <Card.Content>
            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>{t('leaveRequests')}</Text>
              <Text style={styles.viewAll}>{t('viewAll')}</Text>
            </View>
            <View style={styles.leaveRequestsContainer}>
              <View style={styles.leaveRequestItem}>
                <View style={styles.leaveRequestInfo}>
                  <Text style={styles.employeeName}>John Doe</Text>
                  <Text style={styles.leaveType}>Annual Leave</Text>
                  <Text style={styles.leaveDates}>Dec 15 - Dec 22</Text>
                </View>
                <View style={styles.leaveRequestActions}>
                  <Icon
                    name="check-circle"
                    size={24}
                    color={theme.colors.success}
                    style={styles.actionIcon}
                  />
                  <Icon
                    name="cancel"
                    size={24}
                    color={theme.colors.warning}
                    style={styles.actionIcon}
                  />
                </View>
              </View>
              {/* More leave request items would be rendered here */}
            </View>
          </Card.Content>
        </Card>
      </ScrollView>

      {/* Floating Action Button */}
      <FAB
        style={styles.fab}
        icon="add"
        onPress={() => {/* Show quick add menu */}}
        color={theme.colors.surface}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.colors.background,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: theme.colors.background,
  },
  loadingText: {
    marginTop: theme.spacing.md,
    color: theme.colors.text,
  },
  scrollView: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: theme.spacing.lg,
    backgroundColor: theme.colors.surface,
    ...theme.shadows.small,
  },
  greeting: {
    fontSize: 20,
    fontWeight: 'bold',
    color: theme.colors.text,
  },
  role: {
    fontSize: 14,
    color: theme.colors.placeholder,
    marginTop: 2,
  },
  headerActions: {
    position: 'relative',
  },
  notificationIcon: {
    padding: theme.spacing.sm,
  },
  notificationBadge: {
    position: 'absolute',
    top: 4,
    right: 4,
    backgroundColor: theme.colors.warning,
  },
  statsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: theme.spacing.md,
    gap: theme.spacing.md,
  },
  card: {
    margin: theme.spacing.md,
    marginTop: 0,
    ...theme.shadows.small,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: theme.colors.text,
    marginBottom: theme.spacing.md,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: theme.spacing.md,
  },
  viewAll: {
    color: theme.colors.primary,
    fontSize: 14,
    fontWeight: '500',
  },
  chart: {
    marginVertical: theme.spacing.sm,
    borderRadius: 16,
  },
  leaveRequestsContainer: {
    gap: theme.spacing.md,
  },
  leaveRequestItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: theme.spacing.md,
    backgroundColor: theme.colors.background,
    borderRadius: theme.roundness,
  },
  leaveRequestInfo: {
    flex: 1,
  },
  employeeName: {
    fontSize: 16,
    fontWeight: '500',
    color: theme.colors.text,
  },
  leaveType: {
    fontSize: 14,
    color: theme.colors.primary,
    marginTop: 2,
  },
  leaveDates: {
    fontSize: 12,
    color: theme.colors.placeholder,
    marginTop: 2,
  },
  leaveRequestActions: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  actionIcon: {
    padding: theme.spacing.xs,
  },
  fab: {
    position: 'absolute',
    margin: theme.spacing.lg,
    right: 0,
    bottom: 0,
    backgroundColor: theme.colors.primary,
  },
});

export default HRDashboardScreen;