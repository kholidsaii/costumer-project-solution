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

const updateStatus = async (order: any, action: 'approve' | 'reject') => {
  let requestData = {};
  
  if (action === 'approve') {
    if (!confirm('Apakah Anda yakin ingin menyetujui pesanan ini?')) return;
    
    // Minta link akses jika ini adalah pesanan software
    if (order.product?.product_type === 'software') {
      const link = prompt('WAJIB: Masukkan Link Akses/Lisensi Software (Google Drive/Repo/Dll) untuk diberikan ke Pelanggan:');
      if (link === null) return; // Batal jika admin klik cancel
      if (link === '') return alert('Proses dibatalkan: Link akses software tidak boleh kosong!');
      requestData = { software_link: link };
    }
  } else {
    if (!confirm('Apakah Anda yakin menolak pesanan ini? Jika ini pesanan Fisik, stok akan otomatis dikembalikan.')) return;
  }

  const token = localStorage.getItem('access_token');
  try {
    await axios.post(`${BASE_URL}/api/admin/orders/${order.id}/${action}`, requestData, {
      headers: { Authorization: `Bearer ${token}` }
    });
    alert(`Order berhasil di-${action}!`);
    fetchOrders(); // Refresh data
  } catch (error) { 
    alert('Gagal memproses aksi. Cek koneksi atau console log.'); 
  }
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
          <tr v-for="order in orders" :key="order.id" class="border-b border-slate-100 hover:bg-slate-50">
            <td class="p-4 font-bold text-slate-600">{{ order.order_number }}</td>
            <td class="p-4 font-black text-slate-800">{{ order.user?.name }}</td>
            <td class="p-4">
              <p class="text-slate-600">{{ order.product?.name }}</p>
              
              <div v-if="order.payment_proof" class="mt-2">
                <a :href="`${BASE_URL}/storage/${order.payment_proof}`" target="_blank" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded font-bold hover:underline">
                  Lihat Bukti TF ({{ order.payment_method }})
                </a>
              </div>
              <div v-if="order.shipping_address" class="mt-2 text-[10px] text-slate-500 bg-slate-100 p-2 rounded">
                <strong>Pengiriman:</strong> {{ order.courier }} (Rp {{ order.shipping_cost }})<br/>
                {{ order.shipping_address }}
              </div>

            </td>
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