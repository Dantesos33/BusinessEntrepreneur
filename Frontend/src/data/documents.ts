import { Document } from '../types';
import { api } from '../lib/api';

export const getDocuments = async (): Promise<Document[]> => {
  const { data } = await api.get('/documents');
  return data.documents;
};

export const uploadDocument = async (file: File, shared: boolean): Promise<Document> => {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('shared', shared ? '1' : '0');

  const { data } = await api.post('/documents', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return data.document;
};

export const deleteDocument = async (id: string): Promise<void> => {
  await api.delete(`/documents/${id}`);
};
