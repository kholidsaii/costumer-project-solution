// src/api/axios.ts
import axios from 'axios';

const api = axios.create({
  // Menggunakan Environment Variable dari Vite
  // Jika file .env tidak terbaca, ia akan otomatis fallback ke localhost
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
});

// --- TAMBAHKAN BAGIAN INI ---
// Otomatis menempelkan token ke setiap request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
// ---------------------------
// Tambahkan ini di bawah request interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token kedaluwarsa/tidak valid
      localStorage.clear();
      window.location.href = '/customer/login';
    }
    return Promise.reject(error);
  }
);
export default api;