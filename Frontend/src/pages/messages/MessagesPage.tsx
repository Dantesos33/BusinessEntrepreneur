import React, { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { MessageCircle } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { getConversationsForUser } from '../../data/messages';
import { ChatUserList } from '../../components/chat/ChatUserList';
import { ChatConversation } from '../../types';
import { extractErrorMessage } from '../../lib/api';

export const MessagesPage: React.FC = () => {
  const { user } = useAuth();
  const [conversations, setConversations] = useState<ChatConversation[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (!user) return;

    (async () => {
      setIsLoading(true);
      try {
        setConversations(await getConversationsForUser());
      } catch (error) {
        toast.error(extractErrorMessage(error));
      } finally {
        setIsLoading(false);
      }
    })();
  }, [user]);

  if (!user) return null;

  return (
    <div className="h-[calc(100vh-8rem)] bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden animate-fade-in">
      {isLoading ? (
        <p className="text-center text-gray-500 py-8">Loading conversations…</p>
      ) : conversations.length > 0 ? (
        <ChatUserList conversations={conversations} />
      ) : (
        <div className="h-full flex flex-col items-center justify-center p-8">
          <div className="bg-gray-100 p-6 rounded-full mb-4">
            <MessageCircle size={32} className="text-gray-400" />
          </div>
          <h2 className="text-xl font-medium text-gray-900">No messages yet</h2>
          <p className="text-gray-600 text-center mt-2">
            Start connecting with entrepreneurs and investors to begin conversations
          </p>
        </div>
      )}
    </div>
  );
};
