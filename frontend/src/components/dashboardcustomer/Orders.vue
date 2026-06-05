<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const BASE_URL = 'http://localhost:8000';

interface Order {
  id: number;
  order_number: string;
  total_amount: number;
  status: string;
  created_at: string;
  product: { name: string; category: string; };
}

const orders = ref<Order[]>([]);
const loading = ref(true);
const processingId = ref<number | null>(null);

const fetchOrders = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('access_token');
    const response = await axios.get(`${BASE_URL}/api/customer/orders`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    orders.value = response.data;
  } catch (error) {
    console.error('Gagal mengambil data pesanan', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchOrders);

// FUNGSI BAYAR PESANAN
const simulatePayment = async (orderId: number) => {
  processingId.value = orderId;
  const token = localStorage.getItem('access_token');
  try {
    await axios.post(`${BASE_URL}/api/customer/orders/${orderId}/pay`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    });
    alert('Pembayaran sukses! Silakan cek menu Tagihan untuk melihat jadwal perpanjangan tahun depan.');
    fetchOrders(); // Refresh status order
  } catch (error) {
    alert('Gagal memproses pembayaran.');
  } finally {
    processingId.value = null;
  }
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(price);
};
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <div>
        <h2 class="text-lg font-black text-slate-800">Pesanan Saya</h2>
        <p class="text-xs text-slate-500 font-bold mt-1">Daftar produk yang Anda pesan dan status pembayarannya.</p>
      </div>
    </div>

    <div class="p-6">
      <div v-if="loading" class="text-center py-10 text-sm font-bold text-slate-400">Memuat pesanan...</div>
      <div v-else-if="orders.length === 0" class="text-center py-10 text-sm font-bold text-slate-400">Belum ada pesanan aktif.</div>
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
            <div class="flex items-center gap-2">
              <span :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider', order.status === 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600']">
                {{ order.status === 'pending' ? 'Menunggu Pembayaran' : 'Lunas' }}
              </span>
              <!-- Tombol Bayar Muncul Jika Pending -->
              <button 
                v-if="order.status === 'pending'" 
                @click="simulatePayment(order.id)" 
                :disabled="processingId === order.id"
                class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] px-3 py-1 rounded-full font-black tracking-wider transition-colors disabled:opacity-50"
              >
                {{ processingId === order.id ? 'Proses...' : 'Bayar Sekarang' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>