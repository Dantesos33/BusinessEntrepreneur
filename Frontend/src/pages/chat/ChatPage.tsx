import React, { useState, useEffect, useRef } from 'react';
import { useParams } from 'react-router-dom';
import { Send, Phone, Video, Info, Smile, MessageCircle } from 'lucide-react';
import toast from 'react-hot-toast';
import { Avatar } from '../../components/ui/Avatar';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { ChatMessage } from '../../components/chat/ChatMessage';
import { ChatUserList } from '../../components/chat/ChatUserList';
import { useAuth } from '../../context/AuthContext';
import { Message, ChatConversation, User } from '../../types';
import { findUserById } from '../../data/users';
import { getMessagesBetweenUsers, sendMessage, getConversationsForUser } from '../../data/messages';
import { extractErrorMessage } from '../../lib/api';
import { supabase } from '../../lib/supabase';

export const ChatPage: React.FC = () => {
  const { userId } = useParams<{ userId: string }>();
  const { user: currentUser } = useAuth();
  
  const [messages, setMessages] = useState<Message[]>([]);
  const [chatPartner, setChatPartner] = useState<User | null>(null);
  const [newMessage, setNewMessage] = useState("");
  const [conversations, setConversations] = useState<ChatConversation[]>([]);
  const [loading, setIsLoading] = useState(true); // Default to true on initial mount

  const messagesEndRef = useRef<null | HTMLDivElement>(null);

  // 🚀 1. FIXED PARALLEL WORKSPACE LOADER (Runs exactly once per conversation change)
  useEffect(() => {
    if (!currentUser) return;

    let isMounted = true;
    setIsLoading(true); // Turn loader on immediately when switching users

    const initializeChatWorkspace = async () => {
      try {
        if (userId) {
          // Fire backend calls concurrently in a single batch query pass
          const [sidebarConversations, partnerProfile, historicalMessages] = await Promise.all([
            getConversationsForUser(),
            findUserById(userId),
            getMessagesBetweenUsers(userId)
          ]);

          if (isMounted) {
            setConversations(sidebarConversations);
            setChatPartner(partnerProfile);
            setMessages(historicalMessages);
          }
        } else {
          // If no specific chat tab is active, only fetch sidebar context elements
          const sidebarConversations = await getConversationsForUser();
          if (isMounted) {
            setConversations(sidebarConversations);
            setChatPartner(null);
            setMessages([]);
          }
        }
      } catch (error) {
        if (isMounted) toast.error(extractErrorMessage(error));
      } finally {
        if (isMounted) setIsLoading(false); // 💡 Safely turn off the loader
      }
    };

    initializeChatWorkspace();

    return () => {
      isMounted = false;
    };
  }, [currentUser, userId]); // 🌟 CRITICAL: 'loading' state must NEVER be in this dependency array!


  // 🚀 2. REAL-TIME COMMUNICATIONS SUBSCRIBER
  useEffect(() => {
    if (!currentUser) return;

    const myCleanId = String(currentUser.id).trim();
    const channelName = `chat_user_${myCleanId}`;
    const channel = supabase.channel(channelName);

    channel
      .on('broadcast', { event: 'new_message' }, async (payload) => {
        const msg = payload.payload as Message;
        
        if (userId && String(msg.senderId).trim() === String(userId).trim()) {
          setMessages((prev) => {
            if (prev.some(p => String(p.id) === String(msg.id))) return prev;
            return [...prev, msg];
          });
        }
        
        try {
          setConversations(await getConversationsForUser());
        } catch (e) {
          console.error(e);
        }
      })
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, [currentUser, userId]);

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);


  // 🚀 3. OPTIMISTIC MESSAGE DELIVERY SUBMITTER
  const handleSendMessage = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!newMessage.trim() || !currentUser || !userId) return;

    const content = newMessage;
    setNewMessage('');

    const optimisticMessage: Message = {
      id: String(Date.now()),
      senderId: currentUser.id,
      receiverId: userId,
      content: content,
      timestamp: new Date().toISOString(),
      isRead: false,
    };

    setMessages((prev) => [...prev, optimisticMessage]);

    try {
      const savedMessage = await sendMessage(userId, content);
      
      setMessages((prev) => 
        prev.map((msg) => (msg.id === optimisticMessage.id ? savedMessage : msg))
      );

      setConversations(await getConversationsForUser());

      const targetUserId = String(userId).trim();
      await supabase.channel(`chat_user_${targetUserId}`).send({
        type: 'broadcast',
        event: 'new_message',
        payload: savedMessage,
      });

    } catch (error) {
      toast.error(extractErrorMessage(error));
      setMessages((prev) => prev.filter((msg) => msg.id !== optimisticMessage.id));
      setNewMessage(content);
    }
  };

  if (!currentUser) return null;

  // SKELETON PLACEHOLDER SHELL WHILE FETCHING INITIAL CONVERSATIONS
  if (loading && conversations.length === 0) {
    return (
      <div className="flex h-[calc(100vh-4rem)] bg-white border border-gray-200 rounded-lg overflow-hidden animate-pulse">
        <div className="hidden md:block w-1/3 lg:w-1/4 border-r border-gray-200 bg-gray-50 p-4 space-y-4">
          {[1, 2, 3, 4].map((i) => (
            <div key={i} className="flex items-center space-x-3">
              <div className="w-10 h-10 bg-gray-200 rounded-full" />
              <div className="flex-1 space-y-2">
                <div className="h-4 bg-gray-200 rounded w-3/4" />
                <div className="h-3 bg-gray-200 rounded w-1/2" />
              </div>
            </div>
          ))}
        </div>
        <div className="flex-1 flex flex-col bg-white p-6 justify-between">
          <div className="flex items-center border-b border-gray-100 pb-4 space-x-3">
            <div className="w-12 h-12 bg-gray-200 rounded-full" />
            <div className="space-y-2">
              <div className="h-5 bg-gray-200 rounded w-32" />
              <div className="h-3 bg-gray-200 rounded w-20" />
            </div>
          </div>
          <div className="flex-1 bg-gray-50/50 my-4 rounded-xl p-4 space-y-4 overflow-hidden flex flex-col">
            <div className="h-10 bg-gray-200 rounded-lg w-1/3 self-start" />
            <div className="h-10 bg-gray-200 rounded-lg w-1/4 self-end ml-auto" />
            <div className="h-10 bg-gray-200 rounded-lg w-1/2 self-start" />
          </div>
          <div className="h-12 bg-gray-100 rounded-xl w-full" />
        </div>
      </div>
    );
  }

  return (
    <div className="flex h-[calc(100vh-4rem)] bg-white border border-gray-200 rounded-lg overflow-hidden relative">
      <div className="hidden md:block w-1/3 lg:w-1/4 border-r border-gray-200">
        <ChatUserList conversations={conversations} />
      </div>

      <div className="flex-1 flex flex-col">
        {loading && userId ? (
          <div className="flex-1 flex items-center justify-center bg-gray-50">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          </div>
        ) : chatPartner ? (
          <>
            <div className="border-b border-gray-200 p-4 flex justify-between items-center">
              <div className="flex items-center">
                <Avatar src={chatPartner.avatarUrl} alt={chatPartner.name} size="md" status={chatPartner.isOnline ? "online" : "offline"} className="mr-3" />
                <div>
                  <h2 className="text-lg font-medium text-gray-900">{chatPartner.name}</h2>
                  <p className="text-sm text-gray-500">{chatPartner.isOnline ? "Online" : "Last seen recently"}</p>
                </div>
              </div>

              <div className="flex space-x-2">
                <Button variant="ghost" size="sm" className="rounded-full p-2"><Phone size={18} /></Button>
                <Button variant="ghost" size="sm" className="rounded-full p-2"><Video size={18} /></Button>
                <Button variant="ghost" size="sm" className="rounded-full p-2"><Info size={18} /></Button>
              </div>
            </div>

            <div className="flex-1 p-4 overflow-y-auto bg-gray-50">
              {messages.length > 0 ? (
                <div className="space-y-4">
                  {messages.map((message, idx) => (
                    <ChatMessage key={message.id || idx} message={message} isCurrentUser={message.senderId === currentUser.id} sender={message.senderId === currentUser.id ? currentUser : chatPartner} />
                  ))}
                  <div ref={messagesEndRef} />
                </div>
              ) : (
                <div className="h-full flex flex-col items-center justify-center">
                  <div className="bg-gray-100 p-4 rounded-full mb-4"><MessageCircle size={32} className="text-gray-400" /></div>
                  <h3 className="text-lg font-medium text-gray-700">No messages yet</h3>
                  <p className="text-gray-500 mt-1">Send a message to start the conversation</p>
                </div>
              )}
            </div>

            <div className="border-t border-gray-200 p-4">
              <form onSubmit={handleSendMessage} className="flex space-x-2">
                <Button type="button" variant="ghost" size="sm" className="rounded-full p-2"><Smile size={20} /></Button>
                <Input type="text" placeholder="Type a message..." value={newMessage} onChange={(e) => setNewMessage(e.target.value)} fullWidth className="flex-1" />


                <Button
                  type="submit"
                  size="sm"
                  disabled={!newMessage.trim()}
                  className="rounded-full p-2 w-10 h-10 flex items-center justify-center"
                  aria-label="Send message"
                >
                  <Send size={18} />
                </Button>
              </form>
            </div>
          </>
        ) : (
          <div className="h-full flex flex-col items-center justify-center p-4">
            <div className="bg-gray-100 p-6 rounded-full mb-4">
              <MessageCircle size={48} className="text-gray-400" />
            </div>
            <h2 className="text-xl font-medium text-gray-700">
              Select a conversation
            </h2>
            <p className="text-gray-500 mt-2 text-center">
              Choose a contact from the list to start chatting
            </p>
          </div>
        )}
      </div>
    </div>
  );
};
