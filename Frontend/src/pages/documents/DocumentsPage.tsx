import React, { useState, useEffect, useRef } from 'react';
import { FileText, Upload, Download, Trash2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { Card, CardHeader, CardBody } from '../../components/ui/Card';
import { Button } from '../../components/ui/Button';
import { Badge } from '../../components/ui/Badge';
import { Document } from '../../types';
import { getDocuments, uploadDocument, deleteDocument } from '../../data/documents';
import { extractErrorMessage } from '../../lib/api';
import { API_BASE_URL } from '../../lib/api';

export const DocumentsPage: React.FC = () => {
  const [documents, setDocuments] = useState<Document[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isUploading, setIsUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const loadDocuments = async () => {
    setIsLoading(true);
    try {
      setDocuments(await getDocuments());
    } catch (error) {
      toast.error(extractErrorMessage(error));
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadDocuments();
  }, []);

  const handleFileSelected = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setIsUploading(true);
    try {
      await uploadDocument(file, false);
      toast.success('Document uploaded successfully');
      await loadDocuments();
    } catch (error) {
      toast.error(extractErrorMessage(error));
    } finally {
      setIsUploading(false);
      if (fileInputRef.current) fileInputRef.current.value = '';
    }
  };

  const handleDelete = async (id: string) => {
    try {
      await deleteDocument(id);
      setDocuments(prev => prev.filter(d => d.id !== id));
      toast.success('Document deleted');
    } catch (error) {
      toast.error(extractErrorMessage(error));
    }
  };

  const handleDownload = (doc: Document) => {
    // doc.url is already an absolute, authenticated download route
    // (api/documents/{id}/download) — open it directly. The Sanctum
    // session cookie travels with it since it's same-origin to the
    // API base.
    window.open(doc.url.startsWith('http') ? doc.url : `${API_BASE_URL}${doc.url}`, '_blank');
  };

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Documents</h1>
          <p className="text-gray-600">Manage your startup's important files</p>
        </div>

        <input
          ref={fileInputRef}
          type="file"
          className="hidden"
          onChange={handleFileSelected}
        />
        <Button
          leftIcon={<Upload size={18} />}
          isLoading={isUploading}
          onClick={() => fileInputRef.current?.click()}
        >
          Upload Document
        </Button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <Card className="lg:col-span-1">
          <CardHeader>
            <h2 className="text-lg font-medium text-gray-900">Your Files</h2>
          </CardHeader>
          <CardBody className="space-y-4">
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Total documents</span>
              <span className="font-medium text-gray-900">{documents.length}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Shared</span>
              <span className="font-medium text-gray-900">{documents.filter(d => d.shared).length}</span>
            </div>
          </CardBody>
        </Card>

        <div className="lg:col-span-3">
          <Card>
            <CardHeader className="flex justify-between items-center">
              <h2 className="text-lg font-medium text-gray-900">All Documents</h2>
            </CardHeader>
            <CardBody>
              {isLoading ? (
                <p className="text-center text-gray-500 py-8">Loading documents…</p>
              ) : documents.length > 0 ? (
                <div className="space-y-2">
                  {documents.map(doc => (
                    <div
                      key={doc.id}
                      className="flex items-center p-4 hover:bg-gray-50 rounded-lg transition-colors duration-200"
                    >
                      <div className="p-2 bg-primary-50 rounded-lg mr-4">
                        <FileText size={24} className="text-primary-600" />
                      </div>

                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2">
                          <h3 className="text-sm font-medium text-gray-900 truncate">
                            {doc.name}
                          </h3>
                          {doc.shared && (
                            <Badge variant="secondary" size="sm">Shared</Badge>
                          )}
                        </div>

                        <div className="flex items-center gap-4 mt-1 text-sm text-gray-500">
                          <span>{doc.type.toUpperCase()}</span>
                          <span>{doc.size}</span>
                          <span>Modified {new Date(doc.lastModified).toLocaleDateString()}</span>
                        </div>
                      </div>

                      <div className="flex items-center gap-2 ml-4">
                        <Button
                          variant="ghost"
                          size="sm"
                          className="p-2"
                          aria-label="Download"
                          onClick={() => handleDownload(doc)}
                        >
                          <Download size={18} />
                        </Button>

                        <Button
                          variant="ghost"
                          size="sm"
                          className="p-2 text-error-600 hover:text-error-700"
                          aria-label="Delete"
                          onClick={() => handleDelete(doc.id)}
                        >
                          <Trash2 size={18} />
                        </Button>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-center text-gray-500 py-8">No documents uploaded yet.</p>
              )}
            </CardBody>
          </Card>
        </div>
      </div>
    </div>
  );
};
