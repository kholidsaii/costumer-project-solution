<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const BASE_URL = 'http://localhost:8000';
const billings = ref<any[]>([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token');
    const response = await axios.get(`${BASE_URL}/api/admin/billings`, { headers: { Authorization: `Bearer ${token}` } });
    billings.value = response.data;
  } catch (error) { console.error(error); } 
  finally { loading.value = false; }
});

const formatPrice = (price: any) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(Number(price));
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 bg-slate-50">
      <h2 class="text-lg font-black text-slate-800">Daftar Billing & Tagihan</h2>
      <p class="text-xs text-slate-500 font-bold mt-1">Pantau seluruh invoice perpanjangan customer.</p>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-black">
            <th class="p-4">No. Tagihan</th>
            <th class="p-4">Customer</th>
            <th class="p-4">Keterangan</th>
            <th class="p-4">Nominal</th>
            <th class="p-4">Jatuh Tempo</th>
            <th class="p-4">Status</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <tr v-if="loading"><td colspan="6" class="p-8 text-center text-slate-400 font-bold">Memuat...</td></tr>
          <tr v-else-if="billings.length === 0"><td colspan="6" class="p-8 text-center text-slate-400 font-bold">Kosong</td></tr>
          <tr v-else v-for="bill in billings" :key="bill.id" class="border-b border-slate-100 hover:bg-slate-50">
            <td class="p-4 font-bold text-slate-600">{{ bill.invoice_number }}</td>
            <td class="p-4 font-black text-slate-800">{{ bill.user?.name }}</td>
            <td class="p-4 text-slate-600 text-xs">{{ bill.description }}</td>
            <td class="p-4 font-black text-indigo-600">{{ formatPrice(bill.amount) }}</td>
            <td class="p-4 font-bold text-red-500">{{ bill.due_date }}</td>
            <td class="p-4">
              <span :class="['px-2 py-1 rounded text-[10px] font-black uppercase', bill.status === 'unpaid' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700']">
                {{ bill.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>