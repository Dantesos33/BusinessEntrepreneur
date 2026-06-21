import { CollaborationRequest } from '../types';
import { api } from '../lib/api';

/**
 * Returns requests relevant to the current logged-in user — the
 * backend already scopes by investor vs entrepreneur based on the
 * Sanctum session, so there's no need to pass a user id.
 */
export const getMyCollaborationRequests = async (): Promise<CollaborationRequest[]> => {
  const { data } = await api.get('/collaboration-requests');
  return data.collaborationRequests;
};

export const updateRequestStatus = async (
  requestId: string,
  newStatus: 'accepted' | 'rejected'
): Promise<CollaborationRequest> => {
  const { data } = await api.patch(`/collaboration-requests/${requestId}`, { status: newStatus });
  return data.collaborationRequest;
};

export const createCollaborationRequest = async (
  entrepreneurId: string,
  message: string
): Promise<CollaborationRequest> => {
  const { data } = await api.post('/collaboration-requests', { entrepreneurId, message });
  return data.collaborationRequest;
};
