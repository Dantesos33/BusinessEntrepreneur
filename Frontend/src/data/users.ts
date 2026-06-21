import { Entrepreneur, Investor, User } from '../types';
import { api } from '../lib/api';

/**
 * Real-data replacement for the old mock src/data/users.ts.
 * Same spirit as before (one function per lookup), but every call
 * now hits the Laravel API and returns a Promise.
 */

export const getEntrepreneurs = async (search?: string): Promise<Entrepreneur[]> => {
  const { data } = await api.get('/entrepreneurs', { params: search ? { search } : {} });
  return data.entrepreneurs;
};

export const getInvestors = async (search?: string): Promise<Investor[]> => {
  const { data } = await api.get('/investors', { params: search ? { search } : {} });
  return data.investors;
};

export const findUserById = async (id: string): Promise<User> => {
  const { data } = await api.get(`/users/${id}`);
  return data.user;
};
