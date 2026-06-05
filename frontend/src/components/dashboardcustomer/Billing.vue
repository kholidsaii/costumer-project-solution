<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const BASE_URL = 'http://localhost:8000';
const billings = ref<any[]>([]);
const loading = ref(true);

const fetchBillings = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('access_token');
    const response = await axios.get(`${BASE_URL}/api/customer/billings`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    billings.value = response.data;
  } catch (error) {
    console.error('Gagal mengambil data tagihan', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchBillings);

const formatPrice = (price: any) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(Number(price));
};

// Fungsi format tanggal agar enak dibaca (contoh: 5 Juni 2027)
const formatDate = (dateString: string) => {
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <div>
        <h2 class="text-lg font-black text-slate-800">Tagihan & Perpanjangan</h2>
        <p class="text-xs text-slate-500 font-bold mt-1">Pantau tagihan perpanjangan layanan aplikasi Anda di sini.</p>
      </div>
    </div>

    <div class="p-6">
      <div v-if="loading" class="text-center py-10 text-sm font-bold text-slate-400">Memuat tagihan...</div>
      <div v-else-if="billings.length === 0" class="text-center py-10 text-sm font-bold text-slate-400">Tidak ada tagihan tertunda untuk saat ini.</div>
      
      <div v-else class="space-y-4">
        <div v-for="bill in billings" :key="bill.id" class="border border-slate-100 rounded-xl p-5 flex flex-col md:flex-row justify-between md:items-center gap-4 hover:shadow-md transition-shadow bg-white relative overflow-hidden">
          
          <!-- Indikator warna di tepi kiri -->
          <div :class="['absolute left-0 top-0 bottom-0 w-1.5', bill.status === 'unpaid' ? 'bg-amber-400' : 'bg-emerald-400']"></div>

          <div class="flex items-center gap-4 pl-2">
            <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center font-black text-xl border border-slate-200">
              🧾
            </div>
            <div>
              <p class="text-xs text-slate-400 font-bold mb-0.5">{{ bill.invoice_number }}</p>
              <h3 class="text-sm font-black text-slate-800">{{ bill.description }}</h3>
              <p class="text-xs text-red-500 mt-1 font-bold">Jatuh Tempo: {{ formatDate(bill.due_date) }}</p>
            </div>
          </div>
          
          <div class="flex items-center justify-between md:flex-col md:items-end gap-2">
            <span class="text-sm font-black text-slate-800">{{ formatPrice(bill.amount) }}</span>
            <span :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider', bill.status === 'unpaid' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600']">
              {{ bill.status === 'unpaid' ? 'Belum Dibayar' : 'Lunas' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>