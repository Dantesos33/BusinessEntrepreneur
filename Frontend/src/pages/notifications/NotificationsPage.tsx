import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Bell, MessageCircle, UserPlus } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import toast from 'react-hot-toast';
import { Card, CardBody } from '../../components/ui/Card';
import { Badge } from '../../components/ui/Badge';
import { Button } from '../../components/ui/Button';
import { Notification } from '../../types';
import { getNotifications, markAllNotificationsRead, markNotificationRead } from '../../data/notifications';
import { extractErrorMessage } from '../../lib/api';

export const NotificationsPage: React.FC = () => {
  const navigate = useNavigate();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    (async () => {
      setIsLoading(true);
      try {
        setNotifications(await getNotifications());
      } catch (error) {
        toast.error(extractErrorMessage(error));
      } finally {
        setIsLoading(false);
      }
    })();
  }, []);

  const getNotificationIcon = (type: string) => {
    switch (type) {
      case 'message':
        return <MessageCircle size={16} className="text-primary-600" />;
      case 'collaboration_request':
      case 'collaboration_accepted':
        return <UserPlus size={16} className="text-secondary-600" />;
      default:
        return <Bell size={16} className="text-gray-600" />;
    }
  };

  const handleMarkAllRead = async () => {
    try {
      await markAllNotificationsRead();
      setNotifications(prev => prev.map(n => ({ ...n, isRead: true })));
    } catch (error) {
      toast.error(extractErrorMessage(error));
    }
  };

  const handleClick = async (notification: Notification) => {
    if (!notification.isRead) {
      try {
        await markNotificationRead(notification.id);
        setNotifications(prev =>
          prev.map(n => n.id === notification.id ? { ...n, isRead: true } : n)
        );
      } catch {
        // Non-critical — still navigate even if marking-read fails
      }
    }
    if (notification.link) navigate(notification.link);
  };

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Notifications</h1>
          <p className="text-gray-600">Stay updated with your network activity</p>
        </div>

        {notifications.some(n => !n.isRead) && (
          <Button variant="outline" size="sm" onClick={handleMarkAllRead}>
            Mark all as read
          </Button>
        )}
      </div>

      <div className="space-y-4">
        {isLoading ? (
          <p className="text-center text-gray-500 py-8">Loading notifications…</p>
        ) : notifications.length > 0 ? (
          notifications.map(notification => (
            <Card
              key={notification.id}
              className={`transition-colors duration-200 cursor-pointer ${
                !notification.isRead ? 'bg-primary-50' : ''
              }`}
              onClick={() => handleClick(notification)}
            >
              <CardBody className="flex items-start p-4">
                <div className="p-2 bg-gray-100 rounded-full mr-4">
                  {getNotificationIcon(notification.type)}
                </div>

                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2">
                    <span className="font-medium text-gray-900">
                      {notification.title}
                    </span>
                    {!notification.isRead && (
                      <Badge variant="primary" size="sm" rounded>New</Badge>
                    )}
                  </div>

                  {notification.body && (
                    <p className="text-gray-600 mt-1">{notification.body}</p>
                  )}

                  <div className="flex items-center gap-2 mt-2 text-sm text-gray-500">
                    <span>{formatDistanceToNow(new Date(notification.createdAt), { addSuffix: true })}</span>
                  </div>
                </div>
              </CardBody>
            </Card>
          ))
        ) : (
          <p className="text-center text-gray-500 py-8">You're all caught up — no notifications yet.</p>
        )}
      </div>
    </div>
  );
};
