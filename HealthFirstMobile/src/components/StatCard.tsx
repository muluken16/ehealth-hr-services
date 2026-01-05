import React from 'react';
import {View, StyleSheet} from 'react-native';
import {Card, Text} from 'react-native-paper';
import Icon from 'react-native-vector-icons/MaterialIcons';
import {theme} from '../theme';

interface StatCardProps {
  title: string;
  value: number | string;
  icon: string;
  color: string;
  trend?: string;
  trendUp?: boolean;
  subtitle?: string;
  onPress?: () => void;
}

const StatCard: React.FC<StatCardProps> = ({
  title,
  value,
  icon,
  color,
  trend,
  trendUp,
  subtitle,
  onPress,
}) => {
  return (
    <Card
      style={[styles.card, {borderLeftColor: color}]}
      onPress={onPress}
      mode="elevated">
      <Card.Content style={styles.content}>
        <View style={styles.header}>
          <View style={[styles.iconContainer, {backgroundColor: `${color}20`}]}>
            <Icon name={icon} size={24} color={color} />
          </View>
          {trend && (
            <View style={styles.trendContainer}>
              <Icon
                name={trendUp ? 'trending-up' : 'trending-down'}
                size={16}
                color={trendUp ? theme.colors.success : theme.colors.warning}
              />
              <Text
                style={[
                  styles.trendText,
                  {
                    color: trendUp ? theme.colors.success : theme.colors.warning,
                  },
                ]}>
                {trend}
              </Text>
            </View>
          )}
        </View>

        <View style={styles.body}>
          <Text style={styles.value}>{value}</Text>
          <Text style={styles.title}>{title}</Text>
          {subtitle && <Text style={styles.subtitle}>{subtitle}</Text>}
        </View>
      </Card.Content>
    </Card>
  );
};

const styles = StyleSheet.create({
  card: {
    flex: 1,
    minWidth: 150,
    margin: theme.spacing.xs,
    borderLeftWidth: 4,
    ...theme.shadows.small,
  },
  content: {
    padding: theme.spacing.md,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: theme.spacing.md,
  },
  iconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
  },
  trendContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: theme.colors.background,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: theme.spacing.xs,
    borderRadius: 12,
  },
  trendText: {
    fontSize: 12,
    fontWeight: '600',
    marginLeft: 2,
  },
  body: {
    alignItems: 'flex-start',
  },
  value: {
    fontSize: 28,
    fontWeight: 'bold',
    color: theme.colors.text,
    marginBottom: 4,
  },
  title: {
    fontSize: 14,
    color: theme.colors.placeholder,
    fontWeight: '500',
  },
  subtitle: {
    fontSize: 12,
    color: theme.colors.placeholder,
    marginTop: 2,
  },
});

export default StatCard;