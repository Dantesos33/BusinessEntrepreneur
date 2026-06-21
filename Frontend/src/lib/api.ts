import axios from 'axios';

export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export const api = axios.create({
  baseURL: `${API_BASE_URL}/api`,
  withCredentials: true, // Crucial for cross-port cookies
  withXSRFToken: true,   // Automatically reads and attaches X-XSRF-TOKEN
  headers: {
    Accept: 'application/json',
  },
});

// 💡 Interceptor to cleanly handle the initial /user check without breaking React
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // If the check auth status call fails on load, let AuthContext handle it cleanly
    if (error.config && error.config.url === '/user' && error.response?.status === 401) {
      return Promise.resolve({ ...error.response, status: 401 });
    }
    return Promise.reject(error);
  }
);

export async function ensureCsrfCookie(): Promise<void> {
  await axios.get(`${API_BASE_URL}/sanctum/csrf-cookie`, { withCredentials: true });
}

export function extractErrorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data;
    if (data?.errors) {
      const firstField = Object.values(data.errors)[0];
      if (Array.isArray(firstField) && firstField.length > 0) {
        return firstField[0] as string;
      }
    }
    if (data?.message) {
      return data.message as string;
    }
  }
  return (error as Error)?.message || 'Something went wrong';
}
