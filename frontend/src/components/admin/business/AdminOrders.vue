<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const BASE_URL = 'http://localhost:8000';

interface Order {
  id: number; order_number: string; total_amount: number; status: string;
  software_link?: string; software_username?: string; software_password?: string; reject_reason?: string;
  payment_method?: string; payment_proof?: string; shipping_cost?: number; courier?: string; shipping_address?: string;
  user?: { name: string }; product?: { name: string; product_type: string };
}

const orders = ref<Order[]>([]);
const loading = ref(true);

const isActionModalOpen = ref(false);
const modalAction = ref<'setup' | 'reject' | 'ongkir'>('setup');
const selectedOrder = ref<Order | null>(null);
const submittingAction = ref(false);

const actionForm = ref({ software_link: '', software_username: '', software_password: '', reject_reason: '', shipping_cost: 0 });

const fetchOrders = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('access_token');
    const response = await axios.get(`${BASE_URL}/api/admin/orders`, { headers: { Authorization: `Bearer ${token}` }});
    orders.value = response.data;
  } catch (error) { console.error(error); } finally { loading.value = false; }
};

onMounted(fetchOrders);

const openActionModal = (order: Order, action: 'setup' | 'reject' | 'ongkir') => {
  selectedOrder.value = order;
  modalAction.value = action;
  
  if (action === 'setup') {
    actionForm.value = { ...actionForm.value, software_link: order.software_link || '', software_username: order.software_username || '', software_password: order.software_password || '' };
  } else if (action === 'ongkir') {
    actionForm.value = { ...actionForm.value, shipping_cost: 0 };
  } else {
    actionForm.value = { ...actionForm.value, reject_reason: order.reject_reason || '' };
  }
  isActionModalOpen.value = true;
};

const submitAction = async () => {
  if (!selectedOrder.value) return;
  submittingAction.value = true;
  const token = localStorage.getItem('access_token');
  const id = selectedOrder.value.id;
  
  try {
    if (modalAction.value === 'setup') {
      await axios.post(`${BASE_URL}/api/admin/orders/${id}/setup-software`, { software_link: actionForm.value.software_link, software_username: actionForm.value.software_username, software_password: actionForm.value.software_password }, { headers: { Authorization: `Bearer ${token}` }});
      alert('Data Setup Berhasil Diperbarui!');
    } else if (modalAction.value === 'ongkir') {
      await axios.post(`${BASE_URL}/api/admin/orders/${id}/input-ongkir`, { shipping_cost: actionForm.value.shipping_cost }, { headers: { Authorization: `Bearer ${token}` }});
      alert('Harga Ongkir Toko berhasil ditetapkan! Pelanggan kini dapat membayar.');
    } else {
      await axios.post(`${BASE_URL}/api/admin/orders/${id}/reject`, { reject_reason: actionForm.value.reject_reason }, { headers: { Authorization: `Bearer ${token}` }});
      alert('Pesanan ditolak.');
    }
    isActionModalOpen.value = false; fetchOrders();
  } catch (error: any) { alert(error.response?.data?.message || 'Gagal memproses.'); } finally { submittingAction.value = false; }
};

