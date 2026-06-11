<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../../api/axios';

const tiers = ref<any[]>([]);
const form = ref({ id: null, name: '', description: '', price: 0, digital_limit: 0, software_access: false });
const isModalOpen = ref(false);

const fetchTiers = async () => {
  const { data } = await api.get('/admin/tiers');
  tiers.value = data;
};
onMounted(fetchTiers);

const openModal = (tier: any = null) => {
  if (tier) form.value = { ...tier, software_access: tier.software_access === 1 || tier.software_access === true };
  else form.value = { id: null, name: '', description: '', price: 0, digital_limit: 20, software_access: false };
  isModalOpen.value = true;
};

const saveTier = async () => {
  try {
    if (form.value.id) await api.put(`/admin/tiers/${form.value.id}`, form.value);
    else await api.post('/admin/tiers', form.value);
    isModalOpen.value = false;
    fetchTiers();
    alert('Tier berhasil disimpan!');
  } catch (error) { alert('Gagal menyimpan tier'); }
};
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <div>
        <h2 class="font-black text-slate-800">Setup Member Tier</h2>
        <p class="text-xs text-slate-500 font-bold">Custom limit digital & akses software per level.</p>
      </div>
      <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-700">+ Tambah Tier</button>
    </div>
    
    <table class="w-full text-left">
      <thead class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 font-black uppercase">
        <tr>
          <th class="p-4">Nama Tier</th>
          <th class="p-4">Harga</th>
          <th class="p-4">Limit Digital</th>
          <th class="p-4">Akses Software</th>
          <th class="p-4">Aksi</th>
        </tr>
      </thead>
      <tbody class="text-sm text-slate-600">
        <tr v-for="t in tiers" :key="t.id" class="border-b border-slate-100 hover:bg-slate-50">
          <td class="p-4 font-black text-slate-800">{{ t.name }}</td>
          <td class="p-4">Rp {{ Number(t.price).toLocaleString('id-ID') }}</td>
          <td class="p-4 font-bold">{{ t.digital_limit >= 999999 ? 'Unlimited' : t.digital_limit + 'x Download' }}</td>
          <td class="p-4">
            <span :class="t.software_access ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100'" class="px-2 py-1 rounded font-bold text-[10px] uppercase">
              {{ t.software_access ? 'Diberikan' : 'Dilarang' }}
            </span>
          </td>
          <td class="p-4 space-x-2">
            <button @click="openModal(t)" class="text-blue-500 bg-blue-50 px-3 py-1.5 rounded text-xs font-bold border border-blue-200 hover:bg-blue-100">Edit Akses</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4">
      <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-2xl">
        <h2 class="font-black text-lg mb-4">Edit / Tambah Tier</h2>
        <form @submit.prevent="saveTier" class="space-y-4 text-sm">
          <div><label class="block font-bold mb-1">Nama Member</label><input v-model="form.name" required class="w-full border p-2 rounded bg-slate-50" /></div>
          <div><label class="block font-bold mb-1">Harga Minimum</label><input v-model="form.price" type="number" required class="w-full border p-2 rounded bg-slate-50" /></div>
          <div>
            <label class="block font-bold mb-1">Limit Download Digital</label>
            <p class="text-[10px] text-slate-400 mb-1">Isi 999999 untuk Unlimited</p>
            <input v-model="form.digital_limit" type="number" required class="w-full border p-2 rounded bg-slate-50" />
          </div>
          <div class="p-3 border rounded bg-slate-50">
            <label class="flex items-center gap-2 font-bold cursor-pointer">
              <input type="checkbox" v-model="form.software_access" class="w-4 h-4 text-blue-600"> 
              Izinkan Akses Produk Software
            </label>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="isModalOpen=false" class="w-full bg-slate-200 py-2 rounded font-bold hover:bg-slate-300">Batal</button>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700">Simpan Akses</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>