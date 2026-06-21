import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Check, X, MessageCircle } from 'lucide-react';
import toast from 'react-hot-toast';
import { CollaborationRequest } from '../../types';
import { Card, CardBody, CardFooter } from '../ui/Card';
import { Avatar } from '../ui/Avatar';
import { Badge } from '../ui/Badge';
import { Button } from '../ui/Button';
import { updateRequestStatus } from '../../data/collaborationRequests';
import { extractErrorMessage } from '../../lib/api';
import { formatDistanceToNow } from 'date-fns';

interface CollaborationRequestCardProps {
  request: CollaborationRequest;
  onStatusUpdate?: (requestId: string, status: 'accepted' | 'rejected') => void;
}

export const CollaborationRequestCard: React.FC<CollaborationRequestCardProps> = ({
  request,
  onStatusUpdate
}) => {
  const navigate = useNavigate();
  const [isUpdating, setIsUpdating] = useState(false);

  // The investor object is embedded directly by the API now —
  // no separate lookup needed (and no risk of it being missing
  // because of async timing).
  const investor = request.investor;

  if (!investor) return null;

  const handleStatusChange = async (status: 'accepted' | 'rejected') => {
    setIsUpdating(true);
    try {
      await updateRequestStatus(request.id, status);
      onStatusUpdate?.(request.id, status);
    } catch (error) {
      toast.error(extractErrorMessage(error));
    } finally {
      setIsUpdating(false);
    }
  };

  const handleMessage = () => {
    navigate(`/chat/${investor.id}`);
  };

  const handleViewProfile = () => {
    navigate(`/profile/investor/${investor.id}`);
  };

  const getStatusBadge = () => {
    switch (request.status) {
      case 'pending':
        return <Badge variant="warning">Pending</Badge>;
      case 'accepted':
        return <Badge variant="success">Accepted</Badge>;
      case 'rejected':
        return <Badge variant="error">Declined</Badge>;
      default:
        return null;
    }
  };

  return (
    <Card className="transition-all duration-300">
      <CardBody className="flex flex-col">
        <div className="flex justify-between items-start">
          <div className="flex items-start">
            <Avatar
              src={investor.avatarUrl}
              alt={investor.name}
              size="md"
              status={investor.isOnline ? 'online' : 'offline'}
              className="mr-3"
            />

            <div>
              <h3 className="text-md font-semibold text-gray-900">{investor.name}</h3>
              <p className="text-sm text-gray-500">
                {formatDistanceToNow(new Date(request.createdAt), { addSuffix: true })}
              </p>
            </div>
          </div>

          {getStatusBadge()}
        </div>

        <div className="mt-4">
          <p className="text-sm text-gray-600">{request.message}</p>
        </div>
      </CardBody>

      <CardFooter className="border-t border-gray-100 bg-gray-50">
        {request.status === 'pending' ? (
          <div className="flex justify-between w-full">
            <div className="space-x-2">
              <Button
                variant="outline"
                size="sm"
                leftIcon={<X size={16} />}
                onClick={() => handleStatusChange('rejected')}
                disabled={isUpdating}
              >
                Decline
              </Button>
              <Button
                variant="success"
                size="sm"
                leftIcon={<Check size={16} />}
                onClick={() => handleStatusChange('accepted')}
                disabled={isUpdating}
              >
                Accept
              </Button>
            </div>

            <Button
              variant="primary"
              size="sm"
              leftIcon={<MessageCircle size={16} />}
              onClick={handleMessage}
            >
              Message
            </Button>
          </div>
        ) : (
          <div className="flex justify-between w-full">
            <Button
              variant="outline"
              size="sm"
              leftIcon={<MessageCircle size={16} />}
              onClick={handleMessage}
            >
              Message
            </Button>

            <Button
              variant="primary"
              size="sm"
              onClick={handleViewProfile}
            >
              View Profile
            </Button>
          </div>
        )}
      </CardFooter>
    </Card>
  );
};
