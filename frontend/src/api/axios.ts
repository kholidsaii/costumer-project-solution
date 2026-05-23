// src/api/axios.ts
import axios from 'axios';

const api = axios.create({
  // Pastikan port ini sama dengan tempat Anda menjalankan Laravel (php artisan serve)
  baseURL: 'http://localhost:8000/api', 
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export default api;