// Fungsi Hapus Pesanan Permanen
const deleteOrder = async (id: number) => {
  if (!confirm('PERINGATAN: Apakah Anda yakin ingin menghapus permanen data pesanan ini? Aksi ini tidak dapat dibatalkan.')) return;
  
  const token = localStorage.getItem('access_token');
  try {
    await axios.delete(`${BASE_URL}/api/admin/orders/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    alert('Pesanan berhasil dihapus secara permanen.');
    fetchOrders(); // Refresh tabel
  } catch (error: any) {
    alert(error.response?.data?.message || 'Gagal menghapus pesanan.');
  }
};

const handleApproveDirect = async (order: Order) => {
  if (!confirm(`Setujui pesanan INV-${order.order_number} ini?`)) return;
  const token = localStorage.getItem('access_token');
  try {
    await axios.post(`${BASE_URL}/api/admin/orders/${order.id}/approve`, {}, { headers: { Authorization: `Bearer ${token}` }});
    alert('Pesanan berhasil disetujui!'); fetchOrders();
  } catch (error: any) { alert(error.response?.data?.message || 'Gagal menyetujui pesanan.'); }
};

const formatPrice = (price: any) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(price) || 0);
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-xl font-black text-slate-800">Daftar Transaksi</h1>
      <p class="text-xs text-slate-400 font-bold">Kelola pesanan, verifikasi pembayaran, input ongkir, dan setup akun.</p>
    </div>

    <div v-if="loading" class="text-center py-10 font-bold text-slate-400">Memuat transaksi...</div>

    <div v-else class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[800px]">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase">
          <tr><th class="p-4">No. Invoice</th><th class="p-4">Pelanggan</th><th class="p-4">Detail Produk</th><th class="p-4">Total Amount</th><th class="p-4">Status</th><th class="p-4 text-right">Aksi Admin</th></tr>
        </thead>
        <tbody class="text-sm">
          <tr v-for="order in orders" :key="order.id" class="border-b border-slate-100 hover:bg-slate-50">
            <td class="p-4 font-bold text-slate-600">{{ order.order_number }}</td>
            <td class="p-4 font-black text-slate-800">{{ order.user?.name }}</td>
            <td class="p-4">
              <p class="text-slate-600 font-bold">{{ order.product?.name }}</p>
              
              <div class="mt-2 text-xs bg-slate-50 p-2 border rounded border-slate-200 text-slate-700 space-y-1">
                <p v-if="order.courier">🚚 <strong>Kurir:</strong> {{ order.courier }} (Ongkir: Rp {{ order.shipping_cost }})</p>
                <p v-if="order.shipping_address">📍 <strong>Alamat:</strong> {{ order.shipping_address }}</p>
                <p v-if="order.payment_proof">💳 <strong>Bukti Transfer ({{ order.payment_method }}):</strong> 
                  <a :href="`${BASE_URL}/storage/${order.payment_proof}`" target="_blank" class="text-blue-600 underline font-bold bg-blue-100 px-2 py-0.5 rounded">Lihat Foto</a>
                </p>
              </div>

              <div v-if="order.software_link" class="mt-2 text-xs bg-indigo-50 p-2 border rounded border-indigo-100 text-slate-700">
                <p class="font-black text-indigo-600 uppercase tracking-wide mb-1">Akses Akun:</p>
                <p>🔗 <a :href="order.software_link" target="_blank" class="text-blue-600 underline">{{ order.software_link }}</a></p>
                <p>👤 <span class="font-mono">{{ order.software_username }}</span> | 🔑 <span class="font-mono">{{ order.software_password }}</span></p>
              </div>
            </td>
            <td class="p-4 font-black text-indigo-600">{{ formatPrice(order.total_amount) }}</td>
            <td class="p-4">
              <span :class="['px-2 py-1 rounded text-[10px] font-black uppercase', order.status === 'awaiting_ongkir' ? 'bg-purple-100 text-purple-700' : (order.status === 'pending' && !order.payment_proof ? 'bg-amber-100 text-amber-700' : (order.status === 'pending' && order.payment_proof ? 'bg-blue-100 text-blue-700' : (order.status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700')))]">
                {{ order.status === 'awaiting_ongkir' ? 'Input Ongkir' : (order.status === 'pending' && !order.payment_proof ? 'Tunggu TF' : (order.status === 'pending' && order.payment_proof ? 'Verifikasi TF' : order.status)) }}
              </span>
            </td>
            <td class="p-4 text-right space-y-1">
              <div class="flex flex-wrap justify-end gap-1.5">
                
                <button v-if="order.product?.product_type === 'software'" @click="openActionModal(order, 'setup')" class="text-blue-600 bg-blue-50 px-2.5 py-1.5 rounded border border-blue-200 text-[11px] font-bold hover:bg-blue-100">
                  {{ order.software_link ? 'Edit Setup' : 'Setup' }}
                </button>

                <template v-if="order.status === 'awaiting_ongkir'">
                  <button @click="openActionModal(order, 'ongkir')" class="text-purple-600 bg-purple-50 px-2.5 py-1.5 rounded border border-purple-200 text-[11px] font-bold hover:bg-purple-100">Input Ongkir</button>
                  <button @click="openActionModal(order, 'reject')" class="text-red-600 bg-red-50 px-2.5 py-1.5 rounded border border-red-200 text-[11px] font-bold hover:bg-red-100">Reject</button>
                </template>

                <template v-else-if="order.status === 'pending'">
                  <button @click="handleApproveDirect(order)" :disabled="order.product?.product_type === 'software' && !order.software_link" :class="[order.product?.product_type === 'software' && !order.software_link ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border-emerald-200', 'px-2.5 py-1.5 rounded border text-[11px] font-bold']">Approve</button>
                  <button @click="openActionModal(order, 'reject')" class="text-red-600 bg-red-50 px-2.5 py-1.5 rounded border border-red-200 text-[11px] font-bold hover:bg-red-100">Reject</button>
                </template>
                
                <span v-else-if="order.status === 'paid'" class="text-[11px] text-emerald-600 font-bold px-2 block w-full mt-1">✓ Lunas</span>
                <span v-else-if="order.status === 'cancelled'" class="text-[11px] text-red-500 font-bold px-2 block w-full mt-1">✕ Dibatalkan</span>
              </div>
              <div class="mt-2 flex justify-end">
                <button @click="deleteOrder(order.id)" class="text-slate-400 hover:text-white bg-white hover:bg-red-500 border border-slate-200 hover:border-red-500 px-2 py-1 rounded text-[10px] font-black transition-colors flex items-center gap-1">
                  🗑️ Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isActionModalOpen" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4">
      <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-2xl">
        <h2 class="font-black text-lg text-slate-800 mb-1">{{ modalAction === 'setup' ? 'Setup Software' : (modalAction === 'ongkir' ? 'Tentukan Harga Ongkir' : 'Tolak Transaksi') }}</h2>
        <p class="text-xs text-slate-400 font-bold mb-4">Invoice: {{ selectedOrder?.order_number }}</p>

        <form @submit.prevent="submitAction" class="space-y-4">
          <template v-if="modalAction === 'setup'">
            <div><label class="text-xs font-bold block mb-1">URL Website</label><input v-model="actionForm.software_link" type="url" required class="w-full border rounded-lg p-2.5 text-sm bg-slate-50"></div>
            <div><label class="text-xs font-bold block mb-1">Username / Email</label><input v-model="actionForm.software_username" type="text" required class="w-full border rounded-lg p-2.5 text-sm bg-slate-50"></div>
            <div><label class="text-xs font-bold block mb-1">Password</label><input v-model="actionForm.software_password" type="text" required class="w-full border rounded-lg p-2.5 text-sm bg-slate-50"></div>
          </template>

          <template v-if="modalAction === 'ongkir'">
            <div>
              <label class="text-xs font-bold block mb-1">Harga Ongkir Toko (Rp)</label>
              <input v-model="actionForm.shipping_cost" type="number" min="0" required class="w-full border rounded-lg p-2.5 text-sm bg-slate-50 font-bold text-indigo-600">
              <p class="text-[10px] mt-1 text-slate-500">Harga ini akan ditambahkan ke Total Tagihan pelanggan.</p>
            </div>
          </template>

          <template v-if="modalAction === 'reject'">
            <div><label class="text-xs font-bold block mb-1">Alasan Penolakan</label><textarea v-model="actionForm.reject_reason" required rows="3" class="w-full border rounded-lg p-2.5 text-sm bg-slate-50"></textarea></div>
          </template>

          <div class="flex gap-2 pt-3 border-t border-slate-100">
            <button type="button" @click="isActionModalOpen = false" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg font-bold text-sm hover:bg-slate-200">Batal</button>
            <button type="submit" :disabled="submittingAction" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-sm flex-1 disabled:opacity-50 hover:bg-blue-700">Simpan Data</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>