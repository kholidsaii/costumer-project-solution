// src/api/axios.ts
import axios from 'axios';

const api = axios.create({
  // Pastikan port ini sama dengan tempat Anda menjalankan Laravel (php artisan serve)
  baseURL: 'http://localhost:8000/api',
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

export default api;