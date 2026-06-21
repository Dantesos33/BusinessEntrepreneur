import { Deal } from '../types';
import { api } from '../lib/api';

export const getDeals = async (search?: string, statuses?: string[]): Promise<Deal[]> => {
  const params: Record<string, string | string[]> = {};
  if (search) params.search = search;
  if (statuses && statuses.length > 0) params.status = statuses;

  const { data } = await api.get('/deals', { params });
  return data.deals;
};
