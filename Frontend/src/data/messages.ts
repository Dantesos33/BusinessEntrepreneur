import { Message, ChatConversation } from '../types';
import { api } from '../lib/api';

export const getMessagesBetweenUsers = async (otherUserId: string): Promise<Message[]> => {
  const { data } = await api.get(`/messages/${otherUserId}`);
  return data.messages;
};

export const getConversationsForUser = async (): Promise<ChatConversation[]> => {
  const { data } = await api.get('/conversations');
  // Backend returns { participant, lastMessage }[]; shape it into
  // the ChatConversation type the UI expects.
  return data.conversations.map((c: { participant: { id: string }; lastMessage?: Message }) => ({
    id: c.participant.id,
    participants: [c.participant.id],
    lastMessage: c.lastMessage,
    updatedAt: c.lastMessage?.timestamp || new Date().toISOString(),
    participant: c.participant, // extra field: the other user's full profile, for avatar/name display
  }));
};

export const sendMessage = async (receiverId: string, content: string): Promise<Message> => {
  const { data } = await api.post('/messages', { receiverId, content });
  return data.message;
};
