<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const BASE_URL = 'http://localhost:8000';
const activeTab = ref('transaksi');
const members = ref<any[]>([]);
const transactions = ref<any[]>([]);
const loading = ref(true);

const fetchData = async () => {
  loading.value = true;
  const token = localStorage.getItem('access_token');
  try {
    const resMem = await axios.get(`${BASE_URL}/api/admin/member/active`, { headers: { Authorization: `Bearer ${token}` }});
    members.value = resMem.data;
    const resTrx = await axios.get(`${BASE_URL}/api/admin/member/transactions`, { headers: { Authorization: `Bearer ${token}` }});
    transactions.value = resTrx.data;
  } catch (error) { console.error(error); } finally { loading.value = false; }
};

onMounted(fetchData);

const handleAction = async (id: number, action: 'approve' | 'reject') => {
  let reason = '';
  if (action === 'reject') {
    reason = prompt('Alasan menolak bukti transfer upgrade ini:') || '';
    if (!reason) return;
  } else {
    if(!confirm('Setujui transaksi upgrade ini? Member akan langsung aktif.')) return;
  }
  
  const token = localStorage.getItem('access_token');
  try {
    await axios.post(`${BASE_URL}/api/admin/member/transactions/${id}/${action}`, { reject_reason: reason }, { headers: { Authorization: `Bearer ${token}` }});
    alert(`Transaksi berhasil di-${action}.`);
    fetchData();
  } catch (error) { alert('Gagal memproses transaksi.'); }
};
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="flex border-b border-slate-100 bg-slate-50">
      <button @click="activeTab = 'transaksi'" :class="activeTab === 'transaksi' ? 'border-b-2 border-blue-600 text-blue-700 font-black' : 'text-slate-500 font-bold hover:bg-slate-100'" class="px-6 py-4 text-sm transition">Transaksi Upgrade</button>
      <button @click="activeTab = 'member'" :class="activeTab === 'member' ? 'border-b-2 border-blue-600 text-blue-700 font-black' : 'text-slate-500 font-bold hover:bg-slate-100'" class="px-6 py-4 text-sm transition">List Premium Member</button>
    </div>

    <div v-if="activeTab === 'transaksi'" class="p-0 overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase">
          <tr><th class="p-4">Invoice</th><th class="p-4">Customer</th><th class="p-4">Paket Pilihan</th><th class="p-4">Bukti TF</th><th class="p-4">Status</th><th class="p-4">Aksi</th></tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="p-6 text-center text-slate-400 font-bold">Memuat...</td></tr>
          <tr v-else v-for="trx in transactions" :key="trx.id" class="border-b border-slate-100 hover:bg-slate-50">
            <td class="p-4 font-bold text-slate-600">{{ trx.transaction_number }}</td>
            <td class="p-4 font-black text-slate-800">{{ trx.user?.name }}</td>
            <td class="p-4">
              <p class="font-bold text-blue-700">{{ trx.tier?.name }}</p>
              <p class="text-[10px] text-slate-500">Rp {{ Number(trx.amount).toLocaleString('id-ID') }}</p>
            </td>
            <td class="p-4"><a :href="`${BASE_URL}/storage/${trx.payment_proof}`" target="_blank" class="text-blue-500 underline text-xs font-bold">Lihat Bukti</a></td>
            <td class="p-4">
              <span :class="trx.status === 'pending' ? 'bg-amber-100 text-amber-700' : (trx.status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700')" class="px-2 py-1 rounded text-[10px] font-black uppercase">{{ trx.status }}</span>
            </td>
            <td class="p-4 space-x-2">
              <template v-if="trx.status === 'pending'">
                <button @click="handleAction(trx.id, 'approve')" class="text-emerald-600 bg-emerald-50 px-3 py-1 rounded text-xs font-bold border border-emerald-200 hover:bg-emerald-100">Approve</button>
                <button @click="handleAction(trx.id, 'reject')" class="text-red-600 bg-red-50 px-3 py-1 rounded text-xs font-bold border border-red-200 hover:bg-red-100">Reject</button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="activeTab === 'member'" class="p-0 overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase">
          <tr><th class="p-4">Customer</th><th class="p-4">Email / HP</th><th class="p-4">Paket Aktif</th><th class="p-4">Tgl Kedaluwarsa</th></tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="4" class="p-6 text-center text-slate-400 font-bold">Memuat...</td></tr>
          <tr v-else v-for="mem in members" :key="mem.id" class="border-b border-slate-100 hover:bg-slate-50">
            <td class="p-4 font-black text-slate-800">{{ mem.name }}</td>
            <td class="p-4 text-slate-600 text-xs">{{ mem.email }}<br/>{{ mem.phone }}</td>
            <td class="p-4 font-bold text-amber-600">{{ mem.tier?.name }}</td>
            <td class="p-4 font-black text-slate-600">{{ mem.tier_expires_at ? mem.tier_expires_at : 'Selamanya (Lifetime)' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>