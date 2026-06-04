<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

// 1. Definisikan struktur datanya
interface Order {
  id: number;
  order_number: string;
  total_amount: number;
  status: string;
  created_at: string;
  product: {
    name: string;
    category: string;
  };
}

// 2. Terapkan interface tersebut ke dalam ref
const orders = ref<Order[]>([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const token = localStorage.getItem('auth_token');
    // Memanggil API yang sudah kita buat sebelumnya
    const response = await axios.get('/api/customer/orders', {
      headers: { Authorization: `Bearer ${token}` }
    });
    orders.value = response.data;
  } catch (error) {
    console.error('Gagal mengambil data pesanan', error);
  } finally {
    loading.value = false;
  }
});

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(price);
};
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <div>
        <h2 class="text-lg font-black text-slate-800">Pesanan Saya</h2>
        <p class="text-xs text-slate-500 font-bold mt-1">Daftar produk yang telah Anda pesan.</p>
      </div>
    </div>

    <div class="p-6">
      <div v-if="loading" class="text-center py-10 text-sm font-bold text-slate-400">
        Memuat pesanan...
      </div>
      
      <div v-else-if="orders.length === 0" class="text-center py-10 text-sm font-bold text-slate-400">
        Belum ada pesanan. Silakan lihat katalog produk di halaman depan.
      </div>

      <div v-else class="space-y-4">
        <div v-for="order in orders" :key="order.id" class="border border-slate-100 rounded-xl p-4 flex flex-col md:flex-row justify-between md:items-center gap-4 hover:shadow-md transition">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center font-black text-xl border border-blue-100">
              {{ order.product.category.charAt(0) }}
            </div>
            <div>
              <p class="text-xs text-slate-400 font-bold mb-0.5">INV-{{ order.order_number }}</p>
              <h3 class="text-sm font-black text-slate-800">{{ order.product.name }}</h3>
              <p class="text-xs text-slate-500 mt-1">{{ order.created_at.substring(0, 10) }}</p>
            </div>
          </div>
          
          <div class="flex items-center justify-between md:flex-col md:items-end gap-2">
            <span class="text-sm font-black text-indigo-600">{{ formatPrice(order.total_amount) }}</span>
            <span :class="[
              'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider',
              order.status === 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600'
            ]">
              {{ order.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>