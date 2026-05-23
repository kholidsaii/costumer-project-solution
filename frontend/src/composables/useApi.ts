// src/composables/useApi.ts
import { ref } from 'vue';
import api from '@/api/axios'; // Pastikan path ini benar (naik 1 folder ke src, lalu ke api)

export function useApi<T>() {
  const data = ref<T | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const request = async (method: 'get' | 'post' | 'put' | 'delete', url: string, payload?: any) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api[method](url, payload);
      data.value = response.data;
      return response.data;
    } catch (err: any) {
      error.value = err.message;
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  return { data, isLoading, error, request };
}