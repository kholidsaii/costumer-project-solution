<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const userName = ref(localStorage.getItem('user_name') || 'Pelanggan');
const BASE_URL = 'http://localhost:8000';

const cards = ref([
  { title: 'Project Dalam Pengerjaan', value: '1 Sistem', desc: 'Sistem KerjaPro', icon: '💻', color: 'border-blue-500 text-blue-600 bg-blue-50/50' },
  { title: 'Invoice Belum Terbayar', value: 'Rp 0', desc: 'Cek menu Tagihan', icon: '💳', color: 'border-amber-500 text-amber-600 bg-amber-50/50' },
  { title: 'Tiket Support Aktif', value: '0 Tiket', desc: 'Semua kendala terselesaikan', icon: '🎧', color: 'border-emerald-500 text-emerald-600 bg-emerald-50/50' }
]);

onMounted(async () => {
  // --- FITUR AUTO-ORDER JIKA BERASAL DARI HALAMAN LUAR ---
  const intendedProductId = localStorage.getItem('intended_order_product_id');
  const token = localStorage.getItem('access_token');
  
  if (intendedProductId && token) {
    try {
      await axios.post(`${BASE_URL}/api/customer/orders`, { product_id: intendedProductId }, {
        headers: { Authorization: `Bearer ${token}` }
      });
      localStorage.removeItem('intended_order_product_id'); // Bersihkan
      alert('Pesanan tertunda Anda berhasil diproses!');
      router.push('/dashboard/customer/orders'); // Lempar ke halaman pesanan
    } catch (error) {
      console.error('Gagal memproses auto-order', error);
      localStorage.removeItem('intended_order_product_id');
    }
  }
});
</script>

<template>
  <div class="space-y-6">
    <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm">
      <h1 class="text-2xl md:text-3xl font-black text-slate-800">Halo, {{ userName }}! 👋</h1>
      <p class="text-sm text-slate-500 mt-1 font-medium">
        Melalui panel ini Anda dapat memantau progress pengerjaan aplikasi, mencari produk baru, serta mengajukan bantuan teknis.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div 
        v-for="card in cards" 
        :key="card.title"
        :class="['bg-white p-6 rounded-2xl border border-l-[6px] shadow-sm flex justify-between items-start', card.color]"
      >
        <div class="space-y-1">
          <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ card.title }}</p>
          <p class="text-xl font-black text-slate-800">{{ card.value }}</p>
          <p class="text-xs text-slate-500 font-medium">{{ card.desc }}</p>
        </div>
        <span class="text-2xl">{{ card.icon }}</span>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
      <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
        <span>📢</span> Pengumuman Sistem
      </h3>
      <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-600 leading-relaxed">
        Selamat datang di Portal Pelanggan KerjaPro! Sekarang Anda bisa langsung memberikan review pada produk yang sudah Anda gunakan melalui menu <strong>Product Portal</strong> di sidebar.
      </div>
    </div>
  </div>
</template>