import { Notification } from '../types';
import { api } from '../lib/api';

export const getNotifications = async (): Promise<Notification[]> => {
  const { data } = await api.get('/notifications');
  return data.notifications;
};

export const markAllNotificationsRead = async (): Promise<void> => {
  await api.post('/notifications/mark-all-read');
};

export const markNotificationRead = async (id: string): Promise<Notification> => {
  const { data } = await api.patch(`/notifications/${id}/read`);
  return data.notification;
};
