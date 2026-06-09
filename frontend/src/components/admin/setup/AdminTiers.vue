<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const BASE_URL = 'http://localhost:8000';
const getToken = () => localStorage.getItem('access_token');
const axiosConfig = () => ({
  headers: { Authorization: `Bearer ${getToken()}`, Accept: 'application/json' }
});

const tiers = ref<any[]>([]);
const loading = ref(true);
const editingTier = ref<any>(null);
const baseCost = ref(0);

const fetchTiers = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`${BASE_URL}/api/tiers`, axiosConfig());
    tiers.value = response.data;
  } catch (error) {
    console.error('Gagal mengambil data tier member:', error);
  } finally {
    loading.value = false;
  }
};

const openEditModal = (tier: any) => {
  editingTier.value = { ...tier };
  baseCost.value = tier.price || 0;
};

const updateTierCost = async () => {
  try {
    await axios.put(`${BASE_URL}/api/admin/tiers/${editingTier.value.id}`, {
      price: baseCost.value
    }, axiosConfig());
    alert('Biaya dasar tier member berhasil diperbarui!');
    editingTier.value = null;
    fetchTiers();
  } catch (error) {
    alert('Gagal memperbarui harga master member');
  }
};

onMounted(fetchTiers);
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h3 class="text-base font-black text-slate-800">Master Setup Member Tier</h3>
        <p class="text-xs text-slate-400">Atur batasan harga pendaftaran & dasar lisensi member</p>
      </div>
    </div>

    <div v-if="loading" class="text-center py-10 text-xs font-bold text-slate-400">Memuat data master...</div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider">
            <th class="p-4">ID</th>
            <th class="p-4">Nama Tier Keanggotaan</th>
            <th class="p-4">Biaya Pendaftaran / Level</th>
            <th class="p-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
          <tr v-for="tier in tiers" :key="tier.id" class="hover:bg-slate-50/50 transition">
            <td class="p-4">#{{ tier.id }}</td>
            <td class="p-4">
              <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider"
                :class="tier.name.toLowerCase() === 'gold' ? 'bg-amber-100 text-amber-700' : (tier.name.toLowerCase() === 'silver' ? 'bg-slate-200 text-slate-700' : 'bg-blue-100 text-blue-700')">
                {{ tier.name }}
              </span>
            </td>
            <td class="p-4 font-black">Rp {{ (tier.price || 0).toLocaleString('id-ID') }}</td>
            <td class="p-4 text-center">
              <button @click="openEditModal(tier)" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-3 py-1.5 rounded-lg transition">
                Ubah Harga
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="editingTier" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-150">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
          <h3 class="font-black text-sm text-slate-800">Ubah Biaya Master Tier: {{ editingTier.name }}</h3>
          <button @click="editingTier = null" class="text-slate-400 hover:text-slate-600 font-black text-lg">&times;</button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Keanggotaan (IDR)</label>
            <input type="number" v-model="baseCost" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
          <button @click="editingTier = null" class="px-4 py-2 text-xs font-bold text-slate-500 bg-white border border-slate-200 rounded-lg">Batal</button>
          <button @click="updateTierCost" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
        </div>
      </div>
    </div>
  </div>
</template>