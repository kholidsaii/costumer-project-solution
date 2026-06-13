<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const BASE_URL = 'http://localhost:8000';
const customers = ref<any[]>([]);
const loading = ref(true);

const totalFree = computed(() => customers.value.filter(c => !c.tier_id || c.tier?.price == 0).length);
const totalPremium = computed(() => customers.value.filter(c => c.tier_id && c.tier?.price > 0).length);

onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token');
    const response = await axios.get(`${BASE_URL}/api/admin/customers`, { headers: { Authorization: `Bearer ${token}` } });
    customers.value = response.data;
  } catch (error) { console.error(error); } 
  finally { loading.value = false; }
});
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-2 gap-4">
      <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl shadow-sm">
        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Free Members</p>
        <p class="text-3xl font-black text-blue-800">{{ totalFree }} <span class="text-sm font-bold text-blue-500">User</span></p>
      </div>
      <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl shadow-sm">
        <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Premium Members</p>
        <p class="text-3xl font-black text-amber-800">{{ totalPremium }} <span class="text-sm font-bold text-amber-500">User</span></p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-slate-100 bg-slate-50">
        <h2 class="text-lg font-black text-slate-800">Daftar Customer</h2>
        <p class="text-xs text-slate-500 font-bold mt-1">Data seluruh pengguna yang terdaftar sebagai customer.</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-black">
              <th class="p-4">Nama Customer</th>
              <th class="p-4">Email</th>
              <th class="p-4">No. HP</th>
              <th class="p-4">Bergabung Sejak</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-if="loading"><td colspan="4" class="p-8 text-center text-slate-400 font-bold">Memuat...</td></tr>
            <tr v-else-if="customers.length === 0"><td colspan="4" class="p-8 text-center text-slate-400 font-bold">Kosong</td></tr>
            <tr v-else v-for="user in customers" :key="user.id" class="border-b border-slate-100 hover:bg-slate-50">
              <td class="p-4 font-black text-slate-800">{{ user.name }}</td>
              <td class="p-4 font-bold text-slate-600">{{ user.email }}</td>
              <td class="p-4 text-slate-600">{{ user.phone || '-' }}</td>
              <td class="p-4 font-bold text-slate-400">{{ user.created_at?.substring(0, 10) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>