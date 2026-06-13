<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../api/axios';

const user = ref<any>(null);
const softwareOrders = ref<any[]>([]);
const availableTiers = ref<any[]>([]);
const loading = ref(true);

const isUpgradeModalOpen = ref(false);
const selectedTierId = ref<number | null>(null);
const paymentForm = ref({ method: 'BCA', proof: null as File | null });
const submitting = ref(false);

onMounted(async () => {
  try {
    const resUser = await api.get('/customer/me');
    user.value = resUser.data;

    const resOrders = await api.get('/customer/orders');
    softwareOrders.value = resOrders.data.filter((o: any) => o.product?.product_type === 'software' && o.status === 'paid');
    
    // Tarik daftar paket premium
    const resTiers = await api.get('/tiers');
    availableTiers.value = resTiers.data.filter((t: any) => t.price > 0); 
  } catch (error) { console.error(error); }
  finally { loading.value = false; }
});

const submitUpgrade = async () => {
  if (!paymentForm.value.proof || !selectedTierId.value) return alert('Pilih paket dan masukkan bukti bayar!');
  submitting.value = true;
  
  const formData = new FormData();
  formData.append('tier_id', selectedTierId.value.toString());
  formData.append('payment_method', paymentForm.value.method);
  formData.append('payment_proof', paymentForm.value.proof);

  try {
    const token = localStorage.getItem('access_token');
    await api.post('/customer/member/upgrade', formData, { headers: { 'Content-Type': 'multipart/form-data', Authorization: `Bearer ${token}` }});
    alert('Permintaan Upgrade Berhasil Dikirim! Menunggu verifikasi admin.');
    isUpgradeModalOpen.value = false;
  } catch (error) { alert('Gagal memproses upgrade.'); }
  finally { submitting.value = false; }
};
</script>

