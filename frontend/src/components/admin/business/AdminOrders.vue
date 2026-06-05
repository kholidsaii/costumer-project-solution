<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const BASE_URL = 'http://localhost:8000';
const orders = ref<any[]>([]);
const loading = ref(true);

const fetchOrders = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('access_token');
    const response = await axios.get(`${BASE_URL}/api/admin/orders`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    orders.value = response.data;
  } catch (error) { console.error(error); } 
  finally { loading.value = false; }
};

onMounted(fetchOrders);

const updateStatus = async (id: number, action: 'approve' | 'reject') => {
  if (!confirm(`Apakah Anda yakin ingin ${action} order ini?`)) return;
  const token = localStorage.getItem('access_token');
  try {
    await axios.post(`${BASE_URL}/api/admin/orders/${id}/${action}`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    });
    alert(`Order berhasil di-${action}!`);
    fetchOrders();
  } catch (error) { alert('Gagal memproses aksi.'); }
};

const formatPrice = (price: any) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(Number(price));
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <div>
        <h2 class="text-lg font-black text-slate-800">Manajemen Order</h2>
        <p class="text-xs text-slate-500 font-bold mt-1">Konfirmasi pesanan dari pelanggan.</p>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-black">
            <th class="p-4">ID Invoice</th>
            <th class="p-4">Customer</th>
            <th class="p-4">Produk</th>
            <th class="p-4">Total</th>
            <th class="p-4">Status</th>
            <th class="p-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <tr v-if="loading"><td colspan="6" class="p-8 text-center text-slate-400 font-bold">Memuat...</td></tr>
          <tr v-else-if="orders.length === 0"><td colspan="6" class="p-8 text-center text-slate-400 font-bold">Kosong</td></tr>
          <tr v-else v-for="order in orders" :key="order.id" class="border-b border-slate-100 hover:bg-slate-50">
            <td class="p-4 font-bold text-slate-600">{{ order.order_number }}</td>
            <td class="p-4 font-black text-slate-800">{{ order.user?.name }}</td>
            <td class="p-4 text-slate-600">{{ order.product?.name }}</td>
            <td class="p-4 font-black text-indigo-600">{{ formatPrice(order.total_amount) }}</td>
            <td class="p-4">
              <span :class="['px-2 py-1 rounded text-[10px] font-black uppercase', order.status === 'pending' ? 'bg-amber-100 text-amber-700' : (order.status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700')]">
                {{ order.status }}
              </span>
            </td>
            <td class="p-4 text-right space-x-2">
              <template v-if="order.status === 'pending'">
                <button @click="updateStatus(order.id, 'approve')" class="text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded border border-emerald-200 text-xs font-bold hover:bg-emerald-100">Approve</button>
                <button @click="updateStatus(order.id, 'reject')" class="text-red-600 bg-red-50 px-3 py-1.5 rounded border border-red-200 text-xs font-bold hover:bg-red-100">Reject</button>
              </template>
              <span v-else class="text-xs text-slate-400 font-bold">Selesai</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>