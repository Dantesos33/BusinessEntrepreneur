import React, { createContext, useState, useContext, useEffect, useRef } from 'react';
import { User, UserRole, AuthContextType } from '../types';
import toast from 'react-hot-toast';
import { api, ensureCsrfCookie, extractErrorMessage } from '../lib/api';

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  
  // 💡 Guard variable to prevent React StrictMode from double-triggering auth checks
  const hasCheckedAuth = useRef(false);

  // 🚀 CRITICAL FIX: Empty dependency array [] means this runs EXACTLY ONCE on application boot
  useEffect(() => {
    if (hasCheckedAuth.current) return;
    hasCheckedAuth.current = true;

    let isMounted = true;

    const checkAuthStatus = async () => {
      try {
        const response = await api.get('/user', {
          headers: {
            'Accept': 'application/json',
          }
        });
        
        if (isMounted) {
          if (response.status === 401) {
            setUser(null);
          } else {
            setUser(response.data.user);
          }
        }
      } catch (error) {
        if (isMounted) {
          setUser(null);
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    checkAuthStatus();

    return () => {
      isMounted = false;
    };
  }, []); // 🌟 KEEP THIS DEPENDENCY ARRAY ABSOLUTELY EMPTY!

  const login = async (email: string, password: string, role: UserRole): Promise<void> => {
    setIsLoading(true);
    try {
      await ensureCsrfCookie();
      const response = await api.post('/login', { email, password, role });
      
      if (response.status === 401 || response.status === 422) {
        throw response;
      }

      setUser(response.data.user);
      toast.success('Successfully logged in!');
    } catch (error) {
      const message = extractErrorMessage(error);
      toast.error(message);
      throw new Error(message);
    } finally {
      setIsLoading(false);
    }
  };

  const register = async (name: string, email: string, password: string, role: UserRole): Promise<void> => {
    setIsLoading(true);
    try {
      await ensureCsrfCookie();
      const response = await api.post('/register', {
        name,
        email,
        password,
        password_confirmation: password,
        role,
      });

      if (response.status === 422) {
        throw response;
      }

      setUser(response.data.user);
      toast.success('Account created successfully!');
    } catch (error) {
      const message = extractErrorMessage(error);
      toast.error(message);
      throw new Error(message);
    } finally {
      setIsLoading(false);
    }
  };

  const forgotPassword = async (email: string): Promise<void> => {
    try {
      await ensureCsrfCookie();
      const response = await api.post('/forgot-password', { email });
      if (response.status >= 400) throw response;
      toast.success('Password reset instructions sent to your email');
    } catch (error) {
      const message = extractErrorMessage(error);
      toast.error(message);
      throw new Error(message);
    }
  };

  const resetPassword = async (token: string, newPassword: string): Promise<void> => {
    try {
      await ensureCsrfCookie();
      const params = new URLSearchParams(window.location.search);
      const email = params.get('email') || '';

      const response = await api.post('/reset-password', {
        token,
        email,
        password: newPassword,
        password_confirmation: newPassword,
      });

      if (response.status >= 400) throw response;
      toast.success('Password reset successfully');
    } catch (error) {
      const message = extractErrorMessage(error);
      toast.error(message);
      throw new Error(message);
    }
  };

  const logout = async (): Promise<void> => {
    try {
      await api.post('/logout');
    } catch {
      // Fail gracefully if backend dropped session
    } finally {
      setUser(null);
      toast.success('Logged out successfully');
    }
  };

  const updateProfile = async (userId: string, updates: Partial<User>): Promise<void> => {
    try {
      const response = await api.patch(`/users/${userId}`, updates);
      if (response.status >= 400) throw response;

      if (user?.id === userId) {
        setUser(response.data.user);
      }
      toast.success('Profile updated successfully');
    } catch (error) {
      const message = extractErrorMessage(error);
      toast.error(message);
      throw new Error(message);
    }
  };

  const value = {
    user,
    login,
    register,
    logout,
    forgotPassword,
    resetPassword,
    updateProfile,
    isAuthenticated: !!user,
    isLoading,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