<template>
  <div class="mb-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h2 class="text-xl font-black text-slate-800">Status Member Anda</h2>
        <p class="text-xs font-bold text-slate-500 mt-1">Tier saat ini memengaruhi limit dan akses produk Anda.</p>
      </div>
      <button @click="isUpgradeModalOpen = true" class="bg-gradient-to-r from-amber-400 to-yellow-600 text-white shadow-lg shadow-amber-500/30 px-6 py-2.5 rounded-xl font-black text-sm hover:scale-105 transition transform">
        🚀 Upgrade / Perpanjang Paket
      </button>
    </div>

    <div v-if="loading" class="text-slate-500 font-bold animate-pulse p-6">Memuat info...</div>
    <div v-else-if="user" class="space-y-6 mb-6">
      <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
        <div>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Level Member</p>
          <p class="text-2xl font-black text-blue-700 capitalize">{{ user.tier_name }}</p>
          <p v-if="user.tier_expires_at" class="text-xs font-bold text-red-500 mt-1">Berlaku s/d: {{ user.tier_expires_at }}</p>
          <p v-else class="text-xs font-bold text-emerald-500 mt-1">Akses Selamanya (Lifetime)</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="border border-slate-200 p-4 rounded-xl">
          <p class="font-black text-slate-800">Limit Download Digital</p>
          <div class="mt-2 text-sm text-slate-600">
            Terpakai: <strong class="text-indigo-600">{{ user.digital_downloads_count }}</strong> / 
            <span class="font-bold">{{ user.tier?.digital_limit >= 999999 ? 'Unlimited' : user.tier?.digital_limit + 'x' }}</span>
          </div>
        </div>
        <div class="border border-slate-200 p-4 rounded-xl">
          <p class="font-black text-slate-800">Hak Akses Software</p>
          <div class="mt-2 text-sm">
            <span v-if="user.tier?.software_access" class="bg-green-100 text-green-700 px-3 py-1.5 rounded-lg text-xs font-bold">✓ DIIZINKAN AKSES</span>
            <span v-else class="bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-bold">✕ DILARANG (Upgrade Tier)</span>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 overflow-hidden">
      <h2 class="text-lg font-black text-slate-800 mb-1">Akses Lisensi Website & Software</h2>
      <p class="text-xs font-bold text-slate-500 mb-6">Gunakan data akun login di bawah ini untuk mengakses sistem yang telah Anda beli.</p>

      <div v-if="loading" class="text-slate-400 text-sm font-bold">Memeriksa lisensi...</div>
      <div v-else-if="softwareOrders.length === 0" class="text-center p-8 border border-dashed border-slate-300 rounded-xl text-slate-400 font-bold text-sm">Belum ada software aktif yang dibeli.</div>
      
      <div v-else class="overflow-x-auto border border-slate-100 rounded-xl">
        <table class="w-full text-left border-collapse">
          <thead class="bg-slate-50 text-xs font-black text-slate-500 uppercase border-b border-slate-200">
            <tr>
              <th class="p-4">Nama Aplikasi</th>
              <th class="p-4">Username / Email</th>
              <th class="p-4">Password</th>
              <th class="p-4 text-right">Tautan</th>
            </tr>
          </thead>
          <tbody class="text-sm font-bold text-slate-700">
            <tr v-for="order in softwareOrders" :key="order.id" class="border-b border-slate-50 hover:bg-slate-50/50">
              <td class="p-4 text-slate-800 font-black">{{ order.product?.name }}</td>
              <td class="p-4 bg-slate-50/40 select-all font-mono text-xs text-indigo-600">{{ order.software_username }}</td>
              <td class="p-4 font-mono text-xs select-all text-slate-600">{{ order.software_password }}</td>
              <td class="p-4 text-right">
                <a :href="order.software_link" target="_blank" class="inline-block bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-black hover:bg-blue-700 transition">
                  Kunjungi Website ➔
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="isUpgradeModalOpen" class="fixed inset-0 bg-slate-900/70 z-50 flex justify-center items-center p-4 backdrop-blur-sm">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
          <h2 class="font-black text-xl text-slate-800">Pilih Paket Premium</h2>
          <button @click="isUpgradeModalOpen = false" class="text-slate-400 hover:text-red-500 text-2xl font-black">&times;</button>
        </div>
        
        <div class="p-6 overflow-y-auto flex-1 bg-slate-50/50">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div v-for="tier in availableTiers" :key="tier.id" @click="selectedTierId = tier.id" :class="selectedTierId === tier.id ? 'border-blue-500 ring-4 ring-blue-500/20' : 'border-slate-200 hover:border-blue-300'" class="bg-white border-2 rounded-2xl p-5 cursor-pointer transition transform hover:-translate-y-1 relative">
              <div v-if="selectedTierId === tier.id" class="absolute top-3 right-3 text-blue-500 text-xl font-black">✓</div>
              <h3 class="font-black text-lg text-slate-800 uppercase">{{ tier.name }}</h3>
              <p class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded w-max mt-1 mb-4">{{ tier.duration_in_months > 0 ? tier.duration_in_months + ' Bulan' : 'Selamanya (Lifetime)' }}</p>
              
              <div class="text-2xl font-black text-indigo-600 mb-4">Rp {{ Number(tier.price).toLocaleString('id-ID') }}</div>
              
              <ul class="text-xs text-slate-600 space-y-2 font-bold">
                <li class="flex items-center gap-2"><span>📥</span> Limit: {{ tier.digital_limit >= 999999 ? 'Unlimited Download' : tier.digital_limit + 'x Download' }}</li>
                <li class="flex items-center gap-2">
                  <span>{{ tier.software_access ? '💻' : '🚫' }}</span> 
                  <span :class="tier.software_access ? 'text-emerald-600' : 'text-red-500'">{{ tier.software_access ? 'Full Akses Software' : 'Tanpa Akses Software' }}</span>
                </li>
              </ul>
              <p class="text-[11px] text-slate-500 mt-4 leading-relaxed line-clamp-3">{{ tier.description }}</p>
            </div>
          </div>

          <div v-if="selectedTierId" class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm animate-fade-in">
            <h4 class="font-black text-slate-800 mb-4">Selesaikan Pembayaran</h4>
            <form @submit.prevent="submitUpgrade" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-xs font-bold block mb-1 text-slate-700">Transfer Ke Rekening</label>
                  <select v-model="paymentForm.method" required class="w-full border border-slate-200 bg-slate-50 rounded-lg p-2.5 text-sm font-bold outline-none">
                    <option value="BCA">BCA - 123456789 a.n PT Kerjapro</option>
                    <option value="MANDIRI">MANDIRI - 987654321 a.n PT Kerjapro</option>
                  </select>
                </div>
                <div>
                  <label class="text-xs font-bold block mb-1 text-slate-700">Upload Bukti Transfer</label>
                  <input type="file" required accept="image/*" @change="(e: any) => paymentForm.proof = e.target.files[0]" class="w-full text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:font-bold file:bg-blue-100 file:text-blue-700 border border-slate-200 rounded-lg p-1 bg-white">
                </div>
              </div>
              <button type="submit" :disabled="submitting" class="w-full bg-blue-600 text-white py-3 rounded-xl font-black hover:bg-blue-700 transition disabled:opacity-50 mt-2">
                {{ submitting ? 'Memproses...' : 'Kirim Bukti Pembayaran' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped> .animate-fade-in { animation: fadeIn 0.3s ease-in-out; } @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } } </style>