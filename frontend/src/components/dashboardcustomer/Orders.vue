<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const BASE_URL = 'http://localhost:8000';

interface Order {
  id: number; order_number: string; total_amount: number; status: string; created_at: string;
  product: { name: string; category: string; };
  reject_reason?: string; software_link?: string; software_username?: string; software_password?: string;
  payment_proof?: string; // Tambahkan ini
}

const orders = ref<Order[]>([]);
const loading = ref(true);

// --- STATE UPLOAD PAYMENT PROOF ---
const isPaymentModalOpen = ref(false);
const selectedOrderId = ref<number | null>(null);
const submittingPayment = ref(false);
const paymentForm = ref({ method: 'BCA', proof: null as File | null });

const fetchOrders = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('access_token');
    const response = await axios.get(`${BASE_URL}/api/customer/orders`, { headers: { Authorization: `Bearer ${token}` }});
    orders.value = response.data;
  } catch (error) { console.error(error); } finally { loading.value = false; }
};

onMounted(fetchOrders);

const openPaymentModal = (orderId: number) => {
  selectedOrderId.value = orderId;
  paymentForm.value = { method: 'BCA', proof: null };
  isPaymentModalOpen.value = true;
};

const submitPayment = async () => {
  if (!paymentForm.value.proof) return alert('Pilih file bukti transfer!');
  submittingPayment.value = true;
  
  const formData = new FormData();
  formData.append('payment_method', paymentForm.value.method);
  formData.append('payment_proof', paymentForm.value.proof);

  const token = localStorage.getItem('access_token');
  try {
    await axios.post(`${BASE_URL}/api/customer/orders/${selectedOrderId.value}/upload-proof`, formData, {
      headers: { 'Content-Type': 'multipart/form-data', Authorization: `Bearer ${token}` }
    });
    alert('Bukti pembayaran berhasil diunggah! Menunggu verifikasi admin.');
    isPaymentModalOpen.value = false;
    fetchOrders();
  } catch (error) { alert('Gagal mengirim bukti pembayaran.'); } finally { submittingPayment.value = false; }
};

const formatPrice = (price: any) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(price) || 0);
</script>

<template>
  <div class="mb-10">
    <div class="mb-6"><h1 class="text-2xl font-black text-slate-800">Riwayat Pesanan</h1><p class="text-sm font-bold text-slate-500">Pantau dan selesaikan pembayaran layanan Anda.</p></div>
    <div v-if="loading" class="text-center py-20 text-slate-400 font-bold animate-pulse">Memuat data pesanan...</div>
    <div v-else-if="orders.length === 0" class="bg-white rounded-2xl p-10 text-center border border-slate-200 shadow-sm"><span class="text-6xl mb-4 block opacity-50">🛒</span><p class="text-slate-500 font-bold mb-4">Anda belum memiliki riwayat pesanan.</p></div>

    <div v-else class="space-y-4">
      <div v-for="order in orders" :key="order.id" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 transition hover:shadow-md">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-xl shrink-0">{{ order.product.category === 'Software' ? '💻' : '📦' }}</div>
          <div>
            <p class="text-xs text-slate-400 font-bold mb-0.5">INV-{{ order.order_number }}</p>
            <h3 class="text-sm font-black text-slate-800">{{ order.product.name }}</h3>
            <p class="text-xs text-slate-500 mt-1">{{ order.created_at.substring(0, 10) }}</p>
          </div>
        </div>
        
        <div class="flex items-center justify-between md:flex-col md:items-end gap-2">
          <span class="text-sm font-black text-indigo-600">{{ formatPrice(order.total_amount) }}</span>
          <div class="flex items-center gap-2">
            
            <span v-if="order.status === 'awaiting_ongkir'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-700">Menunggu Harga Ongkir Admin</span>
            <span v-else-if="order.status === 'pending' && !order.payment_proof" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-700">Menunggu Pembayaran</span>
            <span v-else-if="order.status === 'pending' && order.payment_proof" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-100 text-blue-700">Menunggu Verifikasi Admin</span>
            <span v-else-if="order.status === 'paid'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700">Lunas / Disetujui</span>
            <span v-else-if="order.status === 'cancelled'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700">Dibatalkan</span>

            <button v-if="order.status === 'pending' && !order.payment_proof" @click="openPaymentModal(order.id)" class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] px-3 py-1.5 rounded-full font-black uppercase tracking-wider transition shadow-sm">
              Upload Bukti Transfer
            </button>
          </div>
        </div>

        <div v-if="order.status === 'cancelled' && order.reject_reason" class="mt-3 text-xs bg-red-50 text-red-700 border border-red-100 rounded-xl p-3 flex flex-col gap-1 w-full max-w-xl">
          <span class="font-black text-[10px] uppercase tracking-wider bg-red-200 text-red-800 px-1.5 py-0.5 rounded w-max">Catatan Penolakan Admin:</span>
          <p class="font-medium leading-relaxed">{{ order.reject_reason }}</p>
        </div>
      </div>
    </div>

    <div v-if="isPaymentModalOpen" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4">
      <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-2xl">
        <h2 class="font-black text-lg text-slate-800 mb-4">Pembayaran Tagihan</h2>
        <form @submit.prevent="submitPayment" class="space-y-4">
          <div>
            <label class="text-xs font-bold block mb-1">Pilih Rekening Tujuan</label>
            <select v-model="paymentForm.method" required class="w-full border rounded-lg p-2.5 bg-slate-50 text-sm font-bold">
              <option value="BCA">BCA - 123456789 a.n Kerjapro</option>
              <option value="MANDIRI">MANDIRI - 987654321 a.n Kerjapro</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold block mb-1">Upload Bukti Transfer (Foto/Screenshot)</label>
            <input type="file" required accept="image/*" @change="(e: any) => paymentForm.proof = e.target.files[0]" class="w-full border rounded-lg p-1 text-sm bg-white">
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="isPaymentModalOpen = false" class="bg-slate-100 px-4 py-2 rounded-lg font-bold text-sm hover:bg-slate-200">Batal</button>
            <button type="submit" :disabled="submittingPayment" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-sm flex-1 disabled:opacity-50">
              {{ submittingPayment ? 'Mengirim...' : 'Kirim Bukti Pembayaran' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>